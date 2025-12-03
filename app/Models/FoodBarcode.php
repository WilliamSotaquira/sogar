<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodBarcode extends Model
{
    use HasFactory;

    protected $table = 'sogar_food_barcodes';

    protected $fillable = [
        'product_id',
        'code',
        'kind',
    ];

    public function product()
    {
        return $this->belongsTo(FoodProduct::class, 'product_id');
    }
}
