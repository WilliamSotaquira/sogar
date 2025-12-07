<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FamilyGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'admin_user_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the administrator of the family group
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_user_id');
    }

    /**
     * Get all members of the family group
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'family_members')
            ->withPivot([
                'role',
                'is_admin',
                'can_manage_finances',
                'can_manage_food',
                'can_manage_shopping',
                'joined_at'
            ])
            ->withTimestamps();
    }

    /**
     * Get family members with specific role
     */
    public function membersByRole($role)
    {
        return $this->members()->wherePivot('role', $role);
    }

    /**
     * Get all family member pivot records
     */
    public function familyMembers()
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * Check if a user is a member of this family group
     */
    public function hasMember($userId)
    {
        return $this->members()->where('user_id', $userId)->exists();
    }

    /**
     * Check if a user is an admin of this family group
     */
    public function isUserAdmin($userId)
    {
        return $this->members()
            ->where('user_id', $userId)
            ->wherePivot('is_admin', true)
            ->exists();
    }
}
