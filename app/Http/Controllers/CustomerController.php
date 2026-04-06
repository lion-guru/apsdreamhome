<?php
namespace App\Http\Controllers;

class CustomerController 
{
    public function index() 
    {
        // Customer Dashboard
        include __DIR__ . "/../../views/customer/dashboard.php";
    }
    
    public function profile() 
    {
        // Customer Profile Management
        include __DIR__ . "/../../views/customer/profile.php";
    }
    
    public function wishlist() 
    {
        // Customer Wishlist
        include __DIR__ . "/../../views/customer/wishlist.php";
    }
    
    public function inquiries() 
    {
        // Customer Inquiries
        include __DIR__ . "/../../views/customer/inquiries.php";
    }
    
    public function documents() 
    {
        // Customer Documents
        include __DIR__ . "/../../views/customer/documents.php";
    }
    
    public function settings() 
    {
        // Customer Settings
        include __DIR__ . "/../../views/customer/settings.php";
    }
    
    public function propertyHistory() 
    {
        // Property History
        include __DIR__ . "/../../views/customer/property_history.php";
    }
    
    public function payments() 
    {
        // Payment History
        include __DIR__ . "/../../views/customer/payments.php";
    }
    
    public function notifications() 
    {
        // Notifications
        include __DIR__ . "/../../views/customer/notifications.php";
    }
}
?>