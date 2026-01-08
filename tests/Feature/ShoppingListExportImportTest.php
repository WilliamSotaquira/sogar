<?php

use App\Models\ShoppingList;
use App\Models\ShoppingListItem;
use App\Models\ShoppingListType;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

it('exports a shopping list as json', function () {
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

it('exports a shopping list as csv', function () {
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

    $res = get(route('food.shopping-list.exportCsv', $list));
    $res->assertOk();
    $res->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $res->streamedContent();
    expect($content)->toContain('list_name');
    expect($content)->toContain('item_name');
    expect($content)->toContain('Arroz');
});

it('imports a shopping list from csv', function () {
    $user = User::factory()->create();
    ShoppingListType::ensureDefaultsForUser($user->id);

    $csv = implode("\n", [
        'list_name;list_type;expected_purchase_on;people_count;purchase_frequency_days;safety_factor;estimated_budget;item_name;priority;qty_to_buy_base',
        'Importada CSV;general;2026-01-08;3;7;1.2;0;Leche;medium;1',
    ]);

    $file = UploadedFile::fake()->createWithContent('list.csv', $csv);

    actingAs($user);

    $res = post(route('food.shopping-list.import'), [
        'file' => $file,
    ]);

    $res->assertRedirect();

    $imported = ShoppingList::where('user_id', $user->id)->latest('id')->first();
    expect($imported)->not->toBeNull();
    expect($imported->name)->toBe('Importada CSV');
    expect($imported->items()->count())->toBe(1);
});

it('imports a shopping list from simple csv (minimal columns)', function () {
    $user = User::factory()->create();
    ShoppingListType::ensureDefaultsForUser($user->id);

    $csv = implode("\n", [
        'producto;cantidad',
        'Pasta;2',
    ]);

    $file = UploadedFile::fake()->createWithContent('list-simple.csv', $csv);

    actingAs($user);

    $res = post(route('food.shopping-list.import'), [
        'file' => $file,
    ]);

    $res->assertRedirect();

    $imported = ShoppingList::where('user_id', $user->id)->latest('id')->first();
    expect($imported)->not->toBeNull();
    expect($imported->name)->toStartWith('Lista importada - ');
    expect($imported->list_type)->toBe('general');

    $item = $imported->items()->first();
    expect($item)->not->toBeNull();
    expect($item->name)->toBe('Pasta');
    expect((float) $item->qty_to_buy_base)->toBe(2.0);
});

it('imports a shopping list from simple csv with empty quantity (defaults to 1)', function () {
    $user = User::factory()->create();
    ShoppingListType::ensureDefaultsForUser($user->id);

    $csv = implode("\n", [
        'producto;cantidad',
        'Pan;',
    ]);

    $file = UploadedFile::fake()->createWithContent('list-simple-empty-qty.csv', $csv);

    actingAs($user);

    $res = post(route('food.shopping-list.import'), [
        'file' => $file,
    ]);

    $res->assertRedirect();

    $imported = ShoppingList::where('user_id', $user->id)->latest('id')->first();
    expect($imported)->not->toBeNull();

    $item = $imported->items()->first();
    expect($item)->not->toBeNull();
    expect($item->name)->toBe('Pan');
    expect((float) $item->qty_to_buy_base)->toBe(1.0);
});

it('downloads a simple csv template', function () {
    $user = User::factory()->create();
    ShoppingListType::ensureDefaultsForUser($user->id);

    actingAs($user);

    $res = get(route('food.shopping-list.templateCsv'));
    $res->assertOk();
    $res->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $res->streamedContent();
    expect($content)->toContain('producto');
    expect($content)->toContain('cantidad');
});
