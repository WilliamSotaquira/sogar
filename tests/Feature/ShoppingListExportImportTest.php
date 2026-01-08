<?php

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\ShoppingListType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('exports a shopping list as csv by default', function () {
    $user = User::factory()->create();
    ShoppingListType::ensureDefaultsForUser($user->id);

    $list = ShoppingList::create([
        'user_id' => $user->id,
        'family_group_id' => $user->active_family_group_id,
        'name' => 'Compra test',
        'list_type' => 'general',
        'status' => 'active',
        'generated_at' => now(),
    ]);

    ShoppingListItem::create([
        'shopping_list_id' => $list->id,
        'name' => 'Arroz',
        'priority' => 'medium',
        'qty_to_buy_base' => 2,
        'estimated_price' => 0,
        'is_checked' => false,
        'sort_order' => 1,
    ]);

    actingAs($user);

    $res = get(route('food.shopping-list.export', $list));

    $res->assertOk();
    $res->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $content = $res->streamedContent();
    expect($content)->toContain('list_name');
    expect($content)->toContain('item_name');
    expect($content)->toContain('Compra test');
    expect($content)->toContain('Arroz');
});

it('exports a shopping list as json when format=json', function () {
    $user = User::factory()->create();
    ShoppingListType::ensureDefaultsForUser($user->id);

    $list = ShoppingList::create([
        'user_id' => $user->id,
        'family_group_id' => $user->active_family_group_id,
        'name' => 'Compra test',
        'list_type' => 'general',
        'status' => 'active',
        'generated_at' => now(),
    ]);

    ShoppingListItem::create([
        'shopping_list_id' => $list->id,
        'name' => 'Arroz',
        'priority' => 'medium',
        'qty_to_buy_base' => 2,
        'estimated_price' => 0,
        'is_checked' => false,
        'sort_order' => 1,
    ]);

    actingAs($user);

    $res = get(route('food.shopping-list.export', ['list' => $list, 'format' => 'json']));

    $res->assertOk();
    $res->assertHeader('content-type', 'application/json; charset=UTF-8');
    $content = $res->streamedContent();
    expect($content)->toContain('sogar.shopping-list');
    expect($content)->toContain('"items"');
});

it('imports a shopping list from json', function () {
    $user = User::factory()->create();
    ShoppingListType::ensureDefaultsForUser($user->id);

    $payload = [
        'type' => 'sogar.shopping-list',
        'version' => 1,
        'exported_at' => now()->toIso8601String(),
        'list' => [
            'name' => 'Importada',
            'list_type' => 'general',
            'expected_purchase_on' => now()->addDays(1)->toDateString(),
            'people_count' => 3,
            'purchase_frequency_days' => 7,
            'safety_factor' => 1.2,
            'estimated_budget' => 0,
            'meta' => [],
        ],
        'items' => [
            [
                'name' => 'Leche',
                'priority' => 'medium',
                'qty_to_buy_base' => 1,
                'qty_suggested_base' => 0,
                'qty_current_base' => 0,
                'qty_unit_label' => null,
                'unit_base' => null,
                'unit_size' => null,
                'estimated_price' => 0,
                'actual_price' => null,
                'vendor_name' => null,
                'is_checked' => false,
                'checked_at' => null,
                'barcode' => null,
                'sort_order' => 0,
                'metadata' => null,
                'product_name' => null,
                'product_barcode' => null,
                'location_name' => null,
                'category_name' => null,
            ],
        ],
    ];

    $file = UploadedFile::fake()->createWithContent('list.json', json_encode($payload, JSON_UNESCAPED_UNICODE));

    actingAs($user);

    $res = post(route('food.shopping-list.import'), [
        'file' => $file,
    ]);

    $res->assertRedirect();

    $imported = ShoppingList::where('user_id', $user->id)->latest('id')->first();
    expect($imported)->not->toBeNull();
    expect($imported->name)->toBe('Importada');
    expect($imported->items()->count())->toBe(1);
});

it('imports a shopping list from csv', function () {
    $user = User::factory()->create();
    ShoppingListType::ensureDefaultsForUser($user->id);

    $csv = "name,qty_to_buy_base\n".
        "Leche,1\n".
        "Pan,2\n";

    $file = UploadedFile::fake()->createWithContent('lista.csv', $csv);

    actingAs($user);

    $res = post(route('food.shopping-list.import'), [
        'file' => $file,
    ]);

    $res->assertRedirect();

    $imported = ShoppingList::where('user_id', $user->id)->latest('id')->first();
    expect($imported)->not->toBeNull();
    expect($imported->items()->count())->toBe(2);
});

it('re-importing an exported csv updates the same list without duplicating items', function () {
    $user = User::factory()->create();
    ShoppingListType::ensureDefaultsForUser($user->id);

    $list = ShoppingList::create([
        'user_id' => $user->id,
        'family_group_id' => $user->active_family_group_id,
        'name' => 'Compra test',
        'list_type' => 'general',
        'status' => 'active',
        'generated_at' => now(),
    ]);

    ShoppingListItem::create([
        'shopping_list_id' => $list->id,
        'name' => 'Arroz',
        'priority' => 'medium',
        'qty_to_buy_base' => 2,
        'estimated_price' => 0,
        'is_checked' => false,
        'sort_order' => 1,
    ]);

    actingAs($user);

    $export = get(route('food.shopping-list.export', $list));
    $export->assertOk();
    $exportedCsv = $export->streamedContent();
    expect($exportedCsv)->toContain('list_id');
    expect($exportedCsv)->toContain((string) $list->id);

    // CSV mínimo (pero en modo "full" porque incluye list_name/list_type)
    $csv = "list_id;list_name;list_type;item_name;priority;qty_to_buy_base\n".
        "{$list->id};Compra test;general;Arroz;medium;5\n";

    $file = UploadedFile::fake()->createWithContent('lista.csv', $csv);

    $res = post(route('food.shopping-list.import'), [
        'file' => $file,
    ]);
    $res->assertRedirect(route('food.shopping-list.show', $list));

    $list->refresh();
    expect($list->items()->count())->toBe(1);
    expect((float) $list->items()->first()->qty_to_buy_base)->toBe(5.0);
});
