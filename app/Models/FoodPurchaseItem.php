<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodPurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'sogar_food_purchase_items';

    protected $fillable = [
        'purchase_id',
        'product_id',
        'type_id',
        'location_id',
        'category_id',
        'budget_id',
        'qty',
        'unit',
        'unit_size',
        'unit_price',
        'subtotal',
        'expires_on',
    ];

    protected $casts = [
        'qty' => 'decimal:3',
        'unit_size' => 'decimal:3',
        'unit_price' => 'decimal:4',
        'subtotal' => 'decimal:2',
        'expires_on' => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(FoodPurchase::class, 'purchase_id');
    }

    public function product()
    {
        return $this->belongsTo(FoodProduct::class, 'product_id');
    }

    public function type()
    {
        return $this->belongsTo(FoodType::class, 'type_id');
    }

    public function location()
    {
        return $this->belongsTo(FoodLocation::class, 'location_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id');
    }
}
