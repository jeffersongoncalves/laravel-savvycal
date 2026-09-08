<?php

namespace Jeffersongoncalves\Savvycal\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Jeffersongoncalves\Savvycal\Savvycal
 */
class Savvycal extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'laravel-savvycal';
    }
}
