<?php

namespace App\Core\Database;

use PDO;
use PDOStatement;
use InvalidArgumentException;

class QueryBuilder
{
    /**
     * The database connection instance.
     */
    protected $db;

    /**
     * The table which the query is targeting.
     */
    protected $table;

    /**
     * The columns that should be returned.
     */
    protected $columns = ['*'];

    /**
     * The where constraints for the query.
     */
    protected $wheres = [];

    /**
     * The orderings for the query.
     */
    protected $orders = [];

    /**
     * The maximum number of records to return.
     */
    protected $limit;

    /**
     * The number of records to skip.
     */
    protected $offset;

    /**
     * The query bindings.
     */
    protected $bindings = [
        'select' => [],
        'where' => [],
        'order' => [],
        'limit' => null,
        'offset' => null,
    ];

    /**
     * Create a new query builder instance.
     */
    public function __construct(Database $db, string $table)
    {
        $this->db = $db;
        $this->table = $table;
    }

    /**
     * Set the table which the query is targeting.
     */
    public function from(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Set the columns to be selected.
     */
    public function select($columns = ['*']): self
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Add a basic where clause to the query.
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and'): self
    {
        if (is_array($column)) {
            return $this->addArrayOfWheres($column, $boolean);
        }

        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean,
        ];

        $this->addBinding($value, 'where');

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     */
    public function orWhere($column, $operator = null, $value = null): self
    {
        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a "where in" clause to the query.
     */
    public function whereIn($column, $values, $boolean = 'and', $not = false): self
    {
        $type = $not ? 'NotIn' : 'In';

        $this->wheres[] = [
            'type' => $type,
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
        ];

        foreach ($values as $value) {
            $this->addBinding($value, 'where');
        }

        return $this;
    }

    /**
     * Add an "order by" clause to the query.
     */
    public function orderBy($column, $direction = 'asc'): self
    {
        $this->orders[] = [
            'column' => $column,
            'direction' => strtolower($direction) === 'asc' ? 'asc' : 'desc',
        ];

        return $this;
    }

    /**
     * Set the "limit" value of the query.
     */
    public function limit(int $value): self
    {
        $this->limit = $value;
        $this->bindings['limit'] = $value;
        return $this;
    }

    /**
     * Set the "offset" value of the query.
     */
    public function offset(int $value): self
    {
        $this->offset = $value;
        $this->bindings['offset'] = $value;
        return $this;
    }

    /**
     * Alias to set the "limit" value of the query.
     */
    public function take(int $value): self
    {
        return $this->limit($value);
    }

    /**
     * Alias to set the "offset" value of the query.
     */
    public function skip(int $value): self
    {
        return $this->offset($value);
    }

    /**
     * Execute the query as a "select" statement.
     */
    public function get($columns = ['*']): array
    {
        if (!empty($columns)) {
            $this->select($columns);
        }

        $sql = $this->toSql();
        $bindings = $this->getBindings();

        return $this->db->fetchAll($sql, $bindings);
    }

    /**
     * Execute the query and get the first result.
     */
    public function first($columns = ['*'])
    {
        $results = $this->limit(1)->get($columns);
        return $results[0] ?? null;
    }

    /**
     * Insert a new record into the database.
     */
    public function insert(array $values): bool
    {
        if (empty($values)) {
            return false;
        }

        $columns = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        return $this->db->query($sql, array_values($values)) !== false;
    }

    /**
     * Update a record in the database.
     */
    public function update(array $values): int
    {
        if (empty($values)) {
            return 0;
        }

        $set = [];
        $bindings = [];

        foreach ($values as $column => $value) {
            $set[] = "{$column} = ?";
            $bindings[] = $value;
        }

        $where = $this->compileWheres();
        $bindings = array_merge($bindings, $this->getBindings('where'));

        $sql = "UPDATE {$this->table} SET " . implode(', ', $set) . $where;

        $statement = $this->db->query($sql, $bindings);

        return $statement ? $statement->rowCount() : 0;
    }

    /**
     * Delete a record from the database.
     */
    public function delete($id = null): int
    {
        if (!is_null($id)) {
            $this->where('id', '=', $id);
        }

        $where = $this->compileWheres();
        $sql = "DELETE FROM {$this->table}" . $where;

        $statement = $this->db->query($sql, $this->getBindings('where'));

        return $statement ? $statement->rowCount() : 0;
    }

    /**
     * Get the SQL representation of the query.
     */
    public function toSql(): string
    {
        $sql = 'SELECT ' . $this->compileColumns() . ' FROM ' . $this->table;

        if (!empty($this->wheres)) {
            $sql .= $this->compileWheres();
        }

        if (!empty($this->orders)) {
            $sql .= $this->compileOrders();
        }

        if (!is_null($this->limit)) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if (!is_null($this->offset)) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    /**
     * Compile the select clause.
     */
    protected function compileColumns(): string
    {
        $select = $this->columns;

        if (is_array($select)) {
            $select = implode(', ', $select);
        }

        return $select;
    }

    /**
     * Compile the where clauses.
     */
    protected function compileWheres(): string
    {
        if (empty($this->wheres)) {
            return '';
        }

        $sql = [];

        foreach ($this->wheres as $index => $where) {
            if ($index === 0) {
                $sql[] = ' WHERE ';
            } else {
                $sql[] = ' ' . strtoupper($where['boolean']) . ' ';
            }

            switch ($where['type']) {
                case 'basic':
                    $sql[] = $where['column'] . ' ' . $where['operator'] . ' ?';
                    break;

                case 'In':
                    $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                    $sql[] = $where['column'] . ' IN (' . $placeholders . ')';
                    break;

                case 'NotIn':
                    $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                    $sql[] = $where['column'] . ' NOT IN (' . $placeholders . ')';
                    break;
            }
        }

        return implode('', $sql);
    }

    /**
     * Compile the order by clauses.
     */
    protected function compileOrders(): string
    {
        if (empty($this->orders)) {
            return '';
        }

        $orders = [];

        foreach ($this->orders as $order) {
            $orders[] = $order['column'] . ' ' . strtoupper($order['direction']);
        }

        return ' ORDER BY ' . implode(', ', $orders);
    }

    /**
     * Add a binding to the query.
     */
    protected function addBinding($value, string $type = 'where'): void
    {
        if (!array_key_exists($type, $this->bindings)) {
            throw new InvalidArgumentException("Invalid binding type: {$type}");
        }

        $this->bindings[$type][] = $value;
    }

    /**
     * Get the bindings for the query.
     */
    public function getBindings(string $type = null): array
    {
        if (is_null($type)) {
            return array_merge(
                $this->bindings['select'] ?? [],
                $this->bindings['where'] ?? []
            );
        }

        return $this->bindings[$type] ?? [];
    }

    /**
     * Add an array of where clauses to the query.
     */
    protected function addArrayOfWheres(array $column, string $boolean): self
    {
        foreach ($column as $key => $value) {
            if (is_numeric($key) && is_array($value)) {
                $this->where(...array_values($value));
            } else {
                $this->where($key, '=', $value, $boolean);
            }
        }

        return $this;
    }
}
