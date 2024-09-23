<?php

namespace Wenprise\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

/**
 * Model Class
 *
 * @package Wenprise\Eloquent
 */
abstract class Model extends Eloquent
{
    /**
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        static::$resolver = new Resolver();

        parent::__construct($attributes);
    }

    /**
     * Get the database connection for the model.
     *
     * @return \Wenprise\Eloquent\Connection
     */
    public function getConnection(): Connection
    {
        return Connection::instance();
    }

    /**
     * Get the table associated with the model.
     *
     * Append the WordPress table prefix with the table name if
     * no table name is provided
     *
     * @return string
     */
    public function getTable(): string
    {
        $table  = $this->table ?? str_replace('\\', '', Str::snake(Str::plural(class_basename($this))));
        $prefix = $this->getConnection()->db->prefix;

        if ( ! Str::startsWith($table, $prefix)) {
            $table = $prefix . $table;
        }

        return $table;
    }


    /**
     * Replace the original hasMany function to forward the connection name.
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $localKey
     * @return HasMany
     */
    public function hasMany($related, $foreignKey = null, $localKey = null): HasMany
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $instance = $this->setInstanceConnection(new $related());

        $localKey = $localKey ?: $this->getKeyName();

        return new HasMany($instance->newQuery(), $this, $foreignKey, $localKey);
    }

    /**
     * Replace the original hasOne function to forward the connection name.
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $localKey
     * @return HasOne
     */
    public function hasOne($related, $foreignKey = null, $localKey = null): HasOne
    {
        $foreignKey = $foreignKey ?: $this->getForeignKey();

        $instance = $this->setInstanceConnection(new $related());

        $localKey = $localKey ?: $this->getKeyName();

        return new HasOne($instance->newQuery(), $this, $instance->getTable().'.'.$foreignKey, $localKey);
    }

    /**
     * Replace the original belongsTo function to forward the connection name.
     *
     * @param string $related
     * @param string $foreignKey
     * @param string $ownerKey
     * @param string $relation
     *
     * @return BelongsTo
     */
    public function belongsTo($related, $foreignKey = null, $ownerKey = null, $relation = null): BelongsTo
    {
        if (is_null($relation)) {
            [, $caller] = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
            $relation = $caller['function'];
        }

        if (is_null($foreignKey)) {
            $foreignKey = Str::snake($relation).'_id';
        }

        $instance = $this->setInstanceConnection(new $related());

        $query = $instance->newQuery();

        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return new BelongsTo($query, $this, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Replace the original belongsToMany function to forward the connection name.
     *
     * @param string $related
     * @param string $table
     * @param string $foreignPivotKey
     * @param string $relatedPivotKey
     * @param string $parentKey
     * @param string $relatedKey
     * @param string $relation
     * @return BelongsToMany
     */
    public function belongsToMany(
        $related,
        $table = null,
        $foreignPivotKey = null,
        $relatedPivotKey = null,
        $parentKey = null,
        $relatedKey = null,
        $relation = null
    ): BelongsToMany {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToManyRelation();
        }

        $instance = $this->setInstanceConnection($this->newRelatedInstance($related));

        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        if (is_null($table)) {
            $table = $this->joiningTable($related);
        }

        $table = $this->getConnection()->db->prefix.$table;

        return new BelongsToMany($instance->newQuery(), $this, $table, $foreignPivotKey, $relatedPivotKey, $parentKey ?: $this->getKeyName(), $relatedKey ?: $instance->getKeyName(), $relation);
    }

    /**
     * Get the relation value setting the connection name.
     *
     * @param string $key
     * @return mixed
     */
    public function getRelationValue($key)
    {
        $relation = parent::getRelationValue($key);

        if ($relation instanceof Collection) {
            $relation->each(function ($model) {
                $this->setRelationConnection($model);
            });

            return $relation;
        }

        $this->setRelationConnection($relation);

        return $relation;
    }

    /**
     * Set the connection name to model.
     *
     * @param $model
     */
    protected function setRelationConnection($model)
    {
        if ($model instanceof Eloquent) {
            $model->setConnection($this->getConnectionName());
        }
    }

    /**
     * @return string
     */
    public function getConnectionName(): string
    {
        return 'wpdb';
    }

    /**
     * @param $instance
     * @return mixed
     */
    protected function setInstanceConnection($instance)
    {
        return $instance->setConnection($instance instanceof self ? $this->getConnection()->getName() : $instance->getConnection()->getName());
    }
}
