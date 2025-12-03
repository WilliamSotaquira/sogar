<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\FoodPurchase;
use App\Models\FoodPurchaseItem;
use App\Models\Transaction;
use App\Models\WalletMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FoodFinanceService
{
    public function registerExpenseFromPurchase(FoodPurchase $purchase): void
    {
        if (!$purchase->wallet_id) {
            return;
        }

        $userId = $purchase->user_id;
        $categoryId = $this->resolveCategoryId($purchase);
        $budgetId = $this->resolveBudgetId($purchase, $categoryId);

        DB::transaction(function () use ($purchase, $userId, $categoryId, $budgetId) {
            $transaction = Transaction::create([
                'user_id' => $userId,
                'category_id' => $categoryId,
                'wallet_id' => $purchase->wallet_id,
                'amount' => $purchase->total * -1,
                'occurred_on' => $purchase->occurred_on ?? Carbon::today(),
                'note' => 'Compra alimentos #' . $purchase->id,
                'origin' => 'food_purchase',
            ]);

            WalletMovement::create([
                'wallet_id' => $purchase->wallet_id,
                'user_id' => $userId,
                'category_id' => $categoryId,
                'transaction_id' => $transaction->id,
                'amount' => $purchase->total * -1,
                'occurred_on' => $transaction->occurred_on,
                'concept' => 'Compra alimentos #' . $purchase->id,
            ]);

            if ($budgetId) {
                FoodPurchaseItem::where('purchase_id', $purchase->id)->update([
                    'budget_id' => $budgetId,
                    'category_id' => $categoryId,
                ]);
            }
        });
    }

    private function resolveCategoryId(FoodPurchase $purchase): ?int
    {
        $itemCategory = FoodPurchaseItem::where('purchase_id', $purchase->id)->value('category_id');
        if ($itemCategory) {
            return $itemCategory;
        }

        return optional($purchase->user->categories()->where('name', 'Alimentos')->first())->id
            ?? optional($purchase->user->categories()->where('type', 'expense')->first())->id;
    }

    private function resolveBudgetId(FoodPurchase $purchase, ?int $categoryId): ?int
    {
        if (!$categoryId) {
            return null;
        }

        $today = Carbon::today();
        return Budget::where('user_id', $purchase->user_id)
            ->where('category_id', $categoryId)
            ->where('month', $today->month)
            ->where('year', $today->year)
            ->value('id');
    }
}
