<?php

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\Routine;
use App\Models\RoutineItem;
use App\Models\RoutineItemLog;
use App\Models\User;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;

function routinesUserForExport(): User
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

it('exports a routine as tsv', function () {
    $user = routinesUserForExport();
    actingAs($user);

    $routine = Routine::create([
        'user_id' => $user->id,
        'family_group_id' => null,
        'name' => 'Día hábil',
        'description' => null,
        'is_active' => true,
    ]);

    RoutineItem::create([
        'routine_id' => $routine->id,
        'title' => 'Despertar',
        'group' => 'Salud',
        'category' => null,
        'start_time' => '05:30',
        'end_time' => '05:55',
        'weekdays_mask' => 31,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $res = get(route('routines.exportTsv', ['routine' => $routine->id]));
    $res->assertOk();
    $res->assertHeader('content-type', 'text/tab-separated-values; charset=UTF-8');

    $content = $res->streamedContent();
    expect($content)->toContain("Desde");
    expect($content)->toContain("Despertar");
});

it('exports a routine as csv', function () {
    $user = routinesUserForExport();
    actingAs($user);

    $routine = Routine::create([
        'user_id' => $user->id,
        'family_group_id' => null,
        'name' => 'Día hábil',
        'description' => null,
        'is_active' => true,
    ]);

    RoutineItem::create([
        'routine_id' => $routine->id,
        'title' => 'Despertar',
        'group' => 'Salud',
        'category' => null,
        'start_time' => '05:30',
        'end_time' => '05:55',
        'weekdays_mask' => 31,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $res = get(route('routines.exportCsv', ['routine' => $routine->id]));
    $res->assertOk();
    $res->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $res->streamedContent();
    expect($content)->toContain("Desde");
    expect($content)->toContain("Despertar");
});

it('includes status when exporting with date param', function () {
    $user = routinesUserForExport();
    actingAs($user);

    $routine = Routine::create([
        'user_id' => $user->id,
        'family_group_id' => null,
        'name' => 'Día hábil',
        'description' => null,
        'is_active' => true,
    ]);

    $item = RoutineItem::create([
        'routine_id' => $routine->id,
        'title' => 'Despertar',
        'group' => 'Salud',
        'category' => null,
        'start_time' => '05:30',
        'end_time' => '05:55',
        'weekdays_mask' => 31,
        'sort_order' => 1,
        'is_active' => true,
    ]);

    $date = now()->toDateString();

    RoutineItemLog::create([
        'routine_item_id' => $item->id,
        'user_id' => $user->id,
        'occurred_on' => $date,
        'status' => 'done',
        'occurred_at' => now(),
        'note' => null,
        'meta' => null,
    ]);

    $res = get(route('routines.exportTsv', ['routine' => $routine->id]) . '?date=' . $date);
    $res->assertOk();

    $content = $res->streamedContent();
    expect($content)->toContain("done");
});
