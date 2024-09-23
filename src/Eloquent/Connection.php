<?php

namespace Wenprise\Eloquent;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\Query\Expression;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Support\Arr;


class Connection implements ConnectionInterface
{

    public $db;

    public $dbh;

    /**
     * Count of active transactions
     *
     * @var int
     */
    public int $transactionCount = 0;


    /**
     * The database connection configuration options.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Get the database connection name.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->getConfig('name');
    }

    /**
     * Initializes the Database class
     *
     * @return \Wenprise\Eloquent\Connection
     */
    public static function instance()
    {
        static $instance = false;

        if ( ! $instance) {
            $instance = new self();
        }

        return $instance;
    }

    /**
     * [__construct description]
     */
    public function __construct()
    {
        global $wpdb;

        $this->config = [
            'name' => 'wpdb',
        ];

        $this->db = $wpdb;

        $this->dbh = (\Closure::bind(function ()
        {
            return $this->dbh;
        }, $this->db, 'wpdb'))();
    }


    /**
     * Get database name
     *
     * @return string
     */
    public function getDatabaseName(): string
    {
        return $this->db->dbname;
    }

    /**
     * Begin a fluent query against a database table.
     *
     *
     * @param \Closure|\Illuminate\Database\Query\Builder|string $table
     * @param null                                               $as *
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function table($table, $as = null): Builder
    {
        $processor = $this->getPostProcessor();

        $query = new Builder($this, $this->getQueryGrammar(), $processor);

        return $query->from($table);
    }

    /**
     * Get a new raw query expression.
     *
     * @param mixed $value
     *
     * @return \Illuminate\Database\Query\Expression
     */
    public function raw($value): Expression
    {
        return new Expression($value);
    }

    /**
     * Run a select statement and return a single result.
     *
     *
     * @param string $query
     * @param array  $bindings
     * @param true   $useReadPdo * @return mixed
     *
     * @throws QueryException
     *
     */
    public function selectOne($query, $bindings = [], $useReadPdo = true)
    {
        $query = $this->bind_params($query, $bindings);

        $result = $this->db->get_row($query);

        if ($result === false || $this->db->last_error) {
            throw new QueryException($query, $bindings, new \Exception($this->db->last_error));
        }

        return $result;
    }


    /**
     * Get a new query builder instance.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    public function query(): Builder
    {
        return new Builder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );
    }

    /**
     * Run a select statement against the database.
     *
     *
     * @param string $query
     * @param array  $bindings
     * @param true   $useReadPdo * @return array
     *
     * @throws QueryException
     *
     */
    public function select($query, $bindings = [], $useReadPdo = true)
    {
        $query = $this->bind_params($query, $bindings);

        $result = $this->db->get_results($query);

        if ($result === false || $this->db->last_error) {
            throw new QueryException($query, $bindings, new \Exception($this->db->last_error));
        }

        return $result;
    }


    /**
     * Run a select statement against the database.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return array
     */
    public function selectFromWriteConnection($query, $bindings = [])
    {
        return $this->select($query, $bindings, false);
    }


    /**
     * A hacky way to emulate bind parameters into SQL query
     *
     * @param      $query
     * @param      $bindings
     * @param bool $update
     *
     * @return array|string|string[]
     */
    private function bind_params($query, $bindings, $update = false)
    {

        $query    = str_replace('"', '`', $query);
        $bindings = $this->prepareBindings($bindings);

        if ( ! $bindings) {
            return $query;
        }

        $bindings = array_map(function ($replace)
        {
            if (is_string($replace)) {
                $replace = "'" . esc_sql($replace) . "'";
            } elseif ($replace === null) {
                $replace = "null";
            }

            return $replace;
        }, $bindings);

        $query = str_replace(['%', '?'], ['%%', '%s'], $query);

        return vsprintf($query, $bindings);
    }

    /**
     * Bind and run the query
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return array
     * @throws QueryException
     *
     */
    public function bind_and_run(string $query, array $bindings = []): array
    {
        $new_query = $this->bind_params($query, $bindings);

        $result = $this->db->query($new_query);

        if ($result === false || $this->db->last_error) {
            throw new QueryException($new_query, $bindings, new \Exception($this->db->last_error));
        }

        return (array)$result;
    }

    /**
     * Run an insert statement against the database.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return bool
     */
    public function insert($query, $bindings = []): bool
    {
        return $this->statement($query, $bindings);
    }

