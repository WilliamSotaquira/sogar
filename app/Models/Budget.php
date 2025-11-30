<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Budget extends Model
{
    use HasFactory;

    protected $table = 'sogar_budgets';

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'month',
        'year',
        'is_flexible',
        'sync_to_calendar',
        'provider_event_id',
        'last_synced_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_flexible' => 'bool',
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
}
