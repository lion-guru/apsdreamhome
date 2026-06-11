<?php
$page_title = $page_title ?? 'Create Agreement - APS Dream Home';
$active_page = 'agreements';
$bookings = $bookings ?? [];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-file-contract me-2"></i>Create New Agreement</h1>
    <a href="<?= BASE_URL ?>/admin/agreements" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to List</a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-edit me-2"></i>Agreement Details</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <form id="agreementForm" method="POST" action="<?= BASE_URL ?>/admin/agreements/store">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Agreement Type <span class="text-danger">*</span></label>
                            <select name="agreement_type" class="form-select" id="agreement_type" required>
                                <option value="">Select Type</option>
                                <option value="sale_deed">Sale Deed</option>
                                <option value="allotment">Allotment Letter</option>
                                <option value="mortgage">Mortgage Agreement</option>
                                <option value="lease">Lease Agreement</option>
                                <option value="nda">Non-Disclosure Agreement</option>
                                <option value="joint_venture">Joint Venture</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Link to Booking (Optional)</label>
                            <select name="booking_id" class="form-select" id="booking_id">
                                <option value="0">— Select Booking (auto-fills parties) —</option>
                                <?php foreach ($bookings as $b): ?>
                                    <option value="<?= $b['id'] ?>"
                                        data-customer="<?= htmlspecialchars($b['customer_name'] ?? '') ?>"
                                        data-plot="<?= htmlspecialchars($b['plot_number'] ?? '') ?>"
                                        data-colony="<?= htmlspecialchars($b['colony_name'] ?? '') ?>"
                                        data-amount="<?= floatval($b['total_amount'] ?? 0) ?>"
                                    >
                                        <?= htmlspecialchars($b['booking_number'] ?? 'BK-' . $b['id']) ?> — <?= htmlspecialchars($b['customer_name'] ?? 'N/A') ?> — <?= htmlspecialchars($b['plot_number'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Party A (Seller/Company) <span class="text-danger">*</span></label>
                            <input type="text" name="party_a_name" class="form-control" id="party_a_name" value="APS Dream Home" required>
                            <input type="hidden" name="party_a_id" id="party_a_id" value="">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Party B (Buyer/Tenant) <span class="text-danger">*</span></label>
                            <input type="text" name="party_b_name" class="form-control" id="party_b_name" value="" required>
                            <input type="hidden" name="party_b_id" id="party_b_id" value="">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Agreement Date <span class="text-danger">*</span></label>
                            <input type="date" name="agreement_date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Validity Date</label>
                            <input type="date" name="validity_date" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Total Value (Rs.)</label>
                            <input type="number" name="total_value" class="form-control" id="total_value" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Stamp Duty (Rs.)</label>
                            <input type="number" name="stamp_duty_amount" class="form-control" step="0.01" min="0" value="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Registration Fee (Rs.)</label>
                            <input type="number" name="registration_fee" class="form-control" step="0.01" min="0" value="0">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Registration Date</label>
                            <input type="date" name="registration_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Plot ID (auto-filled from booking)</label>
                            <input type="number" name="plot_id" class="form-control" id="plot_id" value="">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Additional notes about this agreement..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fas fa-save me-1"></i>Create Agreement</button>
                        <a href="<?= BASE_URL ?>/admin/agreements" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Quick Reference</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <h6 class="text-muted">Agreement Types</h6>
                <ul class="list-unstyled small">
                    <li class="mb-1"><span class="badge bg-primary">Sale Deed</span> Final property transfer document</li>
                    <li class="mb-1"><span class="badge bg-success">Allotment</span> Provisional plot allocation</li>
                    <li class="mb-1"><span class="badge bg-warning text-dark">Mortgage</span> Loan/security agreement</li>
                    <li class="mb-1"><span class="badge bg-info">Lease</span> Rental/lease agreement</li>
                    <li class="mb-1"><span class="badge bg-secondary">NDA</span> Non-disclosure agreement</li>
                    <li class="mb-1"><span class="badge bg-dark">Joint Venture</span> Partnership agreement</li>
                </ul>
                <hr>
                <h6 class="text-muted">Status Flow</h6>
                <p class="small mb-0">Draft &rarr; Pending Signature &rarr; Signed &rarr; Registered</p>
                <p class="small text-muted">Can also be: Cancelled or Expired</p>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Tips</h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <ul class="small mb-0">
                    <li>Link a booking to auto-fill party names and plot details</li>
                    <li>Agreement number is auto-generated (APS/TYPE/YEAR/NNNN)</li>
                    <li>Set validity date for time-bound agreements</li>
                    <li>After creation, you can generate PDF from the agreement detail page</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('booking_id')?.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (this.value > 0) {
        document.getElementById('party_b_name').value = selected.dataset.customer || '';
        document.getElementById('total_value').value = selected.dataset.amount || 0;
    }
});

document.getElementById('agreementForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating...';
    try {
        const fd = new FormData(this);
        const r = await fetch(this.action, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const ct = r.headers.get('content-type') || '';
        if (ct.includes('json')) {
            const data = await r.json();
            if (data.success) {
                window.location.href = data.redirect || '<?= BASE_URL ?>/admin/agreements';
            } else {
                alert(data.error || 'Creation failed');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i>Create Agreement';
            }
        } else {
            window.location.reload();
        }
    } catch (err) {
        alert('Network error: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i>Create Agreement';
    }
});
</script>
