<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Recurrence;
use App\Models\Transaction;
use App\Models\WalletMovement;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunRecurrences extends Command
{
    protected $signature = 'recurrences:run {--date= : Fecha de corte (Y-m-d). Por defecto hoy} {--user_id= : Ejecutar solo para un usuario}';

    protected $description = 'Genera transacciones desde recurrencias vencidas y calcula el próximo vencimiento.';

    public function handle(): int
    {
        $asOf = $this->option('date')
            ? Carbon::parse((string) $this->option('date'))->startOfDay()
            : Carbon::today();

        $userId = $this->option('user_id') ? (int) $this->option('user_id') : null;

        $query = Recurrence::query()
            ->where('is_active', true)
            ->whereNotNull('next_run_on')
            ->whereDate('next_run_on', '<=', $asOf)
            ->orderBy('next_run_on');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $recurrenceIds = $query->pluck('id');

        $createdTransactions = 0;
        $touchedRecurrences = 0;

        foreach ($recurrenceIds as $recurrenceId) {
            $result = DB::transaction(function () use ($recurrenceId, $asOf, &$createdTransactions) {
                $recurrence = Recurrence::with('category')
                    ->lockForUpdate()
                    ->find($recurrenceId);

                if (!$recurrence) {
                    return false;
                }

                $processed = 0;

                while ($recurrence->is_active
                    && $recurrence->next_run_on
                    && $recurrence->next_run_on->lte($asOf)
                ) {
                    $runOn = $recurrence->next_run_on->copy();

                    $category = $recurrence->category;
                    if (!$category) {
                        $this->warn("Recurrencia {$recurrence->id} sin categoría; se omite.");
                        return false;
                    }

                    $alreadyExists = Transaction::query()
                        ->where('recurrence_id', $recurrence->id)
                        ->whereDate('occurred_on', $runOn)
                        ->exists();

                    if (!$alreadyExists) {
                        $transaction = Transaction::create([
                            'user_id' => $recurrence->user_id,
                            'category_id' => $recurrence->category_id,
                            'wallet_id' => $recurrence->wallet_id,
                            'recurrence_id' => $recurrence->id,
                            'amount' => $recurrence->amount,
                            'occurred_on' => $runOn,
                            'note' => $recurrence->note ?: $recurrence->name,
                            'origin' => 'recurrence',
                            'tags' => null,
                        ]);

                        if ($transaction->wallet_id) {
                            $this->createWalletMovement($transaction, $category);
                        }

                        $createdTransactions++;
                    }

                    $recurrence->last_run_at = now();
                    $recurrence->next_run_on = $this->computeNextRunOn($runOn, (string) $recurrence->frequency);
                    $recurrence->save();

                    $processed++;
                    if ($processed > 366) {
                        $this->warn("Recurrencia {$recurrence->id}: demasiadas ejecuciones pendientes; corte preventivo.");
                        break;
                    }
                }

                return true;
            });

            if ($result) {
                $touchedRecurrences++;
            }
        }

        $this->info("Recurrencias procesadas: {$touchedRecurrences}");
        $this->info("Transacciones creadas: {$createdTransactions}");

        return self::SUCCESS;
    }

    private function computeNextRunOn(Carbon $fromDate, string $frequency): Carbon
    {
        return match ($frequency) {
            'daily' => $fromDate->copy()->addDay(),
            'weekly' => $fromDate->copy()->addWeek(),
            'monthly' => $fromDate->copy()->addMonth(),
            'yearly' => $fromDate->copy()->addYear(),
            default => $fromDate->copy()->addDay(),
        };
    }

    private function createWalletMovement(Transaction $transaction, Category $category): void
    {
        $signedAmount = $category->type === 'expense'
            ? -1 * abs($transaction->amount)
            : abs($transaction->amount);

        WalletMovement::create([
            'wallet_id' => $transaction->wallet_id,
            'user_id' => $transaction->user_id,
            'category_id' => $transaction->category_id,
            'transaction_id' => $transaction->id,
            'amount' => $signedAmount,
            'occurred_on' => $transaction->occurred_on,
            'concept' => $transaction->note,
        ]);
    }
}
