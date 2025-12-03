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
}
