<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'family_group_id',
        'user_id',
        'role',
        'is_admin',
        'can_manage_finances',
        'can_manage_food',
        'can_manage_shopping',
        'joined_at',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'can_manage_finances' => 'boolean',
        'can_manage_food' => 'boolean',
        'can_manage_shopping' => 'boolean',
        'joined_at' => 'datetime',
    ];

    /**
     * Get the family group this member belongs to
     */
    public function familyGroup()
    {
        return $this->belongsTo(FamilyGroup::class);
    }

    /**
     * Get the user this family member represents
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if member has permission for a specific module
     */
    public function canManage($module)
    {
        return match($module) {
            'finances' => $this->can_manage_finances || $this->is_admin,
            'food' => $this->can_manage_food || $this->is_admin,
            'shopping' => $this->can_manage_shopping || $this->is_admin,
            default => $this->is_admin,
        };
    }

    /**
     * Get role label in Spanish
     */
    public function getRoleLabelAttribute()
    {
        return match($this->role) {
            'padre' => 'Padre',
            'madre' => 'Madre',
            'hijo' => 'Hijo',
            'hija' => 'Hija',
            default => 'Otro',
        };
    }
}
