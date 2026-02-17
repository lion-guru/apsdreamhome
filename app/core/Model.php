<?php

namespace App\Core;

use PDO;
use PDOException;

abstract class Model
{
    /**
     * @var string The database table name
     */
    protected static $table;

    /**
     * @var string The primary key for the table
     */
    protected static $primaryKey = 'id';

    /**
     * @var array The model's attributes
     */
    protected array $attributes = [];

    /**
     * @var bool Indicates if the model exists in the database
     */
    protected bool $exists = false;

    /**
     * @var array The model's fillable attributes
     */
    protected array $fillable = [];

    /**
     * @var array The model's hidden attributes
     */
    protected array $hidden = [];

    /**
     * @var array The model's default attribute values
     */
    protected array $attributesDefault = [];

    /**
     * @var array The query where conditions
     */
    protected array $wheres = [];

    /**
     * @var array The query order by clauses
     */
    protected array $orders = [];

    /**
     * @var int|null The query limit
     */
    protected ?int $limit = null;

    /**
     * @var int|null The query offset
     */
    protected ?int $offset = null;

    /**
     * @var array The original attributes for dirty checking
     */
    protected array $original = [];

    /**
     * @var PDO The database connection
     */
    protected static $connection;

    /**
     * @var PDO The database connection instance for this model
     */
    protected $db;

    /**
     * Create a new model instance
     */
    public function __construct(array $attributes = [])
    {
        $this->wheres = [];
        $this->db = static::getConnection();
        $this->fill($attributes);
    }

    /**
     * Get the database connection
     */
    protected static function getConnection(): PDO
    {
        if (!static::$connection) {
            // Use the global Database instance to share connection pool
            static::$connection = \App\Core\Database::getInstance()->getConnection();
        }

        return static::$connection;
    }

