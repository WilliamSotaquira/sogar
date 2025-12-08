<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sogar_food_products', function (Blueprint $table) {
            if (!Schema::hasColumn('sogar_food_products', 'image_url')) {
                $table->string('image_url')->nullable()->after('min_stock_qty');
            }
            if (!Schema::hasColumn('sogar_food_products', 'description')) {
                $table->text('description')->nullable()->after('image_url');
            }
            if (!Schema::hasColumn('sogar_food_products', 'image_path')) {
                $table->string('image_path')->nullable()->after('image_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sogar_food_products', function (Blueprint $table) {
            if (Schema::hasColumn('sogar_food_products', 'image_url')) {
                $table->dropColumn('image_url');
            }
            if (Schema::hasColumn('sogar_food_products', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('sogar_food_products', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};
