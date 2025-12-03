<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sogar_food_products', function (Blueprint $table) {
            $table->string('image_url')->nullable()->after('min_stock_qty');
            $table->text('description')->nullable()->after('image_url');
            $table->string('image_path')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('sogar_food_products', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'description']);
        });
    }
};
