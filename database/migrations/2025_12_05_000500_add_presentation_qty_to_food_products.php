<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('sogar_food_products', function (Blueprint $table) {
            if (!Schema::hasColumn('sogar_food_products', 'presentation_qty')) {
                $table->decimal('presentation_qty', 12, 3)->nullable()->after('min_stock_qty');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sogar_food_products', function (Blueprint $table) {
            $table->dropColumn('presentation_qty');
        });
    }
};
