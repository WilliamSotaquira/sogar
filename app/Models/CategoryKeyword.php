<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryKeyword extends Model
{
    use HasFactory;

    protected $table = 'sogar_category_keywords';

    protected $fillable = [
        'user_id',
        'category_id',
        'keyword',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
