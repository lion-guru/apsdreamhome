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
        $employeeModel = $this->model('Employee');
        $roles = $employeeModel->getRoles();
        $departments = $employeeModel->getDepartments();

        return $this->render('admin/employees/create', [
            'roles' => $roles,
            'departments' => $departments,
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
            // Map form fields to model fields if necessary
            // The view likely sends 'role_id' and 'department_id' if using select dropdowns correctly
            // If view sends 'role' instead of 'role_id', we need to map it.
            // Assuming view is updated or sends correct IDs.
            
            $employeeId = $employeeModel->createEmployee($data);

            if ($employeeId) {
                $this->logActivity('Add Employee', 'Added employee: ' . h($data['name']));

                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }
                $this->setFlash('success', $this->mlSupport->translate('Employee added successfully.'));
                return $this->redirect('admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Error adding employee. Please try again.'));
                return $this->back();
            }
        } catch (\Exception $e) {
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
        $departments = $employeeModel->getDepartments();

        if (!$employee) {
            $this->setFlash('error', $this->mlSupport->translate('Employee not found.'));
            return $this->redirect('admin/employees');
        }

        return $this->render('admin/employees/edit', [
            'employee' => $employee,
            'roles' => $roles,
            'departments' => $departments,
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
            $success = $employeeModel->updateEmployee($id, $data);

            if ($success) {
                $this->logActivity('Update Employee', 'Updated employee: ' . h($data['name']));

                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }
                $this->setFlash('success', $this->mlSupport->translate('Employee updated successfully.'));
                return $this->redirect('admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Error updating employee.'));
                return $this->back();
            }
        } catch (\Exception $e) {
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

        try {
            $employeeModel = $this->model('Employee');
            $success = $employeeModel->deleteEmployee($id);

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
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error') . ': ' . h($e->getMessage()));
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
            $employeeModel = $this->model('Employee');
            $employee = $employeeModel->getEmployeeById($id);
            
            if (!$employee) {
                $this->setFlash('error', $this->mlSupport->translate('Employee not found.'));
                return $this->redirect('admin/employees');
            }

            $success = $employeeModel->offboardEmployee($id);

            if ($success) {
                $this->logActivity('Offboard Employee', 'Offboarded employee: ' . ($employee['name'] ?? $id));
                $this->sendOffboardNotification($employee);

                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }
                $this->setFlash('success', $this->mlSupport->translate('Employee offboarded successfully.'));
                return $this->redirect('admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Error offboarding employee.'));
                return $this->back();
            }
        } catch (\Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error offboarding employee') . ': ' . h($e->getMessage()));
            return $this->back();
        }
    }

    protected function sendOffboardNotification($employee)
    {
        try {
            // Include legacy notification system if available
            if (file_exists(ABSPATH . '/includes/notification_manager.php')) {
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
            }
        } catch (\Exception $e) {
            // Log notification error but don't fail the offboarding process
            \error_log("Failed to send offboard notification: " . $e->getMessage());
        }
    }
}
