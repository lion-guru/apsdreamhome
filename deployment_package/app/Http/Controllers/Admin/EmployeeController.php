<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Models\Performance;

class EmployeeController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        // Register middlewares
        $this->middleware('csrf', ['only' => ['store', 'update', 'destroy', 'offboard']]);
    }

    /**
     * Display a listing of employees
     */
    public function index()
    {
        $filters = [
            'search' => $this->request->get('search', ''),
            'department' => $this->request->get('department', '')
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
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $data = $this->request->all();
        
        // Basic Validation
        if (empty($data['name']) || empty($data['email']) || empty($data['role'])) {
            $this->setFlash('error', $this->mlSupport->translate('Please fill in all required fields.'));
            return $this->back();
        }

        try {
            $employeeModel = $this->model('Employee');
            $result = $employeeModel->createEmployee($data);
            
            if ($result) {
                $this->setFlash('success', $this->mlSupport->translate('Employee created successfully.'));
                return $this->redirect('/admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Failed to create employee.'));
                return $this->back();
            }
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error creating employee: ') . $e->getMessage());
            return $this->back();
        }
    }

    /**
     * Display the specified employee
     */
    public function show($id)
    {
        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getEmployeeById($id);
        
        if (!$employee) {
            $this->setFlash('error', $this->mlSupport->translate('Employee not found.'));
            return $this->redirect('/admin/employees');
        }

        return $this->render('admin/employees/show', [
            'employee' => $employee,
            'page_title' => $this->mlSupport->translate('Employee Details') . ' - ' . $this->getConfig('app_name')
        ]);
    }

    /**
     * Show the form for editing the specified employee
     */
    public function edit($id)
    {
        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->getEmployeeById($id);
        
        if (!$employee) {
            $this->setFlash('error', $this->mlSupport->translate('Employee not found.'));
            return $this->redirect('/admin/employees');
        }

        $roles = $employeeModel->getRoles();
        $departments = $employeeModel->getDepartments();

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
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        $data = $this->request->all();
        
        // Basic Validation
        if (empty($data['name']) || empty($data['email']) || empty($data['role'])) {
            $this->setFlash('error', $this->mlSupport->translate('Please fill in all required fields.'));
            return $this->back();
        }

        try {
            $employeeModel = $this->model('Employee');
            $result = $employeeModel->updateEmployee($id, $data);
            
            if ($result) {
                $this->setFlash('success', $this->mlSupport->translate('Employee updated successfully.'));
                return $this->redirect('/admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Failed to update employee.'));
                return $this->back();
            }
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error updating employee: ') . $e->getMessage());
            return $this->back();
        }
    }

    /**
     * Remove the specified employee from storage
     */
    public function destroy($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        try {
            $employeeModel = $this->model('Employee');
            $result = $employeeModel->deleteEmployee($id);
            
            if ($result) {
                $this->setFlash('success', $this->mlSupport->translate('Employee deleted successfully.'));
                return $this->redirect('/admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Failed to delete employee.'));
                return $this->back();
            }
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error deleting employee: ') . $e->getMessage());
            return $this->back();
        }
    }

    /**
     * Offboard employee
     */
    public function offboard($id)
    {
        if ($this->request->method() !== 'POST') {
            $this->setFlash('error', $this->mlSupport->translate('Invalid request method.'));
            return $this->back();
        }

        if (!$this->validateCsrfToken()) {
            $this->setFlash('error', $this->mlSupport->translate('Security validation failed. Please try again.'));
            return $this->back();
        }

        try {
            $employeeModel = $this->model('Employee');
            $result = $employeeModel->offboardEmployee($id);
            
            if ($result) {
                $this->setFlash('success', $this->mlSupport->translate('Employee offboarded successfully.'));
                return $this->redirect('/admin/employees');
            } else {
                $this->setFlash('error', $this->mlSupport->translate('Failed to offboard employee.'));
                return $this->back();
            }
        } catch (Exception $e) {
            $this->setFlash('error', $this->mlSupport->translate('Error offboarding employee: ') . $e->getMessage());
            return $this->back();
        }
    }
}
