<?php

namespace App\Core\Database;

use App\Core\Contracts\Arrayable;
use App\Core\Support\Str;
use App\Core\Support\Collection;
use App\Core\Database\Relations\HasRelationships;
use JsonSerializable;
use ArrayAccess;
use PDO;

abstract class Model implements ArrayAccess, JsonSerializable
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
     * Create a new model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->syncOriginal();
        $this->fill($attributes);
    }

    /**
     * Sync the original attributes with the current.
     *
     * @return $this
     */
    public function syncOriginal()
    {
        $this->original = $this->attributes;
        return $this;
    }

    /**
     * Fill the model with an array of attributes.
     *
     * @param  array  $attributes
     * @return $this
     */
    public function fill(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }
        return $this;
    }

    /**
     * Determine if the given attribute may be mass assigned.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isFillable($key)
    {
        if (static::$unguarded) return true;
        if (in_array($key, $this->fillable)) return true;
        if ($this->isGuarded($key)) return false;
        return empty($this->fillable) && strpos($key, '_') !== 0;
    }

    /**
     * Determine if the given key is guarded.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isGuarded($key)
    {
        return in_array($key, $this->guarded) || $this->guarded == ['*'];
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get an attribute from the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!$key) return null;
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
        return null;
    }

    /**
     * Begin querying the model.
     *
     * @return \App\Core\Database\Builder
     */
    public static function query()
    {
        $db = \App\Core\Database::getInstance();
        $table = (new static)->getTable();
        $queryBuilder = new QueryBuilder($db, $table);
        return (new Builder($queryBuilder))->setModel(new static);
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if (isset(static::$table)) return static::$table;
        return str_replace('\\', '', Str::snake(Str::plural(class_basename($this))));
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return static::$primaryKey;
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @return static|null
     */
    public static function find($id)
    {
        return static::query()->where(static::$primaryKey, '=', $id)->first();
    }

    /**
     * Get all of the models from the database.
     *
     * @return \App\Core\Support\Collection
     */
    public static function all()
    {
        return static::query()->get();
    }

    /**
     * Save a new model and return the instance.
     *
     * @param  array  $attributes
     * @return static
     */
    public static function create(array $attributes = [])
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Get the first record matching the attributes or create it.
     *
     * @param  array  $attributes
     * @param  array  $values
     * @return static
     */
    public static function firstOrCreate(array $attributes, array $values = [])
    {
        if (!is_null($instance = static::query()->where($attributes)->first())) {
            return $instance;
        }

        return static::create(array_merge($attributes, $values));
    }

    /**
     * Save the model to the database.
     *
     * @return bool
     */
    public function save()
    {
        $query = $this->newQuery();
        if ($this->exists()) {
            return $query->where($this->getKeyName(), '=', $this->attributes[$this->getKeyName()])->update($this->attributes);
        } else {
            $id = $query->insertGetId($this->attributes);
            if ($id) {
                $this->attributes[$this->getKeyName()] = $id;
                return true;
            }
            return false;
        }
    }

    /**
     * Determine if binary model exists.
     *
     * @return bool
     */
    public function exists()
    {
        return isset($this->attributes[$this->getKeyName()]);
    }

    /**
     * Create a new query builder for the model instance.
     *
     * @return \App\Core\Database\Builder
     */
    public function newQuery()
    {
        return static::query();
    }

    /**
     * Create a collection of models from raw results.
     *
     * @param  array  $items
     * @return \App\Core\Support\Collection
     */
    public function hydrate(array $items)
    {
        $models = array_map(function($item) {
            return new static($item);
        }, $items);
        return new Collection($models);
    }

    /**
     * Create a new instance of the given model.
     *
     * @param  array  $attributes
     * @param  bool  $exists
     * @return static
     */
    public function newInstance(array $attributes = [], $exists = false)
    {
        $model = new static($attributes);
        if ($exists) {
            $model->syncOriginal();
        }
        return $model;
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->attributes;
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return mixed
     */
    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Determine if the given offset exists.
     *
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset): bool
    {
        return isset($this->attributes[$offset]);
    }

    /**
     * Get the value for a given offset.
     *
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset): mixed
    {
        return $this->getAttribute($offset);
    }

    /**
     * Set the value for a given offset.
     *
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($offset, $value): void
    {
        $this->setAttribute($offset, $value);
    }

    /**
     * Unset the value for a given offset.
     *
     * @param  mixed  $offset
     * @return void
     */
    public function offsetUnset($offset): void
    {
        unset($this->attributes[$offset]);
    }

    /**
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->newQuery()->$method(...$parameters);
    }

    /**
     * Handle dynamic static method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        return static::query()->$method(...$parameters);
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
     * Get the table name and the primary key name.
     *
     * @return string
     */
    public function getQualifiedKeyName()
    {
        return $this->getTable() . '.' . $this->getKeyName();
    }
}
