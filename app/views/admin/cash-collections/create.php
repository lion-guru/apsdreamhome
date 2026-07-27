<?php
$page_title = $page_title ?? 'Submit Collection Receipt';
$page_heading = $page_heading ?? 'Submit Collection Receipt';
$collectors = $collectors ?? [];
$bookings = $bookings ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-file-invoice-dollar me-2"></i>Submit Collection Receipt</h2>
            <p class="text-muted mb-0">Record a field cash collection</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/cash-collections" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <form method="POST" action="<?= BASE_URL ?>/admin/cash-collections/store" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-user me-2"></i>Collection Details</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Collector (Field Agent) *</label>
                                <select class="form-select" name="collector_id" required>
                                    <option value="">Select collector...</option>
                                    <?php foreach ($collectors as $u): ?>
                                        <option value="<?= $u['id'] ?>" <?= ($u['id'] ?? 0) == ($_SESSION['user_id'] ?? 0) ? 'selected' : '' ?>><?= htmlspecialchars($u['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Customer Name *</label>
                                <input type="text" class="form-control" name="customer_name" required placeholder="Customer who paid">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Amount (₹) *</label>
                                <input type="number" class="form-control" name="amount" required min="1" step="0.01" placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Collection Date *</label>
                                <input type="date" class="form-control" name="collection_date" required value="<?= date('Y-m-d') ?>">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Payment Method</label>
                                <select class="form-select" name="payment_method">
                                    <option value="cash">Cash</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="upi">UPI</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-link me-2"></i>Link to Booking (Optional)</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Booking</label>
                                <select class="form-select" name="booking_id" id="bookingSelect">
                                    <option value="">Select booking...</option>
                                    <?php foreach ($bookings as $b): ?>
                                        <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['booking_number'] ?? '#'.$b['id']) ?> — <?= htmlspecialchars($b['customer_name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Installment ID</label>
                                <input type="number" class="form-control" name="installment_id" placeholder="Leave blank if not EMI payment">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Reference Number</label>
                                <input type="text" class="form-control" name="reference_number" placeholder="Cheque no, UPI ref, etc.">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-camera me-2"></i>Receipt & Notes</h5></div>
                    <div class="card-body aps-cp-card-body">
                        <div class="mb-3">
                            <label class="form-label">Receipt Photo</label>
                            <input type="file" class="form-control" name="receipt_photo" accept="image/*">
                            <small class="text-muted">Upload a photo of the physical receipt (JPG, PNG, WebP — max 10MB)</small>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3" placeholder="Any additional notes..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg"><i class="fas fa-save me-2"></i>Submit Collection Receipt</button>
                </div>
            </form>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light"><h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Guidelines</h5></div>
                <div class="card-body aps-cp-card-body">
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Always upload a photo of the physical receipt</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Link to the correct booking if it's an EMI payment</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Admin will verify each receipt before reconciliation</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i>Reconciliation happens daily per collector</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
