<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sogar_food_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('color')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['user_id', 'slug']);
        });

        Schema::create('sogar_food_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'name']);
        });

        Schema::create('sogar_food_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('sogar_food_types')->nullOnDelete();
            $table->foreignId('default_location_id')->nullable()->constrained('sogar_food_locations')->nullOnDelete();
            $table->string('name');
            $table->string('brand')->nullable();
            $table->string('barcode')->nullable();
            $table->string('unit_base', 16)->default('unit'); // unit|g|kg|ml|l
            $table->decimal('unit_size', 10, 3)->default(1); // factor relativo a la unidad base
            $table->unsignedSmallInteger('shelf_life_days')->nullable();
            $table->decimal('min_stock_qty', 12, 3)->nullable();
            $table->decimal('presentation_qty', 12, 3)->nullable();
            $table->string('image_url')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'name']);
            $table->unique(['user_id', 'barcode']);
        });

        Schema::create('sogar_food_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained('sogar_wallets')->nullOnDelete();
            $table->date('occurred_on');
            $table->string('vendor')->nullable();
            $table->string('receipt_number')->nullable();
            $table->decimal('total', 14, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('sogar_food_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('sogar_food_purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('sogar_food_products')->nullOnDelete();
            $table->foreignId('type_id')->nullable()->constrained('sogar_food_types')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('sogar_food_locations')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('sogar_categories')->nullOnDelete();
            $table->foreignId('budget_id')->nullable()->constrained('sogar_budgets')->nullOnDelete();
            $table->decimal('qty', 12, 3);
            $table->string('unit', 16)->default('unit');
            $table->decimal('unit_size', 10, 3)->default(1);
            $table->decimal('unit_price', 14, 4)->default(0);
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->date('expires_on')->nullable();
            $table->timestamps();
        });

        Schema::create('sogar_food_stock_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('sogar_food_products')->cascadeOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('sogar_food_locations')->nullOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained('sogar_food_purchase_items')->nullOnDelete();
            $table->decimal('qty_base', 14, 3);
            $table->decimal('qty_remaining_base', 14, 3);
            $table->string('unit_base', 16)->default('unit');
            $table->date('expires_on')->nullable();
            $table->date('entered_on');
            $table->dateTime('opened_at')->nullable();
            $table->decimal('cost_total', 14, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->string('status', 16)->default('ok'); // ok|consumed|expired|wasted
            $table->timestamps();

            $table->index(['user_id', 'product_id']);
            $table->index(['expires_on', 'status']);
        });

        Schema::create('sogar_food_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('sogar_food_products')->cascadeOnDelete();
            $table->foreignId('batch_id')->nullable()->constrained('sogar_food_stock_batches')->nullOnDelete();
            $table->foreignId('from_location_id')->nullable()->constrained('sogar_food_locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->constrained('sogar_food_locations')->nullOnDelete();
            $table->string('reason', 24); // purchase|consume|transfer|adjust|waste
            $table->decimal('qty_delta_base', 14, 3);
            $table->date('occurred_on');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'product_id']);
            $table->index('occurred_on');
        });

        Schema::create('sogar_food_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('sogar_food_products')->cascadeOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->constrained('sogar_food_purchase_items')->nullOnDelete();
            $table->string('source', 16)->default('manual'); // manual|ticket|scan
            $table->string('vendor')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('price_per_base', 14, 4);
            $table->date('captured_on')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('sogar_food_barcodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('sogar_food_products')->cascadeOnDelete();
            $table->string('code');
            $table->string('kind', 16)->default('ean13'); // ean13|qr|custom
            $table->timestamps();

            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sogar_food_barcodes');
        Schema::dropIfExists('sogar_food_prices');
        Schema::dropIfExists('sogar_food_stock_movements');
        Schema::dropIfExists('sogar_food_stock_batches');
        Schema::dropIfExists('sogar_food_purchase_items');
        Schema::dropIfExists('sogar_food_purchases');
        Schema::dropIfExists('sogar_food_products');
        Schema::dropIfExists('sogar_food_types');
        Schema::dropIfExists('sogar_food_locations');
    }
};
