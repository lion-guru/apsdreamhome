<?php
namespace App\Services;

class PaymentService 
{
    private $db;
    private $settings;
    
    public function __construct() {
        $this->db = new PDO("mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->loadSettings();
    }
    
    private function loadSettings() {
        $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM payment_settings");
        $stmt->execute();
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $this->settings[$row["setting_key"]] = $row["setting_value"];
        }
    }
    
    public function initiatePayment($data) {
        $paymentId = "PAY" . time() . mt_rand(1000, 9999);
        $transactionId = "TXN" . time() . mt_rand(1000, 9999);
        
        // Calculate tax and total amount
        $taxAmount = $data["amount"] * ($this->settings["tax_rate"] / 100);
        $totalAmount = $data["amount"] + $taxAmount - $data["discount_amount"];
        
        // Insert payment record
        $stmt = $this->db->prepare("INSERT INTO payments (
            payment_id, transaction_id, reference_id, customer_id, property_id, property_type,
            payment_type, amount, currency, tax_amount, discount_amount, total_amount,
            gateway, status, description, ip_address, user_agent, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $stmt->execute([
            $paymentId,
            $transactionId,
            $data["reference_id"] ?? null,
            $data["customer_id"] ?? null,
            $data["property_id"] ?? null,
            $data["property_type"] ?? null,
            $data["payment_type"],
            $data["amount"],
            $data["currency"] ?? "INR",
            $taxAmount,
            $data["discount_amount"] ?? 0,
            $totalAmount,
            $data["gateway"],
            "pending",
            $data["description"] ?? "",
            $_SERVER["REMOTE_ADDR"] ?? "",
            $_SERVER["HTTP_USER_AGENT"] ?? ""
        ]);
        
        // Create payment notification
        $this->createNotification($paymentId, "payment_initiated", "Payment Initiated", 
            "Your payment of ₹" . number_format($totalAmount, 2) . " has been initiated.",
            $data["customer_id"] ?? null);
        
        return [
            "success" => true,
            "payment_id" => $paymentId,
            "transaction_id" => $transactionId,
            "amount" => $totalAmount,
            "gateway" => $data["gateway"]
        ];
    }
    
    public function processRazorpay($paymentId, $data) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            return ["success" => false, "message" => "Payment not found"];
        }
        
        // Update payment with gateway response
        $updateStmt = $this->db->prepare("UPDATE payments SET 
            gateway_transaction_id = ?, gateway_response = ?, status = ?, 
            payment_date = ?, payment_time = ?, updated_at = NOW()
            WHERE payment_id = ?");
        
        $status = $data["status"] === "captured" ? "completed" : "failed";
        $updateStmt->execute([
            $data["razorpay_payment_id"] ?? "",
            json_encode($data),
            $status,
            date("Y-m-d"),
            date("H:i:s"),
            $paymentId
        ]);
        
        if ($status === "completed") {
            $this->createNotification($paymentId, "payment_success", "Payment Successful", 
                "Your payment of ₹" . number_format($payment["total_amount"], 2) . " has been successfully processed.",
                $payment["customer_id"]);
        } else {
            $this->createNotification($paymentId, "payment_failed", "Payment Failed", 
                "Your payment of ₹" . number_format($payment["total_amount"], 2) . " has failed. Please try again.",
                $payment["customer_id"]);
        }
        
        return ["success" => true, "status" => $status];
    }
    
