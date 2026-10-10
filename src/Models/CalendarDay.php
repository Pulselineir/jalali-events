<?php

namespace Pulseline\JalaliEvents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarDay extends Model
{
    protected $guarded = [];

    protected $casts = [
        'gregorian_date' => 'date:Y-m-d',
        'is_holiday'     => 'boolean',
        'synced_at'      => 'datetime',
        'is_weekend'     => 'boolean',
    ];

    public function getTable(): string
    {
        return config(
            'jalali-events.database.days_table',
            'jalali_calendar_days'
        );
    }

    public function getConnectionName(): ?string
    {
        return config('jalali-events.database.connection')
            ?: parent::getConnectionName();
    }

    public function events(): HasMany
    {
        return $this->hasMany(
            CalendarEvent::class,
            'calendar_day_id'
        );
    }
}
