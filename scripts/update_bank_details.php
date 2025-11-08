<?php
session_start();
require_once 'config.php';
require_once 'includes/csrf.php';
if (!isset($_SESSION['aid'])) { http_response_code(403); exit('Not authorized'); }
$aid = $_SESSION['aid'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'], 'bank')) {
        http_response_code(403); exit('Invalid CSRF token');
    }
    $fields = ['account_holder','bank_name','account_number','ifsc','branch','pan','aadhaar'];
    $data = [];
    foreach($fields as $f) $data[$f] = trim($_POST[$f] ?? '');
    // Improved input validation
    if (!$data['account_holder'] || !$data['bank_name'] || !$data['account_number'] || !$data['ifsc']) {
        header('Location: associate_dashboard.php?bank_error=1'); exit;
    }
    // Validate IFSC (simple regex)
    if (!preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $data['ifsc'])) {
        header('Location: associate_dashboard.php?bank_error=1'); exit;
    }
    // Validate PAN (if provided)
    if ($data['pan'] && !preg_match('/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/', $data['pan'])) {
        header('Location: associate_dashboard.php?bank_error=1'); exit;
    }
    // Validate Aadhaar (if provided)
    if ($data['aadhaar'] && !preg_match('/^[2-9]{1}[0-9]{11}$/', $data['aadhaar'])) {
        header('Location: associate_dashboard.php?bank_error=1'); exit;
    }
    // Insert/update
    $stmt = $con->prepare("INSERT INTO associate_bank_details (associate_id, account_holder, bank_name, account_number, ifsc, branch, pan, aadhaar) VALUES (?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE account_holder=VALUES(account_holder), bank_name=VALUES(bank_name), account_number=VALUES(account_number), ifsc=VALUES(ifsc), branch=VALUES(branch), pan=VALUES(pan), aadhaar=VALUES(aadhaar), updated_at=NOW()");
    $stmt->bind_param('isssssss', $aid, $data['account_holder'], $data['bank_name'], $data['account_number'], $data['ifsc'], $data['branch'], $data['pan'], $data['aadhaar']);
    $stmt->execute();
    $stmt->close();
    header('Location: associate_dashboard.php?bank_success=1');
    exit;
}
header('Location: associate_dashboard.php');
