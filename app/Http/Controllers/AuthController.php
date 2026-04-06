<?php
namespace App\Http\Controllers;

class AuthController 
{
    public function login() 
    {
        // Login Page
        include __DIR__ . "/../../views/auth/login.php";
    }
    
    public function register() 
    {
        // Registration Page
        include __DIR__ . "/../../views/auth/register.php";
    }
    
    public function forgotPassword() 
    {
        // Forgot Password Page
        include __DIR__ . "/../../views/auth/forgot_password.php";
    }
    
    public function resetPassword() 
    {
        // Reset Password Page
        include __DIR__ . "/../../views/auth/reset_password.php";
    }
    
    public function verifyEmail() 
    {
        // Email Verification Page
        include __DIR__ . "/../../views/auth/verify_email.php";
    }
    
    public function dashboard() 
    {
        // Customer Dashboard
        include __DIR__ . "/../../views/customer/dashboard.php";
    }
    
    public function profile() 
    {
        // Customer Profile
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
}
?>