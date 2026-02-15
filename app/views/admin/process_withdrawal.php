<?php
/**
 * Process MLM Withdrawal Requests
 * 
 * This file handles POST requests from the commission dashboard.
 * It validates the request, checks the associate's balance, and records the withdrawal request.
 */

require_once __DIR__ . '/core/init.php';

// Check if user is logged in
adminAccessControl(['associate', 'admin', 'superadmin']);

// Get database instance
$db = \App\Core\App::database();

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Verification
    if (!validateCsrfToken()) {
        die('Invalid CSRF token. Action blocked.');
    }
    
    $associate_id = getAuthUserId();
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : 'bank_transfer';
    
    // Basic validation
    if ($amount <= 0) {
        setSessionget_flash('error', "Invalid withdrawal amount. Please enter a positive value.");
        header("Location: commission_dashboard.php");
        exit();
    }
    
    // Map payment method to enum values in DB
    $allowed_methods = ['bank_transfer', 'upi', 'cheque', 'cash', 'online'];
    if (!in_array($payment_method, $allowed_methods)) {
        $payment_method = 'bank_transfer';
    }
    
    // Check available balance
    // 1. Total Earned (paid status in mlm_commissions)
    // 2. Total Withdrawn/Requested (pending, approved, processed in mlm_withdrawal_requests)
    $balance_query = "SELECT 
                        (SELECT COALESCE(SUM(commission_amount), 0) FROM mlm_commissions WHERE associate_id = :aid1 AND status = 'paid') as total_earned,
                        (SELECT COALESCE(SUM(amount), 0) FROM mlm_withdrawal_requests WHERE associate_id = :aid2 AND status IN ('pending', 'approved', 'processed')) as total_withdrawn";
    
    $balance_result = $db->fetch($balance_query, ['aid1' => $associate_id, 'aid2' => $associate_id]);
    
    $total_earned = $balance_result['total_earned'] ?? 0;
    $total_withdrawn = $balance_result['total_withdrawn'] ?? 0;
    $available_balance = $total_earned - $total_withdrawn;
    
    if ($amount > $available_balance) {
        setSessionget_flash('error', "Insufficient balance. Your available balance is ₹" . number_format($available_balance, 2));
        header("Location: commission_dashboard.php#earnings");
        exit();
    }
    
    // Insert withdrawal request
    $insert_query = "INSERT INTO mlm_withdrawal_requests (associate_id, amount, payment_method, available_balance, status, request_date, created_at) 
                    VALUES (:aid, :amount, :method, :balance, 'pending', CURDATE(), NOW())";
    
    $success = $db->execute($insert_query, [
        'aid' => $associate_id,
        'amount' => $amount,
        'method' => $payment_method,
        'balance' => $available_balance
    ]);
    
    if ($success) {
        setSessionget_flash('success', "Your withdrawal request for ₹" . number_format($amount, 2) . " via " . str_replace('_', ' ', $payment_method) . " has been submitted and is pending approval.");
    } else {
        setSessionget_flash('error', "Failed to submit withdrawal request. Please contact support.");
    }
    
    header("Location: commission_dashboard.php#earnings");
    exit();
} else {
    // Redirect if not POST
    header("Location: commission_dashboard.php");
    exit();
}

