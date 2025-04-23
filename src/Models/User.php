<?php
namespace Models;

class User {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = \Database::getInstance();
    }

    public function findById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = ?";
        return $this->db->fetch($sql, [$id]);
    }

    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ?";
        return $this->db->fetch($sql, [$email]);
    }

    public function create($data) {
        return $this->db->insert($this->table, $data);
    }

    public function update($id, $data) {
        return $this->db->update($this->table, $data, "id = {$id}");
    }

    public function delete($id) {
        return $this->db->delete($this->table, "id = {$id}");
    }

    public function validateLogin($email, $password) {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }
        return password_verify($password, $user['password']);
    }

    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        return $this->update($id, ['password' => $hashedPassword]);
    }

    public function getAssociates($userId) {
        $sql = "SELECT * FROM {$this->table} WHERE sponsor_id = ?";
        return $this->db->fetchAll($sql, [$userId]);
    }

    public function updateProfile($id, $data) {
        // Remove sensitive fields from update
        unset($data['password']);
        unset($data['email']);
        return $this->update($id, $data);
    }

    public function getRole($id) {
        $user = $this->findById($id);
        return $user ? $user['role'] : null;
    }
}