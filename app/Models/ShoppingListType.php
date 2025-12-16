<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ShoppingListType extends Model
{
    use HasFactory;

    protected $table = 'sogar_shopping_list_types';

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'bool',
        'sort_order' => 'int',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function ensureDefaultsForUser(int $userId): void
    {
        $defaults = [
            ['name' => 'General', 'slug' => 'general', 'sort_order' => 10],
            ['name' => 'Alimentos', 'slug' => 'food', 'sort_order' => 20],
            ['name' => 'Aseo', 'slug' => 'cleaning', 'sort_order' => 30],
            ['name' => 'Mantenimiento/Arreglos', 'slug' => 'maintenance', 'sort_order' => 40],
            ['name' => 'Otro', 'slug' => 'other', 'sort_order' => 50],
        ];

        foreach ($defaults as $row) {
            static::firstOrCreate(
                ['user_id' => $userId, 'slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'sort_order' => $row['sort_order'],
                    'is_active' => true,
                ]
            );
        }

        // Ensure slugs are normalized for existing rows (best-effort)
        static::where('user_id', $userId)
            ->where(function ($q) {
                $q->whereNull('slug')->orWhere('slug', '');
            })
            ->get()
            ->each(function (ShoppingListType $type) use ($userId) {
                $base = Str::slug($type->name) ?: 'tipo';
                $slug = $base;
                $i = 2;
                while (static::where('user_id', $userId)->where('slug', $slug)->where('id', '!=', $type->id)->exists()) {
                    $slug = "{$base}-{$i}";
                    $i++;
                }
                $type->slug = $slug;
                $type->save();
            });
    }
}
