<?php

namespace App\Core\Database;

use App\Core\App;
use App\Core\Contracts\Arrayable;
use App\Core\Database\Relations\HasRelationships;
use App\Core\Support\Str;
use App\Core\Support\Collection;
use PDO;
use RuntimeException;

if (!function_exists('class_basename')) {
    function class_basename($class) {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }
}

abstract class Model implements \ArrayAccess, \JsonSerializable
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table;

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected $connection;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be visible in arrays.
     *
     * @var array
     */
    protected $visible = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    /**
     * The model's attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * The model's original attributes.
     *
     * @var array
     */
    protected $original = [];

    /**
     * The attributes that were changed.
     *
     * @var array
     */
    protected $changes = [];

    /**
     * The attributes that are dirty.
     *
     * @var array
     */
    protected $dirty = [];

    /**
     * The loaded relationships for the model.
     *
     * @var array
     */
    protected $relations = [];

    /**
     * The connection resolver instance.
     *
     * @var \App\Core\Database\ConnectionResolverInterface
     */
    protected static $resolver;

    /**
     * The event dispatcher instance.
     *
     * @var \App\Core\Events\Dispatcher
     */
    protected static $dispatcher;

    /**
     * The array of booted models.
     *
     * @var array
     */
    protected static $booted = [];

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = false;

    /**
     * The cache of the mutated attributes for each class.
     *
     * @var array
     */
    protected static $mutatorCache = [];

    /**
     * The many to many relationship methods.
     *
     * @var array
     */
    public static $manyMethods = ['belongsToMany', 'morphToMany', 'morphedByMany'];

    /**
     * The name of the "created at" column.
     *
     * @var string|null
     */
    const CREATED_AT = 'created_at';

    /**
     * The name of the "updated at" column.
     *
     * @var string|null
     */
    const UPDATED_AT = 'updated_at';

    /**
     * The connection name for the model.
     *
     * @var string|null
     */
    protected static $db;

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->bootIfNotBooted();

        $this->syncOriginal();

        $this->fill($attributes);
    }

    /**
     * Boot the model if it's not booted yet.
     *
     * @return void
     */
    protected function bootIfNotBooted()
    {
        if (!isset(static::$booted[static::class])) {
            static::$booted[static::class] = true;

            static::boot();
        }
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        static::bootTraits();
    }

    /**
     * Boot all of the bootable traits on the model.
     *
     * @return void
     */
    protected static function bootTraits()
    {
        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            if (method_exists($class, $method = 'boot'.class_basename($trait))) {
                forward_static_call([$class, $method]);
            }
        }
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
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Set the database connection instance.
     *
     * @param  \App\Core\Database\Database  $db
     * @return void
     */
    public static function setConnection(Database $db)
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
            static::$db = App::getInstance()->db();
        }

        return static::$db;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if (isset($this->table)) {
            return $this->table;
        }

        return str_replace('\\', '', Str::snake(Str::plural(class_basename($this))));
    }

    /**
     * Get the primary key for the model.
     *
     * @return string
     */
    public function getKeyName()
    {
        return $this->primaryKey;
    }

    /**
     * Set the primary key for the model.
     *
     * @param  string  $key
     * @return void
     */
    public function setKeyName($key)
    {
        $this->primaryKey = $key;
    }

    /**
     * Get the value indicating whether the IDs are incrementing.
     *
     * @return bool
     */
    public function getIncrementing()
    {
        return $this->incrementing;
    }

    /**
     * Set whether IDs are incrementing.
     *
     * @param  bool  $value
     * @return void
     */
    public function setIncrementing($value)
    {
        $this->incrementing = $value;
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
     * Get a plain attribute (not a relationship).
     *
     * @param  string  $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        if (!$key) {
            return;
        }

        // If the attribute exists in the attribute array or has a "get" mutator.
        if (array_key_exists($key, $this->attributes) ||
            $this->hasGetMutator($key)) {
            return $this->getAttributeValue($key);
        }

        // If the key exists as a method, we'll assume the developer is accessing
        // a custom relationship method on the model.
        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }
    }

    /**
     * Get a plain attribute (not a relationship).
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeValue($key)
    {
        $value = $this->getAttributeFromArray($key);

        // If the attribute has a get mutator, we will call that then return what
        // it returns back to the caller as this is a convenient way to mutate
        // values as they are retrieved.
        if ($this->hasGetMutator($key)) {
            return $this->mutateAttribute($key, $value);
        }

        // If the attribute exists within the cast array, we will convert it to
        // the appropriate cast type before returning it.
        if ($this->hasCast($key)) {
            return $this->castAttribute($key, $value);
        }

        // If the attribute is listed as a date, we will convert it to a DateTime
        // instance on retrieval, which makes it quite convenient to work with.
        if (in_array($key, $this->getDates()) && !is_null($value)) {
            return $this->asDateTime($value);
        }

        return $value;
    }

    /**
     * Get an attribute from the $attributes array.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getAttributeFromArray($key)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        return null;
    }

    /**
     * Determine if a get mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasGetMutator($key)
    {
        return method_exists($this, 'get'.Str::studly($key).'Attribute');
    }

    /**
     * Get the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function mutateAttribute($key, $value)
    {
        return $this->{'get'.Str::studly($key).'Attribute'}($value);
    }

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        return $this->casts;
    }

    /**
     * Determine whether an attribute should be cast to a native type.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasCast($key)
    {
        return array_key_exists($key, $this->getCasts());
    }

    /**
     * Cast an attribute to a native PHP type.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        switch ($this->getCastType($key)) {
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
            case 'json':
                return $this->fromJson($value);
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
     * Get the type of cast for a model attribute.
     *
     * @param  string  $key
     * @return string
     */
    protected function getCastType($key)
    {
        return $this->casts[$key];
    }

    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates()
    {
        $defaults = [static::CREATED_AT, static::UPDATED_AT];

        return $this->timestamps ? array_merge($this->dates, $defaults) : $this->dates;
    }

    /**
     * Convert a DateTime to a storable string.
     *
     * @param  \DateTime|int  $value
     * @return string
     */
    public function fromDateTime($value)
    {
        return $this->asDateTime($value)->format($this->getDateFormat());
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return \DateTime
     */
    protected function asDateTime($value)
    {
        if ($value instanceof DateTime) {
            return $value;
        }

        if (is_numeric($value)) {
            return (new DateTime)->setTimestamp($value);
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value)) {
            return DateTime::createFromFormat('Y-m-d', $value)->startOfDay();
        }

        return new DateTime($value);
    }

    /**
     * Return a timestamp as DateTime object.
     *
     * @param  mixed  $value
     * @return int
     */
    protected function asTimestamp($value)
    {
        return $this->asDateTime($value)->getTimestamp();
    }

    /**
     * Get the format for database stored dates.
     *
     * @return string
     */
    protected function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    public function setAttribute($key, $value)
    {
        // First we will check for the presence of a mutator for the set operation
        // which simply lets the developers tweak the attribute as it is set on
        // the model, such as "json_decode" or any additional serialization.
        if ($this->hasSetMutator($key)) {
            return $this->setMutatedAttributeValue($key, $value);
        }

        // If an attribute is listed as a "date", we'll convert it from a DateTime
        // instance into a string for storage on the databases. We'll also set
        // the date format to be used for the model if it hasn't been set.
        elseif ($value && in_array($key, $this->getDates())) {
            $this->setDateAttributes($key, $value);
        }

        // If the attribute is listed as "json", we'll JSON encode it as it's
        // most likely an array or object being stored. Otherwise we'll just
        // store the raw value in the attribute array.
        else {
            $this->attributes[$key] = $value;
        }

        return $this;
    }

    /**
     * Determine if a set mutator exists for an attribute.
     *
     * @param  string  $key
     * @return bool
     */
    public function hasSetMutator($key)
    {
        return method_exists($this, 'set'.Str::studly($key).'Attribute');
    }

    /**
     * Set the value of an attribute using its mutator.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function setMutatedAttributeValue($key, $value)
    {
        return $this->{'set'.Str::studly($key).'Attribute'}($value);
    }

    /**
     * Set the date attributes.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    protected function setDateAttributes($key, $value)
    {
        $this->attributes[$key] = $this->fromDateTime($value);
    }

    /**
     * Get the relationships for the model.
     *
     * @return array
     */
    public function getRelations()
    {
        return $this->relations;
    }

    /**
     * Get a relationship.
     *
     * @param  string  $key
     * @return mixed
     */
    public function getRelation($key)
    {
        return $this->relations[$key];
    }

    /**
     * Determine if the given relation is loaded.
     *
     * @param  string  $key
     * @return bool
     */
    public function relationLoaded($key)
    {
        return array_key_exists($key, $this->relations);
    }

    /**
     * Set the specific relationship in the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return $this
     */
    public function setRelation($key, $value)
    {
        $this->relations[$key] = $value;

        return $this;
    }

    /**
     * Get the model's original attribute values.
     *
     * @param  string|null  $key
     * @param  mixed  $default
     * @return mixed|array
     */
    public function getOriginal($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->original;
        }

        return $this->original[$key] ?? $default;
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
     * Sync a single original attribute with its current value.
     *
     * @param  string  $attribute
     * @return $this
     */
    public function syncOriginalAttribute($attribute)
    {
        $this->original[$attribute] = $this->attributes[$attribute];

        return $this;
    }

    /**
     * Determine if the model or given attribute(s) have been modified.
     *
     * @param  array|string|null  $attributes
     * @return bool
     */
    public function isDirty($attributes = null)
    {
        if (is_null($attributes)) {
            return count($this->getDirty()) > 0;
        }

        $attributes = is_array($attributes) ? $attributes : func_get_args();

        foreach ($attributes as $attribute) {
            if ($this->isDirtyAttribute($attribute)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the given attribute was changed.
     *
     * @param  string  $attribute
     * @return bool
     */
    public function isDirtyAttribute($attribute)
    {
        return !array_key_exists($attribute, $this->original) ||
               $this->attributes[$attribute] !== $this->original[$attribute];
    }

    /**
     * Get the attributes that have been changed since last sync.
     *
     * @return array
     */
    public function getDirty()
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) ||
                $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    /**
     * Get the attributes that were changed.
     *
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * Determine if the model was given any attributes.
     *
     * @return bool
     */
    public function wasChanged($attributes = null)
    {
        if (is_null($attributes)) {
            return count($this->changes) > 0;
        }

        $attributes = is_array($attributes) ? $attributes : func_get_args();

        foreach ($attributes as $attribute) {
            if (array_key_exists($attribute, $this->changes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Save the model to the database.
     *
     * @return bool
     */
    public function save()
    {
        $saved = $this->exists ? $this->update() : $this->insert();

        if ($saved) {
            $this->syncOriginal();
            $this->fireModelEvent('saved', false);
        }

        return $saved;
    }

    /**
     * Insert a new record into the database.
     *
     * @return bool
     */
    public function insert()
    {
        $attributes = $this->getAttributes();

        $sql = "INSERT INTO {$this->getTable()} (" . implode(', ', array_keys($attributes)) . ") VALUES (" . implode(', ', array_fill(0, count($attributes), '?')) . ")";

        $stmt = static::getDatabase()->prepare($sql);

        foreach ($attributes as $key => $value) {
            $stmt->bindValue($key + 1, $value);
        }

        return $stmt->execute();
    }

    /**
     * Update the model in the database.
     *
     * @return bool
     */
    public function update()
    {
        $attributes = $this->getDirty();

        if (empty($attributes)) {
            return true;
        }

        $sql = "UPDATE {$this->getTable()} SET ";

        $set = [];
        foreach ($attributes as $key => $value) {
            $set[] = "$key = ?";
        }

        $sql .= implode(', ', $set) . " WHERE {$this->getKeyName()} = ?";

        $stmt = static::getDatabase()->prepare($sql);

        $i = 1;
        foreach ($attributes as $value) {
            $stmt->bindValue($i++, $value);
        }

        $stmt->bindValue($i, $this->getKey());

        return $stmt->execute();
    }

    /**
     * Delete the model from the database.
     *
     * @return bool
     */
    public function delete()
    {
        if ($this->exists) {
            $sql = "DELETE FROM {$this->getTable()} WHERE {$this->getKeyName()} = ?";

            $stmt = static::getDatabase()->prepare($sql);
            $stmt->bindValue(1, $this->getKey());

            return $stmt->execute();
        }

        return false;
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param  array  $columns
     * @return static|null
     */
    public static function find($id, $columns = ['*'])
    {
        $instance = new static;

        $sql = "SELECT " . implode(', ', $columns) . " FROM {$instance->getTable()} WHERE {$instance->getKeyName()} = ?";

        $stmt = static::getDatabase()->prepare($sql);
        $stmt->bindValue(1, $id);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $instance->fill($result);
            $instance->exists = true;
            return $instance;
        }

        return null;
    }

    /**
     * Get all of the models from the database.
     *
     * @param  array  $columns
     * @return static[]
     */
    public static function all($columns = ['*'])
    {
        $instance = new static;

        $sql = "SELECT " . implode(', ', $columns) . " FROM {$instance->getTable()}";

        $stmt = static::getDatabase()->prepare($sql);
        $stmt->execute();

        $models = [];

        while ($result = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $model = new static;
            $model->fill($result);
            $model->exists = true;
            $models[] = $model;
        }

        return $models;
    }

    /**
     * Convert the model instance to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * Convert the model instance to an array.
     *
     * @return array
     */
    public function toArray()
    {
        $attributes = $this->attributesToArray();

        return array_merge($attributes, $this->relationsToArray());
    }

    /**
     * Convert the model's attributes to an array.
     *
     * @return array
     */
    public function attributesToArray()
    {
        $attributes = $this->getArrayableAttributes();

        $attributes = $this->addCastAttributesToArray(
            $attributes, $this->getMutatedAttributes()
        );

        return $attributes;
    }

    /**
     * Get the model's attributes as a plain array.
     *
     * @return array
     */
    public function getArrayableAttributes()
    {
        return $this->getArrayableItems($this->attributes);
    }

    /**
     * Get the model's relationships as a plain array.
     *
     * @return array
     */
    public function relationsToArray()
    {
        $relations = $this->getArrayableRelations();

        return array_map(function ($values) {
            if ($values instanceof Arrayable) {
                return $values->toArray();
            }

            return $values;
        }, $relations);
    }

    /**
     * Get the model's relationships as a plain array.
     *
     * @return array
     */
    public function getArrayableRelations()
    {
        return $this->getArrayableItems($this->relations);
    }

    /**
     * Get an attribute array of all arrayable attributes.
     *
     * @param  array  $attributes
     * @return array
     */
    protected function getArrayableItems(array $attributes)
    {
        if (count($this->getVisible()) > 0) {
            $attributes = array_intersect_key($attributes, array_flip($this->getVisible()));
        }

        if (count($this->getHidden()) > 0) {
            $attributes = array_diff_key($attributes, array_flip($this->getHidden()));
        }

        return $attributes;
    }

    /**
     * Get the visible attributes for the model.
     *
     * @return array
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * Get the hidden attributes for the model.
     *
     * @return array
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Get the attributes that should be converted to dates.
     *
     * @return array
     */
    public function getDates()
    {
        $defaults = [static::CREATED_AT, static::UPDATED_AT];

        return $this->timestamps ? array_merge($this->dates, $defaults) : $this->dates;
    }

    /**
     * Get the mutated attributes for a given instance.
     *
     * @return array
     */
    public function getMutatedAttributes()
    {
        $class = static::class;

        if (!isset(static::$mutatorCache[$class])) {
            static::mutateAttributesForClass($class);
        }

        return static::$mutatorCache[$class];
    }

    /**
     * Extract and cache all the mutated attributes of a class.
     *
     * @param  string  $class
     * @return void
     */
    protected static function mutateAttributesForClass($class)
    {
        static::$mutatorCache[$class] = collect($class)->map(function ($value) {
            return preg_grep('/^get.+Attribute$/', get_class_methods($value));
        })->flatten()->reduce(function ($mutatedAttributes, $match) {
            if (preg_match('/^get(.+)Attribute$/', $match, $matches)) {
                $mutatedAttributes[] = Str::snake($matches[1]);
            }

            return $mutatedAttributes;
        }, []);
    }

    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        return $this->getAttribute($key);
    }

    /**
     * Dynamically set attributes on the model.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
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
     * Handle dynamic method calls into the model.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        if (in_array($method, static::$manyMethods)) {
            return $this->$method(...$parameters);
        }

        throw new RuntimeException("Method {$method} does not exist.");
    }

    /**
     * Handle dynamic static method calls into the method.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static;

        return $instance->$method(...$parameters);
    }
}
