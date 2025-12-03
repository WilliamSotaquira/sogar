<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodPurchase extends Model
{
    use HasFactory;

    protected $table = 'sogar_food_purchases';

    protected $fillable = [
        'user_id',
        'wallet_id',
        'occurred_on',
        'vendor',
        'receipt_number',
        'total',
        'currency',
        'note',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'occurred_on' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function items()
    {
        return $this->hasMany(FoodPurchaseItem::class, 'purchase_id');
    }
}
