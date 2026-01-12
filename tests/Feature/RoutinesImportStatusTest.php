<?php

use App\Livewire\Routines\Index;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\Routine;
use App\Models\RoutineItem;
use App\Models\RoutineItemLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;

function routinesUserForImportStatus(): User
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

it('imports status column as routine item logs for a given date (csv file)', function () {
    $user = routinesUserForImportStatus();
    $this->actingAs($user);

    $date = now()->toDateString();

    $csv = implode("\n", [
        'Desde;Hasta;Tarea;Categoría;Estado',
        '05:30;05:55;Despertar;Salud;done',
        '06:00;06:25;Meditación;Personal;skipped',
        '06:30;06:55;Leer;Personal;', // sin estado
    ]);

    $file = UploadedFile::fake()->createWithContent('rutina.csv', $csv);

    Livewire::test(Index::class)
        ->set('import_mode', 'new')
        ->set('import_routine_name', 'Rutina import status')
        ->set('import_days', 'weekday')
        ->set('import_apply_status', true)
        ->set('import_status_date', $date)
        ->set('import_file', $file)
        ->call('importBlocks');

    $routine = Routine::query()->where('user_id', $user->id)->where('name', 'Rutina import status')->first();
    expect($routine)->not->toBeNull();

    $items = RoutineItem::query()->where('routine_id', $routine->id)->orderBy('sort_order')->get();
    expect($items)->toHaveCount(3);

    $logs = RoutineItemLog::query()->where('user_id', $user->id)->whereDate('occurred_on', $date)->get();
    expect($logs)->toHaveCount(2);

    $first = $items[0];
    $second = $items[1];

    $log1 = RoutineItemLog::query()->where('routine_item_id', $first->id)->where('user_id', $user->id)->whereDate('occurred_on', $date)->first();
    $log2 = RoutineItemLog::query()->where('routine_item_id', $second->id)->where('user_id', $user->id)->whereDate('occurred_on', $date)->first();

    expect($log1?->status)->toBe('done');
    expect($log2?->status)->toBe('skipped');
});

it('normalizes spanish status values when importing logs (csv file)', function () {
    $user = routinesUserForImportStatus();
    $this->actingAs($user);

    $date = now()->toDateString();

    $csv = implode("\n", [
        'Desde;Hasta;Tarea;Categoría;Estado',
        '05:30;05:55;Despertar;Salud;Hecho',
        '06:00;06:25;Meditación;Personal;Saltado',
    ]);

    $file = UploadedFile::fake()->createWithContent('rutina.csv', $csv);

    Livewire::test(Index::class)
        ->set('import_mode', 'new')
        ->set('import_routine_name', 'Rutina import status es')
        ->set('import_days', 'weekday')
        ->set('import_apply_status', true)
        ->set('import_status_date', $date)
        ->set('import_file', $file)
        ->call('importBlocks');

    $routine = Routine::query()->where('user_id', $user->id)->where('name', 'Rutina import status es')->first();
    expect($routine)->not->toBeNull();

    $items = RoutineItem::query()->where('routine_id', $routine->id)->orderBy('sort_order')->get();
    expect($items)->toHaveCount(2);

    $log1 = RoutineItemLog::query()->where('routine_item_id', $items[0]->id)->where('user_id', $user->id)->whereDate('occurred_on', $date)->first();
    $log2 = RoutineItemLog::query()->where('routine_item_id', $items[1]->id)->where('user_id', $user->id)->whereDate('occurred_on', $date)->first();

    expect($log1?->status)->toBe('done');
    expect($log2?->status)->toBe('skipped');
});

it('imports comma separated csv files too', function () {
    $user = routinesUserForImportStatus();
    $this->actingAs($user);

    $date = now()->toDateString();

    $csv = implode("\n", [
        'Desde,Hasta,Tarea,Categoría,Estado',
        '05:30,05:55,Despertar,Salud,Hecho',
        '06:00,06:25,Meditación,Personal,Saltado',
    ]);

    $file = UploadedFile::fake()->createWithContent('rutina.csv', $csv);

    Livewire::test(Index::class)
        ->set('import_mode', 'new')
        ->set('import_routine_name', 'Rutina import csv semicolon')
        ->set('import_days', 'weekday')
        ->set('import_apply_status', true)
        ->set('import_status_date', $date)
        ->set('import_file', $file)
        ->call('importBlocks');

    $routine = Routine::query()->where('user_id', $user->id)->where('name', 'Rutina import csv semicolon')->first();
    expect($routine)->not->toBeNull();

    $items = RoutineItem::query()->where('routine_id', $routine->id)->orderBy('sort_order')->get();
    expect($items)->toHaveCount(2);

    $log1 = RoutineItemLog::query()->where('routine_item_id', $items[0]->id)->where('user_id', $user->id)->whereDate('occurred_on', $date)->first();
    $log2 = RoutineItemLog::query()->where('routine_item_id', $items[1]->id)->where('user_id', $user->id)->whereDate('occurred_on', $date)->first();

    expect($log1?->status)->toBe('done');
    expect($log2?->status)->toBe('skipped');
});
