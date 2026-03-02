<?php
/**
 * APS Dream Home - Query Builder
 */

namespace App\Security;

class QueryBuilder
{
    private $table;
    private $select = '*';
    private $where = [];
    private $orderBy = [];
    private $limit;
    private $offset;
    private $joins = [];
    private $groupBy = [];
    private $having = [];

    public function __construct($table)
    {
        $this->table = $table;
    }

    public function select($columns = '*')
    {
        $this->select = is_array($columns) ? implode(', ', $columns) : $columns;
        return $this;
    }

    public function where($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->where[] = [$column, $operator, $value];
        return $this;
    }

    public function whereIn($column, $values)
    {
        $placeholders = str_repeat('?,', count($values) - 1) . '?';
        $this->where[] = [$column, 'IN', $values];
        return $this;
    }

    public function whereLike($column, $value)
    {
        $this->where[] = [$column, 'LIKE', $value];
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->orderBy[] = [$column, strtoupper($direction)];
        return $this;
    }

    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    public function offset($offset)
    {
        $this->offset = $offset;
        return $this;
    }

    public function join($table, $first, $operator = null, $second = null)
    {
        if (func_num_args() === 3) {
            $second = $operator;
            $operator = '=';
        }

        $this->joins[] = ['INNER', $table, $first, $operator, $second];
        return $this;
    }

    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        if (func_num_args() === 3) {
            $second = $operator;
            $operator = '=';
        }

        $this->joins[] = ['LEFT', $table, $first, $operator, $second];
        return $this;
    }

    public function groupBy($columns)
    {
        $this->groupBy = is_array($columns) ? $columns : [$columns];
        return $this;
    }

    public function having($column, $operator = null, $value = null)
    {
        if (func_num_args() === 2) {
            $value = $operator;
            $operator = '=';
        }

        $this->having[] = [$column, $operator, $value];
        return $this;
    }

    public function build()
    {
        $sql = "SELECT {$this->select} FROM {$this->table}";

        // Add joins
        foreach ($this->joins as $join) {
            $sql .= " {$join[0]} JOIN {$join[1]} ON {$join[2]} {$join[3]} {$join[4]}";
        }

        // Add where conditions
        if (!empty($this->where)) {
            $sql .= ' WHERE ';
            $conditions = [];
            foreach ($this->where as $where) {
                if ($where[1] === 'IN') {
                    $conditions[] = "{$where[0]} {$where[1]} (" . str_repeat('?,', count($where[2]) - 1) . '?)';
                } else {
                    $conditions[] = "{$where[0]} {$where[1]} ?";
                }
            }
            $sql .= implode(' AND ', $conditions);
        }

        // Add group by
        if (!empty($this->groupBy)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groupBy);
        }

        // Add having conditions
        if (!empty($this->having)) {
            $sql .= ' HAVING ';
            $conditions = [];
            foreach ($this->having as $having) {
                $conditions[] = "{$having[0]} {$having[1]} ?";
            }
            $sql .= implode(' AND ', $conditions);
        }

        // Add order by
        if (!empty($this->orderBy)) {
            $orders = [];
            foreach ($this->orderBy as $order) {
                $orders[] = "{$order[0]} {$order[1]}";
            }
            $sql .= ' ORDER BY ' . implode(', ', $orders);
        }

        // Add limit
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";
        }

        // Add offset
        if ($this->offset !== null) {
            $sql .= " OFFSET {$this->offset}";
        }

        return $sql;
    }

    public function getParams()
    {
        $params = [];

        // Add where parameters
        foreach ($this->where as $where) {
            if ($where[1] === 'IN') {
                foreach ($where[2] as $value) {
                    $params[] = $value;
                }
            } else {
                $params[] = $where[2];
            }
        }

        // Add having parameters
        foreach ($this->having as $having) {
            $params[] = $having[2];
        }

        return $params;
    }
}
