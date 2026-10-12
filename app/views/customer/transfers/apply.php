<?php
/**
 * Customer Resale & Plot Transfer Desk — Application Form
 */
$page_title = $page_title ?? 'Apply for Plot Ownership Transfer';
$booking = $booking ?? [];
$eligibility = $eligibility ?? ['eligible' => true];
$csrf = $_SESSION['csrf_token'] ?? '';
?>

<div class="container py-4">
    <div class="max-w-700 mx-auto" style="max-width: 750px;">
        <div class="mb-3">
            <a href="/customer/plot-transfers" class="text-decoration-none text-muted small">
                <i class="fas fa-arrow-left me-1"></i>Back to Transfer Desk
            </a>
            <h3 class="fw-bold mt-1 mb-0">Ownership Transfer & Resale NOC Application</h3>
            <small class="text-muted">Transfer ownership rights to a new buyer or family member with official company approval.</small>
        </div>

        <!-- Property Summary Card -->
        <div class="card border-0 shadow-sm mb-4 bg-light">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="fw-bold text-primary mb-0">Plot #<?= htmlspecialchars($booking['plot_number'] ?? '—') ?></h5>
                    <span class="badge bg-secondary">Booking #<?= htmlspecialchars($booking['booking_number'] ?? $booking['id']) ?></span>
                </div>
                <div class="small text-muted">
                    <span><strong>Township:</strong> <?= htmlspecialchars($booking['colony_name'] ?? '—') ?></span> · 
                    <span><strong>Area:</strong> <?= (float)($booking['area_sqft'] ?? 0) ?> Sq.Ft</span> · 
                    <span><strong>Dimensions:</strong> <?= htmlspecialchars($booking['dimensions'] ?? 'Standard') ?></span>
                </div>
            </div>
        </div>

        <!-- Application Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="/customer/plot-transfers/apply">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                    <input type="hidden" name="booking_id" value="<?= (int)$booking['id'] ?>">

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2">
                        <i class="fas fa-user-plus text-primary me-2"></i>Proposed Transferee (New Buyer) Details
                    </h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">New Buyer Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="new_buyer_name" class="form-control" placeholder="e.g. Rahul Sharma" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">New Buyer Mobile Phone <span class="text-danger">*</span></label>
                            <input type="tel" name="new_buyer_phone" class="form-control" placeholder="e.g. 9876543210" pattern="[0-9]{10}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">New Buyer Aadhaar Number</label>
                            <input type="text" name="new_buyer_aadhaar" class="form-control" placeholder="12-digit Aadhaar">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">New Buyer PAN Number</label>
                            <input type="text" name="new_buyer_pan" class="form-control text-uppercase" placeholder="e.g. ABCDE1234F">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Permanent Residential Address</label>
                            <textarea name="new_buyer_address" class="form-control" rows="2" placeholder="Full postal address of the new buyer..."></textarea>
                        </div>
                    </div>

                    <h5 class="fw-bold text-dark mb-3 border-bottom pb-2 mt-4">
                        <i class="fas fa-handshake text-primary me-2"></i>Transfer Reason & Commercials
                    </h5>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Transfer Nature <span class="text-danger">*</span></label>
                            <select name="transfer_type" class="form-select" required>
                                <option value="resale">Open Market Resale to Third-Party Buyer</option>
                                <option value="family_gift">Family Gift / Blood Relative Transfer</option>
                                <option value="legal_heir">Legal Heir / Succession</option>
                                <option value="direct_transfer">Direct Re-allotment</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Agreed Resale / Transfer Price (₹)</label>
                            <input type="number" step="1000" name="transfer_amount" class="form-control" value="<?= (float)($booking['total_amount'] ?? 0) ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold">Remarks / Reason for Transfer</label>
                            <textarea name="transfer_reason" class="form-control" rows="2" placeholder="e.g. Plot being sold due to relocation."></textarea>
                        </div>
                    </div>

                    <div class="alert alert-info small mb-4">
                        <i class="fas fa-info-circle me-1"></i>
                        <strong>Standard Procedure:</strong> Upon submission, APS Dream Home administration will verify zero outstanding dues and schedule the official NOC certificate issuance. A standard transfer fee of ₹5,000 applies upon final approval.
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="/customer/plot-transfers" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <i class="fas fa-paper-plane me-1"></i>Submit Transfer Application
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
