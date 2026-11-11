<?php

namespace App\Core\Database;

/**
 * PDO subclass that tolerates the legacy call pattern query($sql, $params).
 * Native PDO fatals with a TypeError when the 2nd argument is an array
 * (it expects an int fetch mode). Codebase-wide, ~38 call sites pass
 * parameter arrays to ->query(). This shim routes those calls through
 * prepare()/execute(); all standard PDO::query() usage is untouched.
 *
 * S355: Also provides Database wrapper methods (fetch, fetchOne, fetchAll,
 * insert, update, delete, execute) so that code calling getConnection()
 * (which returns PdoCompat) can use both PDO methods and wrapper methods.
 * This fixes silent failures where getConnection() returned raw PDO and
 * wrapper methods like fetchOne() didn't exist on PDO.
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
     * Wrapper: fetch single row
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    /**
     * Wrapper: fetch single row (alias for fetch)
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        return $this->fetch($sql, $params);
    }

    /**
     * Wrapper: fetch single row (alias)
     */
    public function fetchRow(string $sql, array $params = []): ?array
    {
        return $this->fetch($sql, $params);
    }

    /**
     * Wrapper: fetch all rows
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Wrapper: fetch column value
     */
    public function fetchColumn(string $sql, array $params = [], int $column = 0): mixed
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn($column);
    }

    /**
     * Wrapper: execute statement (INSERT/UPDATE/DELETE)
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Wrapper: insert row and return last insert ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $stmt = $this->prepare($sql);
        $stmt->execute(array_values($data));
        return (int)$this->lastInsertId();
    }

    /**
     * Wrapper: update rows
     */
    public function update(string $table, array $data, string $where = '', array $whereParams = []): int
    {
        $sets = [];
        $values = [];
        foreach ($data as $key => $val) {
            $sets[] = "{$key} = ?";
            $values[] = $val;
        }
        $sql = "UPDATE {$table} SET " . implode(', ', $sets);
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        $values = array_merge($values, $whereParams);
        $stmt = $this->prepare($sql);
        $stmt->execute($values);
        return $stmt->rowCount();
    }

    /**
     * Wrapper: delete rows
     */
    public function delete(string $table, string $where = '', array $params = []): int
    {
        $sql = "DELETE FROM {$table}";
        if ($where) {
            $sql .= " WHERE " . $where;
        }
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Wrapper: select (alias for fetchAll)
     */
    public function select(string $sql, array $params = []): array
    {
        return $this->fetchAll($sql, $params);
    }

    /**
     * Wrapper: selectOne (alias for fetch)
     */
    public function selectOne(string $sql, array $params = []): ?array
    {
        return $this->fetch($sql, $params);
    }

    /**
     * Wrapper: lastInsertId (already in PDO, but ensure int return)
     */
    public function getLastInsertId(): int
    {
        return (int)$this->lastInsertId();
    }

    /**
     * Wrapper: beginTransaction (already in PDO)
     */
    public function beginTransaction(): bool
    {
        return parent::beginTransaction();
    }

    /**
     * Wrapper: inTransaction (already in PDO 5.3.3+)
     */
    public function inTransaction(): bool
    {
        return parent::inTransaction();
    }
}