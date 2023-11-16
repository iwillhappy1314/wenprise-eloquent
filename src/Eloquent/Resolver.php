<?php
namespace Wenprise\Eloquent;

use Illuminate\Database\ConnectionResolverInterface;

class Resolver implements ConnectionResolverInterface {

    /**
     * Get a database connection instance.
     *
     * @param  string $name
     *
     * @return false|\Wenprise\Eloquent\Database
     */
    public function connection( $name = null ) {
        return Database::instance();
    }

    /**
     * Get the default connection name.
     *
     * @return void
     */
    public function getDefaultConnection() {
        // TODO: Implement getDefaultConnection() method.
    }

    /**
     * Set the default connection name.
     *
     * @param  string $name
     *
     * @return void
     */
    public function setDefaultConnection( $name ) {
        // TODO: Implement setDefaultConnection() method.
    }
}