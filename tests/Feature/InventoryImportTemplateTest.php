<?php

use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Models\FoodLocation;
use App\Models\FoodProduct;
use App\Models\FoodStockBatch;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

function foodUser(): User
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
        'can_manage_food' => true,
        'can_manage_shopping' => false,
        'joined_at' => now(),
    ]);

    $user->update(['active_family_group_id' => $familyGroup->id]);

    return $user;
}

it('downloads an inventory csv template', function () {
    $user = foodUser();
    actingAs($user);

    $res = get(route('food.inventory.templateCsv'));
    $res->assertOk();
    $res->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $res->streamedContent();
    expect($content)->toContain('producto');
    expect($content)->toContain('cantidad');
});

it('imports inventory from a simple csv', function () {
    $user = foodUser();

    $location = FoodLocation::create([
        'user_id' => $user->id,
        'name' => 'Despensa',
        'slug' => Str::slug('Despensa'),
        'sort_order' => 0,
        'is_default' => true,
    ]);

    $product = FoodProduct::create([
        'user_id' => $user->id,
        'name' => 'Arroz',
        'barcode' => '1234567890',
        'default_location_id' => $location->id,
        'unit_base' => 'unit',
        'unit_size' => 1,
        'is_active' => true,
    ]);

    $csv = implode("\n", [
        'producto;cantidad',
        'Arroz;2',
    ]);

    $file = UploadedFile::fake()->createWithContent('inventory.csv', $csv);

    actingAs($user);

    $res = post(route('food.inventory.import'), [
        'file' => $file,
    ]);

    $res->assertRedirect(route('food.inventory.index'));

    $batch = FoodStockBatch::where('user_id', $user->id)->latest('id')->first();
    expect($batch)->not->toBeNull();
    expect($batch->product_id)->toBe($product->id);
    expect((float) $batch->qty_remaining_base)->toBe(2.0);
    expect($batch->location_id)->toBe($location->id);
});

it('downloads inventory as csv', function () {
    $user = foodUser();

    $location = FoodLocation::create([
        'user_id' => $user->id,
        'name' => 'Despensa',
        'slug' => Str::slug('Despensa'),
        'sort_order' => 0,
        'is_default' => true,
    ]);

    $defaultOnlyLocation = FoodLocation::create([
        'user_id' => $user->id,
        'name' => 'Nevera',
        'slug' => Str::slug('Nevera'),
        'sort_order' => 1,
        'is_default' => false,
    ]);

    $product = FoodProduct::create([
        'user_id' => $user->id,
        'name' => 'Pasta',
        'barcode' => '99887766',
        'default_location_id' => $location->id,
        'unit_base' => 'unit',
        'unit_size' => 1,
        'is_active' => true,
    ]);

    $productWithDefaultOnly = FoodProduct::create([
        'user_id' => $user->id,
        'name' => 'Yogur',
        'barcode' => '11223344',
        'default_location_id' => $defaultOnlyLocation->id,
        'unit_base' => 'unit',
        'unit_size' => 1,
        'is_active' => true,
    ]);

    FoodStockBatch::create([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'location_id' => $location->id,
        'qty_base' => 3,
        'qty_remaining_base' => 3,
        'unit_base' => 'unit',
        'entered_on' => now()->toDateString(),
        'status' => 'ok',
    ]);

    // Batch sin ubicación: debe exportar la ubicación por defecto del producto
    FoodStockBatch::create([
        'user_id' => $user->id,
        'product_id' => $productWithDefaultOnly->id,
        'location_id' => null,
        'qty_base' => 1,
        'qty_remaining_base' => 1,
        'unit_base' => 'unit',
        'entered_on' => now()->toDateString(),
        'status' => 'ok',
    ]);

    actingAs($user);

    $res = get(route('food.inventory.exportCsv'));
    $res->assertOk();
    $res->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $content = $res->streamedContent();
    expect($content)->toContain('producto');
    expect($content)->toContain('Pasta');
    expect($content)->toContain('Despensa');
    expect($content)->toContain('Yogur');
    expect($content)->toContain('Nevera');
});
