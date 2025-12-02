<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Integration;
use App\Models\Transaction;
use App\Models\Wallet;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $transactions = Transaction::with('category')
            ->where('user_id', $user->id)
            ->whereBetween('occurred_on', [$monthStart, $monthEnd])
            ->get();

        $income = $transactions->where('category.type', 'income')->sum('amount');
        $expenses = $transactions->where('category.type', 'expense')->sum('amount');
        $savingsRate = $income > 0 ? max(0, min(1, ($income - $expenses) / $income)) : 0;

        $budgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('month', $now->format('m'))
            ->where('year', $now->format('Y'))
            ->get()
            ->map(function (Budget $budget) use ($transactions) {
                $spent = $transactions
                    ->where('category_id', $budget->category_id)
                    ->where('category.type', 'expense')
                    ->sum('amount');
                $percent = $budget->amount > 0 ? min(100, ($spent / $budget->amount) * 100) : 0;

                return [
                    'category' => $budget->category?->name ?? 'Sin categoría',
                    'amount' => $budget->amount,
                    'spent' => $spent,
                    'percent' => round($percent, 1),
                ];
            });

        $alerts = $budgets
            ->filter(fn ($b) => $b['percent'] >= 80)
            ->map(fn ($b) => [
                'title' => $b['percent'] >= 100 ? 'Presupuesto excedido' : 'Presupuesto alto',
                'message' => "{$b['category']} va en {$b['percent']}% ({$b['spent']}/{$b['amount']})",
            ])
            ->values();

        $wallets = Wallet::where('user_id', $user->id)
            ->where('is_active', true)
            ->with(['transactions.category', 'movements'])
            ->get()
            ->map(function (Wallet $wallet) {
            $balance = $wallet->initial_balance + $wallet->movements->sum('amount');

            return [
                'name' => $wallet->name,
                'balance' => $balance,
                'target' => $wallet->target_amount,
                'is_shared' => $wallet->is_shared,
            ];
        });

        $healthScore = $this->calculateHealthScore($income, $expenses, $budgets, $alerts->count());
        $projectedSavings = max(0, ($income - $expenses)) * 6;
        $googleIntegration = Integration::where('user_id', $user->id)->where('provider', 'google')->first();

        return view('dashboard', [
            'income' => $income,
            'expenses' => $expenses,
            'savingsRate' => $savingsRate,
            'healthScore' => $healthScore,
            'projectedSavings' => $projectedSavings,
            'budgets' => $budgets,
            'wallets' => $wallets,
            'alerts' => $alerts,
            'googleIntegration' => $googleIntegration,
        ]);
    }

    private function calculateHealthScore(float $income, float $expenses, $budgets, int $alertCount): int
    {
        if ($income <= 0) {
            return 40;
        }

        $savingsRate = max(0, min(1, ($income - $expenses) / $income));
        $budgetPressure = $budgets->avg(fn ($b) => $b['percent']) ?? 0;

        $score = (50 * $savingsRate) + (30 * (1 - min(1, $budgetPressure / 100))) + (20 * ($alertCount === 0 ? 1 : max(0, 1 - ($alertCount * 0.2))));

        return (int) round(max(1, min(100, $score)));
    }
}
