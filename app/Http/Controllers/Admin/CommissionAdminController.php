<?php
namespace App\Http\Controllers\Admin;

class CommissionAdminController 
{
    public function index() 
    {
        // Commission Dashboard
        include __DIR__ . "/../../../views/admin/commission/index.php";
    }
    
    public function rules() 
    {
        // Commission Rules Management
        include __DIR__ . "/../../../views/admin/commission/rules.php";
    }
    
    public function createRule() 
    {
        // Create New Commission Rule
        include __DIR__ . "/../../../views/admin/commission/create_rule.php";
    }
    
    public function editRule($id) 
    {
        // Edit Commission Rule
        include __DIR__ . "/../../../views/admin/commission/edit_rule.php";
    }
    
    public function calculations() 
    {
        // Commission Calculations
        include __DIR__ . "/../../../views/admin/commission/calculations.php";
    }
    
    public function payments() 
    {
        // Commission Payments
        include __DIR__ . "/../../../views/admin/commission/payments.php";
    }
    
    public function reports() 
    {
        // Commission Reports
        include __DIR__ . "/../../../views/admin/commission/reports.php";
    }
}