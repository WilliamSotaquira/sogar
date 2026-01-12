<?php

namespace App\Livewire\Routines;

use App\Models\Routine;
use App\Models\RoutineItem;
use App\Models\RoutineItemLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    #[Url(as: 'date')]
    public string $date = '';

    #[Url(as: 'view')]
    public string $view = 'day';

    public bool $showRoutineForm = false;
    public ?int $editingRoutineId = null;

    public string $routine_name = '';
    public ?string $routine_description = null;
    public bool $routine_is_active = true;
    public bool $routine_is_shared = true;

    public bool $showItemForm = false;
    public ?int $editingItemId = null;

    public ?int $item_routine_id = null;
    public string $item_title = '';
    public string $item_group = 'Personal';
    public ?string $item_category = null;
    public string $item_start_time = '08:00';
    public string $item_end_time = '09:00';
    /** @var array<int> */
    public array $item_weekdays = [1, 2, 3, 4, 5];
    public int $item_sort_order = 0;
    public bool $item_is_active = true;

    public bool $showImportForm = false;
    public string $import_mode = 'new'; // new|existing
    public ?int $import_routine_id = null;
    public string $import_routine_name = '';
    public string $import_days = 'weekday'; // weekday|saturday|sunday|all
    public bool $import_replace_existing = false;
    public bool $import_apply_status = false;
    public string $import_status_date = '';
    public $import_file = null;

    public function mount(): void
    {
        if (!$this->date) {
            $this->date = Carbon::today()->toDateString();
        }

        // Normalizar formato
        try {
            $this->date = Carbon::parse($this->date)->toDateString();
        } catch (\Throwable $e) {
            $this->date = Carbon::today()->toDateString();
        }

        $this->view = $this->normalizeView($this->view);

        $this->routine_is_shared = (bool) auth()->user()?->active_family_group_id;
    }

    public function openImportForm(): void
    {
        $this->resetValidation();
        $this->showImportForm = true;
        $this->import_mode = 'new';
        $this->import_routine_id = null;
        $this->import_routine_name = '';
        $this->import_days = 'weekday';
        $this->import_replace_existing = false;
        $this->import_apply_status = false;
        $this->import_status_date = Carbon::today()->toDateString();
        $this->import_file = null;
    }

    public function closeImportForm(): void
    {
        $this->showImportForm = false;
        $this->import_file = null;
        $this->resetValidation();
    }

    protected function importRules(): array
    {
        return [
            'import_mode' => ['required', Rule::in(['new', 'existing'])],
            'import_routine_id' => ['nullable', 'integer', 'required_if:import_mode,existing'],
            'import_routine_name' => ['nullable', 'string', 'max:255', 'required_if:import_mode,new'],
            'import_days' => ['required', Rule::in(['weekday', 'saturday', 'sunday', 'all'])],
            'import_replace_existing' => ['boolean'],
            'import_apply_status' => ['boolean'],
            'import_status_date' => ['nullable', 'date', 'required_if:import_apply_status,1'],
            // Importación únicamente por archivo CSV.
            'import_file' => ['required', 'file', 'max:5120', 'mimes:csv'],
        ];
    }

    public function importBlocks(): void
    {
        $data = $this->validate($this->importRules());

        $user = auth()->user();
        abort_unless($user, 401);

        $weekdaysMask = match ($data['import_days']) {
            'weekday' => 31,
            'saturday' => 32,
            'sunday' => 64,
            'all' => 127,
            default => 31,
        };

        try {
            $importText = (string) $data['import_file']->get();
        } catch (\Throwable $e) {
            $importText = '';
        }

        $rows = $this->parseImportText($importText);
        if (empty($rows)) {
            $this->addError('import_file', 'No se detectaron filas válidas. Asegúrate de incluir columnas: Desde, Hasta, Tarea, Categoría.');
            return;
        }

        $occurredOn = null;
        if (!empty($data['import_apply_status'])) {
            try {
                $occurredOn = Carbon::parse((string) $data['import_status_date'])->toDateString();
            } catch (\Throwable $e) {
                $occurredOn = null;
            }
        }

        DB::transaction(function () use ($data, $user, $rows, $weekdaysMask, $occurredOn): void {
            if ($data['import_mode'] === 'existing') {
                $routine = Routine::query()->visibleTo($user)->findOrFail((int) $data['import_routine_id']);
                if ($routine->user_id !== $user->id && !$user->isSystemAdmin()) {
                    abort(403);
                }

                if (!empty($data['import_replace_existing'])) {
                    RoutineItem::query()
                        ->where('routine_id', $routine->id)
                        ->where('weekdays_mask', $weekdaysMask)
                        ->delete();
                }
            } else {
                $routine = Routine::create([
                    'user_id' => $user->id,
                    'family_group_id' => null,
                    'name' => trim((string) $data['import_routine_name']),
                    'description' => 'Importada desde tabla.',
                    'is_active' => true,
                ]);
            }

            $sort = 0;
            if ($data['import_mode'] === 'existing' && empty($data['import_replace_existing'])) {
                $sort = (int) (RoutineItem::query()->where('routine_id', $routine->id)->max('sort_order') ?? 0);
            }

            $createdItemsWithStatus = [];

            foreach ($rows as $row) {
                $start = $this->normalizeTime($row['start']);
                $end = $this->normalizeTime($row['end']);
                if (!$start || !$end) {
                    continue;
                }

                $title = trim((string) $row['title']);
                if ($title === '') {
                    continue;
                }

                $group = trim((string) $row['group']);
                if ($group === '') {
                    $group = 'Personal';
                }

                $sort++;

                $item = RoutineItem::create([
                    'routine_id' => $routine->id,
                    'title' => $title,
                    'group' => mb_substr($group, 0, 32),
                    'category' => null,
                    'start_time' => $start,
                    'end_time' => $end,
                    'weekdays_mask' => $weekdaysMask,
                    'sort_order' => $sort,
                    'is_active' => true,
                ]);

                $createdItemsWithStatus[] = [
                    'item_id' => (int) $item->id,
                    'raw_status' => (string) ($row['status'] ?? ''),
                ];
            }

            if ($occurredOn) {
                foreach ($createdItemsWithStatus as $pair) {
                    $status = $this->normalizeImportStatus($pair['raw_status']);
                    if (!$status) {
                        continue;
                    }

                    RoutineItemLog::updateOrCreate(
                        [
                            'routine_item_id' => (int) $pair['item_id'],
                            'user_id' => (int) $user->id,
                            'occurred_on' => $occurredOn,
                        ],
                        [
                            'status' => $status,
                            'occurred_at' => now(),
                        ],
                    );
                }
            }
        });

        $this->closeImportForm();
        $this->view = 'routines';
    }

    /**
     * @return array<int, array{start:string,end:string,title:string,group:string,status:string}>
     */
    private function parseImportText(string $text): array
    {
        // Tolerar BOM (por copiado desde plantillas Excel/Sheets)
        if (str_starts_with($text, "\xEF\xBB\xBF")) {
            $text = substr($text, 3);
        }

        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $lines = array_values(array_filter(array_map('trim', explode("\n", $text)), fn ($l) => $l !== ''));
        if (empty($lines)) {
            return [];
        }

        // Detectar delimitador:
        // - TSV (tab) típico de pegado desde Excel/Sheets.
        // - CSV con ',' o ';' (muy común en locales ES).
        $tabCount = substr_count($lines[0], "\t");
        $semicolonCount = substr_count($lines[0], ';');
        $commaCount = substr_count($lines[0], ',');

        if ($tabCount > 0) {
            $delimiter = "\t";
        } elseif ($semicolonCount > $commaCount) {
            $delimiter = ';';
        } else {
            $delimiter = ',';
        }

        $rows = [];
        $startIndex = 0;
        $firstCells = array_map('trim', str_getcsv($lines[0], $delimiter));
        if (!empty($firstCells) && str_starts_with((string) $firstCells[0], "\xEF\xBB\xBF")) {
            $firstCells[0] = substr((string) $firstCells[0], 3);
        }
        $header = array_map(fn ($c) => mb_strtolower($c), $firstCells);
        $looksLikeHeader = in_array('desde', $header, true) || in_array('tarea', $header, true) || in_array('hasta', $header, true);
        if ($looksLikeHeader) {
            $startIndex = 1;
        }

        for ($i = $startIndex; $i < count($lines); $i++) {
            $cells = array_map('trim', str_getcsv($lines[$i], $delimiter));
            if (count($cells) < 3) {
                continue;
            }

            // Formatos esperados:
            // Desde | Hasta | Tarea | Categoria | Estado
            $start = (string) ($cells[0] ?? '');
            $end = (string) ($cells[1] ?? '');
            $title = (string) ($cells[2] ?? '');
            $group = (string) ($cells[3] ?? '');
            $status = (string) ($cells[4] ?? '');

            if (trim($start) === '' || trim($end) === '' || trim($title) === '') {
                continue;
            }

            $rows[] = [
                'start' => $start,
                'end' => $end,
                'title' => $title,
                'group' => $group,
                'status' => $status,
            ];
        }

        return $rows;
    }

    private function normalizeImportStatus(string $status): ?string
    {
        $status = mb_strtolower(trim($status));
        if ($status === '') {
            return null;
        }

        // Normalizaciones comunes desde Excel/Sheets.
        return match ($status) {
            'done', 'hecho', 'realizado', 'ok', 'x', 'si', 'sí', '1' => 'done',
            'skipped', 'skip', 'saltado', 'saltar', '0' => 'skipped',
            default => null,
        };
    }

    private function normalizeTime(string $time): ?string
    {
        $time = trim($time);
        if ($time === '') {
            return null;
        }

        // Acepta H:MM o HH:MM
        if (!preg_match('/^(\d{1,2}):(\d{2})$/', $time, $m)) {
            return null;
        }

        $h = (int) $m[1];
        $min = (int) $m[2];
        if ($h < 0 || $h > 23 || $min < 0 || $min > 59) {
            return null;
        }

        return sprintf('%02d:%02d', $h, $min);
    }

    private function normalizeView(?string $view): string
    {
        $view = strtolower(trim((string) $view));
        return in_array($view, ['day', 'timeline', 'week', 'month', 'routines'], true) ? $view : 'day';
    }

    public function setView(string $view): void
    {
        $this->view = $this->normalizeView($view);
    }

    public function prevPeriod(): void
    {
        $date = Carbon::parse($this->date);
        $this->date = match ($this->view) {
            'week' => $date->subWeek()->toDateString(),
            'month' => $date->subMonth()->toDateString(),
            default => $date->subDay()->toDateString(),
        };
    }

    public function nextPeriod(): void
    {
        $date = Carbon::parse($this->date);
        $this->date = match ($this->view) {
            'week' => $date->addWeek()->toDateString(),
            'month' => $date->addMonth()->toDateString(),
            default => $date->addDay()->toDateString(),
        };
    }

    public function openDay(string $date): void
    {
        try {
            $this->date = Carbon::parse($date)->toDateString();
        } catch (\Throwable $e) {
            return;
        }

        $this->view = 'day';
    }

    public function prevDay(): void
    {
        $this->prevPeriod();
    }

    public function nextDay(): void
    {
        $this->nextPeriod();
    }

    public function goToday(): void
    {
        $this->date = Carbon::today()->toDateString();
    }

    protected function routineRules(): array
    {
        return [
            'routine_name' => ['required', 'string', 'max:255'],
            'routine_description' => ['nullable', 'string', 'max:2000'],
            'routine_is_active' => ['boolean'],
            'routine_is_shared' => ['boolean'],
        ];
    }

    protected function itemRules(): array
    {
        return [
            'item_routine_id' => ['required', 'integer'],
            'item_title' => ['required', 'string', 'max:255'],
            'item_group' => ['required', 'string', 'max:32'],
            'item_category' => ['nullable', 'string', 'max:64'],
            'item_start_time' => ['required', 'date_format:H:i'],
            'item_end_time' => ['required', 'date_format:H:i'],
            'item_weekdays' => ['required', 'array', 'min:1'],
            'item_weekdays.*' => ['integer', Rule::in([1, 2, 3, 4, 5, 6, 7])],
            'item_sort_order' => ['required', 'integer', 'min:0', 'max:10000'],
            'item_is_active' => ['boolean'],
        ];
    }

    public function openRoutineForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingRoutineId = $id;
        $this->showRoutineForm = true;

        if (!$id) {
            $this->routine_name = '';
            $this->routine_description = null;
            $this->routine_is_active = true;
            $this->routine_is_shared = (bool) auth()->user()?->active_family_group_id;
            return;
        }

        $user = auth()->user();
        abort_unless($user, 401);

        $routine = Routine::query()->visibleTo($user)->findOrFail($id);

        if ($routine->user_id !== $user->id && !$user->isSystemAdmin()) {
            abort(403);
        }

        $this->routine_name = (string) $routine->name;
        $this->routine_description = $routine->description;
        $this->routine_is_active = (bool) $routine->is_active;
        $this->routine_is_shared = (bool) $routine->family_group_id;
    }

    public function closeRoutineForm(): void
    {
        $this->showRoutineForm = false;
        $this->editingRoutineId = null;
        $this->resetValidation();
    }

    public function saveRoutine(): void
    {
        $data = $this->validate($this->routineRules());

        $user = auth()->user();
        abort_unless($user, 401);

        $familyGroupId = null;
        if ($data['routine_is_shared'] && $user->active_family_group_id) {
            $familyGroupId = (int) $user->active_family_group_id;
        }

        if ($this->editingRoutineId) {
            $routine = Routine::query()->visibleTo($user)->findOrFail($this->editingRoutineId);

            if ($routine->user_id !== $user->id && !$user->isSystemAdmin()) {
                abort(403);
            }

            $routine->update([
                'name' => $data['routine_name'],
                'description' => $data['routine_description'],
                'is_active' => (bool) $data['routine_is_active'],
                'family_group_id' => $familyGroupId,
            ]);
        } else {
            Routine::create([
                'user_id' => $user->id,
                'family_group_id' => $familyGroupId,
                'name' => $data['routine_name'],
                'description' => $data['routine_description'],
                'is_active' => (bool) $data['routine_is_active'],
            ]);
        }

        $this->closeRoutineForm();
    }

    public function openItemForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->editingItemId = $id;
        $this->showItemForm = true;

        $user = auth()->user();
        abort_unless($user, 401);

        if (!$id) {
            $this->item_routine_id = $this->item_routine_id ?: Routine::query()
                ->visibleTo($user)
                ->where('user_id', $user->id)
                ->orderBy('id')
                ->value('id');

            $this->item_title = '';
            $this->item_group = 'Personal';
            $this->item_category = null;
            $this->item_start_time = '08:00';
            $this->item_end_time = '09:00';
            $this->item_weekdays = [1, 2, 3, 4, 5];
            $this->item_sort_order = 0;
            $this->item_is_active = true;
            return;
        }

        $item = RoutineItem::query()
            ->with('routine')
            ->findOrFail($id);

        $routine = $item->routine;
        abort_unless($routine, 404);

        abort_unless(Routine::query()->visibleTo($user)->whereKey($routine->id)->exists(), 403);

        if ($routine->user_id !== $user->id && !$user->isSystemAdmin()) {
            abort(403);
        }

        $this->item_routine_id = (int) $routine->id;
        $this->item_title = (string) $item->title;
        $this->item_group = (string) $item->group;
        $this->item_category = $item->category;
        $this->item_start_time = substr((string) $item->start_time, 0, 5);
        $this->item_end_time = substr((string) $item->end_time, 0, 5);
        $this->item_weekdays = $this->maskToWeekdays((int) $item->weekdays_mask);
        $this->item_sort_order = (int) $item->sort_order;
        $this->item_is_active = (bool) $item->is_active;
    }

    public function closeItemForm(): void
    {
        $this->showItemForm = false;
        $this->editingItemId = null;
        $this->resetValidation();
    }

    public function saveItem(): void
    {
        $data = $this->validate($this->itemRules());

        $user = auth()->user();
        abort_unless($user, 401);

        $routine = Routine::query()->visibleTo($user)->findOrFail((int) $data['item_routine_id']);
        if ($routine->user_id !== $user->id && !$user->isSystemAdmin()) {
            abort(403);
        }

        $weekdaysMask = $this->weekdaysToMask($data['item_weekdays']);

        if ($this->editingItemId) {
            $item = RoutineItem::query()->findOrFail($this->editingItemId);
            abort_unless((int) $item->routine_id === (int) $routine->id, 403);

            $item->update([
                'title' => $data['item_title'],
                'group' => $data['item_group'],
                'category' => $data['item_category'],
                'start_time' => $data['item_start_time'],
                'end_time' => $data['item_end_time'],
                'weekdays_mask' => $weekdaysMask,
                'sort_order' => (int) $data['item_sort_order'],
                'is_active' => (bool) $data['item_is_active'],
            ]);
        } else {
            RoutineItem::create([
                'routine_id' => $routine->id,
                'title' => $data['item_title'],
                'group' => $data['item_group'],
                'category' => $data['item_category'],
                'start_time' => $data['item_start_time'],
                'end_time' => $data['item_end_time'],
                'weekdays_mask' => $weekdaysMask,
                'sort_order' => (int) $data['item_sort_order'],
                'is_active' => (bool) $data['item_is_active'],
            ]);
        }

        $this->closeItemForm();
    }

    public function toggleDone(int $routineItemId, ?string $forDate = null): void
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $date = Carbon::parse($forDate ?: $this->date)->toDateString();

        $item = RoutineItem::query()->with('routine')->findOrFail($routineItemId);
        abort_unless($item->routine, 404);
        abort_unless(Routine::query()->visibleTo($user)->whereKey($item->routine->id)->exists(), 403);

        $existing = RoutineItemLog::query()
            ->where('routine_item_id', $routineItemId)
            ->where('user_id', $user->id)
            ->whereDate('occurred_on', $date)
            ->first();

        if ($existing && (string) $existing->status === 'done') {
            $existing->delete();
            return;
        }

        RoutineItemLog::updateOrCreate(
            [
                'routine_item_id' => $routineItemId,
                'user_id' => $user->id,
                'occurred_on' => $date,
            ],
            [
                'status' => 'done',
                'occurred_at' => now(),
            ]
        );
    }

    public function markSkipped(int $routineItemId, ?string $forDate = null): void
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $date = Carbon::parse($forDate ?: $this->date)->toDateString();

        $item = RoutineItem::query()->with('routine')->findOrFail($routineItemId);
        abort_unless($item->routine, 404);
        abort_unless(Routine::query()->visibleTo($user)->whereKey($item->routine->id)->exists(), 403);

        RoutineItemLog::updateOrCreate(
            [
                'routine_item_id' => $routineItemId,
                'user_id' => $user->id,
                'occurred_on' => $date,
            ],
            [
                'status' => 'skipped',
                'occurred_at' => now(),
            ]
        );
    }

    public function clearStatus(int $routineItemId, ?string $forDate = null): void
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $date = Carbon::parse($forDate ?: $this->date)->toDateString();

        RoutineItemLog::query()
            ->where('routine_item_id', $routineItemId)
            ->where('user_id', $user->id)
            ->whereDate('occurred_on', $date)
            ->delete();
    }

    private function weekdaysToMask(array $isoWeekdays): int
    {
        $mask = 0;
        foreach ($isoWeekdays as $iso) {
            $mask |= RoutineItem::weekdayToMaskBit((int) $iso);
        }
        return $mask;
    }

    private function maskToWeekdays(int $mask): array
    {
        $days = [];
        for ($iso = 1; $iso <= 7; $iso++) {
            $bit = RoutineItem::weekdayToMaskBit($iso);
            if (($mask & $bit) === $bit) {
                $days[] = $iso;
            }
        }
        return $days;
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $this->view = $this->normalizeView($this->view);

        $activeFamilyGroupId = $user->active_family_group_id;

        $routines = Routine::query()
            ->visibleTo($user)
            ->when($activeFamilyGroupId, function ($q) use ($user, $activeFamilyGroupId) {
                $q->where(function ($qq) use ($user, $activeFamilyGroupId) {
                    $qq->where(function ($qPersonal) use ($user) {
                        $qPersonal->whereNull('family_group_id')->where('user_id', $user->id);
                    })->orWhere('family_group_id', $activeFamilyGroupId);
                });
            }, function ($q) use ($user) {
                $q->whereNull('family_group_id')->where('user_id', $user->id);
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $date = Carbon::parse($this->date);
        $routineIds = $routines->pluck('id')->all();

        $items = collect();
        $groupSummary = [];
        $weekDays = [];
        $weekItems = [];
        $weekDaySummary = [];
        $weekItemStatus = [];
        $monthWeeks = [];
        $monthDaySummary = [];

        if ($this->view === 'week' && !empty($routineIds)) {
            $weekStart = $date->copy()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->addDays(6);
            $weekDays = collect(range(0, 6))
                ->map(fn (int $i) => $weekStart->copy()->addDays($i))
                ->all();

            $allItems = RoutineItem::query()
                ->whereIn('routine_id', $routineIds)
                ->where('is_active', true)
                ->with('routine')
                ->get();

            $itemIds = $allItems->pluck('id')->all();
            $logsByKey = [];
            if (!empty($itemIds)) {
                $logs = RoutineItemLog::query()
                    ->whereIn('routine_item_id', $itemIds)
                    ->where('user_id', $user->id)
                    ->whereDate('occurred_on', '>=', $weekStart->toDateString())
                    ->whereDate('occurred_on', '<=', $weekEnd->toDateString())
                    ->get();

                foreach ($logs as $log) {
                    $key = $log->occurred_on . '|' . $log->routine_item_id;
                    $logsByKey[$key] = $log;
                }
            }

            foreach ($weekDays as $day) {
                $bit = RoutineItem::weekdayToMaskBit($day->isoWeekday());
                $dayItems = $allItems
                    ->filter(fn (RoutineItem $item) => ((int) $item->weekdays_mask & $bit) === $bit)
                    ->sortBy([
                        fn (RoutineItem $item) => (string) $item->start_time,
                        fn (RoutineItem $item) => (int) $item->sort_order,
                        fn (RoutineItem $item) => (int) $item->id,
                    ])
                    ->values();

                $dayKey = $day->toDateString();
                $weekItems[$dayKey] = $dayItems;

                $summary = [
                    'total_items' => 0,
                    'done_items' => 0,
                    'skipped_items' => 0,
                    'total_minutes' => 0,
                    'done_minutes' => 0,
                ];

                foreach ($dayItems as $item) {
                    $summary['total_items']++;
                    $minutes = (int) max(0, (int) ($item->duration_minutes ?? 0));
                    $summary['total_minutes'] += $minutes;

                    $log = $logsByKey[$dayKey . '|' . $item->id] ?? null;
                    $status = $log?->status;
                    if ($status) {
                        $weekItemStatus[$dayKey][$item->id] = (string) $status;
                    }
                    if ($status === 'done') {
                        $summary['done_items']++;
                        $summary['done_minutes'] += $minutes;
                    } elseif ($status === 'skipped') {
                        $summary['skipped_items']++;
                    }
                }

                $weekDaySummary[$dayKey] = $summary;
            }
        } elseif ($this->view === 'month' && !empty($routineIds)) {
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $allItems = RoutineItem::query()
                ->whereIn('routine_id', $routineIds)
                ->where('is_active', true)
                ->with('routine')
                ->get();

            $itemIds = $allItems->pluck('id')->all();
            $logsByKey = [];
            if (!empty($itemIds)) {
                $logs = RoutineItemLog::query()
                    ->whereIn('routine_item_id', $itemIds)
                    ->where('user_id', $user->id)
                    ->whereDate('occurred_on', '>=', $monthStart->toDateString())
                    ->whereDate('occurred_on', '<=', $monthEnd->toDateString())
                    ->get();

                foreach ($logs as $log) {
                    $key = $log->occurred_on . '|' . $log->routine_item_id;
                    $logsByKey[$key] = $log;
                }
            }

            // Resumen por cada día del mes (solo días del mes, no padding del calendario)
            for ($d = $monthStart->copy(); $d->lessThanOrEqualTo($monthEnd); $d->addDay()) {
                $dayKey = $d->toDateString();
                $bit = RoutineItem::weekdayToMaskBit($d->isoWeekday());
                $dayItems = $allItems->filter(fn (RoutineItem $item) => ((int) $item->weekdays_mask & $bit) === $bit);

                $summary = [
                    'total_items' => 0,
                    'done_items' => 0,
                    'skipped_items' => 0,
                    'total_minutes' => 0,
                    'done_minutes' => 0,
                ];

                foreach ($dayItems as $item) {
                    $summary['total_items']++;
                    $minutes = (int) max(0, (int) ($item->duration_minutes ?? 0));
                    $summary['total_minutes'] += $minutes;

                    $log = $logsByKey[$dayKey . '|' . $item->id] ?? null;
                    $status = $log?->status;
                    if ($status === 'done') {
                        $summary['done_items']++;
                        $summary['done_minutes'] += $minutes;
                    } elseif ($status === 'skipped') {
                        $summary['skipped_items']++;
                    }
                }

                $state = 'empty';
                if ($summary['total_items'] > 0) {
                    if ($summary['done_items'] === $summary['total_items']) {
                        $state = 'all_done';
                    } elseif ($summary['done_items'] > 0) {
                        $state = 'partial';
                    } elseif ($summary['skipped_items'] > 0) {
                        $state = 'skipped_only';
                    } else {
                        $state = 'pending';
                    }
                }

                $monthDaySummary[$dayKey] = array_merge($summary, ['state' => $state]);
            }

            // Construir grid de calendario (semanas) con padding de inicio/fin
            $gridStart = $monthStart->copy()->startOfWeek(Carbon::MONDAY);
            $gridEnd = $monthEnd->copy()->endOfWeek(Carbon::SUNDAY);

            $cursor = $gridStart->copy();
            $week = [];
            while ($cursor->lessThanOrEqualTo($gridEnd)) {
                $week[] = $cursor->copy();
                if (count($week) === 7) {
                    $monthWeeks[] = $week;
                    $week = [];
                }
                $cursor->addDay();
            }
        } elseif (!empty($routineIds)) {
            $bit = RoutineItem::weekdayToMaskBit($date->isoWeekday());

            $items = RoutineItem::query()
                ->whereIn('routine_id', $routineIds)
                ->where('is_active', true)
                ->whereRaw('(weekdays_mask & ?) = ?', [$bit, $bit])
                ->with([
                    'routine',
                    'logs' => function ($q) use ($user, $date) {
                        $q->where('user_id', $user->id)->whereDate('occurred_on', $date->toDateString());
                    },
                ])
                ->orderBy('start_time')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            foreach ($items as $item) {
                $group = (string) $item->group;
                $minutes = (int) max(0, (int) ($item->duration_minutes ?? 0));
                $log = $item->logs->first();
                $status = $log?->status;

                $groupSummary[$group] ??= [
                    'total_items' => 0,
                    'done_items' => 0,
                    'skipped_items' => 0,
                    'total_minutes' => 0,
                    'done_minutes' => 0,
                ];

                $groupSummary[$group]['total_items']++;
                $groupSummary[$group]['total_minutes'] += $minutes;

                if ($status === 'done') {
                    $groupSummary[$group]['done_items']++;
                    $groupSummary[$group]['done_minutes'] += $minutes;
                } elseif ($status === 'skipped') {
                    $groupSummary[$group]['skipped_items']++;
                }
            }

            ksort($groupSummary);
        }

        $dateLabel = $this->view === 'week'
            ? 'Semana del ' . \App\Support\Format::dateLong($date->copy()->startOfWeek(Carbon::MONDAY)) . ' – ' . \App\Support\Format::dateLong($date->copy()->startOfWeek(Carbon::MONDAY)->addDays(6))
            : ($this->view === 'month'
                ? \App\Support\Format::monthYear($date)
                : \App\Support\Format::dateLong($date));

        return view('livewire.routines.index', [
            'view' => $this->view,
            'routines' => $routines,
            'items' => $items,
            'groupSummary' => $groupSummary,
            'weekDays' => $weekDays,
            'weekItems' => $weekItems,
            'weekDaySummary' => $weekDaySummary,
            'weekItemStatus' => $weekItemStatus,
            'monthWeeks' => $monthWeeks,
            'monthDaySummary' => $monthDaySummary,
            'currentDate' => $date->toDateString(),
            'currentMonth' => $date->month,
            'dateLabel' => $dateLabel,
        ]);
    }
}
