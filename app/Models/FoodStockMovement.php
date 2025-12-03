<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodStockMovement extends Model
{
    use HasFactory;

    protected $table = 'sogar_food_stock_movements';

    protected $fillable = [
        'user_id',
        'product_id',
        'batch_id',
        'from_location_id',
        'to_location_id',
        'reason',
        'qty_delta_base',
        'occurred_on',
        'note',
    ];

    protected $casts = [
        'qty_delta_base' => 'decimal:3',
        'occurred_on' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(FoodProduct::class, 'product_id');
    }

    public function batch()
    {
        return $this->belongsTo(FoodStockBatch::class, 'batch_id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(FoodLocation::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(FoodLocation::class, 'to_location_id');
    }
}
