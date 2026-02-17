<?php

namespace App\Core\Database;

use App\Core\App;
use App\Core\Contracts\Arrayable;
use App\Core\Database\Relations\HasRelationships;
use App\Core\Support\Str;
use App\Core\Support\Collection;
use PDO;
use RuntimeException;

abstract class Model
{
    use HasRelationships;
    /**
     * The table associated with the model.
     */
    protected static $table;

    /**
     * The primary key for the model.
     */
    protected static $primaryKey = 'id';

    /**
     * Indicates if the model should be timestamped.
     */
    public $timestamps = true;

    /**
     * The model's attributes.
     */
    protected $attributes = [];

    /**
     * The model attribute's original state.
     */
    protected $original = [];

    /**
     * The loaded relationships for the model.
     */
    protected $relations = [];

    /**
     * The attributes that are mass assignable.
     */
    protected array $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     */
    protected array $guarded = ['*'];

    /**
     * The attributes that should be hidden for arrays.
     */
    protected array $hidden = [];

    /**
     * The attributes that should be visible in arrays.
     */
    protected $visible = [];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [];

    /**
     * The cache of the mutated attributes for each class.
     *
     * @var array
     */
    protected static $mutatorCache = [];

    /**
     * Indicates whether attributes are snake cased on arrays.
     */
    public static $snakeAttributes = true;

    /**
     * Indicates if the model exists.
     */
    public $exists = false;

    /**
     * Indicates if the model was inserted during the current request lifecycle.
     */
    public $wasRecentlyCreated = false;

    /**
     * The database connection instance.
     */
    protected static $db;

    /**
     * Create a new model instance.
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public static function getTable()
    {
        return static::$table ?? Str::snake(Str::pluralStudly(class_basename(static::class)));
    }

    /**
     * Get the table qualified key name.
     * 
     * @return string
     */
    public function getQualifiedKeyName()
    {
        return $this->getTable() . '.' . $this->getKeyName();
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public static function getPrimaryKey()
    {
        return static::$primaryKey;
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return static::getPrimaryKey();
    }

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return Str::snake(class_basename($this)) . '_' . $this->getKeyName();
    }

    /**
     * Set the database connection instance.
     *
     * @param  Database  $db
     * @return void
     */
    public static function setDatabase(Database $db)
    {
        static::$db = $db;
    }

    /**
     * Get the database connection instance.
     *
     * @return Database
     */
    protected static function getDatabase()
    {
        if (!static::$db) {
            static::$db = App::getInstance()->database();
        }

        return static::$db;
    }

    /**
     * Get the current connection name for the model.
     *
     * @return string|null
     */
    public function getConnectionName()
    {
        return $this->connection ?? null;
    }

    /**
     * Set the connection associated with the model.
     *
     * @param  string  $name
     * @return $this
     */
    public function setConnection($name)
    {
        $this->connection = $name;
        return $this;
    }

    /**
     * Get the database connection for the model.
     *
     * @return Database
     */
    /**
     * Get the database connection for the model.
     *
     * @return \App\Core\Database\Database
     */
    public function getConnection()
    {
        return static::resolveConnection($this->getConnectionName());
    }

    /**
     * Resolve a connection instance.
     *
     * @param  string|null  $connection
     * @return Database
     */
    public static function resolveConnection($connection = null)
    {
        return static::getDatabase();
    }

    /**
     * Get a new query builder for the model's table.
     *
     * @return \App\Core\Database\Builder
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Get a new query builder that doesn't have any global scopes.
     *
     * @return \App\Core\Database\Builder
     */
    public function newQueryWithoutScopes()
    {
        $builder = $this->newEloquentBuilder(
            $this->newBaseQueryBuilder()
        );

        return $builder->setModel($this);
    }

    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \App\Core\Database\QueryBuilder  $query
     * @return \App\Core\Database\Builder
     */
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /**
     * Get a new query builder instance for the connection.
     *
     * @return \App\Core\Database\QueryBuilder
     */
    protected function newBaseQueryBuilder()
    {
        return $this->getConnection()->table($this->getTable());
    }

    /**
     * Register the global scopes for this builder instance.
     *
     * @param  \App\Core\Database\Builder  $builder
     * @return \App\Core\Database\Builder
     */
    public function registerGlobalScopes($builder)
    {
        foreach ($this->getGlobalScopes() as $identifier => $scope) {
            $builder->withGlobalScope($identifier, $scope);
        }

        return $builder;
    }

    /**
     * Get the global scopes for this class instance.
     *
     * @return array
     */
    public function getGlobalScopes()
    {
        return [];
    }

