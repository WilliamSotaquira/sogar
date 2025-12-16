<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Integration;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\ShoppingList;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\ProductPerformanceService;
use App\Services\PriceChangeService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $now = now();

        // Consultas base
        $income = Transaction::where('user_id', $user->id)
            ->whereHas('category', fn($q) => $q->where('type', 'income'))
            ->whereYear('occurred_on', $now->year)
            ->whereMonth('occurred_on', $now->month)
            ->sum('amount');

        $expenses = Transaction::where('user_id', $user->id)
            ->whereHas('category', fn($q) => $q->where('type', 'expense'))
            ->whereYear('occurred_on', $now->year)
            ->whereMonth('occurred_on', $now->month)
            ->sum('amount');

        $savingsRate = $income > 0 ? ($income - $expenses) / $income : 0;
        $projectedSavings = ($income - $expenses) * 6;
        $netThisMonth = $income - $expenses;

        // Presupuestos
        $budgets = Budget::with('category')
            ->where('user_id', $user->id)
            ->where('month', $now->month)
            ->where('year', $now->year)
            ->get()
            ->map(function ($budget) use ($now) {
                $spent = Transaction::where('category_id', $budget->category_id)
                    ->whereYear('occurred_on', $now->year)
                    ->whereMonth('occurred_on', $now->month)
                    ->sum('amount');

                $percent = $budget->amount > 0 ? min(100, ($spent / $budget->amount) * 100) : 0;

                return [
                    'category' => $budget->category->name,
                    'amount' => $budget->amount,
                    'spent' => $spent,
                    'percent' => round($percent, 1),
                ];
            });

        $budgetsAtRiskCount = $budgets->where('percent', '>=', 90)->count();

        // Wallets
        $wallets = Wallet::where('user_id', $user->id)
            ->where('is_active', true)
            ->get()
            ->map(function ($wallet) {
                $balance = $wallet->initial_balance + $wallet->movements()->sum('amount');
                return [
                    'name' => $wallet->name,
                    'balance' => $balance,
                    'target' => $wallet->target_amount,
                    'is_shared' => $wallet->is_shared,
                ];
            });

        $activeWalletsCount = $wallets->count();

        // Alertas
        $alerts = collect();

        // Alertas de presupuestos
        foreach ($budgets as $budget) {
            if ($budget['percent'] >= 90) {
                $alerts->push([
                    'title' => 'Presupuesto alto',
                    'message' => "Has gastado {$budget['percent']}% del presupuesto de {$budget['category']}.",
                    'route' => route('budgets.index'),
                ]);
            }
        }

        // Alertas de alimentos (rendimiento y precios)
        try {
            $performanceService = app(ProductPerformanceService::class);
            $performanceAlerts = $performanceService->generatePerformanceAlerts($user->id);

            foreach ($performanceAlerts['low_performance'] as $alert) {
                $alerts->push([
                    'title' => '📉 Producto de bajo rendimiento',
                    'message' => $alert['message'],
                    'route' => route('food.products.index'),
                ]);
            }

            $priceService = app(PriceChangeService::class);
            $priceAlerts = $priceService->getPriceAlerts($user->id, 7);

            foreach (array_slice($priceAlerts, 0, 3) as $alert) {
                $icon = $alert['severity'] === 'warning' ? '⚠️' : '✅';
                $direction = $alert['change_percent'] > 0 ? 'subió' : 'bajó';
                $alerts->push([
                    'title' => "{$icon} Cambio de precio",
                    'message' => "{$alert['product']} {$direction} " . abs(round($alert['change_percent'])) . "% en {$alert['vendor']}",
                    'route' => route('food.products.index'),
                ]);
            }
        } catch (\Exception $e) {
            // Ignorar errores del módulo de alimentos si no está completamente configurado
        }

        // Resumen del módulo de alimentos
        $foodProductsCount = FoodProduct::where('user_id', $user->id)->active()->count();
        $foodLowStockCount = FoodProduct::where('user_id', $user->id)->active()->lowStock()->count();
        $foodExpiringSoonCount = FoodStockBatch::where('user_id', $user->id)->active()->expiringSoon(7)->count();

        $listsQuery = $this->accessibleShoppingListsQuery($user);
        $foodActiveListsCount = (clone $listsQuery)->where('status', 'active')->count();
        $foodLatestActiveList = (clone $listsQuery)
            ->where('status', 'active')
            ->withCount([
                'items as pending_items_count' => fn ($q) => $q->where('is_checked', false),
            ])
            ->latest('generated_at')
            ->first(['id', 'name', 'generated_at']);

        // Resumen del módulo de familia
        $activeFamilyGroup = $user->activeFamilyGroup()
            ->withCount('members')
            ->first();

        // Calcular health score
        $healthScore = $this->calculateHealthScore($income, $expenses, $budgets, $alerts->count());

        $googleIntegration = Integration::where('user_id', $user->id)
            ->where('provider', 'google')
            ->first();

        return view('dashboard', compact(
            'income',
            'expenses',
            'savingsRate',
            'projectedSavings',
            'netThisMonth',
            'healthScore',
            'budgets',
            'budgetsAtRiskCount',
            'wallets',
            'activeWalletsCount',
            'alerts',
            'googleIntegration',
            'foodProductsCount',
            'foodLowStockCount',
            'foodExpiringSoonCount',
            'foodActiveListsCount',
            'foodLatestActiveList',
            'activeFamilyGroup'
        ));
    }

    private function calculateHealthScore(float $income, float $expenses, $budgets, int $alertCount): int
    {
        if ($income <= 0) {
            return 40;
        }

        $savingsRate = max(0, min(1, ($income - $expenses) / $income));
        $budgetPressure = $budgets->avg(fn ($b) => $b['percent']) ?? 0;

        $score = (50 * $savingsRate) 
            + (30 * (1 - min(1, $budgetPressure / 100))) 
            + (20 * ($alertCount === 0 ? 1 : max(0, 1 - ($alertCount * 0.2))));

        return (int) round(max(1, min(100, $score)));
    }

    private function accessibleShoppingListsQuery($user)
    {
        $familyGroupIds = method_exists($user, 'familyGroupIds') ? $user->familyGroupIds() : [];

        if (method_exists($user, 'isSystemAdmin') && $user->isSystemAdmin()) {
            return ShoppingList::query();
        }

        return ShoppingList::where(function ($query) use ($user, $familyGroupIds) {
            $query->where('user_id', $user->id);

            if (!empty($familyGroupIds)) {
                $query->orWhereIn('family_group_id', $familyGroupIds);
            }
        });
    }
}
