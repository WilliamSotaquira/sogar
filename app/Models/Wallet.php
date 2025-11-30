<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $table = 'sogar_wallets';

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'initial_balance',
        'target_amount',
        'is_shared',
        'is_active',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'target_amount' => 'decimal:2',
        'is_shared' => 'bool',
        'is_active' => 'bool',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function movements()
    {
        return $this->hasMany(WalletMovement::class, 'wallet_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'wallet_id');
    }
}
