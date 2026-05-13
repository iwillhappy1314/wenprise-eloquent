<?php
namespace Wenprise\Eloquent;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\ConnectionResolverInterface;

class Resolver implements ConnectionResolverInterface {

    /**
     * Get a database connection instance.
     *
     * @param  string $name
     *
     * @return false|\Wenprise\Eloquent\Connection
     */
    public function connection($name = null): ConnectionInterface
    {
        return Connection::instance();
    }

    /**
     * Get the default connection name.
     *
     * @return void
     */
    public function getDefaultConnection(): string
    {
        return 'wpdb';
    }

    /**
     * Set the default connection name.
     *
     * @param  string $name
     *
     * @return void
     */
    public function setDefaultConnection($name): void
    {
        //
    }
}