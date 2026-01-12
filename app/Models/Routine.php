<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    use HasFactory;

    protected $table = 'sogar_routines';

    protected $fillable = [
        'user_id',
        'family_group_id',
        'name',
        'description',
        'is_active',
        'meta',
    ];

    protected $casts = [
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

    public function items()
    {
        return $this->hasMany(RoutineItem::class, 'routine_id')->orderBy('start_time')->orderBy('sort_order')->orderBy('id');
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
