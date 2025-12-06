<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShoppingList extends Model
{
    use HasFactory;

    protected $table = 'sogar_shopping_lists';

    protected $fillable = [
        'user_id',
        'budget_id',
        'category_id',
        'name',
        'list_type',
        'status',
        'generated_at',
        'expected_purchase_on',
        'estimated_budget',
        'actual_total',
        'is_collaborative',
        'people_count',
        'purchase_frequency_days',
        'safety_factor',
        'meta',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'expected_purchase_on' => 'date',
        'estimated_budget' => 'decimal:2',
        'actual_total' => 'decimal:2',
        'safety_factor' => 'decimal:2',
        'is_collaborative' => 'boolean',
        'meta' => 'array',
    ];

    public function items()
    {
        return $this->hasMany(ShoppingListItem::class, 'shopping_list_id')->orderBy('sort_order');
    }

    public function events()
    {
        return $this->hasMany(ShoppingListEvent::class, 'shopping_list_id');
    }

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
