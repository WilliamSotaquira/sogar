<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodProduct extends Model
{
    use HasFactory;

    protected $table = 'sogar_food_products';

    protected $fillable = [
        'user_id',
        'type_id',
        'default_location_id',
        'name',
        'brand',
        'barcode',
        'unit_base',
        'unit_size',
        'shelf_life_days',
        'min_stock_qty',
        'performance_index',
        'avg_consumption_rate',
        'last_performance_calc',
        'presentation_qty',
        'notes',
        'is_active',
        'image_url',
        'image_path',
        'description',
    ];

    protected $casts = [
        'unit_size' => 'decimal:3',
        'min_stock_qty' => 'decimal:3',
        'presentation_qty' => 'decimal:3',
        'performance_index' => 'decimal:2',
        'avg_consumption_rate' => 'decimal:3',
        'last_performance_calc' => 'date',
        'is_active' => 'bool',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type()
    {
        return $this->belongsTo(FoodType::class, 'type_id');
    }

    public function defaultLocation()
    {
        return $this->belongsTo(FoodLocation::class, 'default_location_id');
    }

    public function barcodes()
    {
        return $this->hasMany(FoodBarcode::class, 'product_id');
    }

    public function batches()
    {
        return $this->hasMany(FoodStockBatch::class, 'product_id');
    }

    public function purchaseItems()
    {
        return $this->hasMany(FoodPurchaseItem::class, 'product_id');
    }

    public function prices()
    {
        return $this->hasMany(FoodPrice::class, 'product_id');
    }

    public function movements()
    {
        return $this->hasMany(FoodStockMovement::class, 'product_id');
    }

    // Scopes para queries frecuentes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeWithBarcode($query)
    {
        return $query->whereNotNull('barcode');
    }

    public function scopeByType($query, $typeId)
    {
        return $query->where('type_id', $typeId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('(
            SELECT COALESCE(SUM(qty_remaining_base), 0)
            FROM sogar_food_stock_batches
            WHERE product_id = sogar_food_products.id
            AND status = "ok"
        ) < min_stock_qty');
    }
}