    /**
     * Get the table name for the model
     */
    protected static function getTableName(): string
    {
        if (static::$table) {
            return static::$table;
        }

        // Convert class name to snake_case and pluralize
        $className = (new \ReflectionClass(static::class))->getShortName();
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className)) . 's';
    }

    /**
     * Fill the model with an array of attributes
     */
    public function fill(array $attributes): self
    {
        foreach ($this->fillableFromArray($attributes) as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    /**
     * Get the fillable attributes from the given array
     */
    protected function fillableFromArray(array $attributes): array
    {
        if (count($this->fillable) > 0) {
            return array_intersect_key($attributes, array_flip($this->fillable));
        }

        return $attributes;
    }

    /**
     * Set a given attribute on the model
     */
    public function setAttribute($key, $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Get an attribute from the model
     */
    public function getAttribute($key, $default = null)
    {
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        // Check for accessor method
        $method = 'get' . str_replace('_', '', ucwords($key, '_')) . 'Attribute';
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        return $default;
    }

    /**
     * Get all of the current attributes on the model
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Get the model's hidden attributes
     */
    public function getHidden(): array
    {
        return $this->hidden;
    }

    /**
     * Convert the model's attributes to an array
     */
    public function toArray(): array
    {
        $attributes = $this->getAttributes();

        // Remove hidden attributes
        foreach ($this->getHidden() as $hidden) {
            unset($attributes[$hidden]);
        }

        // Convert any nested objects to arrays
        foreach ($attributes as $key => $value) {
            if (is_object($value) && method_exists($value, 'toArray')) {
                $attributes[$key] = $value->toArray();
            }
        }

        return $attributes;
    }

    /**
     * Convert the model to JSON
     */
    public function toJson(int $options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Save the model to the database
     */
    public function save(): bool
    {
        return $this->exists ? $this->performUpdate() : $this->performInsert();
    }

    /**
     * Perform a model insert operation
     */
    protected function performInsert(): bool
    {
        $attributes = $this->getAttributes();

        // Set timestamps if they exist
        if ($this->usesTimestamps()) {
            $this->setTimestamps();
            $attributes = $this->getAttributes();
        }

        $columns = array_keys($attributes);
        $values = array_values($attributes);

        $placeholders = array_fill(0, count($columns), '?');

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            static::getTableName(),
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $stmt = static::getConnection()->prepare($sql);
        $result = $stmt->execute($values);

        if ($result) {
            $this->exists = true;
            $this->setAttribute(static::$primaryKey, static::getConnection()->lastInsertId());
            return true;
        }

        return false;
    }

    /**
     * Perform a model update operation
     */
    protected function performUpdate(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $attributes = $this->getDirty();

        if (empty($attributes)) {
            return true;
        }

        // Update timestamps if they exist
        if ($this->usesTimestamps()) {
            $this->updateTimestamps();
            $attributes = array_merge($attributes, $this->getTimestamps());
        }

        $columns = array_keys($attributes);
        $values = array_values($attributes);

        $setClause = implode(' = ?, ', $columns) . ' = ?';

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = ?',
            static::getTableName(),
            $setClause,
            static::$primaryKey
        );

        // Add the primary key value to the values array
        $values[] = $this->getAttribute(static::$primaryKey);

        $stmt = static::getConnection()->prepare($sql);
        return $stmt->execute($values);
    }

    /**
     * Delete the model from the database
     */
    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $sql = sprintf(
            'DELETE FROM %s WHERE %s = ?',
            static::getTableName(),
            static::$primaryKey
        );

        $stmt = static::getConnection()->prepare($sql);
        $result = $stmt->execute([$this->getAttribute(static::$primaryKey)]);

        if ($result) {
            $this->exists = false;
            return true;
        }

        return false;
    }

    /**
     * Get the attributes that have been changed since the last sync
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
     * Determine if the model uses timestamps
     */
    public function usesTimestamps(): bool
    {
        return property_exists($this, 'timestamps') && $this->timestamps === true;
    }

    /**
     * Set the creation and update timestamps
     */
    protected function setTimestamps(): void
    {
        $time = $this->freshTimestamp();

        if (!$this->getAttribute('created_at')) {
            $this->setAttribute('created_at', $time);
        }

        $this->setAttribute('updated_at', $time);
    }

    /**
     * Update the creation and update timestamps
     */
    protected function updateTimestamps(): void
    {
        $time = $this->freshTimestamp();
        $this->setAttribute('updated_at', $time);
    }

    /**
     * Get the current timestamp
     */
    public function freshTimestamp(): string
    {
        return date('Y-m-d H:i:s');
    }

    /**
     * Get the timestamps
     */
    protected function getTimestamps(): array
    {
        return [
            'created_at' => $this->getAttribute('created_at'),
            'updated_at' => $this->getAttribute('updated_at')
        ];
    }

    /**
     * Find a model by its primary key
     */
    public static function find($id)
    {
        $instance = new static();

        $sql = sprintf(
            'SELECT * FROM %s WHERE %s = ? LIMIT 1',
            static::getTableName(),
            static::$primaryKey
        );

        $stmt = static::getConnection()->prepare($sql);
        $stmt->execute([$id]);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return static::hydrate($row);
        }

        return null;
    }

    /**
     * Get all records
     */
    public static function all()
    {
        $instance = new static();

        $sql = sprintf('SELECT * FROM %s', static::getTableName());
        $stmt = static::getConnection()->query($sql);

        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = static::hydrate($row);
        }

        return $results;
    }

    /**
     * Create a new record
     */
    public static function create(array $attributes)
    {
        $model = new static($attributes);
        $model->save();
        return $model;
    }

    /**
     * Begin querying the model
     */
    public static function query()
    {
        return new static();
    }

    /**
     * Add a basic where clause to the query
     */
    protected function addWhere($column, $operator = null, $value = null, $boolean = 'AND')
    {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];

        return $this;
    }

    /**
     * Add a basic where clause to the query (public wrapper)
     */
    public function where($column, $operator = null, $value = null, $boolean = 'AND')
    {
        // Handle dynamic where clauses (when operator is the value and operator defaults to '=')
        if ($operator !== null && $value === null && func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        return $this->addWhere($column, $operator, $value, $boolean);
    }

    /**
     * Static method to create a query with where clause
     */
    public static function whereStatic($column, $operator = null, $value = null, $boolean = 'AND')
    {
        $instance = new static();
        // Handle dynamic where clauses (when operator is the value and operator defaults to '=')
        if ($operator !== null && $value === null && func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }
        return $instance->where($column, $operator, $value, $boolean);
    }

    /**
     * Add an array of where clauses to the query
     */
    protected function addArrayOfWheres(array $wheres, $boolean = 'and')
    {
        foreach ($wheres as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                $this->addWhere(...array_values($value));
            } else {
                $this->addWhere($key, '=', $value, $boolean);
            }
        }

        return $this;
    }

    /**
     * Add an order by clause to the query
     */
    public function orderBy($column, $direction = 'ASC')
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtoupper($direction)
        ];
        return $this;
    }

    /**
     * Set the query limit
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the query offset
     */
    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Get the first record matching the query
     */
    public function first()
    {
        $sql = sprintf('SELECT * FROM %s', static::getTableName());
        $params = [];

        if (!empty($this->wheres)) {
            $whereClause = [];

            foreach ($this->wheres as $where) {
                $whereClause[] = sprintf('%s %s ?', $where['column'], $where['operator']);
                $params[] = $where['value'];
            }

            $sql .= ' WHERE ' . implode(' AND ', $whereClause);
        }

        if (!empty($this->orders)) {
            $orderClause = [];
            foreach ($this->orders as $order) {
                $orderClause[] = sprintf('%s %s', $order['column'], $order['direction']);
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClause);
        }

        $sql .= ' LIMIT 1';

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        $stmt = static::getConnection()->prepare($sql);
        $stmt->execute($params);

        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return static::hydrate($row);
        }

        return null;
    }

    /**
     * Get the results of the query
     */
    public function get()
    {
        $sql = sprintf('SELECT * FROM %s', static::getTableName());
        $params = [];

        if (!empty($this->wheres)) {
            $whereClause = [];

            foreach ($this->wheres as $where) {
                $whereClause[] = sprintf('%s %s ?', $where['column'], $where['operator']);
                $params[] = $where['value'];
            }

            $sql .= ' WHERE ' . implode(' AND ', $whereClause);
        }

        if (!empty($this->orders)) {
            $orderClause = [];
            foreach ($this->orders as $order) {
                $orderClause[] = sprintf('%s %s', $order['column'], $order['direction']);
            }
            $sql .= ' ORDER BY ' . implode(', ', $orderClause);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        $stmt = static::getConnection()->prepare($sql);
        $stmt->execute($params);

        $results = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = static::hydrate($row);
        }

        return $results;
    }

    /**
     * Handle dynamic method calls
     */
    public function __call($method, $parameters)
    {
        // Handle dynamic where methods (e.g., whereName('John'))
        if (strpos($method, 'where') === 0) {
            $column = lcfirst(substr($method, 5));
            return $this->addWhere($column, '=', $parameters[0]);
        }

        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.',
            static::class,
            $method
        ));
    }

    /**
     * Handle dynamic static method calls
     */
    public static function __callStatic($method, $parameters)
    {
        $instance = new static();
        return $instance->$method(...$parameters);
    }

    /**
     * Get an attribute from the model
     */
    public function __get($key)
    {
        // First check if it's a declared property
        if (property_exists($this, $key)) {
            return $this->$key;
        }
        return $this->getAttribute($key);
    }

    /**
     * Set a given attribute on the model
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    /**
     * Check if an attribute exists on the model
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]);
    }

    /**
     * Create a new instance of the model from raw attributes
     * Bypassing fillable checks for database hydration
     */
    public static function hydrate(array $attributes): self
    {
        $instance = new static();
        $instance->attributes = $attributes;
        $instance->original = $attributes;
        $instance->exists = true;
        return $instance;
    }

    /**
     * Unset an attribute on the model
     */
    public function __unset($key)
    {
        unset($this->attributes[$key]);
    }

    /**
     * Convert the model to its string representation
     */
    public function __toString()
    {
        return $this->toJson();
    }
}
