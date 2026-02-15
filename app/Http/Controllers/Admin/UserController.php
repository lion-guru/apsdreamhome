<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Models\User;
use Exception;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        // Ensure only admins can access these methods
        if (!$this->isAdmin()) {
            $this->redirect('login');
            return;
        }
    }

    /**
     * List all users
     */
    public function index()
    {
        $users = $this->db->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();

        return $this->render('admin/users/index', [
            'users' => $users,
            'page_title' => 'User Management - APS Dream Home'
        ]);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        return $this->render('admin/users/create', [
            'page_title' => 'Add New User - APS Dream Home'
        ]);
    }

    /**
     * Store new user
     */
    public function store()
    {
        try {
            $data = $_POST;

            // Basic validation
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                $this->setFlash('error', "Username, Email and Password are required.");
                $this->redirect('admin/users/create');
                return;
            }

            // Check if email or username already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
            $stmt->execute([
                ':email' => $data['email'],
                ':username' => $data['username']
            ]);
            if ($stmt->fetch()) {
                $this->setFlash('error', "Email or Username already exists.");
                $this->redirect('admin/users/create');
                return;
            }

            $sql = "INSERT INTO users (username, email, password, mobile, role, status, created_at) 
                    VALUES (:username, :email, :password, :mobile, :role, :status, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':mobile' => $data['mobile'] ?? '',
                ':role' => $data['role'] ?? 'customer',
                ':status' => $data['status'] ?? 'active'
            ]);

            $this->setFlash('success', "User created successfully!");
            $this->redirect('admin/users');
        } catch (Exception $e) {
            $this->setFlash('error', "Error creating user: " . $e->getMessage());
            $this->redirect('admin/users/create');
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->setFlash('error', "User not found.");
            $this->redirect('admin/users');
            return;
        }

        return $this->render('admin/users/edit', [
            'user' => $user,
            'page_title' => 'Edit User - APS Dream Home'
        ]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        try {
            $data = $_POST;

            $sql = "UPDATE users SET username = :username, email = :email, mobile = :mobile, role = :role, status = :status, updated_at = NOW()";
            $params = [
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':mobile' => $data['mobile'] ?? '',
                ':role' => $data['role'] ?? 'customer',
                ':status' => $data['status'] ?? 'active'
            ];

            if (!empty($data['password'])) {
                $sql .= ", password = :password";
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = :id";
            $params[':id'] = $id;

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $this->setFlash('success', "User updated successfully!");
            $this->redirect('admin/users');
        } catch (Exception $e) {
            $this->setFlash('error', "Error updating user: " . $e->getMessage());
            $this->redirect('admin/users/edit/' . $id);
        }
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        try {
            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $this->setFlash('success', "User deleted successfully!");
        } catch (Exception $e) {
            $this->setFlash('error', "Error deleting user: " . $e->getMessage());
        }
        $this->redirect('admin/users');
    }
}
