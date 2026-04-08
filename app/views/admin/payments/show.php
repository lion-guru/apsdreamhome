<?php
/**
 * Payment Details View
 */
$payment = $payment ?? [];
$history = $history ?? [];
$page_title = $page_title ?? 'Payment Details';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">Payment Details</h2>
                <p class="text-muted mb-0">Transaction #<?php echo htmlspecialchars($payment['transaction_id'] ?? '-'); ?></p>
            </div>
            <a href="<?php echo $base; ?>/admin/payments" class="btn btn-outline-secondary">Back to Payments</a>
        </div>
        
        <?php if (!empty($payment)): ?>
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Payment Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Transaction ID</p>
                                <h6><?php echo htmlspecialchars($payment['transaction_id'] ?? '-'); ?></h6>
                            </div>
                            <div class="col-md-6">
                                <p class="text-muted mb-1">Status</p>
                                <span class="badge bg-<?php echo ($payment['status'] ?? '') === 'completed' ? 'success' : (($payment['status'] ?? '') === 'pending' ? 'warning' : 'danger'); ?>">
                                    <?php echo ucfirst($payment['status'] ?? 'unknown'); ?>
                                </span>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Amount</p>
                                <h5 class="text-primary">₹<?php echo number_format($payment['amount'] ?? 0); ?></h5>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Payment Method</p>
                                <h6><?php echo ucfirst($payment['payment_method'] ?? '-'); ?></h6>
                            </div>
                            <div class="col-md-4">
                                <p class="text-muted mb-1">Payment Date</p>
                                <h6><?php echo isset($payment['payment_date']) ? date('M d, Y H:i', strtotime($payment['payment_date'])) : '-'; ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Payment History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($history)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($history as $item): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <span><?php echo ucfirst($item['action'] ?? '-'); ?></span>
                                            <small class="text-muted"><?php echo isset($item['created_at']) ? date('M d, Y H:i', strtotime($item['created_at'])) : '-'; ?></small>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">No history available</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Customer & Booking</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-1">Customer</p>
                        <h6><?php echo htmlspecialchars($payment['customer_name'] ?? '-'); ?></h6>
                        <p class="text-muted small"><?php echo htmlspecialchars($payment['customer_email'] ?? '-'); ?></p>
                        <hr>
                        <p class="text-muted mb-1">Booking</p>
                        <h6><?php echo htmlspecialchars($payment['booking_number'] ?? '-'); ?></h6>
                        <p class="text-muted small"><?php echo htmlspecialchars($payment['property_title'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">Payment not found.</div>
        <?php endif; ?>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
