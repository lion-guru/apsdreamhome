<?php
namespace App\Http\Controllers\Admin;

class ResellPropertiesAdminController 
{
    public function index() 
    {
        // Resell Properties Dashboard
        include __DIR__ . "/../../../views/admin/resell_properties/index.php";
    }
    
    public function create() 
    {
        // Create New Resell Property
        include __DIR__ . "/../../../views/admin/resell_properties/create.php";
    }
    
    public function edit($id) 
    {
        // Edit Resell Property
        include __DIR__ . "/../../../views/admin/resell_properties/edit.php";
    }
    
    public function view($id) 
    {
        // View Resell Property Details
        include __DIR__ . "/../../../views/admin/resell_properties/view.php";
    }
    
    public function images($id) 
    {
        // Manage Property Images
        include __DIR__ . "/../../../views/admin/resell_properties/images.php";
    }
    
    public function status($id) 
    {
        // Update Property Status
        include __DIR__ . "/../../../views/admin/resell_properties/status.php";
    }
    
    public function commission($id) 
    {
        // Manage Commission
        include __DIR__ . "/../../../views/admin/resell_properties/commission.php";
    }
}