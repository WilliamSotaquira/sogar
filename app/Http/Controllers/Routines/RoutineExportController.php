<?php

namespace App\Http\Controllers\Routines;

use App\Http\Controllers\Controller;
use App\Models\Routine;
use App\Models\RoutineItem;
use App\Models\RoutineItemLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RoutineExportController extends Controller
{
    public function tsv(Request $request, int $routineId)
    {
        return $this->export($request, $routineId, 'tsv');
    }

    public function csv(Request $request, int $routineId)
    {
        return $this->export($request, $routineId, 'csv');
    }

    private function export(Request $request, int $routineId, string $format)
    {
        $user = $request->user();
        abort_unless($user, 401);

        $routine = Routine::query()->visibleTo($user)->findOrFail($routineId);

        // Si la rutina no es del usuario y no es system admin, solo permitimos exportar si es compartida del núcleo activo.
        // visibleTo() ya limita visibilidad, pero reforzamos ownership en caso de rutinas personales ajenas.
        if ($routine->user_id !== $user->id && !$user->isSystemAdmin()) {
            abort_unless((int) $routine->family_group_id === (int) $user->active_family_group_id, 403);
        }

        $dateParam = trim((string) $request->query('date', ''));
        $occurredOn = null;
        if ($dateParam !== '') {
            try {
                $occurredOn = Carbon::parse($dateParam)->toDateString();
            } catch (\Throwable $e) {
                $occurredOn = null;
            }
        }

        $items = RoutineItem::query()
            ->where('routine_id', $routine->id)
            ->orderBy('sort_order')
            ->orderBy('start_time')
            ->orderBy('id')
            ->get();

        $statusByItemId = [];
        if ($occurredOn && $items->isNotEmpty()) {
            $logs = RoutineItemLog::query()
                ->whereIn('routine_item_id', $items->modelKeys())
                ->where('user_id', $user->id)
                ->whereDate('occurred_on', $occurredOn)
                ->get(['routine_item_id', 'status']);

            foreach ($logs as $log) {
                $statusByItemId[(int) $log->routine_item_id] = (string) $log->status;
            }
        }

        $baseName = $routine->name ? \Illuminate\Support\Str::slug($routine->name) : 'routine';
        $fileName = "routine-{$routine->id}-{$baseName}." . ($format === 'csv' ? 'csv' : 'tsv');

        $delimiter = $format === 'csv' ? ';' : "\t";
        $contentType = $format === 'csv'
            ? 'text/csv; charset=UTF-8'
            : 'text/tab-separated-values; charset=UTF-8';

        $callback = function () use ($items, $statusByItemId, $delimiter) {
            $out = fopen('php://output', 'w');
            // UTF-8 BOM para que Excel/Sheets detecten correctamente.
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, ['Desde', 'Hasta', 'Tarea', 'Categoría', 'Estado'], $delimiter);

            foreach ($items as $item) {
                $start = substr((string) $item->start_time, 0, 5);
                $end = substr((string) $item->end_time, 0, 5);
                $status = $statusByItemId[(int) $item->id] ?? '';

                // Nota: El importador toma la columna "Categoría" como "group".
                fputcsv($out, [$start, $end, $item->title, $item->group, $status], $delimiter);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => $contentType,
        ]);
    }
}
