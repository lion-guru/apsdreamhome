<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class CommissionAdminController extends AdminController
{
    public function index() 
    {
        $this->data['page_title'] = 'Commission Dashboard';
        return $this->render('admin/commission/index');
    }
    
    public function rules() 
    {
        $this->data['page_title'] = 'Commission Rules';
        return $this->render('admin/commission/rules');
    }
    
    public function createRule() 
    {
        $this->data['page_title'] = 'Create Commission Rule';
        return $this->render('admin/commission/create_rule');
    }
    
    public function editRule($id) 
    {
        $this->data['page_title'] = 'Edit Commission Rule';
        return $this->render('admin/commission/edit_rule');
    }
    
    public function calculations() 
    {
        $this->data['page_title'] = 'Commission Calculations';
        return $this->render('admin/commission/calculations');
    }
    
    public function payments() 
    {
        $this->data['page_title'] = 'Commission Payments';
        return $this->render('admin/commission/payments');
    }
    
    public function reports() 
    {
        $this->data['page_title'] = 'Commission Reports';
        return $this->render('admin/commission/reports');
    }
}