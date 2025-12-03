<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodStockBatch extends Model
{
    use HasFactory;

    protected $table = 'sogar_food_stock_batches';

    protected $fillable = [
        'user_id',
        'product_id',
        'location_id',
        'purchase_item_id',
        'qty_base',
        'qty_remaining_base',
        'unit_base',
        'expires_on',
        'entered_on',
        'opened_at',
        'cost_total',
        'currency',
        'status',
    ];

    protected $casts = [
        'qty_base' => 'decimal:3',
        'qty_remaining_base' => 'decimal:3',
        'entered_on' => 'date',
        'expires_on' => 'date',
        'opened_at' => 'datetime',
        'cost_total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(FoodProduct::class, 'product_id');
    }

    public function location()
    {
        return $this->belongsTo(FoodLocation::class, 'location_id');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(FoodPurchaseItem::class, 'purchase_item_id');
    }

    public function movements()
    {
        return $this->hasMany(FoodStockMovement::class, 'batch_id');
    }
}
