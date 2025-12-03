<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsumptionLog extends Model
{
    use HasFactory;

    protected $table = 'sogar_consumption_logs';

    protected $fillable = [
        'user_id',
        'product_id',
        'occurred_on',
        'qty_base',
        'source',
        'note',
    ];

    protected $casts = [
        'qty_base' => 'decimal:3',
        'occurred_on' => 'date',
    ];
}
