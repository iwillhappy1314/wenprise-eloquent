<?php

namespace Wenprise\Eloquent\Facades;

use Illuminate\Support\Facades\Facade;
use Wenprise\Eloquent\Database;

/**
 * @see \Illuminate\Database\DatabaseManager
 * @see \Illuminate\Database\Connection
 */
class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return false|\Wenprise\Eloquent\Database
     */
    protected static function getFacadeAccessor()
    {
        return Database::instance();
    }
}