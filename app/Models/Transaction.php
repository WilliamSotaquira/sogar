<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'sogar_transactions';

    protected $fillable = [
        'user_id',
        'category_id',
        'wallet_id',
        'recurrence_id',
        'amount',
        'occurred_on',
        'note',
        'origin',
        'tags',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'occurred_on' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function recurrence()
    {
        return $this->belongsTo(Recurrence::class, 'recurrence_id');
    }

    public function movements()
    {
        return $this->hasMany(WalletMovement::class, 'transaction_id');
    }
}
