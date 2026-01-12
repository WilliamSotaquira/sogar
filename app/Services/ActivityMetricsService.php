<?php

namespace App\Services;

use App\Models\Activity;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ActivityMetricsService
{
    /**
     * @return array{start: Carbon, end: Carbon, label: string}
     */
    public function periodRange(Activity $activity, Carbon $asOf): array
    {
        $start = $asOf->copy()->startOfDay();
        $end = $asOf->copy()->endOfDay();
        $label = 'Hoy';

        switch ((string) $activity->cadence) {
            case 'weekly':
                $start = $asOf->copy()->startOfWeek(Carbon::MONDAY)->startOfDay();
                $end = $asOf->copy()->endOfWeek(Carbon::SUNDAY)->endOfDay();
                $label = 'Semana';
                break;
            case 'monthly':
                $start = $asOf->copy()->startOfMonth()->startOfDay();
                $end = $asOf->copy()->endOfMonth()->endOfDay();
                $label = 'Mes';
                break;
            case 'once':
                $label = 'Único';
                $start = ($activity->start_on ? $activity->start_on->copy() : $activity->created_at->copy())->startOfDay();
                $end = ($activity->due_on ? $activity->due_on->copy() : ($activity->end_on ? $activity->end_on->copy() : $asOf->copy()))->endOfDay();
                break;
            case 'daily':
            default:
                break;
        }

        if ($activity->start_on) {
            $start = $start->max($activity->start_on->copy()->startOfDay());
        }

        if ($activity->end_on) {
            $end = $end->min($activity->end_on->copy()->endOfDay());
        }

        return ['start' => $start, 'end' => $end, 'label' => $label];
    }

    /**
     * @return array{done: float, target: float, percent: float, projected: float, range_label: string, streak: int}
     */
    public function summary(Activity $activity, int $userId, Carbon $asOf): array
    {
        $range = $this->periodRange($activity, $asOf);

        $done = $this->sumQty($this->logsInRange($activity, $userId, $range['start'], $range['end']));
        $target = max(1, (int) $activity->target_count);

        $percent = $target > 0 ? min(100, round(($done / $target) * 100, 1)) : 0;

        // Proyección lineal a fin de periodo (si aplica)
        $daysTotal = max(1, $range['start']->copy()->startOfDay()->diffInDays($range['end']->copy()->endOfDay()) + 1);
        $daysElapsed = max(1, $range['start']->copy()->startOfDay()->diffInDays($asOf->copy()->endOfDay()) + 1);
        $projected = round(($done / $daysElapsed) * $daysTotal, 2);

        $streak = $this->streak($activity, $userId, $asOf);

        return [
            'done' => (float) $done,
            'target' => (float) $target,
            'percent' => (float) $percent,
            'projected' => (float) $projected,
            'range_label' => (string) $range['label'],
            'streak' => (int) $streak,
        ];
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    private function logsInRange(Activity $activity, int $userId, Carbon $start, Carbon $end): Collection
    {
        if ($activity->relationLoaded('logs')) {
            return $activity->logs
                ->where('user_id', $userId)
                ->filter(function (ActivityLog $log) use ($start, $end) {
                    $day = $log->occurred_on?->copy()?->startOfDay();
                    if (!$day) {
                        return false;
                    }
                    return $day->betweenIncluded($start->copy()->startOfDay(), $end->copy()->endOfDay());
                })
                ->values();
        }

        return ActivityLog::query()
            ->where('activity_id', $activity->id)
            ->where('user_id', $userId)
            ->whereDate('occurred_on', '>=', $start->toDateString())
            ->whereDate('occurred_on', '<=', $end->toDateString())
            ->get();
    }

    private function sumQty(Collection $logs): float
    {
        return (float) $logs->sum(function (ActivityLog $log) {
            return (float) $log->qty;
        });
    }

    private function streak(Activity $activity, int $userId, Carbon $asOf): int
    {
        if ((string) $activity->cadence !== 'daily') {
            return 0;
        }

        $target = max(1, (int) $activity->target_count);
        $start = $asOf->copy()->subDays(365)->startOfDay();

        $logs = $activity->relationLoaded('logs')
            ? $activity->logs
            : ActivityLog::query()
                ->where('activity_id', $activity->id)
                ->where('user_id', $userId)
                ->whereDate('occurred_on', '>=', $start->toDateString())
                ->get();

        $byDay = $logs
            ->where('user_id', $userId)
            ->groupBy(fn (ActivityLog $log) => optional($log->occurred_on)->toDateString())
            ->map(fn (Collection $items) => (float) $items->sum(fn (ActivityLog $l) => (float) $l->qty));

        $streak = 0;
        $cursor = $asOf->copy()->startOfDay();

        for ($i = 0; $i < 366; $i++) {
            $key = $cursor->toDateString();
            $done = (float) ($byDay[$key] ?? 0);
            if ($done < $target) {
                break;
            }
            $streak++;
            $cursor->subDay();
        }

        return $streak;
    }
}
