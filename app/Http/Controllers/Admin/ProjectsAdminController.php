<?php
namespace App\Http\Controllers\Admin;

class ProjectsAdminController 
{
    public function index() 
    {
        // Projects Dashboard
        include __DIR__ . "/../../../views/admin/projects/index.php";
    }
    
    public function create() 
    {
        // Create New Project
        include __DIR__ . "/../../../views/admin/projects/create.php";
    }
    
    public function edit($id) 
    {
        // Edit Project
        include __DIR__ . "/../../../views/admin/projects/edit.php";
    }
    
    public function view($id) 
    {
        // View Project Details
        include __DIR__ . "/../../../views/admin/projects/view.php";
    }
    
    public function images($id) 
    {
        // Manage Project Images
        include __DIR__ . "/../../../views/admin/projects/images.php";
    }
    
    public function status($id) 
    {
        // Update Project Status
        include __DIR__ . "/../../../views/admin/projects/status.php";
    }
}