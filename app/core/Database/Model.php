<?php

namespace App\Core\Database;

use App\Core\Contracts\Arrayable;
use App\Core\Support\Str;
use App\Core\Support\Collection;
use PDO;

abstract class Model implements \ArrayAccess, \JsonSerializable
{
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
    protected $timestamps = true;

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
     * The attributes that should be hidden for arrays.
     */
    protected $hidden = [];

    /**
     * The attributes that should be visible in arrays.
     */
    protected $visible = [];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['*'];

    /**
     * The attributes that should be cast to native types.
     */
    protected $casts = [];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [];

    /**
     * Indicates whether attributes are snake cased on arrays.
     */
    protected static $snakeAttributes = true;

    /**
     * Indicates if all models are unguarded.
     */
    protected static $unguarded = false;

    /**
     * The array of booted models.
     */
    protected static $booted = [];

    /**
     * The array of global scopes on the model.
     */
    protected static $globalScopes = [];

    /**
     * The cache of the mutated attributes per class.
     */
    protected static $mutatorCache = [];

    /**
     * The array of attributes that can be mass assigned.
     */
    protected $dirty = [];

    /**
     * Indicates if the model exists.
     */
    public $exists = false;

    /**
     * The connection name for the model.
     */
    protected $connection;

    /**
     * The number of models to return for pagination.
     */
    protected $perPage = 15;

    /**
     * The relations to eager load on every query.
     */
    protected $with = [];

    /**
     * The relationship counts that should be eager loaded on every query.
     */
    protected $withCount = [];

    /**
     * The connection resolver instance.
     */
    protected static $resolver;

    /**
     * The event dispatcher instance.
     */
    protected static $dispatcher;

    /**
     * The array of booted callbacks.
     */
    protected static $bootedCallbacks = [];

    /**
     * The array of initializing callbacks.
     */
    protected static $initializingCallbacks = [];

    /**
     * The array of global scopes on the model.
     */
    protected static $scopes = [];

    /**
     * The array of eager loaded relations.
     */
    protected $relationsLoaded = [];

    /**
     * Create a new Eloquent model instance.
     */
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();

        $this->initializeTraits();

        $this->syncOriginal();

