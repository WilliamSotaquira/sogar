<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletMovement extends Model
{
    use HasFactory;

    protected $table = 'sogar_wallet_movements';

    protected $fillable = [
        'wallet_id',
        'user_id',
        'category_id',
        'transaction_id',
        'amount',
        'occurred_on',
        'concept',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_on' => 'date',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
