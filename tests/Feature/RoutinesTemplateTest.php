<?php

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function routinesUserWithPermission(): User
{
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
        'can_manage_habits' => false,
        'can_manage_routines' => true,
        'joined_at' => now(),
    ]);

    $user->update(['active_family_group_id' => $familyGroup->id]);

    return $user;
}

it('downloads routines tsv template', function () {
    $user = routinesUserWithPermission();
    actingAs($user);

    $res = get(route('routines.templateTsv'));
    $res->assertOk();
    $res->assertHeader('content-type', 'text/tab-separated-values; charset=UTF-8');

    $content = $res->streamedContent();
    expect($content)->toContain("Desde");
    expect($content)->toContain("Hasta");
    expect($content)->toContain("Tarea");
});

it('downloads routines csv template', function () {
    $user = routinesUserWithPermission();
    actingAs($user);

    $res = get(route('routines.templateCsv'));
    $res->assertOk();
    $res->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $res->streamedContent();
    expect($content)->toContain("Desde");
    expect($content)->toContain("Hasta");
    expect($content)->toContain("Tarea");
});
