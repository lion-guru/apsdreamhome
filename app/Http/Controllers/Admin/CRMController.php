<?php
// TODO: Consider async file operations for better performance

namespace App\Http\Controllers\Admin;

class CRMController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        return $this->render('admin/crm/index', []);
    }
    
    public function users()
    {
        $this->requireAdmin();
        return $this->render('admin/crm/users', []);
    }
    
    public function createCustomer()
    {
        $this->requireAdmin();
        return $this->render('admin/crm/users/create', []);
    }
    
    public function groups()
    {
        $this->requireAdmin();
        return $this->render('admin/crm/groups', []);
    }
    
    public function followups()
    {
        $this->requireAdmin();
        return $this->render('admin/crm/followups', []);
    }
    
    public function feedback()
    {
        $this->requireAdmin();
        return $this->render('admin/crm/feedback', []);
    }
    
    public function support()
    {
        $this->requireAdmin();
        return $this->render('admin/crm/support', []);
    }
}