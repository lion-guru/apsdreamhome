<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;

class HRMController extends BaseController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/hrm/index', []);
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
}