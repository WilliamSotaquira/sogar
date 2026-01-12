<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutineItemLog extends Model
{
    use HasFactory;

    protected $table = 'sogar_routine_item_logs';

    protected $fillable = [
        'routine_item_id',
        'user_id',
        'occurred_on',
        'status',
        'occurred_at',
        'note',
        'meta',
    ];

    protected $casts = [
        'occurred_on' => 'date',
        'occurred_at' => 'datetime',
        'meta' => 'array',
    ];

    public function routineItem()
    {
        return $this->belongsTo(RoutineItem::class, 'routine_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
