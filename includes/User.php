<?php
class User {
    private $db;
    private $table = 'users';

    public function __construct(Database $database) {
        $this->db = $database;
    }

    public function getById(string $uid): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE uid = ?",
            [$uid]
        );
    }

    public function getByEmail(string $email): ?array {
        return $this->db->fetchOne(
            "SELECT * FROM {$this->table} WHERE email = ?",
            [$email]
        );
    }

    public function create(array $userData): array {
        try {
            $this->db->beginTransaction();

            $data = [
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'],
                'password' => password_hash($userData['password'], PASSWORD_DEFAULT),
                'utype' => $userData['utype'],
                'created_at' => date('Y-m-d H:i:s')
            ];

            $this->db->insert($this->table, $data);
            $userId = $this->db->lastInsertId();

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'User created successfully',
                'user_id' => $userId
            ];
        } catch (Exception $e) {
            $this->db->rollback();
            return [
                'success' => false,
                'message' => 'Failed to create user: ' . $e->getMessage()
            ];
        }
    }

    public function update(string $uid, array $data): array {
        try {
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $this->db->update(
                $this->table,
                $data,
                'uid = ?',
                [$uid]
            );

            return [
                'success' => true,
                'message' => 'User updated successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to update user: ' . $e->getMessage()
            ];
        }
    }

    public function delete(string $uid): array {
        try {
            $this->db->delete($this->table, 'uid = ?', [$uid]);
            return [
                'success' => true,
                'message' => 'User deleted successfully'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Failed to delete user: ' . $e->getMessage()
            ];
        }
    }

    public function validatePassword(string $password): bool {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }

    public function validatePhone(string $phone): bool {
        return strlen($phone) === 10 && ctype_digit($phone);
    }

    public function validateEmail(string $email): bool {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}