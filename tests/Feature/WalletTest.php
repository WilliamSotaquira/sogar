<?php

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletMovement;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;
use function Pest\Laravel\put;
use function Pest\Laravel\delete;

it('creates a wallet for the user', function () {
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('wallets.store'), [
            'name' => 'Viajes',
            'description' => 'Ahorro vacaciones',
            'initial_balance' => 500,
            'target_amount' => 2000,
            'is_shared' => '1',
            'is_active' => '1',
        ])
        ->assertRedirect(route('wallets.index'));

    $this->assertDatabaseHas('sogar_wallets', [
        'user_id' => $user->id,
        'name' => 'Viajes',
        'is_shared' => true,
        'is_active' => true,
        'target_amount' => 2000,
    ]);
});

it('updates only own wallet and rejects others', function () {
    [$owner, $other] = User::factory()->count(2)->create();

    $wallet = Wallet::create([
        'user_id' => $owner->id,
        'name' => 'General',
        'initial_balance' => 100,
        'is_shared' => false,
        'is_active' => true,
    ]);

    actingAs($owner)
        ->put(route('wallets.update', $wallet), [
            'name' => 'General actualizado',
            'initial_balance' => 150,
            'target_amount' => 1000,
            'description' => 'Notas',
            'is_shared' => '1',
            'is_active' => '0',
        ])
        ->assertRedirect(route('wallets.index'));

    $this->assertDatabaseHas('sogar_wallets', [
        'id' => $wallet->id,
        'name' => 'General actualizado',
        'is_shared' => true,
        'is_active' => false,
    ]);

    actingAs($other)
        ->put(route('wallets.update', $wallet), [
            'name' => 'Malicioso',
            'initial_balance' => 10,
        ])
        ->assertStatus(403);
});

it('prevents deleting a wallet with usage', function () {
    $user = User::factory()->create();
    $wallet = Wallet::create([
        'user_id' => $user->id,
        'name' => 'Servicios',
        'initial_balance' => 0,
        'is_shared' => false,
        'is_active' => true,
    ]);

    WalletMovement::create([
        'wallet_id' => $wallet->id,
        'user_id' => $user->id,
        'amount' => 100,
        'occurred_on' => now()->toDateString(),
    ]);

    actingAs($user)
        ->delete(route('wallets.destroy', $wallet))
        ->assertRedirect(route('wallets.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('sogar_wallets', ['id' => $wallet->id]);
});

it('validates unique name per user and blocks inactive wallet for movements', function () {
    $user = User::factory()->create();

    $wallet = Wallet::create([
        'user_id' => $user->id,
        'name' => 'Caja',
        'initial_balance' => 0,
        'is_shared' => false,
        'is_active' => false,
    ]);

    actingAs($user)
        ->post(route('wallets.store'), [
            'name' => 'Caja',
            'initial_balance' => 0,
        ])
        ->assertSessionHasErrors('name');

    actingAs($user)
        ->post(route('wallets.movements.store', $wallet), [
            'amount' => 50,
            'occurred_on' => now()->toDateString(),
            'concept' => 'Depósito',
        ])
        ->assertRedirect(route('wallets.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseMissing('sogar_wallet_movements', [
        'wallet_id' => $wallet->id,
        'amount' => 50,
    ]);
});