    /**
     * Fill the model with an array of attributes.
     */
    public function fill(array $attributes): self
    {
        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * Get the fillable attributes of a given array.
     */
    protected function fillableFromArray(array $attributes)
    {
        if (count($this->getFillable()) > 0) {
            return array_intersect_key($attributes, array_flip($this->getFillable()));
        }

        return $attributes;
    }

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * Determine if the given attribute may be mass assigned.
     */
    public function isFillable($key)
    {
        if (in_array($key, $this->getGuarded())) {
            return false;
        }

        return empty($this->getFillable()) || in_array($key, $this->getFillable());
    }

    /**
     * Get the guarded attributes for the model.
     */
    public function getGuarded()
    {
        return $this->guarded;
    }

    /**
     * Set a given attribute on the model.
     */
    public function setAttribute($key, $value): self
    {
        if ($this->hasSetMutator($key)) {
            $method = 'set' . Str::studly($key) . 'Attribute';
            return $this->{$method}($value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Determine if a set mutator exists for an attribute.
     */
    public function hasSetMutator($key)
    {
        return method_exists($this, 'set' . Str::studly($key) . 'Attribute');
    }

    /**
     * Get an attribute from the model.
     */
    public function getAttribute($key)
    {
        if (array_key_exists($key, $this->attributes) || $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        if (method_exists(self::class, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        return null;
    }

    /**
     * Get a plain attribute (not a relationship).
     */
    public function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        return $value;
    }

    /**
     * Get an attribute from the $attributes array.
     */
    protected function getAttributeFromArray($key)
    {
        return $this->attributes[$key] ?? null;
    }

    /**
     * Determine if a get mutator exists for an attribute.
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get' . Str::studly($key) . 'Attribute');
    }

    /**
     * Get the value of an attribute using its mutator.
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * Get all of the current attributes on the model.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get the model's original attribute values.
     */
    public function getOriginal(): array
    {
        return $this->original;
    }

    /**
     * Save the model to the database.
{{ ... }}
     */
    public function save(array $options = []): bool
    {
        if ($this->exists) {
            $saved = $this->performUpdate();
        } else {
            $saved = $this->performInsert();

            // Set the connection name if not already set
            if (!$this->getConnectionName()) {
                $this->setConnection('default');
            }
        }

        if ($saved) {
            $this->syncOriginal();
        }

        return $saved;
    }

    /**
     * Perform a model insert operation.
     */
    protected function performInsert(): bool
    {
        $attributes = $this->getAttributes();

        if ($this->timestamps) {
            $now = date('Y-m-d H:i:s');
            $this->setAttribute('created_at', $now);
            $this->setAttribute('updated_at', $now);

            $attributes = $this->getAttributes();
        }

        $result = static::query()->insert($attributes);

        if ($result) {
            $this->setAttribute(static::getPrimaryKey(), static::getDatabase()->lastInsertId());
            $this->exists = true;
            $this->syncOriginal();
        }

        return $result;
    }

    /**
     * Perform a model update operation.
     */
    protected function performUpdate(): bool
    {
        if ($this->timestamps) {
            $this->setAttribute('updated_at', date('Y-m-d H:i:s'));
        }

        $dirty = $this->getDirty();

        if (empty($dirty)) {
            return true;
        }

        $result = static::query()
            ->where(static::getPrimaryKey(), $this->getAttribute(static::getPrimaryKey()))
            ->update($dirty);

        if ($result) {
            $this->syncOriginal();
        }

        return $result > 0;
    }

    /**
     * Delete the model from the database.
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $result = static::query()
            ->where(static::getPrimaryKey(), $this->getAttribute(static::getPrimaryKey()))
            ->delete();

        if ($result) {
            $this->exists = false;
        }

        return $result > 0;
    }

    /**
     * Get the attributes that have been changed since last sync.
     */
    public function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Sync the original attributes with the current ones.
     */
    public function syncOriginal(): self
    {
        $this->original = $this->attributes;
        return $this;
    }

    /**
     * Find a model by its primary key.
     */
    public static function find($id, array $columns = ['*'])
    {
        $instance = new static();

        $model = static::query()
            ->select($columns)
            ->where(static::getPrimaryKey(), $id)
            ->first();

        if (!$model) {
            return null;
        }

        return $instance->newFromBuilder((array) $model);
    }

    /**
     * Find a model by its primary key or throw an exception.
     */
    public static function findOrFail($id, array $columns = ['*'])
    {
        $model = static::find($id, $columns);

        if (!$model) {
            throw new \RuntimeException("No query results for model [" . static::class . "]");
        }

        return $model;
    }

    /**
     * Get all of the models from the database.
     */
    public static function all(array $columns = ['*']): array
    {
        $instance = new static();

        $models = static::query()
            ->select($columns)
            ->get();

        return array_map(function ($model) use ($instance) {
            return $instance->newFromBuilder((array) $model);
        }, $models);
    }

    /**
     * Create a new Eloquent Collection instance.
     *
     * @param  array  $models
     * @return \App\Core\Support\Collection
     */
    public function newCollection(array $models = [])
    {
        return new Collection($models);
    }

    /**
     * Create a collection of models from plain arrays.
     *
     * @param  array  $items
     * @return \App\Core\Support\Collection
     */
    public function hydrate(array $items)
    {
        $instance = new static;

        $items = array_map(function ($item) use ($instance) {
            return $instance->newFromBuilder((array) $item);
        }, $items);

        return $instance->newCollection($items);
    }

    /**
     * Create a new model instance that is existing.
     */
    public function newFromBuilder(array $attributes = [])
    {
        $model = new static();
        $model->fill($attributes);
        $model->exists = true;
        $model->syncOriginal();

        return $model;
    }

    /**
     * Dynamically retrieve attributes on the model.
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Determine if an attribute exists on the model.
     */
    public function __isset($key)
    {
        return $this->offsetExists($key);
    }

    /**
     * Unset an attribute on the model.
     */
    public function __unset($key)
    {
        $this->offsetUnset($key);
    }

    /**
     * Determine if the given attribute exists.
     */
    public function offsetExists($offset)
    {
        return !is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     */
    public function offsetGet($offset)
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     */
    public function offsetSet($offset, $value)
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     */
    public function offsetUnset($offset)
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Convert the model to its string representation.
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Convert the model instance to JSON.
     */
    public function toJson($options = 0)
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Convert the model instance to an array.
     */
    public function toArray()
    {
        return $this->attributesToArray();
    }

    /**
     * Convert the model's attributes to an array.
     */
    public function attributesToArray()
    {
        $attributes = $this->getArrayableAttributes();

        $mutatedAttributes = $this->getMutatedAttributes();

        foreach ($mutatedAttributes as $key) {
            if (!array_key_exists($key, $attributes)) {
                continue;
            }

            $attributes[$key] = $this->mutateAttributeForArray(
                $key,
                $attributes[$key]
            );
        }

        foreach ($this->getArrayableRelations() as $key => $value) {
            if (in_array($key, $this->hidden)) {
                continue;
            }

            if ($value instanceof Arrayable) {
                $relation = $value->toArray();
            } elseif (is_null($value)) {
                $relation = $value;
            }

            $attributes[$key] = $relation ?? $value;

            if (isset($relation) && ($this->relationLoaded($key) || $value !== null)) {
                $attributes[$key] = $relation;
            }
        }

        return $attributes;
    }

    /**
     * Get an attribute array of all arrayable attributes.
     */
    protected function getArrayableAttributes()
    {
        return $this->getArrayableItems($this->attributes);
    }

    /**
     * Get all of the appendable values that are arrayable.
     */
    protected function getArrayableAppends()
    {
        if (!count($this->appends)) {
            return [];
        }

        $appends = array_combine($this->appends, $this->appends);

        return $this->getArrayableItems($appends);
    }

    /**
     * Get an attribute array of all arrayable relations.
     */
    protected function getArrayableRelations()
    {
        return $this->getArrayableItems($this->relations);
    }

    /**
     * Get an attribute array of all arrayable values.
     */
    protected function getArrayableItems(array $values)
    {
        if (count($this->visible) > 0) {
            $values = array_intersect_key($values, array_flip($this->visible));
        }

        foreach ($this->hidden as $hidden) {
            unset($values[$hidden]);
        }

        return $values;
    }


    /**
     * Get the mutated attributes for a given instance.
     */
    public function getMutatedAttributes()
    {
        $class = static::class;

        if (!isset(static::$mutatorCache[$class])) {
            static::cacheMutatedAttributes($class);
        }

        return static::$mutatorCache[$class];
    }

    /**
     * Extract and cache all the mutated attributes of a class.
     */
    public static function cacheMutatedAttributes($class)
    {
        $mutatedAttributes = [];

        if (preg_match_all('/(?<=^|;)get([^;]+?)Attribute(;|$)/', implode(';', get_class_methods($class)), $matches)) {
            foreach ($matches[1] as $match) {
                if (static::$snakeAttributes) {
                    $match = Str::snake($match);
                }

                $mutatedAttributes[] = lcfirst($match);
            }
        }

        static::$mutatorCache[$class] = $mutatedAttributes;
    }

    /**
     * Handle dynamic static method calls into the model.
     */
    public static function __callStatic($method, $parameters)
    {
        return static::query()->$method(...$parameters);
    }

    /**
     * Handle dynamic method calls into the model.
     */
    public function __call($method, $parameters)
    {
        return $this->newQuery()->$method(...$parameters);
    }

    /**
     * Get a new query builder for the model's table.
     */
    public function newQuery()
    {
        return $this->registerGlobalScopes($this->newQueryWithoutScopes());
    }
}
