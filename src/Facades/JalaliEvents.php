<?php

namespace Pulseline\JalaliEvents\Facades;

use Illuminate\Support\Facades\Facade;

class JalaliEvents extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'jalali-events';
    }
}
