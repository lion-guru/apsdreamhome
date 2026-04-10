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
        
        // Get customer data from session
        $userId = $_SESSION['user_id'] ?? null;
        $user = [];

        if ($userId) {
            try {
                $user = $this->db->fetch(
                    "SELECT * FROM users WHERE id = ? AND status = 'active'",
                    [$userId]
                );
            } catch (\Exception $e) {
                error_log("Error getting customer: " . $e->getMessage());
            }
        }

        // Define BASE_PATH for shared view
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 3));
        }

        // Set variables for shared view
        $userRole = 'customer';
        $profileUrl = BASE_URL . '/customer/profile';
        $securityUrl = null; // Customers don't have security page yet
        $canEdit = true;

        // Use unified shared profile view
        include __DIR__ . '/../../../views/shared/profile.php';
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