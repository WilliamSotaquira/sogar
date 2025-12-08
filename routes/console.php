<?php

use App\Jobs\ComputeShoppingMetrics;
use App\Models\Category;
use App\Models\Recurrence;
use App\Models\Transaction;
use App\Models\WalletMovement;
use Carbon\Carbon;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('shopping:metrics {userId?}', function (?int $userId = null) {
    ComputeShoppingMetrics::dispatch($userId);
    $this->info('Job ComputeShoppingMetrics dispatchado' . ($userId ? " para el usuario {$userId}" : ' para todos los usuarios') . '.');
})->purpose('Despacha el job que genera métricas y eventos de observabilidad para las listas de compras.');

Artisan::command('recurrences:run', function () {
    $today = Carbon::today();

    $recurrences = Recurrence::with(['category'])
        ->where('is_active', true)
        ->whereDate('next_run_on', '<=', $today)
        ->get();

    $count = 0;
    foreach ($recurrences as $recurrence) {
        $transaction = Transaction::create([
            'user_id' => $recurrence->user_id,
            'category_id' => $recurrence->category_id,
            'wallet_id' => $recurrence->wallet_id,
            'recurrence_id' => $recurrence->id,
            'amount' => $recurrence->amount,
            'occurred_on' => $today,
            'note' => $recurrence->note,
            'origin' => 'recurrence',
        ]);

        if ($recurrence->wallet_id) {
            $category = Category::find($recurrence->category_id);
            if ($category) {
                $signedAmount = $category->type === 'expense'
                    ? -1 * abs($recurrence->amount)
                    : abs($recurrence->amount);

                WalletMovement::create([
                    'wallet_id' => $recurrence->wallet_id,
                    'user_id' => $recurrence->user_id,
                    'category_id' => $recurrence->category_id,
                    'transaction_id' => $transaction->id,
                    'amount' => $signedAmount,
                    'occurred_on' => $today,
                    'concept' => $recurrence->note,
                ]);
            }
        }

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
