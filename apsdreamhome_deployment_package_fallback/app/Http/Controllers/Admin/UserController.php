<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Exception;

class UserController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        // AdminController handles auth check
    }

    /**
     * List all users
     */
    public function index()
    {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY created_at DESC");
        $users = $stmt->fetchAll();

        return $this->render('admin/users/index', [
            'users' => $users,
            'page_title' => $this->mlSupport->translate('User Management') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Show create user form
     */
    public function create()
    {
        return $this->render('admin/users/create', [
            'page_title' => $this->mlSupport->translate('Add New User') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Store new user
     */
    public function store()
    {
        try {
            if ($this->request->method() !== 'POST') {
                $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
                $this->redirect('/admin/users/create');
                return;
            }

            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
                $this->redirect('/admin/users/create');
                return;
            }

            $data = $this->request->post();

            // Basic validation
            if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
                $this->setFlash('error', $this->mlSupport->translate("Username, Email and Password are required."));
                $this->redirect('/admin/users/create');
                return;
            }

            // Check if email or username already exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email OR username = :username");
            $stmt->execute([
                ':email' => $data['email'],
                ':username' => $data['username']
            ]);
            if ($stmt->fetch()) {
                $this->setFlash('error', $this->mlSupport->translate("Email or Username already exists."));
                $this->redirect('/admin/users/create');
                return;
            }

            // Default role and status
            $role = $data['role'] ?? 'customer';
            $status = $data['status'] ?? 'active';
            $mobile = $data['mobile'] ?? '';

            // Insert user
            $sql = "INSERT INTO users (username, email, password, mobile, role, status, created_at, updated_at) 
                    VALUES (:username, :email, :password, :mobile, :role, :status, NOW(), NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':password' => password_hash($data['password'], PASSWORD_DEFAULT),
                ':mobile' => $mobile,
                ':role' => $role,
                ':status' => $status
            ]);

            $this->setFlash('success', $this->mlSupport->translate("User created successfully!"));
            $this->redirect('/admin/users');
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate("Error creating user: ") . $e->getMessage());
            $this->redirect('/admin/users/create');
        }
    }

    /**
     * Show edit user form
     */
    public function edit($id)
    {
        $id = intval($id);
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        if (!$user) {
            $this->setFlash('error', $this->mlSupport->translate("User not found."));
            $this->redirect('/admin/users');
            return;
        }

        return $this->render('admin/users/edit', [
            'user' => $user,
            'page_title' => $this->mlSupport->translate('Edit User') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Update user
     */
    public function update($id)
    {
        try {
            $id = intval($id);
            if ($this->request->method() !== 'POST') {
                $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
                $this->redirect("/admin/users/edit/$id");
                return;
            }

            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
                $this->redirect("/admin/users/edit/$id");
                return;
            }

            $data = $this->request->post();

            // Basic validation
            if (empty($data['username']) || empty($data['email'])) {
                $this->setFlash('error', $this->mlSupport->translate("Username and Email are required."));
                $this->redirect("/admin/users/edit/$id");
                return;
            }

            // Check if email or username already exists (excluding current user)
            $stmt = $this->db->prepare("SELECT id FROM users WHERE (email = :email OR username = :username) AND id != :id");
            $stmt->execute([
                ':email' => $data['email'],
                ':username' => $data['username'],
                ':id' => $id
            ]);
            if ($stmt->fetch()) {
                $this->setFlash('error', $this->mlSupport->translate("Email or Username already exists."));
                $this->redirect("/admin/users/edit/$id");
                return;
            }

            // Update user
            $sql = "UPDATE users SET username = :username, email = :email, mobile = :mobile, role = :role, status = :status, updated_at = NOW() WHERE id = :id";
            $params = [
                ':username' => $data['username'],
                ':email' => $data['email'],
                ':mobile' => $data['mobile'] ?? '',
                ':role' => $data['role'] ?? 'customer',
                ':status' => $data['status'] ?? 'active',
                ':id' => $id
            ];

            // Update password if provided
            if (!empty($data['password'])) {
                $sql = "UPDATE users SET username = :username, email = :email, password = :password, mobile = :mobile, role = :role, status = :status, updated_at = NOW() WHERE id = :id";
                $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            $this->setFlash('success', $this->mlSupport->translate("User updated successfully!"));
            $this->redirect('/admin/users');
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate("Error updating user: ") . $e->getMessage());
            $this->redirect("/admin/users/edit/$id");
        }
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        try {
            $id = intval($id);

            if ($this->request->method() !== 'POST') {
                $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
                $this->redirect('/admin/users');
                return;
            }

            if (!$this->validateCsrfToken()) {
                $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
                $this->redirect('/admin/users');
                return;
            }

            // Prevent deleting self
            if ($id == $this->session->get('user_id')) {
                $this->setFlash('error', $this->mlSupport->translate("You cannot delete yourself."));
                $this->redirect('/admin/users');
                return;
            }

            $stmt = $this->db->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $id]);

            $this->setFlash('success', $this->mlSupport->translate("User deleted successfully!"));
            $this->redirect('/admin/users');
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate("Error deleting user: ") . $e->getMessage());
            $this->redirect('/admin/users');
        }
    }
}
