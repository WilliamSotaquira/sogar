<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodType extends Model
{
    use HasFactory;

    protected $table = 'sogar_food_types';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'color',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(FoodProduct::class, 'type_id');
    }

    // Scope para queries frecuentes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
