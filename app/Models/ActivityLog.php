<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'sogar_activity_logs';

    protected $fillable = [
        'activity_id',
        'user_id',
        'occurred_on',
        'occurred_at',
        'qty',
        'note',
        'meta',
    ];

    protected $casts = [
        'occurred_on' => 'date',
        'occurred_at' => 'datetime',
        'qty' => 'decimal:3',
        'meta' => 'array',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
