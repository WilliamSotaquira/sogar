<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutineItem extends Model
{
    use HasFactory;

    protected $table = 'sogar_routine_items';

    protected $fillable = [
        'routine_id',
        'title',
        'group',
        'category',
        'start_time',
        'end_time',
        'weekdays_mask',
        'sort_order',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'weekdays_mask' => 'int',
        'sort_order' => 'int',
        'is_active' => 'bool',
        'meta' => 'array',
    ];

    public function routine()
    {
        return $this->belongsTo(Routine::class, 'routine_id');
    }

    public function logs()
    {
        return $this->hasMany(RoutineItemLog::class, 'routine_item_id');
    }

    /**
     * ISO-8601: Monday=1 ... Sunday=7.
     */
    public static function weekdayToMaskBit(int $isoWeekday): int
    {
        return match ($isoWeekday) {
            1 => 1 << 0,
            2 => 1 << 1,
            3 => 1 << 2,
            4 => 1 << 3,
            5 => 1 << 4,
            6 => 1 << 5,
            7 => 1 << 6,
            default => 0,
        };
    }

    public function appliesOnDate(CarbonInterface $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $bit = self::weekdayToMaskBit($date->isoWeekday());

        return ($this->weekdays_mask & $bit) === $bit;
    }

    protected function durationMinutes(): Attribute
    {
        return Attribute::get(function () {
            if (!$this->start_time || !$this->end_time) {
                return null;
            }

            // HH:MM:SS
            [$sh, $sm] = array_map('intval', explode(':', substr($this->start_time, 0, 5)));
            [$eh, $em] = array_map('intval', explode(':', substr($this->end_time, 0, 5)));

            return (($eh * 60 + $em) - ($sh * 60 + $sm));
        });
    }
}
