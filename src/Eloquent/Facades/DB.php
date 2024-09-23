<?php

namespace Wenprise\Eloquent\Facades;

use Illuminate\Support\Facades\Facade;
use Wenprise\Eloquent\Connection;

/**
 * @see \Illuminate\Database\DatabaseManager
 * @see \Illuminate\Database\Connection
 */
class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return false|\Wenprise\Eloquent\Connection
     */
    protected static function getFacadeAccessor()
    {
        return Connection::instance();
    }
}