    public function processPaytm($paymentId, $data) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            return ["success" => false, "message" => "Payment not found"];
        }
        
        $status = ($data["STATUS"] === "TXN_SUCCESS") ? "completed" : "failed";
        
        $updateStmt = $this->db->prepare("UPDATE payments SET 
            gateway_transaction_id = ?, gateway_response = ?, status = ?, 
            payment_date = ?, payment_time = ?, updated_at = NOW()
            WHERE payment_id = ?");
        
        $updateStmt->execute([
            $data["TXNID"] ?? "",
            json_encode($data),
            $status,
            date("Y-m-d"),
            date("H:i:s"),
            $paymentId
        ]);
        
        if ($status === "completed") {
            $this->createNotification($paymentId, "payment_success", "Payment Successful", 
                "Your payment of ₹" . number_format($payment["total_amount"], 2) . " has been successfully processed.",
                $payment["customer_id"]);
        } else {
            $this->createNotification($paymentId, "payment_failed", "Payment Failed", 
                "Your payment of ₹" . number_format($payment["total_amount"], 2) . " has failed. Please try again.",
                $payment["customer_id"]);
        }
        
        return ["success" => true, "status" => $status];
    }
    
    public function calculateEMI($principal, $rate, $tenure) {
        $monthlyRate = $rate / 12 / 100;
        $emi = $principal * $monthlyRate * pow(1 + $monthlyRate, $tenure) / (pow(1 + $monthlyRate, $tenure) - 1);
        
        return [
            "emi" => round($emi, 2),
            "total_interest" => round(($emi * $tenure) - $principal, 2),
            "total_amount" => round($emi * $tenure, 2)
        ];
    }
    
    public function getPaymentPlans() {
        $stmt = $this->db->prepare("SELECT * FROM payment_plans WHERE is_active = 1 ORDER BY is_default DESC, plan_name ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPaymentHistory($customerId, $limit = 20, $offset = 0) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE customer_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->execute([$customerId, $limit, $offset]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPayment($paymentId) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $stmt->execute([$paymentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function refundPayment($paymentId, $refundAmount, $refundReason) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $stmt->execute([$paymentId]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$payment) {
            return ["success" => false, "message" => "Payment not found"];
        }
        
        $refundTransactionId = "REF" . time() . mt_rand(1000, 9999);
        
        $updateStmt = $this->db->prepare("UPDATE payments SET 
            refund_amount = ?, refund_reason = ?, refund_date = ?, refund_transaction_id = ?, 
            status = ?, updated_at = NOW()
            WHERE payment_id = ?");
        
        $updateStmt->execute([
            $refundAmount,
            $refundReason,
            date("Y-m-d"),
            $refundTransactionId,
            "refunded",
            $paymentId
        ]);
        
        $this->createNotification($paymentId, "payment_refunded", "Payment Refunded", 
            "Your refund of ₹" . number_format($refundAmount, 2) . " has been processed. Reason: " . $refundReason,
            $payment["customer_id"]);
        
        return ["success" => true, "refund_transaction_id" => $refundTransactionId];
    }
    
    public function getPaymentSettings() {
        return $this->settings;
    }
    
    public function updatePaymentSetting($key, $value) {
        $stmt = $this->db->prepare("INSERT INTO payment_settings (setting_key, setting_value, setting_type, setting_category) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()");
        return $stmt->execute([$key, $value, "string", "general"]);
    }
    
    private function createNotification($paymentId, $type, $title, $message, $customerId = null) {
        $stmt = $this->db->prepare("SELECT customer_email, customer_phone FROM customers WHERE id = ?");
        $stmt->execute([$customerId]);
        $customer = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $notifStmt = $this->db->prepare("INSERT INTO payment_notifications (
            payment_id, notification_type, title, message, customer_id, customer_email, customer_phone, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $notifStmt->execute([
            $paymentId,
            $type,
            $title,
            $message,
            $customerId,
            $customer["customer_email"] ?? null,
            $customer["customer_phone"] ?? null
        ]);
        
        // Send email notification (implement email service)
        // Send SMS notification (implement SMS service)
    }
    
    public function generatePaymentLink($paymentId, $gateway) {
        $baseUrl = $this->settings["payment_success_url"] ?? "https://apsdreamhome.com/payment/success";
        
        switch ($gateway) {
            case "razorpay":
                return "https://razorpay.com/payment/" . $paymentId;
            case "paytm":
                return "https://secure.paytm.in/order/pay?orderId=" . $paymentId;
            case "phonepe":
                return "upi://pay?pa=" . $this->settings["upi_vpa"] . "&pn=APS Dream Home&am=" . $paymentId;
            default:
                return $baseUrl . "?payment_id=" . $paymentId;
        }
    }
}
?>