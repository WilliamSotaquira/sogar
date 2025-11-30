<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\CategoryKeyword;
use App\Models\Recurrence;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Models\WalletMovement;
use App\Models\User;
use Carbon\Carbon;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\artisan;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('suggests category from keyword when storing a transaction', function () {
    $user = User::factory()->create();
    $category = Category::create([
        'name' => 'Mercado',
        'type' => 'expense',
        'user_id' => null,
        'is_active' => true,
    ]);
    CategoryKeyword::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'keyword' => 'super',
    ]);

    $wallet = Wallet::create([
        'user_id' => $user->id,
        'name' => 'General',
        'initial_balance' => 0,
        'is_shared' => true,
        'is_active' => true,
    ]);

    actingAs($user)
        ->post(route('transactions.store'), [
            'amount' => 12000,
            'occurred_on' => now()->toDateString(),
            'note' => 'Compra super mercado',
            'wallet_id' => $wallet->id,
        ])
        ->assertRedirect(route('dashboard'));

    $this->assertDatabaseHas('sogar_transactions', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'note' => 'Compra super mercado',
    ]);

    $this->assertDatabaseHas('sogar_wallet_movements', [
        'wallet_id' => $wallet->id,
        'transaction_id' => Transaction::first()->id,
        'amount' => -12000,
    ]);
});

it('calculates budget consumption and shows alerts on dashboard', function () {
    $user = User::factory()->create();
    $category = Category::create([
        'name' => 'Ocio',
        'type' => 'expense',
        'user_id' => null,
        'is_active' => true,
    ]);

    Budget::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 100,
        'month' => (int) now()->format('m'),
        'year' => (int) now()->format('Y'),
        'is_flexible' => false,
    ]);

    Transaction::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'amount' => 90,
        'occurred_on' => now()->toDateString(),
        'origin' => 'manual',
    ]);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertStatus(200)
        ->assertViewHas('budgets', function ($budgets) {
            return $budgets->first()['percent'] === 90.0;
        })
        ->assertViewHas('alerts', function ($alerts) {
            return $alerts->count() === 1;
        });
});

it('computes health score based on savings, budgets and alerts', function () {
    Carbon::setTestNow('2025-01-01 10:00:00');

    $user = User::factory()->create();

    $incomeCategory = Category::create([
        'name' => 'Salario',
        'type' => 'income',
        'user_id' => null,
        'is_active' => true,
    ]);

    $expenseCategory = Category::create([
        'name' => 'Transporte',
        'type' => 'expense',
        'user_id' => null,
        'is_active' => true,
    ]);

    Budget::create([
        'user_id' => $user->id,
        'category_id' => $expenseCategory->id,
        'amount' => 200,
        'month' => 1,
        'year' => 2025,
        'is_flexible' => false,
    ]);

    Transaction::create([
        'user_id' => $user->id,
        'category_id' => $incomeCategory->id,
        'amount' => 1000,
        'occurred_on' => Carbon::today(),
        'origin' => 'manual',
    ]);

    Transaction::create([
        'user_id' => $user->id,
        'category_id' => $expenseCategory->id,
        'amount' => 100,
        'occurred_on' => Carbon::today(),
        'origin' => 'manual',
    ]);

    actingAs($user)
        ->get(route('dashboard'))
        ->assertStatus(200)
        ->assertViewHas('healthScore', 80);

    Carbon::setTestNow();
});

it('runs recurrences command and schedules next run', function () {
    Carbon::setTestNow('2025-01-01 00:00:00');

    $user = User::factory()->create();
    $category = Category::create([
        'name' => 'Servicio',
        'type' => 'expense',
        'user_id' => null,
        'is_active' => true,
    ]);

    $recurrence = Recurrence::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'wallet_id' => null,
        'name' => 'Internet',
        'amount' => 50,
        'frequency' => 'daily',
        'next_run_on' => Carbon::today(),
        'last_run_at' => null,
        'is_active' => true,
        'sync_to_calendar' => false,
        'wallet_id' => $wallet->id,
    ]);

    artisan('recurrences:run')->assertExitCode(0);

    $this->assertDatabaseHas('sogar_transactions', [
        'user_id' => $user->id,
        'recurrence_id' => $recurrence->id,
        'amount' => 50,
    ]);

    $this->assertDatabaseHas('sogar_wallet_movements', [
        'wallet_id' => $wallet->id,
        'transaction_id' => Transaction::first()->id,
        'amount' => -50,
    ]);

    $recurrence->refresh();
    expect($recurrence->next_run_on->toDateString())->toBe(Carbon::today()->addDay()->toDateString());

    Carbon::setTestNow();
});