        $this->fill($attributes);
    }

    /**
     * Check if the model needs to be booted and if so, do it.
     */
    protected function bootIfNotBooted()
    {
        if (!isset(static::$booted[static::class])) {
            static::boot();

            static::$booted[static::class] = true;
        }
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        static::bootTraits();
    }

    /**
     * Boot all of the bootable traits on a model.
     */
    protected static function bootTraits()
    {
        $class = static::class;

        foreach (self::getClassUsesRecursive($class) as $trait) {
            if (method_exists($class, $method = 'boot' . self::getClassBasename($trait))) {
                forward_static_call([$class, $method]);
            }
        }
    }

    /**
     * Initialize any initializable traits on the model.
     */
    protected function initializeTraits()
    {
        // Trait initialization logic can be implemented here if needed
    }

    /**
     * Get all traits used by a class and its parents.
     */
    protected static function getClassUsesRecursive($class)
    {
        $traits = [];

        do {
            $traits = array_merge($traits, class_uses($class));
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait), $traits);
        }

        return array_unique($traits);
    }

    /**
     * Get the base name of a class.
     */
    protected static function getClassBasename($class)
    {
        return basename(str_replace('\\', '/', $class));
    }

    /**
     * Check if the model is unguarded.
     */
    protected function isUnguarded()
    {
        return static::$unguarded;
    }

    /**
     * Fill the model with an array of attributes.
     */
    public function fill(array $attributes): self
    {
        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Get the fillable attributes of a given array.
     */
    protected function fillableFromArray(array $attributes)
    {
        if (count($this->fillable) > 0 && !$this->isUnguarded()) {
            return array_intersect_key($attributes, array_flip($this->fillable));
        }

        return $attributes;
    }

    /**
     * Set a given attribute on the model.
     */
    public function setAttribute($key, $value)
    {
        if ($this->hasGetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get an attribute from the model.
     */
    public function getAttribute($key)
    {
        if (!$key) {
            return;
        }

        if (
            array_key_exists($key, $this->attributes) ||
            $this->hasGetMutator($key) ||
            $this->hasCast($key) ||
            $this->isClassCastable($key)
        ) {
            return $this->getAttributeValue($key);
        }

        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        return $this->getRelationValue($key);
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

        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
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
     * Determine if a set mutator exists for an attribute.
     */
    public function hasSetMutator($key)
    {
        return method_exists($this, 'set' . Str::studly($key) . 'Attribute');
    }

    /**
     * Get the value of an attribute using its mutator.
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * Set the value of an attribute using its mutator.
     */
    protected function setMutatedAttributeValue($key, $value)
    {
        return $this->{'set' . Str::studly($key) . 'Attribute'}($value);
    }

    /**
     * Determine if the given attribute is castable.
     */
    protected function hasCast($key)
    {
        return array_key_exists($key, $this->casts);
    }

    /**
     * Determine if the given attribute is class castable.
     */
    protected function isClassCastable($key)
    {
        return false;
    }

    /**
     * Cast an attribute to a native PHP type.
     */
    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        $cast = $this->casts[$key];

        if ($value instanceof $cast) {
            return $value;
        }

        switch ($cast) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'real':
            case 'float':
            case 'double':
                return (float) $value;
            case 'string':
                return (string) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'array':
                return $this->fromJson($value);
            case 'object':
                return json_decode($value);
            case 'date':
                return $this->asDate($value);
            case 'datetime':
                return $this->asDateTime($value);
            case 'timestamp':
                return $this->asTimestamp($value);
            default:
                return $value;
        }
    }

    /**
     * Get the value of the model's primary key.
     */
    public function getKey()
    {
        return $this->getAttribute($this->getKeyName());
    }

    /**
     * Get the primary key for the model.
     */
    public function getKeyName()
    {
        return static::$primaryKey;
    }

    /**
     * Get the table associated with the model.
     */
    public function getTable()
    {
        return static::$table ?: static::class;
    }

    /**
     * Get the table qualified name.
     */
    public function getQualifiedKeyName()
    {
        return $this->getTable() . '.' . $this->getKeyName();
    }

    /**
     * Determine if the model uses timestamps.
     */
    public function usesTimestamps()
    {
        return $this->timestamps;
    }

    /**
     * Get the hidden attributes for the model.
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Set the hidden attributes for the model.
     */
    public function setHidden(array $hidden)
    {
        $this->hidden = $hidden;

        return $this;
    }

    /**
     * Add hidden attributes for the model.
     */
    public function addHidden($attributes = null)
    {
        $this->hidden = array_merge(
            $this->hidden,
            is_array($attributes) ? $attributes : func_get_args()
        );

        return $this;
    }

    /**
     * Make the given attributes visible.
     */
    public function makeVisible($attributes)
    {
        $this->hidden = array_diff($this->hidden, (array) $attributes);

        if (!empty($this->visible)) {
            $this->addVisible($attributes);
        }

        return $this;
    }

    /**
     * Get the visible attributes for the model.
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Set the visible attributes for the model.
     */
    public function setVisible(array $visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Add visible attributes for the model.
     */
    public function addVisible($attributes = null)
    {
        $this->visible = array_merge(
            $this->visible,
            is_array($attributes) ? $attributes : func_get_args()
        );

        return $this;
    }

    /**
     * Get the fillable attributes for the model.
     */
    public function getFillable()
    {
        return $this->fillable;
    }

    /**
     * Set the fillable attributes for the model.
     */
    public function fillable(array $fillable)
    {
        $this->fillable = $fillable;

        return $this;
    }

    /**
     * Get the guarded attributes for the model.
     */
    public function getGuarded()
    {
        return $this->guarded;
    }

    /**
     * Set the guarded attributes for the model.
     */
    public function guard(array $guarded)
    {
        $this->guarded = $guarded;

        return $this;
    }

    /**
     * Determine if the given attribute may be mass assigned.
     */
    public function isFillable($key)
    {
        if (static::$unguarded) {
            return true;
        }

        if (in_array($key, $this->guarded)) {
            return false;
        }

        if (in_array('*', $this->guarded)) {
            return false;
        }

        return empty($this->fillable) || in_array($key, $this->fillable);
    }

    /**
     * Sync the original attributes with the current.
     */
    public function syncOriginal()
    {
        $this->original = $this->attributes;

        return $this;
    }

    /**
     * Sync a single original attribute with its current value.
     */
    public function syncOriginalAttribute($attribute)
    {
        $this->original[$attribute] = $this->attributes[$attribute];

        return $this;
    }

    /**
     * Determine if the model or a given attribute has been modified.
     */
    public function isDirty($attributes = null)
    {
        $dirty = $this->getDirty();

        if (is_null($attributes)) {
            return count($dirty) > 0;
        }

        if (!is_array($attributes)) {
            $attributes = func_get_args();
        }

        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $dirty)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the attributes that have been changed since last sync.
     */
    public function getDirty()
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original)) {
                $dirty[$key] = $value;
            } elseif (
                $value !== $this->original[$key] &&
                !$this->originalIsNumericallyEquivalent($key)
            ) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Determine if the new and old values for a given key are numerically equivalent.
     */
    protected function originalIsNumericallyEquivalent($key)
    {
        $current = $this->attributes[$key];

        $original = $this->original[$key];

        return is_numeric($current) && is_numeric($original) && strcmp((string) $current, (string) $original) === 0;
    }

    /**
     * Determine if the model was given any attributes.
     */
    public function wasChanged($attributes = null)
    {
        if (empty($attributes)) {
            return $this->getChanges();
        }

        $changes = $this->getChanges();

        if (!is_array($attributes)) {
            $attributes = func_get_args();
        }

        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $changes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the attributes that were changed.
     */
    public function getChanges()
    {
        return $this->getDirty();
    }

    /**
     * Get the original value of the given attribute.
     */
    public function getOriginal($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->original;
        }

        return $this->original[$key] ?? $default;
    }

    /**
     * Get the model's original attribute values.
     */
    public function getRawOriginal($key = null, $default = null)
    {
        return $this->getOriginal($key, $default);
    }

    /**
     * Determine if the model was recently created.
     */
    public function wasRecentlyCreated()
    {
        return $this->wasRecentlyCreated;
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
     * Determine if the given attribute exists.
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
    public function offsetExists(mixed $offset): bool
    {
        return !is_null($this->getAttribute($offset));
    }

    /**
     * Get the value for a given offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     */
    public function offsetUnset(mixed $offset): void
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
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
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

    /**
     * Get a new query builder that doesn't have any global scopes.
     */
    public function newQueryWithoutScopes()
    {
        return new QueryBuilder($this);
    }

    /**
     * Register the global scopes for this builder instance.
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
     */
    public function getGlobalScopes()
    {
        return static::$globalScopes;
    }

    /**
     * Begin querying the model.
     */
    public static function query()
    {
        return (new static)->newQuery();
    }

    /**
     * Get a new query builder for the model's table.
     */
    public function newQueryWithoutRelationships()
    {
        return $this->newQuery();
    }

    /**
     * Create a new model instance.
     */
    public function newInstance($attributes = [])
    {
        return new static($attributes);
    }

    /**
     * Determine if a relation is loaded.
     */
    public function relationLoaded($key)
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Get the value of the model's route key.
     */
    public function getRouteKey()
    {
        return $this->getAttribute($this->getRouteKeyName());
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName()
    {
        return $this->getKeyName();
    }

    /**
     * Retrieve the model for a bound value.
     */
    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === null) {
            $field = $this->getRouteKeyName();
        }

        return $this->where($field, $value)->first();
    }

    /**
     * Retrieve the child model for a bound value.
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        return $this->resolveRouteBinding($value, $field);
    }

    /**
     * Get the table name for the model.
     */
    public function getTableName()
    {
        if (isset(static::$table)) {
            return static::$table;
        }

        return str_replace('\\', '', Str::snake(Str::plural(self::getClassBasename(static::class))));
    }

    /**
     * Get the foreign key for the model.
     */
    public function getForeignKey()
    {
        return Str::snake(self::getClassBasename(static::class)) . '_id';
    }

    /**
     * Get the connection for the model.
     */
    public function getConnection()
    {
        return static::resolveConnection($this->connection);
    }

    /**
     * Get the current connection name for the model.
     */
    public function getConnectionName()
    {
        return $this->connection;
    }

    /**
     * Set the connection associated with the model.
     */
    public function setConnection($name)
    {
        $this->connection = $name;

        return $this;
    }

    /**
     * Resolve a connection instance.
     */
    public static function resolveConnection($connection = null)
    {
        return static::$resolver->connection($connection);
    }

    /**
     * Get the connection resolver instance.
     */
    public static function getConnectionResolver()
    {
        return static::$resolver;
    }

    /**
     * Set the connection resolver instance.
     */
    public static function setConnectionResolver($resolver)
    {
        static::$resolver = $resolver;
    }

    /**
     * Unset the connection resolver for models.
     */
    public static function unsetConnectionResolver()
    {
        static::$resolver = null;
    }

    /**
     * Get a fresh timestamp for the model.
     */
    public function freshTimestamp()
    {
        return new \DateTime();
    }

    /**
     * Get a fresh timestamp for the model.
     */
    public function freshTimestampString()
    {
        return $this->freshTimestamp()->format($this->getDateFormat());
    }

    /**
     * Get the format for database stored dates.
     */
    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Set the date format.
     */
    public function setDateFormat($format)
    {
        $this->dateFormat = $format;

        return $this;
    }

    /**
     * Get the attributes that should be converted to dates.
     */
    public function getDates()
    {
        return $this->dates ?? [];
    }

    /**
     * Set the attributes that should be converted to dates.
     */
    public function setDates(array $dates)
    {
        $this->dates = $dates;

        return $this;
    }
}

