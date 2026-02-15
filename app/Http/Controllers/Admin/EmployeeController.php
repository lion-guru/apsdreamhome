<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class EmployeeController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        // Register middlewares
        $this->middleware('role:admin');
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'offboard']]);
    }

    /**
     * Display a listing of employees
     */
    public function index()
    {
        $request = $this->request();
        $filters = [
            'search' => $request->get('search', ''),
            'department' => $request->get('department', '')
        ];

        $employees = $this->model('Employee')->getAllEmployees($filters);

        return $this->render('admin/employees/index', [
            'employees' => $employees,
            'filters' => $filters,
            'page_title' => $this->mlSupport->translate('Employee Management') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Show the form for creating a new employee
     */
    public function create()
    {
        $roles = $this->model('Employee')->getRoles();

        return $this->render('admin/employees/create', [
            'roles' => $roles,
            'page_title' => $this->mlSupport->translate('Add New Employee') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Store a newly created employee in storage
     */
    public function store()
    {
        $request = $this->request();
        $data = $request->all();

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        // Basic Validation
        if (empty($data['name']) || empty($data['email'])) {
            $this->setFlash('error', $this->mlSupport->translate('Name and Email are required.'));
            return $this->back();
        }

        if (!\filter_var($data['email'], \FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', $this->mlSupport->translate('Invalid email address.'));
            return $this->back();
        }

        // Sanitize data
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitizedData[$key] = h($value);
            } else {
                $sanitizedData[$key] = $value;
            }
        }
        $data = $sanitizedData;

        $employeeModel = $this->model('Employee');
        // Check if email already exists
        $existing = $employeeModel->getEmployeeByEmail($data['email']);
        if ($existing) {
            $this->setFlash('error', $this->mlSupport->translate('Email already registered.'));
            return $this->back();
        }

        try {
            $this->db->beginTransaction();

            $success = $employeeModel->createEmployee($data);

            if ($success) {
                $employeeId = $this->db->lastInsertId();

                // Assign role if provided
                if (!empty($data['role_id'])) {
                    $employeeModel->assignRole($employeeId, $data['role_id']);
                }

                $this->logActivity('Add Employee', 'Added employee: ' . h($data['name']));

                $this->db->commit();
                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }
                $this->setFlash('success', $this->mlSupport->translate('Employee added successfully.'));
                return $this->redirect('admin/employees');
            } else {
                $this->db->rollBack();
                $this->setFlash('error', $this->mlSupport->translate('Error adding employee. Please try again.'));
                return $this->back();
            }
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->setFlash('error', $this->mlSupport->translate('Error') . ': ' . h($e->getMessage()));
            return $this->back();
        }
    }

    /**
     * Show the form for editing an employee
     */
    public function edit($id)
    {
        $id = intval($id);
        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getEmployeeById($id);
        $roles = $employeeModel->getRoles();
        $assignedRole = $employeeModel->getEmployeeRole($id);

        if (!$employee) {
            $this->setFlash('error', $this->mlSupport->translate('Employee not found.'));
            return $this->redirect('admin/employees');
        }

        return $this->render('admin/employees/edit', [
            'employee' => $employee,
            'roles' => $roles,
            'assignedRole' => $assignedRole,
            'page_title' => $this->mlSupport->translate('Edit Employee') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Update the specified employee in storage
     */
    public function update($id)
    {
        $id = intval($id);
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $request = $this->request();
        $data = $request->all();
        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getEmployeeById($id);

        if (!$employee) {
            $this->setFlash('error', $this->mlSupport->translate('Employee not found.'));
            return $this->redirect('admin/employees');
        }

        // Basic Validation
        if (empty($data['name'])) {
            $this->setFlash('error', $this->mlSupport->translate('Name is required.'));
            return $this->back();
        }

        // Sanitize data
        $sanitizedData = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $sanitizedData[$key] = h($value);
            } else {
                $sanitizedData[$key] = $value;
            }
        }
        $data = $sanitizedData;

        try {
            $this->db->beginTransaction();

            $updateData = [
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'department' => $data['department'] ?? 'General',
                'role' => $data['role'] ?? 'employee',
                'salary' => $data['salary'] ?? 0,
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'active',
                'updated_at' => \date('Y-m-d H:i:s')
            ];

            $success = $this->db->update('employees', $updateData, 'id = ?', [$id]);

            if ($success) {
                // Update role assignment
                if (!empty($data['role_id'])) {
                    $employeeModel->assignRole($id, $data['role_id']);
                }

                $this->logActivity('Update Employee', 'Updated employee: ' . h($data['name']));

                $this->db->commit();
                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }
                $this->setFlash('success', $this->mlSupport->translate('Employee updated successfully.'));
                return $this->redirect('admin/employees');
            } else {
                $this->db->rollBack();
                $this->setFlash('error', $this->mlSupport->translate('Error updating employee.'));
                return $this->back();
            }
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->setFlash('error', $this->mlSupport->translate('Error') . ': ' . h($e->getMessage()));
            return $this->back();
        }
    }

    /**
     * Remove the specified employee from storage
     */
    public function destroy($id)
    {
        $id = intval($id);
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
            return $this->back();
        }

        $success = $this->db->update('employees', [
            'status' => 'deleted',
            'updated_at' => \date('Y-m-d H:i:s'),
            'deleted_at' => \date('Y-m-d H:i:s')
        ], 'id = ?', [$id]);

        if ($success) {
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->logActivity('Delete Employee', 'Soft deleted employee ID: ' . $id);
            $this->setFlash('success', $this->mlSupport->translate('Employee deleted successfully.'));
            return $this->redirect('admin/employees');
        } else {
            $this->setFlash('error', $this->mlSupport->translate('Error deleting employee.'));
            return $this->back();
        }
    }

    /**
     * Offboard an employee (deactivate and revoke roles)
     */
    public function offboard($id)
    {
        $id = intval($id);
        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed.'));
            return $this->back();
        }

        try {
            $this->db->beginTransaction();

            $employeeModel = $this->model('Employee');
            // Fetch employee data before deactivation for notification
            $employee = $employeeModel->getEmployeeById($id);
            if (!$employee) {
                $this->setFlash('error', $this->mlSupport->translate('Employee not found.'));
                return $this->redirect('admin/employees');
            }

            // Deactivate employee
            $this->db->update('employees', ['status' => 'inactive'], 'id = ?', [$id]);

            // Remove all roles
            $this->db->delete('user_roles', 'user_id = ?', [$id]);

            $this->logActivity('Offboard Employee', 'Offboarded employee: ' . ($employee['name'] ?? $id));

            // Send Notification (Admin Notification)
            $this->sendOffboardNotification($employee);

            $this->db->commit();
            // Invalidate dashboard cache
            if (function_exists('getPerformanceManager')) {
                getPerformanceManager()->clearCache('query_');
            }
            $this->setFlash('success', $this->mlSupport->translate('Employee offboarded successfully.'));
            return $this->redirect('admin/employees');
        } catch (\Exception $e) {
            $this->db->rollBack();
            $this->setFlash('error', $this->mlSupport->translate('Error offboarding employee') . ': ' . h($e->getMessage()));
            return $this->back();
        }
    }

    protected function sendOffboardNotification($employee)
    {
        try {
            // Include legacy notification system
            require_once ABSPATH . '/includes/notification_manager.php';
            require_once ABSPATH . '/includes/email_service.php';

            $nm = new \NotificationManager(null, new \EmailService());

            // Notify Admin
            $nm->send([
                'user_id' => 1, // Admin ID
                'template' => 'EMPLOYEE_OFFBOARDED',
                'data' => [
                    'employee_name' => $employee['name'],
                    'admin_name' => $this->request()->session('auth')['username'] ?? 'Admin'
                ],
                'channels' => ['db']
            ]);
        } catch (\Exception $e) {
            // Log notification error but don't fail the offboarding process
            \error_log("Failed to send offboard notification: " . $e->getMessage());
        }
    }
}
