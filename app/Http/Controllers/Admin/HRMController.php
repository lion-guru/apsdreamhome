<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class HRMController extends AdminController
{
    private function hr($path)
    {
        $this->requireAdmin();
        $this->redirect('/admin/hr' . $path);
        exit;
    }

    public function index()            { $this->hr('/users'); }
    public function users()            { $this->hr('/users'); }
    public function createEmployee()   { $this->hr('/users/create'); }
    public function attendance()       { $this->hr('/attendance'); }
    public function leave()            { $this->hr('/leave'); }
    public function payroll()          { $this->hr('/salary-structure'); }
    public function salarySlips()      { $this->hr('/salary-structure'); }
    public function performance()      { $this->hr('/performance'); }
    public function recruitment()      { $this->hr('/users/create'); }
    public function jobs()             { $this->hr('/settings'); }
    public function departments()      { $this->hr('/settings'); }
    public function designations()     { $this->hr('/settings'); }
    public function settings()         { $this->hr('/settings'); }
    public function applicants()       { $this->hr('/users'); }
    public function documents()        { $this->hr('/documents'); }
    public function employeeList()     { $this->hr('/users'); }
}
