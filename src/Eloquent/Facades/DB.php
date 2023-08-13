<?php

namespace Wenprise\ORM\Eloquent\Facades;

use Illuminate\Support\Facades\Facade;
use Wenprise\ORM\Eloquent\Database;

/**
 * @see \Illuminate\Database\DatabaseManager
 * @see \Illuminate\Database\Connection
 */
class DB extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return false|\Wenprise\ORM\Eloquent\Database
     */
    protected static function getFacadeAccessor()
    {
        return Database::instance();
    }
}