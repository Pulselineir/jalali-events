<?php

namespace Pulseline\JalaliEvents\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    protected $guarded = [];

    public function getTable(): string
    {
        return config(
            'jalali-events.database.events_table',
            'jalali_events'
        );
    }

    public function getConnectionName(): ?string
    {
        return config('jalali-events.database.connection')
            ?: parent::getConnectionName();
    }

    public function day(): BelongsTo
    {
        return $this->belongsTo(
            CalendarDay::class,
            'calendar_day_id'
        );
    }
}
