<?php
namespace App\Http\Controllers\Admin;

class MLMController 
{
    public function index() 
    {
        // MLM Dashboard
        include __DIR__ . "/../../../views/admin/mlm/dashboard.php";
    }
    
    public function associates() 
    {
        // Associates Management
        include __DIR__ . "/../../../views/admin/mlm/associates/index.php";
    }
    
    public function createAssociate() 
    {
        // Create New Associate
        include __DIR__ . "/../../../views/admin/mlm/associates/create.php";
    }
    
    public function commission() 
    {
        // Commission Management
        include __DIR__ . "/../../../views/admin/mlm/commission/index.php";
    }
    
    public function network() 
    {
        // Network Tree View
        include __DIR__ . "/../../../views/admin/mlm/network/tree.php";
    }
    
    public function payouts() 
    {
        // Payout Management
        include __DIR__ . "/../../../views/admin/mlm/payouts/index.php";
    }
}