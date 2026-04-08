<?php
/**
 * EMI Plan Details View
 */
$emi_plan = $emi_plan ?? [];
$schedule = $schedule ?? [];
$payments = $payments ?? [];
$page_title = $page_title ?? 'EMI Plan Details';
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
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="mb-1">EMI Plan Details</h2>
                <p class="text-muted mb-0">View payment schedule and history</p>
            </div>
            <a href="<?php echo $base; ?>/admin/emi" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to EMI Plans
            </a>
        </div>
        
        <?php if (!empty($emi_plan)): ?>
        <!-- EMI Plan Info -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Plan Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Booking</p>
                        <h6><?php echo htmlspecialchars($emi_plan['booking_number'] ?? '-'); ?></h6>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Customer</p>
                        <h6><?php echo htmlspecialchars($emi_plan['customer_name'] ?? '-'); ?></h6>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Property</p>
                        <h6><?php echo htmlspecialchars($emi_plan['property_title'] ?? '-'); ?></h6>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1">Status</p>
                        <span class="badge bg-<?php echo ($emi_plan['status'] ?? '') === 'active' ? 'success' : (($emi_plan['status'] ?? '') === 'completed' ? 'info' : 'danger'); ?>">
                            <?php echo ucfirst($emi_plan['status'] ?? 'unknown'); ?>
                        </span>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-2">
                        <p class="text-muted mb-1">Total Amount</p>
                        <h6>₹<?php echo number_format($emi_plan['total_amount'] ?? 0); ?></h6>
                    </div>
                    <div class="col-md-2">
                        <p class="text-muted mb-1">Down Payment</p>
                        <h6>₹<?php echo number_format($emi_plan['down_payment'] ?? 0); ?></h6>
                    </div>
                    <div class="col-md-2">
                        <p class="text-muted mb-1">Loan Amount</p>
                        <h6>₹<?php echo number_format($emi_plan['loan_amount'] ?? 0); ?></h6>
                    </div>
                    <div class="col-md-2">
                        <p class="text-muted mb-1">Interest Rate</p>
                        <h6><?php echo $emi_plan['interest_rate'] ?? 0; ?>%</h6>
                    </div>
                    <div class="col-md-2">
                        <p class="text-muted mb-1">Tenure</p>
                        <h6><?php echo $emi_plan['tenure_months'] ?? 0; ?> months</h6>
                    </div>
                    <div class="col-md-2">
                        <p class="text-muted mb-1">Monthly EMI</p>
                        <h6>₹<?php echo number_format($emi_plan['emi_amount'] ?? 0); ?></h6>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Payment Schedule -->
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Payment Schedule</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($schedule)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Due Date</th>
                                            <th>Due Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($schedule as $item): ?>
                                            <tr>
                                                <td><?php echo $item['installment_number']; ?></td>
                                                <td><?php echo isset($item['due_date']) ? date('M d, Y', strtotime($item['due_date'])) : '-'; ?></td>
                                                <td>₹<?php echo number_format($item['due_amount'] ?? 0); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo ($item['status'] ?? '') === 'paid' ? 'success' : (($item['status'] ?? '') === 'pending' ? 'warning' : 'danger'); ?>">
                                                        <?php echo ucfirst($item['status'] ?? 'unknown'); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (($item['status'] ?? '') === 'pending'): ?>
                                                        <button class="btn btn-sm btn-primary" onclick="processPayment(<?php echo $item['id']; ?>, <?php echo $item['due_amount']; ?>)">
                                                            <i class="fas fa-money-bill-wave me-1"></i>Pay
                                                        </button>
                                                    <?php else: ?>
                                                        <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <p class="text-muted">No schedule found</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Payment History -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-history me-2"></i>Payment History</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($payments)): ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($payments as $payment): ?>
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between">
                                            <span>Installment #<?php echo $payment['installment_number'] ?? '-'; ?></span>
                                            <span class="fw-bold">₹<?php echo number_format($payment['amount'] ?? 0); ?></span>
                                        </div>
                                        <small class="text-muted">
                                            <?php echo isset($payment['payment_date']) ? date('M d, Y', strtotime($payment['payment_date'])) : '-'; ?> | 
                                            <?php echo htmlspecialchars($payment['payment_method'] ?? '-'); ?>
                                        </small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted small">No payments recorded yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            EMI plan not found.
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Process Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm">
                        <input type="hidden" id="scheduleId">
                        <div class="mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" id="paymentAmount" class="form-control" step="0.01" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method</label>
                            <select id="paymentMethod" class="form-select" required>
                                <option value="">Select method...</option>
                                <option value="cash">Cash</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank_transfer">Bank Transfer</option>
                                <option value="online">Online Payment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Transaction ID</label>
                            <input type="text" id="transactionId" class="form-control" placeholder="Optional">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" onclick="submitPayment()">Process Payment</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));
        
        function processPayment(scheduleId, amount) {
            document.getElementById('scheduleId').value = scheduleId;
            document.getElementById('paymentAmount').value = amount;
            paymentModal.show();
        }
        
        function submitPayment() {
            const formData = new FormData();
            formData.append('schedule_id', document.getElementById('scheduleId').value);
            formData.append('amount', document.getElementById('paymentAmount').value);
            formData.append('payment_method', document.getElementById('paymentMethod').value);
            formData.append('transaction_id', document.getElementById('transactionId').value);
            
            fetch('<?php echo $base; ?>/admin/emi/<?php echo $emi_plan['id'] ?? 0; ?>/process-payment', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Payment processed successfully!');
                    location.reload();
                } else {
                    alert(data.message || 'Payment failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
            
            paymentModal.hide();
        }
    </script>
</body>
</html>
