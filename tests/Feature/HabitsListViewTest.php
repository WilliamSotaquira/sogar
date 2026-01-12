<?php

use App\Models\Activity;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\User;

function habitsUserForListView(): User
{
    $user = User::factory()->create([
        'active_family_group_id' => null,
        'is_system_admin' => false,
    ]);

    $familyGroup = FamilyGroup::create([
        'name' => 'Familia Test Habits',
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
        'can_manage_habits' => true,
        'can_manage_routines' => false,
        'joined_at' => now(),
    ]);

    $user->update(['active_family_group_id' => $familyGroup->id]);

    return $user;
}

it('renders habits list view via query string', function () {
    $user = habitsUserForListView();
    $this->actingAs($user);

    Activity::create([
        'user_id' => $user->id,
        'family_group_id' => null,
        'title' => 'Leer 20 minutos',
        'description' => null,
        'kind' => 'habit',
        'cadence' => 'daily',
        'target_count' => 1,
        'unit' => 'min',
        'due_on' => null,
        'is_active' => true,
        'subject_type' => null,
        'subject_id' => null,
        'meta' => [],
    ]);

    $resp = $this->get(route('habits.index', ['view' => 'list']));

    $resp->assertOk();
    $resp->assertSee('Actividad');
    $resp->assertSee('Leer 20 minutos');
});
