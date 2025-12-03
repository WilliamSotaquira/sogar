<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingListItem extends Model
{
    use HasFactory;

    protected $table = 'sogar_shopping_list_items';

    protected $fillable = [
        'shopping_list_id',
        'product_id',
        'category_id',
        'location_id',
        'name',
        'priority',
        'qty_suggested_base',
        'qty_current_base',
        'qty_to_buy_base',
        'qty_unit_label',
        'unit_base',
        'unit_size',
        'estimated_price',
        'is_checked',
        'barcode',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'qty_suggested_base' => 'decimal:3',
        'qty_current_base' => 'decimal:3',
        'qty_to_buy_base' => 'decimal:3',
        'unit_size' => 'decimal:3',
        'estimated_price' => 'decimal:2',
        'is_checked' => 'bool',
        'metadata' => 'array',
    ];

    public function list()
    {
        return $this->belongsTo(ShoppingList::class, 'shopping_list_id');
    }

    public function product()
    {
        return $this->belongsTo(FoodProduct::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function location()
    {
        return $this->belongsTo(FoodLocation::class, 'location_id');
    }
}
