<?php

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

it('denies shopping routes when active family group member lacks permission', function () {
    $user = User::factory()->create([
        'active_family_group_id' => null,
        'is_system_admin' => false,
    ]);

    $familyGroup = FamilyGroup::create([
        'name' => 'Familia Test',
        'description' => null,
        'admin_user_id' => $user->id,
        'is_active' => true,
    ]);

    FamilyMember::create([
        'family_group_id' => $familyGroup->id,
        'user_id' => $user->id,
        'role' => 'otro',
        'is_admin' => false,
        'can_manage_finances' => false,
        'can_manage_food' => false,
        'can_manage_shopping' => false,
        'joined_at' => now(),
    ]);

    $user->update(['active_family_group_id' => $familyGroup->id]);

    actingAs($user)
        ->get(route('food.shopping-list.index'))
        ->assertStatus(403);
});

it('allows shopping routes when active family group member has permission', function () {
    $user = User::factory()->create([
        'active_family_group_id' => null,
        'is_system_admin' => false,
    ]);

    $familyGroup = FamilyGroup::create([
        'name' => 'Familia Test',
        'description' => null,
        'admin_user_id' => $user->id,
        'is_active' => true,
    ]);

    FamilyMember::create([
        'family_group_id' => $familyGroup->id,
        'user_id' => $user->id,
        'role' => 'otro',
        'is_admin' => false,
        'can_manage_finances' => false,
        'can_manage_food' => false,
        'can_manage_shopping' => true,
        'joined_at' => now(),
    ]);

    $user->update(['active_family_group_id' => $familyGroup->id]);

    actingAs($user)
        ->get(route('food.shopping-list.index'))
        ->assertStatus(200);
});
