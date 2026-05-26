<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class HRMController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/index', []);
    }

    public function employees()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/employees/index', []);
    }
    
    public function createEmployee()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/employees/create', []);
    }
    
    public function attendance()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/attendance', []);
    }
    
    public function leave()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/leave', []);
    }
    
    public function payroll()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/payroll', []);
    }
    
    public function salarySlips()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/salary-slips', []);
    }
    
    public function performance()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/performance', []);
    }
    
    public function recruitment()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/recruitment', []);
    }
    
    public function jobs()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/jobs', []);
    }
    
    public function departments()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/departments', []);
    }
    
    public function designations()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/designations', []);
    }
    
    public function settings()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/settings', []);
    }

    public function applicants()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/applicants', []);
    }

    public function documents()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/documents', []);
    }

    public function employeeList()
    {
        $this->requireAdmin();
        return $this->render('admin/employees/index', ['page_title' => 'Employees']);
    }
}