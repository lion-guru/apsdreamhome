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
}
