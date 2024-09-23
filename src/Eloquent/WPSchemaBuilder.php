<?php

namespace Wenprise\Eloquent;

class WPSchemaBuilder
{
    /**
     * The database connection instance.
     *
     * @var \Wenprise\Eloquent\Connection
     */
    protected Connection $connection;

    /**
     * Create a new database Schema manager.
     *
     * @param \Wenprise\Eloquent\Connection $connection
     *
     * @return void
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }


    /**
     * Check table is exist
     *
     * @param string $table table name
     *
     * @return bool
     */
    public function hasTable(string $table): bool
    {
        $query  = "SHOW TABLES LIKE '$table'";
        $result = $this->connection->db->get_var($query);

        return $table === $result;
    }

    /**
     * get all table columns
     *
     * @param string $table table name
     *
     * @return array
     */
    public function getColumnListing(string $table): array
    {
        $table   = $this->connection->getTablePrefix() . $table;
        $columns = $this->connection->db->get_results("DESCRIBE $table");

        return wp_list_pluck($columns, 'Field');
    }


    /**
     * Get the columns for a given table.
     *
     * @param string $table
     *
     * @return array
     */
    public function getColumns(string $table): array
    {
        return $this->getColumnListing($table);
    }
}