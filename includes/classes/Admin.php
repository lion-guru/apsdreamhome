<?php
class Admin {
    private $db;
    private $table = 'admin';

    public function __construct(Database $database) {
        $this->db = $database;
    }

    public function getByEmail(string $email): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    public function getByPhone(string $phone): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE phone = ?",
            [$phone]
        );
    }

    public function getByUsername(string $username): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE auser = ?",
            [$username]
        );
    }
}
