<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recurrence extends Model
{
    use HasFactory;

    protected $table = 'sogar_recurrences';

    protected $fillable = [
        'user_id',
        'category_id',
        'wallet_id',
        'name',
        'amount',
        'frequency',
        'next_run_on',
        'last_run_at',
        'is_active',
        'sync_to_calendar',
        'provider_event_id',
        'last_synced_at',
        'note',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_run_on' => 'date',
        'last_run_at' => 'datetime',
        'is_active' => 'bool',
        'sync_to_calendar' => 'bool',
        'last_synced_at' => 'datetime',
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

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'recurrence_id');
    }
}
