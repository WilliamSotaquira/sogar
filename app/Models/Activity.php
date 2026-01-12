<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $table = 'sogar_activities';

    protected $fillable = [
        'user_id',
        'family_group_id',
        'title',
        'description',
        'kind',
        'cadence',
        'target_count',
        'unit',
        'start_on',
        'end_on',
        'due_on',
        'is_active',
        'subject_type',
        'subject_id',
        'meta',
    ];

    protected $casts = [
        'target_count' => 'integer',
        'start_on' => 'date',
        'end_on' => 'date',
        'due_on' => 'date',
        'is_active' => 'bool',
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function familyGroup()
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function logs()
    {
        return $this->hasMany(ActivityLog::class, 'activity_id');
    }

    public function goals()
    {
        return $this->hasMany(ActivityGoal::class, 'activity_id');
    }

    public function activeGoal()
    {
        return $this->hasOne(ActivityGoal::class, 'activity_id')->where('is_active', true);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $familyIds = $user->familyGroupIds();

        return $query->where(function (Builder $q) use ($user, $familyIds) {
            $q->where('user_id', $user->id)
                ->orWhere(function (Builder $q2) use ($familyIds) {
                    $q2->whereNotNull('family_group_id')
                        ->whereIn('family_group_id', $familyIds);
                });
        });
    }
}
