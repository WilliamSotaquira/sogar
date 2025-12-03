<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodPrice extends Model
{
    use HasFactory;

    protected $table = 'sogar_food_prices';

    protected $fillable = [
        'product_id',
        'purchase_item_id',
        'source',
        'vendor',
        'currency',
        'price_per_base',
        'captured_on',
        'note',
    ];

    protected $casts = [
        'price_per_base' => 'decimal:4',
        'captured_on' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(FoodProduct::class, 'product_id');
    }

    public function purchaseItem()
    {
        return $this->belongsTo(FoodPurchaseItem::class, 'purchase_item_id');
    }
}