    /**
     * Run an update statement against the database.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int
     */
    public function update($query, $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Run a delete statement against the database.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int
     */
    public function delete($query, $bindings = []): int
    {
        return $this->affectingStatement($query, $bindings);
    }

    /**
     * Execute an SQL statement and return the boolean result.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return bool
     */
    public function statement($query, $bindings = []): bool
    {
        $new_query = $this->bind_params($query, $bindings, true);

        return $this->unprepared($new_query);
    }

    /**
     * Run an SQL statement and get the number of rows affected.
     *
     * @param string $query
     * @param array  $bindings
     *
     * @return int
     */
    public function affectingStatement($query, $bindings = []): int
    {
        $new_query = $this->bind_params($query, $bindings, true);

        $result = $this->db->query($new_query);

        if ($result === false || $this->db->last_error) {
            throw new QueryException($new_query, $bindings, new \Exception($this->db->last_error));
        }

        return intval($result);
    }

    /**
     * Run a raw, unprepared query against the PDO connection.
     *
     * @param string $query
     *
     * @return bool
     */
    public function unprepared($query): bool
    {
        $result = $this->db->query($query);

        return ($result === false || $this->db->last_error);
    }

    /**
     * Prepare the query bindings for execution.
     *
     * @param array $bindings
     *
     * @return array
     */
    public function prepareBindings(array $bindings): array
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {

            // Micro-optimization: check for scalar values before instances
            if (is_bool($value)) {
                $bindings[ $key ] = intval($value);
            } elseif (is_scalar($value)) {
                continue;
            } elseif ($value instanceof \DateTime) {
                // We need to transform all instances of the DateTime class into an actual
                // date string. Each query grammar maintains its own date string format
                // so we'll just ask the grammar for the format to get from the date.
                $bindings[ $key ] = $value->format($grammar->getDateFormat());
            }
        }

        return $bindings;
    }

    /**
     * Execute a Closure within a transaction.
     *
     * @param \Closure $callback
     * @param int      $attempts
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function transaction(\Closure $callback, $attempts = 1)
    {
        $this->beginTransaction();
        try {
            $data = $callback();
            $this->commit();

            return $data;
        } catch (\Exception $e) {
            $this->rollBack();
            throw $e;
        }
    }


    /**
     * +     * Get an option from the configuration options.
     * +     *
     * +     * @param string|null $option
     * +     * @return mixed
     * +     */
    public function getConfig(string $option = null)
    {
        return Arr::get($this->config, $option);
    }


    /**
     * Start a new database transaction.
     *
     * @return void
     */
    public function beginTransaction()
    {
        $transaction = $this->unprepared("START TRANSACTION;");
        if (false !== $transaction) {
            $this->transactionCount++;
        }
    }

    /**
     * Commit the active database transaction.
     *
     * @return void
     */
    public function commit()
    {
        if ($this->transactionCount < 1) {
            return;
        }
        $transaction = $this->unprepared("COMMIT;");
        if (false !== $transaction) {
            $this->transactionCount--;
        }
    }

    /**
     * Rollback the active database transaction.
     *
     * @return void
     */
    public function rollBack()
    {
        if ($this->transactionCount < 1) {
            return;
        }
        $transaction = $this->unprepared("ROLLBACK;");
        if (false !== $transaction) {
            $this->transactionCount--;
        }
    }

    /**
     * Get the number of active transactions.
     *
     * @return int
     */
    public function transactionLevel(): int
    {
        return $this->transactionCount;
    }

    /**
     * Execute the given callback in "dry run" mode.
     *
     * @param \Closure $callback
     *
     * @return void
     */
    public function pretend(\Closure $callback)
    {
        // TODO: Implement pretend() method.
    }

    public function getPostProcessor(): Processor
    {
        return new Processor();
    }

    public function getQueryGrammar(): Grammar
    {
        return new Grammar();
    }

    public function getSchemaBuilder()
    {
        return new WPSchemaBuilder($this);
    }

    public function getSchemaGrammar()
    {
        return new MySqlGrammar();
    }

    public function getTablePrefix(): string
    {
        return '';
    }

    /**
     * Return self as PDO
     *
     * @return \Wenprise\Eloquent\Connection
     */
    public function getPdo(): Connection
    {
        return $this;
    }

    /**
     * Return the last insert id
     *
     * @param string $args
     *
     * @return int
     */
    public function lastInsertId(string $args): int
    {
        return $this->db->insert_id;
    }


    /**
     * Run a select statement against the database and returns a generator.
     * TODO: Implement cursor and all the related sub-methods.
     *
     * @param string $query
     * @param array  $bindings
     * @param bool   $useReadPdo
     *
     * @return \Generator
     */
    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $query = $this->bind_params($query, $bindings);

        if ($result = mysqli_query($this->dbh, $query)) {
            while ($row = mysqli_fetch_object($result)) {
                yield $row;
            }
            mysqli_free_result($result);
        }
    }

}