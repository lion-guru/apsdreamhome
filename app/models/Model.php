<?php
namespace App\Models;

use App\Core\Database;

abstract class Model {
    protected static string $table = '';
    protected array $attributes = [];
    protected array $original = [];
    protected array $fillable = [];

    public function __construct(array $attributes = []) {
        $this->fill($attributes);
        $this->original = $this->attributes;
    }

    protected function fill(array $attributes): void {
        foreach ($attributes as $key => $value) {
            if (in_array($key, $this->fillable)) {
                $this->attributes[$key] = $value;
            }
        }
    }

    public function __get(string $name) {
        return $this->attributes[$name] ?? null;
    }

    public function __set(string $name, $value): void {
        if (in_array($name, $this->fillable)) {
            $this->attributes[$name] = $value;
        }
    }

    public static function find($id) {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT * FROM " . static::$table . " WHERE id = ?",
            [$id]
        );
        $result = $stmt->fetch();
        return $result ? new static($result) : null;
    }

    public static function all(): array {
        $db = Database::getInstance();
        $stmt = $db->query("SELECT * FROM " . static::$table);
        $results = $stmt->fetchAll();
        return array_map(fn($result) => new static($result), $results);
    }

    public function save(): bool {
        $db = Database::getInstance();
        
        if (isset($this->attributes['id'])) {
            $updates = [];
            $values = [];
            foreach ($this->attributes as $key => $value) {
                if ($key === 'id') continue;
                if ($value !== ($this->original[$key] ?? null)) {
                    $updates[] = "{$key} = ?";
                    $values[] = $value;
                }
            }
            
            if (empty($updates)) {
                return true;
            }
            
            $values[] = $this->attributes['id'];
            $sql = "UPDATE " . static::$table . " SET " . implode(", ", $updates) . " WHERE id = ?";
            $db->query($sql, $values);
        } else {
            $columns = array_keys(array_filter($this->attributes));
            $values = array_values(array_filter($this->attributes));
            $placeholders = array_fill(0, count($values), '?');
            
            $sql = "INSERT INTO " . static::$table . 
                   " (" . implode(", ", $columns) . ") " .
                   "VALUES (" . implode(", ", $placeholders) . ")";
            
            $db->query($sql, $values);
            $this->attributes['id'] = $db->lastInsertId();
        }
        
        $this->original = $this->attributes;
        return true;
    }

    public function delete(): bool {
        if (!isset($this->attributes['id'])) {
            return false;
        }

        $db = Database::getInstance();
        $db->query(
            "DELETE FROM " . static::$table . " WHERE id = ?",
            [$this->attributes['id']]
        );

        return true;
    }

    public static function where(string $column, string $operator, $value): array {
        $db = Database::getInstance();
        $stmt = $db->query(
            "SELECT * FROM " . static::$table . " WHERE {$column} {$operator} ?",
            [$value]
        );
        $results = $stmt->fetchAll();
        return array_map(fn($result) => new static($result), $results);
    }

    public function toArray(): array {
        return $this->attributes;
    }
}