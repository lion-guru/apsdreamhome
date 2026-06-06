<?php
$pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$pdo->exec("
ALTER TABLE kyc_requests
ADD COLUMN pan_document VARCHAR(255) NULL AFTER pan_number,
ADD COLUMN aadhaar_front_document VARCHAR(255) NULL AFTER aadhaar_number,
ADD COLUMN aadhaar_back_document VARCHAR(255) NULL AFTER aadhaar_front_document,
ADD COLUMN verified_by BIGINT(20) UNSIGNED NULL AFTER reason,
ADD COLUMN verified_at TIMESTAMP NULL AFTER verified_by,
ADD COLUMN rejection_reason TEXT NULL AFTER verified_at
");
echo "kyc_requests document columns added\n";
