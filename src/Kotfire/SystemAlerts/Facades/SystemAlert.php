<?php

namespace Kotfire\SystemAlerts\Facades;

use Illuminate\Support\Facades\Facade;

class SystemAlert extends Facade
{
    /**
     * Get the registered component.
     *
     * @return object
     */
    protected static function getFacadeAccessor()
    {
        return 'system-alert';
    }
}