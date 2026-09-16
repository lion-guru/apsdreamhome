<?php

namespace App\Core\Database;

/**
 * PDO subclass that tolerates the legacy call pattern query($sql, $params).
 * Native PDO fatals with a TypeError when the 2nd argument is an array
 * (it expects an int fetch mode). Codebase-wide, ~38 call sites pass
 * parameter arrays to ->query(). This shim routes those calls through
 * prepare()/execute(); all standard PDO::query() usage is untouched.
 */
class PdoCompat extends \PDO
{
    public function query($query, ...$args): \PDOStatement|false
    {
        if (isset($args[0]) && is_array($args[0])) {
            $stmt = $this->prepare($query);
            if (!$stmt) {
                return false;
            }
            $stmt->execute($args[0]);
            return $stmt;
        }

        return parent::query(...array_merge([$query], $args));
    }

    /**
     * Wrapper-style helpers mirroring App\Core\Database\Database::fetchAll/
     * fetchOne()/insert(). Controllers that call Database::getInstance()
     * ->getConnection() receive this class (not the wrapper), so without
     * these helpers every $db->fetchAll($sql,$params) fatals with
     * "Call to undefined method" (caught upstream -> empty pages).
     */
    public function execute($sql, $params = [])
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetch($sql, $params = [])
    {
        return $this->execute($sql, $params)->fetch(\PDO::FETCH_ASSOC);
    }

    public function fetchOne($sql, $params = [])
    {
        return $this->fetch($sql, $params);
    }

    public function fetchRow($sql, $params = [])
    {
        return $this->fetch($sql, $params);
    }

    public function fetchAll($sql, $params = [])
    {
        return $this->execute($sql, $params)->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function select($sql, $params = [])
    {
        return $this->fetchAll($sql, $params);
    }

    public function selectOne($sql, $params = [])
    {
        return $this->fetch($sql, $params);
    }

    public function fetchColumn($sql, $params = [], $column = 0)
    {
        return $this->execute($sql, $params)->fetchColumn($column);
    }

    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_map(fn($key) => ":$key", array_keys($data)));
        $this->execute("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})", $data);
        return $this->lastInsertId();
    }

    public function update($table, $data, $where, $whereParams = [])
    {
        $set = implode(', ', array_map(fn($key) => "$key = :$key", array_keys($data)));
        if (is_array($where)) {
            $conditions = [];
            $bindings = [];
            foreach ($where as $col => $val) {
                $conditions[] = "$col = :_where_$col";
                $bindings["_where_$col"] = $val;
            }
            $sql = "UPDATE {$table} SET {$set} WHERE " . implode(' AND ', $conditions);
            $params = array_merge($data, $bindings);
        } else {
            $sql = "UPDATE {$table} SET {$set} WHERE {$where}";
            $params = array_merge($data, $whereParams);
        }
        return $this->execute($sql, $params)->rowCount();
    }
}
