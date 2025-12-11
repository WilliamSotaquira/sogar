<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Índices para sogar_food_products
        Schema::table('sogar_food_products', function (Blueprint $table) {
            // Búsqueda por user_id y barcode (query frecuente en quickStore)
            $table->index(['user_id', 'barcode'], 'idx_products_user_barcode');
            
            // Búsqueda por user_id y is_active
            $table->index(['user_id', 'is_active'], 'idx_products_user_active');
            
            // Búsqueda por type_id para filtrado
            $table->index('type_id', 'idx_products_type');
            
            // Ordenamiento por nombre (query muy frecuente)
            $table->index(['user_id', 'name'], 'idx_products_user_name');
        });

        // Índices para sogar_food_stock_batches
        Schema::table('sogar_food_stock_batches', function (Blueprint $table) {
            // Búsqueda por product_id y status (cálculo de stock)
            $table->index(['product_id', 'status'], 'idx_batches_product_status');
            
            // Búsqueda por location_id y status
            $table->index(['location_id', 'status'], 'idx_batches_location_status');
            
            // Búsqueda de productos próximos a vencer
            $table->index(['status', 'expires_on'], 'idx_batches_status_expires');
            
            // Para purchase_item_id (relación con compras)
            $table->index('purchase_item_id', 'idx_batches_purchase_item');
        });

        // Índices para sogar_food_prices
        Schema::table('sogar_food_prices', function (Blueprint $table) {
            // Búsqueda del último precio por producto
            $table->index(['product_id', 'captured_on', 'created_at'], 'idx_prices_product_date');
        });

        // Índices para sogar_shopping_lists
        Schema::table('sogar_shopping_lists', function (Blueprint $table) {
            // Búsqueda por user_id y status
            $table->index(['user_id', 'status'], 'idx_lists_user_status');
            
            // Ordenamiento por generated_at
            $table->index(['user_id', 'generated_at'], 'idx_lists_user_generated');
        });

        // Índices para sogar_shopping_list_items
        Schema::table('sogar_shopping_list_items', function (Blueprint $table) {
            // Búsqueda por shopping_list_id y is_checked
            $table->index(['shopping_list_id', 'is_checked'], 'idx_list_items_list_checked');
            
            // Para product_id
            $table->index('product_id', 'idx_list_items_product');
            
            // Para sort_order
            $table->index(['shopping_list_id', 'sort_order'], 'idx_list_items_list_sort');
        });

        // Índices para sogar_food_purchases
        Schema::table('sogar_food_purchases', function (Blueprint $table) {
            // Búsqueda por user_id ordenado por fecha
            $table->index(['user_id', 'occurred_on'], 'idx_purchases_user_date');
        });

        // Índices para sogar_food_purchase_items
        Schema::table('sogar_food_purchase_items', function (Blueprint $table) {
            // Búsqueda por purchase_id
            $table->index('purchase_id', 'idx_purchase_items_purchase');
            
            // Búsqueda por product_id
            $table->index('product_id', 'idx_purchase_items_product');
        });

        // Índices para sogar_food_types
        Schema::table('sogar_food_types', function (Blueprint $table) {
            // Búsqueda por user_id y is_active
            $table->index(['user_id', 'is_active', 'sort_order'], 'idx_types_user_active_sort');
        });

        // Índices para sogar_food_locations
        Schema::table('sogar_food_locations', function (Blueprint $table) {
            // Búsqueda por user_id ordenado
            $table->index(['user_id', 'sort_order'], 'idx_locations_user_sort');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sogar_food_products', function (Blueprint $table) {
            $table->dropIndex('idx_products_user_barcode');
            $table->dropIndex('idx_products_user_active');
            $table->dropIndex('idx_products_type');
            $table->dropIndex('idx_products_user_name');
        });

        Schema::table('sogar_food_stock_batches', function (Blueprint $table) {
            $table->dropIndex('idx_batches_product_status');
            $table->dropIndex('idx_batches_location_status');
            $table->dropIndex('idx_batches_status_expires');
            $table->dropIndex('idx_batches_purchase_item');
        });

        Schema::table('sogar_food_prices', function (Blueprint $table) {
            $table->dropIndex('idx_prices_product_date');
        });

        Schema::table('sogar_shopping_lists', function (Blueprint $table) {
            $table->dropIndex('idx_lists_user_status');
            $table->dropIndex('idx_lists_user_generated');
        });

        Schema::table('sogar_shopping_list_items', function (Blueprint $table) {
            $table->dropIndex('idx_list_items_list_checked');
            $table->dropIndex('idx_list_items_product');
            $table->dropIndex('idx_list_items_list_sort');
        });

        Schema::table('sogar_food_purchases', function (Blueprint $table) {
            $table->dropIndex('idx_purchases_user_date');
        });

        Schema::table('sogar_food_purchase_items', function (Blueprint $table) {
            $table->dropIndex('idx_purchase_items_purchase');
            $table->dropIndex('idx_purchase_items_product');
        });

        Schema::table('sogar_food_types', function (Blueprint $table) {
            $table->dropIndex('idx_types_user_active_sort');
        });

        Schema::table('sogar_food_locations', function (Blueprint $table) {
            $table->dropIndex('idx_locations_user_sort');
        });
    }
};
