<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3 mb-0 text-gray-800"><?php echo $mlSupport->translate('EMI Plan Details'); ?></h1>
            <p class="text-muted small">ID: #<?php echo $plan['id']; ?></p>
        </div>
        <div class="col-md-6 text-end">
            <?php if ($plan['status'] === 'active'): ?>
                <button type="button" class="btn btn-warning shadow-sm me-2" onclick="openForecloseModal(<?php echo $plan['id']; ?>)">
                    <i class="fas fa-hand-holding-usd fa-sm text-white-50"></i> <?php echo $mlSupport->translate('Foreclose Plan'); ?>
                </button>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>admin/emi" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left fa-sm text-white-50"></i> <?php echo $mlSupport->translate('Back to List'); ?>
            </a>
        </div>
    </div>

    <!-- Plan Overview Cards -->
    <div class="row g-4 mb-4">
        <!-- Customer Details -->
        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 fw-bold"><?php echo $mlSupport->translate('Customer Details'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="small text-muted d-block"><?php echo $mlSupport->translate('Name'); ?></label>
                        <span class="fw-bold"><?php echo htmlspecialchars($plan['customer_name']); ?></span>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted d-block"><?php echo $mlSupport->translate('Contact'); ?></label>
                        <div><i class="fas fa-phone me-2 text-muted"></i> <?php echo htmlspecialchars($plan['customer_phone']); ?></div>
                        <div><i class="fas fa-envelope me-2 text-muted"></i> <?php echo htmlspecialchars($plan['customer_email']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Property Details -->
        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="m-0 fw-bold"><?php echo $mlSupport->translate('Property Details'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="small text-muted d-block"><?php echo $mlSupport->translate('Title'); ?></label>
                        <span class="fw-bold"><?php echo htmlspecialchars($plan['property_title']); ?></span>
                    </div>
                    <?php if (!empty($plan['property_location'])): ?>
                        <div class="mb-3">
                            <label class="small text-muted d-block"><?php echo $mlSupport->translate('Location'); ?></label>
                            <span><i class="fas fa-map-marker-alt me-2 text-muted"></i> <?php echo htmlspecialchars($plan['property_location']); ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Plan Summary -->
        <div class="col-md-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-success text-white py-3">
                    <h6 class="m-0 fw-bold"><?php echo $mlSupport->translate('Plan Summary'); ?></h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="small text-muted d-block"><?php echo $mlSupport->translate('Total Amount'); ?></label>
                            <span class="fw-bold">₹<?php echo number_format($plan['total_amount'], 2); ?></span>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block"><?php echo $mlSupport->translate('Down Payment'); ?></label>
                            <span class="fw-bold text-success">₹<?php echo number_format($plan['down_payment'], 2); ?></span>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block"><?php echo $mlSupport->translate('EMI Amount'); ?></label>
                            <span class="fw-bold text-primary">₹<?php echo number_format($plan['emi_amount'], 2); ?></span>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted d-block"><?php echo $mlSupport->translate('Status'); ?></label>
                            <span class="badge bg-<?php echo $plan['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($plan['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if ($plan['status'] === 'completed' && !empty($plan['foreclosure_date'])): ?>
        <!-- Foreclosure Info -->
        <div class="alert alert-info shadow-sm border-0 mb-4">
            <h6 class="alert-heading fw-bold"><i class="fas fa-info-circle me-2"></i><?php echo $mlSupport->translate('Plan Foreclosed'); ?></h6>
            <p class="mb-0">
                This plan was foreclosed on <strong><?php echo date('d M Y', strtotime($plan['foreclosure_date'])); ?></strong>
                with a final settlement of <strong>₹<?php echo number_format($plan['foreclosure_amount'], 2); ?></strong>.
            </p>
        </div>
    <?php endif; ?>

    <!-- Installments Table -->
    <div class="card shadow border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 fw-bold text-primary"><?php echo $mlSupport->translate('Installment Schedule'); ?></h6>
            <?php if ($plan['status'] === 'active'): ?>
                <button class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="fas fa-plus me-1"></i> <?php echo $mlSupport->translate('Record Payment'); ?>
                </button>
            <?php endif; ?>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th><?php echo $mlSupport->translate('Due Date'); ?></th>
                            <th><?php echo $mlSupport->translate('Amount'); ?></th>
                            <th><?php echo $mlSupport->translate('Principal'); ?></th>
                            <th><?php echo $mlSupport->translate('Interest'); ?></th>
                            <th><?php echo $mlSupport->translate('Status'); ?></th>
                            <th><?php echo $mlSupport->translate('Paid On'); ?></th>
                            <th class="text-end pe-4"><?php echo $mlSupport->translate('Actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($installments)): ?>
                            <?php foreach ($installments as $inst): ?>
                                <tr>
                                    <td class="ps-4"><?php echo $inst['installment_number']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($inst['due_date'])); ?></td>
                                    <td class="fw-bold">₹<?php echo number_format($inst['amount'], 2); ?></td>
                                    <td class="text-muted small">₹<?php echo number_format($inst['principal_component'], 2); ?></td>
                                    <td class="text-muted small">₹<?php echo number_format($inst['interest_component'], 2); ?></td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?php
                                                                            echo match ($inst['status']) {
                                                                                'paid' => 'success',
                                                                                'pending' => 'warning',
                                                                                'overdue' => 'danger',
                                                                                default => 'secondary'
                                                                            };
                                                                            ?>">
                                            <?php echo ucfirst($inst['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $inst['payment_date'] ? date('d M Y', strtotime($inst['payment_date'])) : '-'; ?></td>
                                    <td class="text-end pe-4">
                                        <?php if ($inst['status'] === 'paid'): ?>
                                            <a href="<?php echo BASE_URL; ?>admin/emi/generateReceipt/<?php echo $inst['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank">
                                                <i class="fas fa-file-invoice me-1"></i> <?php echo $mlSupport->translate('Receipt'); ?>
                                            </a>
                                        <?php elseif ($plan['status'] === 'active'): ?>
                                            <button class="btn btn-sm btn-outline-success" onclick="openPaymentModal(<?php echo $inst['id']; ?>, <?php echo $inst['amount']; ?>)">
                                                <i class="fas fa-check me-1"></i> <?php echo $mlSupport->translate('Pay'); ?>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No installments found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo BASE_URL; ?>admin/emi/pay" method="POST" id="paymentForm">
                <input type="hidden" name="emi_plan_id" value="<?php echo $plan['id']; ?>">
                <input type="hidden" name="installment_id" id="paymentInstallmentId">

                <div class="modal-header">
                    <h5 class="modal-title"><?php echo $mlSupport->translate('Record Payment'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo $mlSupport->translate('Amount'); ?></label>
                        <input type="number" step="0.01" name="amount" id="paymentAmount" class="form-control" required readonly>
                        <div class="form-text text-muted">Installment amount is fixed.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $mlSupport->translate('Payment Date'); ?></label>
                        <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $mlSupport->translate('Payment Method'); ?></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $mlSupport->translate('Notes'); ?></label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $mlSupport->translate('Cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo $mlSupport->translate('Record Payment'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Foreclose Modal -->
<div class="modal fade" id="forecloseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo BASE_URL; ?>admin/emi/foreclose" method="POST" id="forecloseForm">
                <input type="hidden" name="emi_plan_id" value="<?php echo $plan['id']; ?>">

                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i><?php echo $mlSupport->translate('Foreclose Plan'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle me-2"></i>
                        <?php echo $mlSupport->translate('This action will mark all pending installments as paid and close the plan. This cannot be undone.'); ?>
                    </div>

                    <div class="text-center mb-4">
                        <h6 class="text-muted text-uppercase small mb-1"><?php echo $mlSupport->translate('Outstanding Principal Amount'); ?></h6>
                        <h2 class="fw-bold text-primary" id="forecloseAmountDisplay">Loading...</h2>
                    </div>

                    <input type="hidden" name="amount" id="forecloseAmountInput">

                    <div class="mb-3">
                        <label class="form-label"><?php echo $mlSupport->translate('Payment Date'); ?></label>
                        <input type="date" name="payment_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $mlSupport->translate('Payment Method'); ?></label>
                        <select name="payment_method" class="form-select" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo $mlSupport->translate('Notes / Reference'); ?></label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Cheque No, Transaction ID, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo $mlSupport->translate('Cancel'); ?></button>
                    <button type="submit" class="btn btn-warning"><?php echo $mlSupport->translate('Confirm Foreclosure'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Handle Payment Modal
    function openPaymentModal(installmentId, amount) {
        document.getElementById('paymentInstallmentId').value = installmentId;
        document.getElementById('paymentAmount').value = amount;
        new bootstrap.Modal(document.getElementById('recordPaymentModal')).show();
    }

    // Handle Foreclose Modal
    function openForecloseModal(planId) {
        const modal = new bootstrap.Modal(document.getElementById('forecloseModal'));
        modal.show();

        // Fetch foreclosure amount
        fetch('<?php echo BASE_URL; ?>admin/emi/getForeclosureAmount/' + planId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('forecloseAmountDisplay').textContent = data.formatted_amount;
                    document.getElementById('forecloseAmountInput').value = data.amount;
                } else {
                    alert('Error fetching foreclosure amount: ' + data.message);
                    modal.hide();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching details');
            });
    }

    // Form Submissions
    const handleFormSubmit = (formId) => {
        const form = document.getElementById(formId);
        if (!form) return;

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const submitBtn = this.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Processing...';

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
                        // Show success message or reload
                        window.location.reload();
                    } else {
                        alert(data.message || 'Error processing request');
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalText;
                });
        });
    };

    handleFormSubmit('paymentForm');
    handleFormSubmit('forecloseForm');
</script>