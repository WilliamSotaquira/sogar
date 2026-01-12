<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityGoal extends Model
{
    use HasFactory;

    protected $table = 'sogar_activity_goals';

    protected $fillable = [
        'activity_id',
        'user_id',
        'family_group_id',
        'goal_type',
        'target_value',
        'period',
        'starts_on',
        'ends_on',
        'is_active',
    ];

    protected $casts = [
        'target_value' => 'integer',
        'starts_on' => 'date',
        'ends_on' => 'date',
        'is_active' => 'bool',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class, 'activity_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function familyGroup()
    {
        return $this->belongsTo(FamilyGroup::class);
    }
}
