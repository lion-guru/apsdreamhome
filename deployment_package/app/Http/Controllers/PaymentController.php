<?php
namespace App\Http\Controllers;

class PaymentController extends Controller
{
    public function processStripePayment()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "payment_id" => "pi_" . uniqid(),
                "status" => "succeeded",
                "amount" => 150000,
                "currency" => "usd",
                "payment_method" => "stripe"
            ]
        ]);
    }
    
    public function processPayPalPayment()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "payment_id" => "PAYID_" . uniqid(),
                "status" => "completed",
                "amount" => 150000,
                "currency" => "USD",
                "payment_method" => "paypal"
            ]
        ]);
    }
    
    public function getPaymentHistory()
    {
        return $this->json([
            "success" => true,
            "data" => [
                "payments" => [
                    [
                        "id" => "pi_123",
                        "amount" => 150000,
                        "status" => "succeeded",
                        "date" => "2026-03-01",
                        "method" => "stripe"
                    ],
                    [
                        "id" => "PAYID_456",
                        "amount" => 200000,
                        "status" => "completed",
                        "date" => "2026-02-28",
                        "method" => "paypal"
                    ]
                ],
                "total_count" => 2
            ]
        ]);
    }
}
?>