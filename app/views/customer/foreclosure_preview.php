<?php
/** @var array $booking */
/** @var array $foreclosure */
/** @var array $upi */
$booking = $booking ?? [];
$foreclosure = $foreclosure ?? [];
$upi = $upi ?? [];
$base = BASE_URL ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foreclosure Preview - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        .foreclosure-hero { background: linear-gradient(135deg, #0d6efd, #0b5ed7); color: #fff; border-radius: 16px; }
        .rebate-badge { background: linear-gradient(135deg, #198754, #157347); color: #fff; }
        .upi-qr-box img { width: 200px; height: 200px; border: 1px solid #dee2e6; border-radius: 8px; background: #fff; padding: 10px; }
        .installment-row { border-left: 4px solid #0d6efd; }
        .installment-row.overdue { border-left-color: #dc3545; }
        .installment-row.paid { border-left-color: #198754; }
        @media print { .no-print { display: none !important; } .foreclosure-hero { -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    </style>
</head>
<body>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="mb-1"><i class="fas fa-money-bill-wave me-2"></i>Foreclosure Preview</h4>
            <p class="text-muted mb-0">Booking: <?= htmlspecialchars($booking['booking_number'] ?? '') ?> | Plot: <strong><?= htmlspecialchars($booking['plot_number'] ?? 'N/A') ?></strong></p>
        </div>
        <a href="<?= $base ?>/customer/passbook?booking_id=<?= (int)$booking['id'] ?>" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Passbook</a>
    </div>

    <!-- Rebate Summary Card -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
                <div class="text-muted small d-block">Total Principal</div>
                <strong class="h4 text-primary">₹<?= number_format(floatval($foreclosure['total_principal'] ?? 0), 2) ?></strong>
            </div></div></div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
                <div class="text-muted small d-block">Rebate</div>
                <strong class="h4 rebate-badge px-3 py-1 rounded"><?= floatval($foreclosure['rebate_percent'] ?? 0) ?>% Off</strong>
                <div class="text-success small mt-1">₹<?= number_format(floatval($foreclosure['rebate_amount'] ?? 0), 2) ?></div>
            </div></div></div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
                <div class="text-muted small d-block">Months Remaining</div>
                <strong class="h4 text-info"><?= (int)($foreclosure['months_remaining'] ?? 0) ?></strong>
            </div></div></div>
        <div class="col-md-3">
            <div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
                <div class="text-muted small d-block">Final Payoff</div>
                <strong class="h4 text-success">₹<?= number_format(floatval($foreclosure['final_amount'] ?? 0), 2) ?></strong>
            </div></div></div>
    </div>

    <!-- UPI Payoff Section -->
    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header"><span><i class="fas fa-qrcode me-2"></i>Instant Payoff via UPI</span></div>
        <div class="aps-cp-card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <div class="upi-qr-box">
                        <img src="<?= htmlspecialchars($upi['qr_url'] ?? '') ?>" alt="UPI QR Code">
                    </div>
                    <div class="mt-2 small text-muted">Scan with GPay / PhonePe / Paytm</div>
                </div>
                <div class="col-md-6">
                    <div class="bg-light p-3 rounded">
                        <div class="fw-bold fs-5 text-success">₹<?= number_format(floatval($foreclosure['final_amount'] ?? 0), 2) ?></div>
                        <div class="small text-muted">Payable Amount (after rebate)</div>
                        <div class="mt-2 small text-muted">
                            UPI ID: <strong><?= htmlspecialchars($upi['upi_id'] ?? '') ?></strong><br>
                            Ref: <code><?= htmlspecialchars($upi['txn_ref'] ?? '') ?></code>
                        </div>
                        <div class="mt-3">
                            <a class="btn btn-success btn-lg w-100 no-print" href="<?= htmlspecialchars($upi['upi_string'] ?? '') ?>" target="_blank">
                                <i class="fab fa-google-pay me-1"></i>Pay with GPay
                            </a>
                            <div class="d-flex gap-2 mt-2 no-print">
                                <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars($upi['upi_string'] ?? '') ?>">PhonePe</a>
                                <a class="btn btn-outline-danger btn-sm" href="<?= htmlspecialchars($upi['upi_string'] ?? '') ?>">Paytm</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Installment Breakdown -->
    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header"><span><i class="fas fa-list-check me-2"></i>Installment Breakdown (Rebate Applied)</span></div>
        <div class="aps-cp-card-body p-0">
            <?php if (empty($foreclosure['installments'])): ?>
                <div class="text-center text-muted py-4">No pending installments</div>
            <?php else: ?>
            <div class="table-responsive"><table class="table table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>#</th><th>Due Date</th><th>Amount</th><th>Status</th><th>Rebate Applied</th>
                </tr></thead>
                <tbody>
                <?php foreach ($foreclosure['installments'] as $inst): 
                    $remaining = max(0, (float)$inst['amount'] - (float)$inst['paid_amount']);
                    $isOverdue = $inst['status'] === 'overdue';
                    $st = $inst['status'] ?? 'pending';
                ?>
                <tr class="installment-row <?= $st ?>">
                    <td><?= (int)$inst['installment_no'] ?></td>
                    <td><?= date('d M Y', strtotime($inst['due_date'])) ?><?= $isOverdue ? ' <span class="badge bg-danger">Overdue</span>' : '' ?></td>
                    <td class="fw-bold">₹<?= number_format(floatval($inst['amount']), 2) ?></td>
                    <td><span class="badge bg-<?= $st === 'paid' ? 'success' : ($st === 'overdue' ? 'danger' : 'warning text-dark') ?>"><?= ucfirst($st) ?></span></td>
                    <td>
                        <?php if ($st !== 'paid'): ?>
                            <span class="badge bg-success">-<?= floatval($foreclosure['rebate_percent']) ?>%</span>
                        <?php else: ?>
                            <span class="text-muted">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Payoff Form -->
    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header"><span><i class="fas fa-bolt me-2"></i>Process Foreclosure Now</span></div>
        <div class="aps-cp-card-body">
            <form method="POST" action="<?= BASE_URL ?>/customer/foreclosure/process" id="forecloseForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">
                <input type="hidden" name="amount" value="<?= floatval($foreclosure['final_amount']) ?>">
                <input type="hidden" name="payment_method" value="upi" id="payMethod">
                <input type="hidden" name="transaction_id" value="" id="txnRef">

                <div class="row g-3 mb-3">
                    <div class="col-md-4"><label class="form-label small">Final Payoff Amount</label>
                        <input type="text" class="form-control form-control-sm" value="₹<?= number_format(floatval($foreclosure['final_amount']), 2) ?>" readonly style="background:#f8f9fa"></div>
                    <div class="col-md-4"><label class="form-label small">Rebate Applied</label>
                        <input type="text" class="form-control form-control-sm" value="<?= floatval($foreclosure['rebate_percent']) ?>% (₹<?= number_format(floatval($foreclosure['rebate_amount']), 2) ?>)" readonly style="background:#f8f9fa"></div>
                    <div class="col-md-4"><label class="form-label small">Payment Method</label>
                        <select name="payment_method" class="form-select form-select-sm" id="payMethodSelect">
                            <option value="upi" selected>UPI (GPay/PhonePe/Paytm)</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="cash">Cash</option>
                        </select></div>
                </div>
                <div class="mb-3">
                    <label class="form-label small">Transaction Reference (optional)</label>
                    <input type="text" name="transaction_id" id="txnRefInput" class="form-control form-control-sm" placeholder="UTR / Cheque No / Receipt No">
                </div>
                <div class="alert alert-info small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Paying <strong>₹<?= number_format(floatval($foreclosure['final_amount']), 2) ?></strong> will clear all <strong><?= (int)$foreclosure['months_remaining'] ?></strong> remaining installments with a <strong><?= floatval($foreclosure['rebate_percent']) ?>% rebate</strong> (₹<?= number_format(floatval($foreclosure['rebate_amount']), 2) ?> saved).
                </div>
                <button type="submit" class="btn btn-warning btn-lg w-100 no-print" onclick="return confirm('Confirm foreclosure payment?')">
                    <i class="fas fa-check-circle me-1"></i>Confirm & Process Foreclosure
                </button>
            </form>
        </div>
    </div>

    <!-- UPI Apps -->
    <div class="d-flex gap-2 justify-content-center mb-3 no-print">
        <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars($upi['upi_string'] ?? '') ?>" target="_blank"><i class="fab fa-google-pay me-1"></i>GPay</a>
        <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars($upi['upi_string'] ?? '') ?>" target="_blank"><i class="fab fa-phonepe me-1"></i>PhonePe</a>
        <a class="btn btn-outline-primary btn-sm" href="<?= htmlspecialchars($upi['upi_string'] ?? '') ?>" target="_blank"><i class="fab fa-paytm me-1"></i>Paytm</a>
        <button class="btn btn-outline-success btn-sm" onclick="downloadQR()"><i class="fas fa-download me-1"></i>Download QR</button>
    </div>

    <script>
        function downloadQR() {
            const link = document.createElement('a');
            link.href = '<?= htmlspecialchars($upi['qr_url']) ?>';
            link.download = 'foreclosure-qr-<?= $booking['booking_number'] ?>.png';
            link.click();
        }
    </script>
</body>
</html>