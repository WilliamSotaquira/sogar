<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodLocation extends Model
{
    use HasFactory;

    protected $table = 'sogar_food_locations';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'color',
        'sort_order',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'bool',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(FoodProduct::class, 'default_location_id');
    }

    public function batches()
    {
        return $this->hasMany(FoodStockBatch::class, 'location_id');
    }
}
