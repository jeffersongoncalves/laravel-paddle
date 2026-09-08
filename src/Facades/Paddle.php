<?php

namespace JeffersonGoncalves\Paddle\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \JeffersonGoncalves\Paddle\Paddle
 */
class Paddle extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \JeffersonGoncalves\Paddle\Paddle::class;
    }
}
