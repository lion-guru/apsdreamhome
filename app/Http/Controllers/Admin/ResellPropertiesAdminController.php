<?php
namespace App\Http\Controllers\Admin;

class ResellPropertiesAdminController extends AdminController
{
    public function index() 
    {
        $this->requireAdmin();
        return $this->render('admin/resell_properties/index', ['page_title' => 'Resell Properties']);
    }
    
    public function create() 
    {
        $this->requireAdmin();
        return $this->render('admin/resell_properties/create', ['page_title' => 'Add Resell Property']);
    }
    
    public function edit($id) 
    {
        $this->requireAdmin();
        return $this->render('admin/resell_properties/edit', ['page_title' => 'Edit Resell Property', 'id' => $id]);
    }
    
    public function details($id) 
    {
        $this->requireAdmin();
        return $this->render('admin/resell_properties/view', ['page_title' => 'Resell Property Details', 'id' => $id]);
    }
    
    public function images($id) 
    {
        $this->requireAdmin();
        return $this->render('admin/resell_properties/images', ['page_title' => 'Property Images', 'id' => $id]);
    }
    
    public function status($id) 
    {
        $this->requireAdmin();
        return $this->render('admin/resell_properties/status', ['page_title' => 'Update Status', 'id' => $id]);
    }
    
    public function commission($id) 
    {
        $this->requireAdmin();
        return $this->render('admin/resell_properties/commission', ['page_title' => 'Manage Commission', 'id' => $id]);
    }
}
