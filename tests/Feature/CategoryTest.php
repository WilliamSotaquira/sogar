<?php

use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;

it('allows a user to create a personal category', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('categories.store'), [
            'name' => 'Transporte',
            'type' => 'expense',
            'description' => 'Uber y buses',
            'color' => '#ff9900',
            'is_active' => '1',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('sogar_categories', [
        'name' => 'Transporte',
        'user_id' => $user->id,
        'type' => 'expense',
        'is_active' => true,
        'description' => 'Uber y buses',
    ]);
});

it('lets a user update their own category but not the base ones', function () {
    $user = User::factory()->create();
    $ownCategory = Category::create([
        'name' => 'Suscripciones',
        'type' => 'expense',
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    actingAs($user)
        ->put(route('categories.update', $ownCategory), [
            'name' => 'Streaming',
            'type' => 'expense',
            'description' => 'Series y música',
            'color' => '#123abc',
            'is_active' => '0',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('sogar_categories', [
        'id' => $ownCategory->id,
        'name' => 'Streaming',
        'description' => 'Series y música',
        'is_active' => false,
    ]);

    $baseCategory = Category::create([
        'name' => 'Salario',
        'type' => 'income',
        'user_id' => null,
        'is_active' => true,
    ]);

    actingAs($user)
        ->put(route('categories.update', $baseCategory), [
            'name' => 'No importa',
            'type' => 'income',
        ])
        ->assertStatus(403);
});

it('blocks deleting a category that has usage', function () {
    $user = User::factory()->create();
    $category = Category::create([
        'name' => 'Servicios',
        'type' => 'expense',
        'user_id' => $user->id,
        'is_active' => true,
    ]);
    $wallet = Wallet::create([
        'user_id' => $user->id,
        'name' => 'General',
        'initial_balance' => 0,
        'is_shared' => false,
        'is_active' => true,
    ]);

    Transaction::create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'wallet_id' => $wallet->id,
        'amount' => 50,
        'occurred_on' => now()->toDateString(),
        'origin' => 'manual',
    ]);

    actingAs($user)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('sogar_categories', [
        'id' => $category->id,
    ]);
});

it('validates duplicate names per user', function () {
    $user = User::factory()->create();

    Category::create([
        'name' => 'Mascotas',
        'type' => 'expense',
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    actingAs($user)
        ->post(route('categories.store'), [
            'name' => 'Mascotas',
            'type' => 'expense',
        ])
        ->assertSessionHasErrors('name');

    expect(Category::where('name', 'Mascotas')->where('user_id', $user->id)->count())->toBe(1);
});
