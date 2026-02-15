<?php
/**
 * Admin EMI Plan Details View
 */
?>

<div class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>admin" class="text-white opacity-75">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>admin/emi" class="text-white opacity-75">EMI Plans</a></li>
                        <li class="breadcrumb-item active text-white" aria-current="page">Plan Details</li>
                    </ol>
                </nav>
                <h1 class="mb-0">
                    <i class="fas fa-file-invoice-dollar me-2"></i>
                    Plan #EMI-<?php echo $plan['id']; ?>
                </h1>
            </div>
            <div class="col-lg-6 text-lg-end">
                <span class="badge bg-<?php echo $plan['status'] === 'active' ? 'success' : ($plan['status'] === 'completed' ? 'primary' : 'warning'); ?> fs-6 p-2 px-3">
                    <?php echo ucfirst($plan['status']); ?>
                </span>
            </div>
        </div>
    </div>
</div>

<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Left Column: Plan Summary -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Customer Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase">Name</label>
                            <div class="fw-bold"><?php echo htmlspecialchars($plan['customer_name']); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase">Email</label>
                            <div><?php echo htmlspecialchars($plan['email']); ?></div>
                        </div>
                        <div class="mb-3">
                            <label class="text-muted small text-uppercase">Phone</label>
                            <div><?php echo htmlspecialchars($plan['phone']); ?></div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0">Financial Summary</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Booking Total:</span>
                            <span class="fw-bold">₹<?php echo number_format($plan['booking_total'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total EMI Principal:</span>
                            <span class="fw-bold text-primary">₹<?php echo number_format($plan['total_amount'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Down Payment:</span>
                            <span class="fw-bold text-success">₹<?php echo number_format($plan['down_payment'], 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Monthly EMI:</span>
                            <span class="h5 mb-0 text-primary">₹<?php echo number_format($plan['emi_amount'], 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Tenure:</span>
                            <span><?php echo $plan['tenure_months']; ?> Months</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Payment Schedule -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Payment Schedule</h5>
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                            <i class="fas fa-plus me-1"></i> Record Payment
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Inst. #</th>
                                        <th>Due Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Payment Date</th>
                                        <th class="pe-4 text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (isset($payments) && !empty($payments)): ?>
                                        <?php $count = 1; foreach ($payments as $payment): ?>
                                            <tr>
                                                <td class="ps-4"><?php echo $count++; ?></td>
                                                <td><?php echo date('d M Y', strtotime($payment['due_date'])); ?></td>
                                                <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                                <td>
                                                    <span class="badge rounded-pill bg-<?php 
                                                        echo match($payment['status']) {
                                                            'paid' => 'success',
                                                            'overdue' => 'danger',
                                                            'pending' => 'warning',
                                                            default => 'secondary'
                                                        };
                                                    ?>">
                                                        <?php echo ucfirst($payment['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php echo $payment['payment_date'] ? date('d M Y', strtotime($payment['payment_date'])) : '-'; ?>
                                                </td>
                                                <td class="pe-4 text-end">
                                                    <?php if ($payment['status'] !== 'paid'): ?>
                                                        <button class="btn btn-sm btn-outline-success mark-paid" data-id="<?php echo $payment['id']; ?>">
                                                            Mark Paid
                                                        </button>
                                                    <?php else: ?>
                                                        <i class="fas fa-check-circle text-success"></i>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No schedule generated.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo BASE_URL; ?>admin/emi/pay" method="POST" id="paymentForm">
                <input type="hidden" name="emi_plan_id" value="<?php echo $plan['id']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Record EMI Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Amount Paid (₹)</label>
                        <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo $plan['emi_amount']; ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Transaction ID / Reference</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="Optional">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const body = new URLSearchParams();
    for (const pair of formData) {
        body.append(pair[0], pair[1]);
    }

    fetch(this.action, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: body
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error recording payment');
        }
    });
});
</script>
