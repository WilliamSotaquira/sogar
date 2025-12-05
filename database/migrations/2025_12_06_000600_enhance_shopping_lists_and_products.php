<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Agregar campos para vincular listas de compras con presupuestos
        Schema::table('sogar_shopping_lists', function (Blueprint $table) {
            $table->foreignId('budget_id')->nullable()->after('user_id')->constrained('sogar_budgets')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->after('budget_id')->constrained('sogar_categories')->nullOnDelete();
            $table->decimal('actual_total', 14, 2)->default(0)->after('estimated_budget');
            $table->boolean('is_collaborative')->default(false)->after('status');
        });

        // Agregar campos de rendimiento a productos
        Schema::table('sogar_food_products', function (Blueprint $table) {
            $table->decimal('performance_index', 5, 2)->nullable()->after('min_stock_qty')
                ->comment('Índice de rendimiento/rentabilidad del producto');
            $table->decimal('avg_consumption_rate', 10, 3)->nullable()->after('performance_index')
                ->comment('Tasa promedio de consumo (unidades base por día)');
            $table->date('last_performance_calc')->nullable()->after('avg_consumption_rate');
        });

        // Agregar campos para alertas de precio en FoodPrice
        Schema::table('sogar_food_prices', function (Blueprint $table) {
            $table->decimal('price_change_percent', 8, 2)->nullable()->after('price_per_base')
                ->comment('Cambio porcentual respecto al precio anterior');
            $table->boolean('is_price_alert')->default(false)->after('price_change_percent')
                ->comment('Indica si este precio generó una alerta');
        });

        // Agregar campos de escáner mejorado a shopping_list_items
        Schema::table('sogar_shopping_list_items', function (Blueprint $table) {
            $table->decimal('actual_price', 14, 2)->nullable()->after('estimated_price')
                ->comment('Precio real pagado al marcar como comprado');
            $table->string('vendor_name')->nullable()->after('actual_price')
                ->comment('Nombre del proveedor donde se compró');
            $table->timestamp('checked_at')->nullable()->after('is_checked')
                ->comment('Fecha y hora cuando se marcó como comprado');
            $table->boolean('low_stock_alert')->default(false)->after('metadata')
                ->comment('Indica si tiene alerta de stock bajo');
        });
    }

    public function down(): void
    {
        Schema::table('sogar_shopping_lists', function (Blueprint $table) {
            $table->dropForeign(['budget_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['budget_id', 'category_id', 'actual_total', 'is_collaborative']);
        });

        Schema::table('sogar_food_products', function (Blueprint $table) {
            $table->dropColumn(['performance_index', 'avg_consumption_rate', 'last_performance_calc']);
        });

        Schema::table('sogar_food_prices', function (Blueprint $table) {
            $table->dropColumn(['price_change_percent', 'is_price_alert']);
        });

        Schema::table('sogar_shopping_list_items', function (Blueprint $table) {
            $table->dropColumn(['actual_price', 'vendor_name', 'checked_at', 'low_stock_alert']);
        });
    }
};
