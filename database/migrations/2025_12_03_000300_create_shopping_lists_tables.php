<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sogar_shopping_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable();
            $table->enum('status', ['active', 'closed'])->default('active');
            $table->timestamp('generated_at')->nullable();
            $table->date('expected_purchase_on')->nullable();
            $table->decimal('estimated_budget', 14, 2)->default(0);
            $table->unsignedTinyInteger('people_count')->default(3);
            $table->unsignedTinyInteger('purchase_frequency_days')->default(7);
            $table->decimal('safety_factor', 4, 2)->default(1.2);
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('sogar_shopping_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_list_id')->constrained('sogar_shopping_lists')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('sogar_food_products')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('sogar_categories')->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained('sogar_food_locations')->nullOnDelete();
            $table->string('name');
            $table->string('priority', 16)->default('medium'); // high|medium|low
            $table->decimal('qty_suggested_base', 14, 3)->default(0);
            $table->decimal('qty_current_base', 14, 3)->default(0);
            $table->decimal('qty_to_buy_base', 14, 3)->default(0);
            $table->string('qty_unit_label')->nullable();
            $table->string('unit_base', 16)->nullable();
            $table->decimal('unit_size', 10, 3)->nullable();
            $table->decimal('estimated_price', 14, 2)->default(0);
            $table->boolean('is_checked')->default(false);
            $table->string('barcode')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('sogar_shopping_list_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shopping_list_id')->constrained('sogar_shopping_lists')->cascadeOnDelete();
            $table->string('event');
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        Schema::create('sogar_consumption_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('sogar_food_products')->cascadeOnDelete();
            $table->date('occurred_on');
            $table->decimal('qty_base', 14, 3);
            $table->string('source', 24)->default('manual'); // stock_movement|manual
            $table->string('note')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'product_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sogar_consumption_logs');
        Schema::dropIfExists('sogar_shopping_list_events');
        Schema::dropIfExists('sogar_shopping_list_items');
        Schema::dropIfExists('sogar_shopping_lists');
    }
};
