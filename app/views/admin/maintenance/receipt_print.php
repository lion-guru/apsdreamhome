<?php
/**
 * Maintenance Payment Receipt (print view)
 * Expects: $due (maintenance_dues row + colony_name, customer_name,
 * customer_phone, booking_number, plot_number)
 */
$due = $due ?? [];
$fmt = function ($v) { return '₹' . number_format((float)($v ?? 0), 2); };
$receiptNo = $due['receipt_id'] ?? ($due['receipt_number'] ?? ('MNT-' . ($due['id'] ?? 0)));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Receipt <?= htmlspecialchars($receiptNo) ?> - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; padding: 16px; }
        }
        .receipt-box { max-width: 720px; margin: 0 auto; border: 1px solid #dee2e6; border-radius: 12px; overflow: hidden; }
        .receipt-header { background: linear-gradient(135deg, #0f172a, #1e3a5f); color: #fff; padding: 20px 24px; }
        .receipt-body { padding: 24px; }
        .detail-row { display: flex; padding: 6px 0; border-bottom: 1px dashed #e5e7eb; }
        .detail-label { width: 220px; font-weight: 600; color: #374151; }
        .detail-value { flex: 1; color: #111827; }
        .total-row { font-size: 1.1rem; font-weight: 700; }
        .stamp { border: 2px dashed #0d9488; color: #0d9488; border-radius: 8px; padding: 6px 14px; font-weight: 700; display: inline-block; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="no-print text-center mb-3">
            <a href="<?= BASE_URL ?>/admin/maintenance-dues" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to Dues
            </a>
            <button class="btn btn-primary ms-2 js-print">
                <i class="fas fa-print me-1"></i>Print / Save PDF
            </button>
        </div>
        <div class="receipt-box">
            <div class="receipt-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0 fw-bold">APS Dream Home</h4>
                    <small>Society Maintenance Receipt</small>
                </div>
                <span class="stamp"><?= htmlspecialchars(strtoupper($due['status'] ?? 'paid')) ?></span>
            </div>
            <div class="receipt-body">
                <div class="detail-row">
                    <span class="detail-label">Receipt No</span>
                    <span class="detail-value fw-bold"><?= htmlspecialchars($receiptNo) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Customer</span>
                    <span class="detail-value"><?= htmlspecialchars($due['customer_name'] ?? '—') ?> (<?= htmlspecialchars($due['customer_phone'] ?? '—') ?>)</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Colony / Plot</span>
                    <span class="detail-value"><?= htmlspecialchars($due['colony_name'] ?? '—') ?> / <?= htmlspecialchars($due['plot_number'] ?? '—') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Booking No</span>
                    <span class="detail-value"><?= htmlspecialchars($due['booking_number'] ?? '—') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Billing Period</span>
                    <span class="detail-value"><?= htmlspecialchars($due['bill_month'] ?? '—') ?>-<?= htmlspecialchars($due['bill_year'] ?? '') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Base (<?= htmlspecialchars($due['area_sqft'] ?? 0) ?> sqft × ₹<?= htmlspecialchars($due['rate_per_sqft'] ?? 0) ?>)</span>
                    <span class="detail-value"><?= $fmt($due['base_amount'] ?? 0) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Water + Security</span>
                    <span class="detail-value"><?= $fmt(($due['water_charges'] ?? 0) + ($due['security_charges'] ?? 0)) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Other Charges</span>
                    <span class="detail-value"><?= $fmt($due['other_charges'] ?? 0) ?></span>
                </div>
                <div class="detail-row total-row">
                    <span class="detail-label">Amount Paid</span>
                    <span class="detail-value text-success"><?= $fmt($due['paid_amount'] ?? 0) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Payment Mode</span>
                    <span class="detail-value"><?= htmlspecialchars($due['payment_mode'] ?? '—') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Paid On</span>
                    <span class="detail-value"><?= htmlspecialchars($due['paid_at'] ?? '—') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Due Date Was</span>
                    <span class="detail-value"><?= htmlspecialchars($due['due_date'] ?? '—') ?></span>
                </div>
                <p class="text-muted small mt-3 mb-0">System-generated receipt. Pending dues block registry NOC issuance.</p>
            </div>
        </div>
        <div class="no-print text-center mt-3">
            <button class="btn btn-primary js-print">
                <i class="fas fa-print me-1"></i>Print / Save PDF
            </button>
        </div>
    </div>
<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">document.querySelectorAll(".js-print").forEach(function(b){b.addEventListener("click",function(){window.print();});});</script></body>
</html>
