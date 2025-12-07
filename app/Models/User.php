<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Integration;
use App\Models\Recurrence;
use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'active_family_group_id',
        'is_system_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_system_admin' => 'boolean',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    // Food module relations
    public function foodLocations()
    {
        return $this->hasMany(FoodLocation::class);
    }

    public function foodTypes()
    {
        return $this->hasMany(FoodType::class);
    }

    public function foodProducts()
    {
        return $this->hasMany(FoodProduct::class);
    }

    public function foodPurchases()
    {
        return $this->hasMany(FoodPurchase::class);
    }

    public function foodStockBatches()
    {
        return $this->hasMany(FoodStockBatch::class);
    }

    // Shopping lists
    public function shoppingLists()
    {
        return $this->hasMany(ShoppingList::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function recurrences()
    {
        return $this->hasMany(Recurrence::class);
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class);
    }

    // Family group relations
    /**
     * Get the family groups where this user is the administrator
     */
    public function administeredFamilyGroups()
    {
        return $this->hasMany(FamilyGroup::class, 'admin_user_id');
    }

    /**
     * Get all family groups this user belongs to
     */
    public function familyGroups()
    {
        return $this->belongsToMany(FamilyGroup::class, 'family_members')
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
     * Get the active family group for this user
     */
    public function activeFamilyGroup()
    {
        return $this->belongsTo(FamilyGroup::class, 'active_family_group_id');
    }

    /**
     * Get all family member pivot records for this user
     */
    public function familyMemberships()
    {
        return $this->hasMany(FamilyMember::class);
    }

    /**
     * Check if user belongs to a specific family group
     */
    public function belongsToFamilyGroup($familyGroupId)
    {
        return $this->familyGroups()->where('family_groups.id', $familyGroupId)->exists();
    }

    /**
     * Check if user is admin of a specific family group
     */
    public function isAdminOfFamilyGroup($familyGroupId)
    {
        return $this->familyGroups()
            ->where('family_groups.id', $familyGroupId)
            ->wherePivot('is_admin', true)
            ->exists();
    }

    /**
     * Get the user's role in a specific family group
     */
    public function getRoleInFamilyGroup($familyGroupId)
    {
        $membership = $this->familyGroups()
            ->where('family_groups.id', $familyGroupId)
            ->first();
        
        return $membership ? $membership->pivot->role : null;
    }

    /**
     * Check if user is a system administrator
     */
    public function isSystemAdmin(): bool
    {
        return $this->is_system_admin === true;
    }
}