// Simple QueryBuilder for basic functionality
class QueryBuilder
{
    protected $model;
    protected $query;
    protected $bindings = [];
    protected $wheres = [];

    public function __construct($model)
    {
        $this->model = $model;
        $this->query = "SELECT * FROM " . $model->getTable();
    }

    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        if ($operator !== null && $value === null && func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];

        return $this;
    }

    public function first()
    {
        $sql = $this->query;
        $params = [];

        if (!empty($this->wheres)) {
            $whereClause = [];
            foreach ($this->wheres as $where) {
                $whereClause[] = $where['column'] . ' ' . $where['operator'] . ' ?';
                $params[] = $where['value'];
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClause);
        }

        $sql .= ' LIMIT 1';

        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return $this->model->newInstance()->fill($result);
        }

        return null;
    }

    public function get()
    {
        $sql = $this->query;
        $params = [];

        if (!empty($this->wheres)) {
            $whereClause = [];
            foreach ($this->wheres as $where) {
                $whereClause[] = $where['column'] . ' ' . $where['operator'] . ' ?';
                $params[] = $where['value'];
            }
            $sql .= ' WHERE ' . implode(' AND ', $whereClause);
        }

        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $models = [];
        foreach ($results as $result) {
            $models[] = $this->model->newInstance()->fill($result);
        }

        return new Collection($models);
    }

    public function create(array $attributes)
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Save the model to the database
     */
    public function save()
    {
        $this->insert();
    }

    /**
     * Insert the model into the database
     */
    public function insert()
    {
        // Implementation would go here
        // For now, this is a placeholder
    }
}
