<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use \App\Traits\TenantAwareTrait;

class CompanyController extends AdminController
{
    use TenantAwareTrait;
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function settings()
    {
        $this->requireAdmin();
        try {
            $company = $this->db->fetch("SELECT * FROM company_settings LIMIT 1");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        $this->render('admin/company/settings', [
            'page_title' => 'Company Settings',
            'company' => $company ?: []
        ]);
    }

    public function updateSettings()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/company/settings');
        }

        $company = $this->db->fetch("SELECT id FROM company_settings LIMIT 1");

        $data = [
            'company_name' => $_POST['company_name'] ?? '',
            'phone' => $_POST['phone'] ?? '',
            'email' => $_POST['email'] ?? '',
            'address' => $_POST['address'] ?? '',
            'description' => $_POST['description'] ?? '',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($company) {
            $this->db->update('company_settings', $data, ['id' => $company['id']]);
        } else {
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert('company_settings', $data);
        }

        $_SESSION['success'] = 'Company settings updated successfully!';
        $this->redirect('/admin/company/settings');
    }

    public function users()
    {
        $this->requireAdmin();
        $users = [];
        try {
            $users = $this->db->fetchAll("
                SELECT ce.*, u.name as user_name, u.email as user_email, u.phone as user_phone
                FROM company_employees ce
                JOIN users u ON u.id = ce.user_id
                WHERE ce.company_id = (SELECT id FROM company_settings LIMIT 1)
                ORDER BY ce.join_date DESC
            ") ?: [];
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }

        $employeeUsers = [];
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $employeeUsers = $this->db->fetchAll("SELECT id, name, email FROM users WHERE role = 'employee'{$tidSql} ORDER BY name", $tidParams) ?: [];
        } catch (\Throwable $e) {
        // Gracefully handle missing table
        error_log($e->getMessage());
        }

        $this->render('admin/company/users', [
            'page_title' => 'Company users',
            'users' => $users,
            'employeeUsers' => $employeeUsers,
        ]);
    }

    public function addEmployee()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/company/users');
        }

        try {
            $company = $this->db->fetch("SELECT id FROM company_settings LIMIT 1");
        } catch (\Throwable $e) {
        // Gracefully handle dropped table ref
        error_log($e->getMessage());
        }
        if (!$company) {
            $_SESSION['error'] = 'Please save company settings first.';
            $this->redirect('/admin/company/settings');
        }

        $this->db->insert('company_employees', [
            'company_id' => $company['id'],
            'user_id' => intval($_POST['user_id'] ?? 0),
            'position' => $_POST['position'] ?? '',
            'salary' => floatval($_POST['salary'] ?? 0),
            'join_date' => $_POST['join_date'] ?? date('Y-m-d'),
            'status' => $_POST['status'] ?? 'active'
        ]);

        $_SESSION['success'] = 'Employee added successfully!';
        $this->redirect('/admin/company/users');
    }
}
