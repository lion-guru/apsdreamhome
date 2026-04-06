<?php
namespace App\Http\Controllers;

class PaymentController 
{
    public function index() 
    {
        // Payment Dashboard
        include __DIR__ . "/../../views/payment/index.php";
    }
    
    public function initiate() 
    {
        // Initiate Payment
        include __DIR__ . "/../../views/payment/initiate.php";
    }
    
    public function process() 
    {
        // Process Payment
        include __DIR__ . "/../../views/payment/process.php";
    }
    
    public function success() 
    {
        // Payment Success
        include __DIR__ . "/../../views/payment/success.php";
    }
    
    public function failure() 
    {
        // Payment Failure
        include __DIR__ . "/../../views/payment/failure.php";
    }
    
    public function webhook() 
    {
        // Payment Webhook
        include __DIR__ . "/../../views/payment/webhook.php";
    }
    
    public function history() 
    {
        // Payment History
        include __DIR__ . "/../../views/payment/history.php";
    }
    
    public function plans() 
    {
        // Payment Plans
        include __DIR__ . "/../../views/payment/plans.php";
    }
    
    public function emiCalculator() 
    {
        // EMI Calculator
        include __DIR__ . "/../../views/payment/emi_calculator.php";
    }
    
    public function refund() 
    {
        // Refund Payment
        include __DIR__ . "/../../views/payment/refund.php";
    }
    
    public function settings() 
    {
        // Payment Settings
        include __DIR__ . "/../../views/payment/settings.php";
    }
}
?>