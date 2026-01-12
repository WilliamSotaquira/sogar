<?php

namespace App\Livewire\Habits;

use App\Models\Activity;
use App\Models\ActivityLog;
use App\Models\Budget;
use App\Models\ShoppingList;
use App\Models\Wallet;
use App\Services\ActivityMetricsService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url(as: 'view')]
    public string $view = 'cards';

    public bool $showForm = false;
    public ?int $editingId = null;

    public string $title = '';
    public ?string $description = null;
    public string $kind = 'habit';
    public string $cadence = 'daily';
    public int $target_count = 1;
    public ?string $unit = null;
    public ?string $due_on = null;
    public bool $is_active = true;
    public bool $is_shared = true;

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'kind' => ['required', Rule::in(['habit', 'task'])],
            'cadence' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'once'])],
            'target_count' => ['required', 'integer', 'min:1', 'max:1000'],
            'unit' => ['nullable', 'string', 'max:32'],
            'due_on' => ['nullable', 'date'],
            'is_active' => ['boolean'],
            'is_shared' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        $this->is_active = true;
        $this->is_shared = (bool) auth()->user()?->active_family_group_id;

        if (!in_array($this->view, ['cards', 'list'], true)) {
            $this->view = 'cards';
        }
    }

    public function setView(string $view): void
    {
        $this->view = in_array($view, ['cards', 'list'], true) ? $view : 'cards';
    }

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();

        $this->editingId = $id;
        $this->showForm = true;

        if (!$id) {
            $this->resetFormFields();
            return;
        }

        $user = auth()->user();
        abort_unless($user, 401);

        $activity = Activity::query()
            ->visibleTo($user)
            ->findOrFail($id);

        $this->title = (string) $activity->title;
        $this->description = $activity->description;
        $this->kind = (string) $activity->kind;
        $this->cadence = (string) $activity->cadence;
        $this->target_count = (int) $activity->target_count;
        $this->unit = $activity->unit;
        $this->due_on = $activity->due_on?->toDateString();
        $this->is_active = (bool) $activity->is_active;
        $this->is_shared = (bool) $activity->family_group_id;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->resetValidation();
    }

    public function save(): void
    {
        $data = $this->validate();

        $user = auth()->user();
        abort_unless($user, 401);

        $familyGroupId = null;
        if ($data['is_shared'] && $user->active_family_group_id) {
            $familyGroupId = (int) $user->active_family_group_id;
        }

        if ($this->editingId) {
            $activity = Activity::query()
                ->visibleTo($user)
                ->findOrFail($this->editingId);

            // Si la actividad pertenece a otro user pero al mismo family group, solo admin puede editar.
            // Por ahora: restringimos edición a creador o admin del sistema.
            if ($activity->user_id !== $user->id && !$user->isSystemAdmin()) {
                abort(403);
            }

            $activity->update([
                'title' => $data['title'],
                'description' => $data['description'],
                'kind' => $data['kind'],
                'cadence' => $data['cadence'],
                'target_count' => $data['target_count'],
                'unit' => $data['unit'],
                'due_on' => $data['due_on'],
                'is_active' => (bool) $data['is_active'],
                'family_group_id' => $familyGroupId,
            ]);
        } else {
            Activity::create([
                'user_id' => $user->id,
                'family_group_id' => $familyGroupId,
                'title' => $data['title'],
                'description' => $data['description'],
                'kind' => $data['kind'],
                'cadence' => $data['cadence'],
                'target_count' => $data['target_count'],
                'unit' => $data['unit'],
                'due_on' => $data['due_on'],
                'is_active' => (bool) $data['is_active'],
            ]);
        }

        $this->closeForm();
    }

    public function toggleActive(int $id): void
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $activity = Activity::query()
            ->visibleTo($user)
            ->findOrFail($id);

        if ($activity->user_id !== $user->id && !$user->isSystemAdmin()) {
            abort(403);
        }

        $activity->update(['is_active' => !$activity->is_active]);
    }

    public function checkIn(int $id): void
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $activity = Activity::query()
            ->visibleTo($user)
            ->where('is_active', true)
            ->findOrFail($id);

        $today = Carbon::today();

        $log = ActivityLog::query()
            ->where('activity_id', $activity->id)
            ->where('user_id', $user->id)
            ->whereDate('occurred_on', $today)
            ->first();

        if ($log) {
            $log->update([
                'qty' => ((float) $log->qty) + 1,
                'occurred_at' => now(),
            ]);
        } else {
            ActivityLog::create([
                'activity_id' => $activity->id,
                'user_id' => $user->id,
                'occurred_on' => $today,
                'occurred_at' => now(),
                'qty' => 1,
            ]);
        }

        if ((string) $activity->kind === 'task' && (string) $activity->cadence === 'once') {
            $activity->update(['is_active' => false]);
        }
    }

    private function resetFormFields(): void
    {
        $this->title = '';
        $this->description = null;
        $this->kind = 'habit';
        $this->cadence = 'daily';
        $this->target_count = 1;
        $this->unit = null;
        $this->due_on = null;
        $this->is_active = true;
        $this->is_shared = (bool) auth()->user()?->active_family_group_id;
    }

    public function render()
    {
        $user = auth()->user();
        abort_unless($user, 401);

        $activeFamilyGroupId = $user->active_family_group_id;

        $activities = Activity::query()
            ->visibleTo($user)
            ->when($activeFamilyGroupId, function ($q) use ($user, $activeFamilyGroupId) {
                $q->where(function ($qq) use ($user, $activeFamilyGroupId) {
                    $qq->where(function ($qPersonal) use ($user) {
                        $qPersonal->whereNull('family_group_id')->where('user_id', $user->id);
                    })->orWhere('family_group_id', $activeFamilyGroupId);
                });
            })
            ->with([
                'subject' => function (MorphTo $morphTo) {
                    $morphTo->morphWith([
                        Budget::class => ['category'],
                        ShoppingList::class => [],
                        Wallet::class => [],
                    ]);
                },
                'logs' => function ($q) {
                    $q->whereDate('occurred_on', '>=', now()->subDays(60)->toDateString());
                },
            ])
            ->orderByDesc('is_active')
            ->orderBy('kind')
            ->orderBy('title')
            ->get();

        $metrics = [];
        $svc = app(ActivityMetricsService::class);
        $asOf = Carbon::today();

        foreach ($activities as $activity) {
            $metrics[$activity->id] = $svc->summary($activity, $user->id, $asOf);
        }

        return view('livewire.habits.index', [
            'activities' => $activities,
            'metrics' => $metrics,
        ]);
    }
}
