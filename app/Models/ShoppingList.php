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
        'family_group_id',
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

    public function familyGroup()
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    // Scopes para queries frecuentes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeNotArchived($query)
    {
        return $query->whereNotIn('status', ['archived', 'cancelled']);
    }

    public function scopeRecent($query, $limit = 12)
    {
        return $query->orderByDesc('generated_at')->limit($limit);
    }

    public function scopeWithFullDetails($query)
    {
        return $query->with([
            'items' => fn($q) => $q->orderBy('sort_order'),
            'items.product:id,name,brand,unit_base,default_location_id',
            'items.product.defaultLocation:id,name',
            'items.location:id,name',
            'budget:id,category_id,month,year',
            'budget.category:id,name',
        ])->withCount([
            'items',
            'items as checked_items_count' => fn($q) => $q->where('is_checked', true),
        ]);
    }
}
