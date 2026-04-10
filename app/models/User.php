<?php

namespace App\Models;

use App\Core\UnifiedModel;

/**
 * User Model
 * Represents user data
 */
class User extends UnifiedModel
{
    public static $table = 'users';
    public static $primaryKey = 'id';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'role',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * Find user by email
     */
    public function findByEmail($email)
    {
        return $this->where('email', $email)->first();
    }

    /**
     * Update user data
     */
    public function update($id, $data)
    {
        return $this->where('id', $id)->update($data);
    }

    /**
     * Find user by ID
     */
    public function findById($id)
    {
        return $this->find($id);
    }

    /**
     * Get customers for select dropdown
     * 
     * @param string $status Filter by status (default: active)
     * @param array $columns Columns to select (default: id, name, email)
     * @return array
     */
    public static function getCustomers($status = 'active', $columns = ['id', 'name', 'email'])
    {
        try {
            $columnList = implode(', ', $columns);
            $where = ["role = 'customer'"];
            $params = [];

            if ($status !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $status;
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT {$columnList} FROM users {$whereClause} ORDER BY name ASC";

            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in User::getCustomers: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get associates for select dropdown
     * 
     * @param string $status Filter by status (default: active)
     * @param array $columns Columns to select (default: id, name, email)
     * @return array
     */
    public static function getAssociates($status = 'active', $columns = ['id', 'name', 'email'])
    {
        try {
            $columnList = implode(', ', $columns);
            $where = ["role = 'associate'"];
            $params = [];

            if ($status !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $status;
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT {$columnList} FROM users {$whereClause} ORDER BY name ASC";

            $db = \App\Core\Database::getInstance();
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in User::getAssociates: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get agents (admin, support, associate) for select dropdown
     * 
     * @param string $status Filter by status (default: active)
     * @param array $roles Specific roles to fetch (default: admin, support, associate)
     * @param array $columns Columns to select (default: id, name, email, role)
     * @return array
     */
    public static function getAgents($status = 'active', $roles = ['admin', 'support', 'associate'], $columns = ['id', 'name', 'email', 'role'])
    {
        try {
            $columnList = implode(', ', $columns);
            $placeholders = implode(',', array_fill(0, count($roles), '?'));
            $where = ["role IN ($placeholders)"];
            $params = $roles;

            if ($status !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $status;
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT {$columnList} FROM users {$whereClause} ORDER BY name ASC";

            $db = \App\Core\Database\getInstance();
            $stmt = $db->prepare($sql);
            $paramIndex = 1;
            foreach ($roles as $role) {
                $stmt->bindValue($paramIndex++, $role);
            }
            if ($status !== 'all') {
                $stmt->bindValue(':' . 'status', $status);
            }
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in User::getAgents: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users by role for select dropdown
     * 
     * @param string|array $roles Role(s) to fetch
     * @param string $status Filter by status (default: active)
     * @param array $columns Columns to select (default: id, name, email)
     * @return array
     */
    public static function getByRole($roles, $status = 'active', $columns = ['id', 'name', 'email'])
    {
        try {
            $columnList = implode(', ', $columns);
            $roleArray = is_array($roles) ? $roles : [$roles];
            $placeholders = implode(',', array_fill(0, count($roleArray), '?'));
            $where = ["role IN ($placeholders)"];
            $params = $roleArray;

            if ($status !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $status;
            }

            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT {$columnList} FROM users {$whereClause} ORDER BY name ASC";

            $db = \App\Core\Database\getInstance();
            $stmt = $db->prepare($sql);
            $paramIndex = 1;
            foreach ($roleArray as $role) {
                $stmt->bindValue($paramIndex++, $role);
            }
            if ($status !== 'all') {
                $stmt->bindValue(':' . 'status', $status);
            }
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in User::getByRole: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get users for select dropdown
     * 
     * @param string $role Filter by role
     * @param string $status Filter by status (default: active)
     * @param array $columns Columns to select (default: id, name, email)
     * @return array
     */
    public static function getForSelect($role = null, $status = 'active', $columns = ['id', 'name', 'email'])
    {
        try {
            $columnList = implode(', ', $columns);
            $where = [];
            $params = [];

            if ($role) {
                $where[] = "role = :role";
                $params['role'] = $role;
            }

            if ($status !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $status;
            }

            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT {$columnList} FROM users {$whereClause} ORDER BY name ASC";

            $db = \App\Core\Database\getInstance();
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in User::getForSelect: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employees for select dropdown
     * 
     * @param string $status Filter by status (default: active)
     * @param array $columns Columns to select (default: id, name, email)
     * @return array
     */
    public static function getEmployees($status = 'active', $columns = ['id', 'name', 'email'])
    {
        return self::getByRole('employee', $status, $columns);
    }

    /**
     * Get admins for select dropdown
     * 
     * @param string $status Filter by status (default: active)
     * @param array $columns Columns to select (default: id, name, email)
     * @return array
     */
    public static function getAdmins($status = 'active', $columns = ['id', 'name', 'email'])
    {
        return self::getByRole('admin', $status, $columns);
    }
}
