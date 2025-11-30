<?php

use App\Models\Recurrence;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('recurrences:run', function () {
    $today = Carbon::today();

    $recurrences = Recurrence::with(['category'])
        ->where('is_active', true)
        ->whereDate('next_run_on', '<=', $today)
        ->get();

    $count = 0;
    foreach ($recurrences as $recurrence) {
        Transaction::create([
            'user_id' => $recurrence->user_id,
            'category_id' => $recurrence->category_id,
            'wallet_id' => $recurrence->wallet_id,
            'recurrence_id' => $recurrence->id,
            'amount' => $recurrence->amount,
            'occurred_on' => $today,
            'note' => $recurrence->note,
            'origin' => 'recurrence',
        ]);

        $recurrence->update([
            'last_run_at' => Carbon::now(),
            'next_run_on' => match ($recurrence->frequency) {
                'daily' => $today->copy()->addDay(),
                'weekly' => $today->copy()->addWeek(),
                'monthly' => $today->copy()->addMonth(),
                'yearly' => $today->copy()->addYear(),
                default => $today->copy()->addMonth(),
            },
        ]);

        $count++;
    }

    $this->info("Recurrencias ejecutadas: {$count}");
})->purpose('Run due recurrences and create transactions.');
