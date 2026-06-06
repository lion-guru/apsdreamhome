<?php
$layout = 'layouts/customer';
$page_title = $page_title ?? 'KYC Verification - APS Dream Home';
$current_page = 'kyc';
$user = $user ?? ['name' => '', 'email' => '', 'phone' => ''];
$existing = $existing ?? null;
$history = $history ?? [];
$status = $existing['status'] ?? 'not_started';
$statusColor = match($status) {
    'approved' => 'success',
    'rejected' => 'danger',
    'pending' => 'warning',
    default => 'secondary'
};
$statusLabel = match($status) {
    'approved' => 'Verified',
    'rejected' => 'Rejected',
    'pending' => 'Under Review',
    default => 'Not Submitted'
};

ob_start();
?>

<div class="aps-cp-page-header">
    <h2><i class="fas fa-id-card"></i> KYC Verification</h2>
    <p>Complete your KYC to unlock property bookings, loans, and payouts.</p>
</div>

<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h3>Current Status</h3>
        <span class="aps-cp-badge aps-cp-badge-<?= $statusColor ?>"><?= htmlspecialchars($statusLabel) ?></span>
    </div>
    <div class="aps-cp-card-body">
        <?php if ($existing): ?>
        <div class="aps-cp-info-grid">
            <div><strong>Legal Name:</strong> <?= htmlspecialchars($existing['legal_name'] ?? '—') ?></div>
            <div><strong>PAN:</strong> <?= htmlspecialchars($aadhaarMasked ?? '—') === '—' ? '—' : htmlspecialchars($panMasked) ?></div>
            <div><strong>Aadhaar:</strong> <?= htmlspecialchars($aadhaarMasked) ?></div>
            <div><strong>Submitted:</strong> <?= htmlspecialchars(date('M j, Y', strtotime($existing['created_at'] ?? 'now'))) ?></div>
        </div>
        <?php if (!empty($existing['reason'])): ?>
        <div class="aps-cp-alert aps-cp-alert-danger mt-3">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($existing['reason']) ?>
        </div>
        <?php endif; ?>
        <?php if ($status === 'approved'): ?>
        <div class="aps-cp-alert aps-cp-alert-success mt-3">
            <i class="fas fa-check-circle"></i> Your KYC is verified. All features are unlocked.
        </div>
        <?php endif; ?>
        <?php else: ?>
        <p>You haven't submitted KYC yet. Use the form below to verify your identity.</p>
        <?php endif; ?>
    </div>
</div>

<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header">
        <h3>Submit / Update KYC</h3>
    </div>
    <div class="aps-cp-card-body">
        <form id="kycForm" class="aps-cp-form" data-ajax="true" action="<?= BASE_URL ?>/user/kyc/submit" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="legal_name">Legal Name (as on PAN)</label>
                <input type="text" id="legal_name" name="legal_name" class="aps-cp-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
            </div>

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="pan_number">PAN Number</label>
                <input type="text" id="pan_number" name="pan_number" class="aps-cp-input" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" placeholder="ABCDE1234F" maxlength="10" required>
                <small class="aps-cp-help">10-character PAN. Example: ABCDE1234F</small>
            </div>

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="aadhaar_number">Aadhaar Number</label>
                <input type="text" id="aadhaar_number" name="aadhaar_number" class="aps-cp-input" pattern="[0-9]{12}" placeholder="123412341234" maxlength="12" required>
                <small class="aps-cp-help">12-digit Aadhaar. Will be verified via UIDAI.</small>
            </div>

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="dob">Date of Birth</label>
                <input type="date" id="dob" name="dob" class="aps-cp-input" required>
            </div>

            <div class="aps-cp-form-actions">
                <button type="submit" class="aps-cp-btn aps-cp-btn-primary" id="kycSubmitBtn">
                    <i class="fas fa-paper-plane"></i> Submit for Verification
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($history)): ?>
<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header">
        <h3>Submission History</h3>
    </div>
    <div class="aps-cp-card-body">
        <div class="aps-cp-table-wrap">
        <table class="aps-cp-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>PAN</th>
                    <th>Status</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($history as $h):
                $hc = match($h['status']) {
                    'approved' => 'success',
                    'rejected' => 'danger',
                    'pending' => 'warning',
                    default => 'secondary'
                };
            ?>
                <tr>
                    <td><?= htmlspecialchars(date('M j, Y', strtotime($h['created_at'] ?? 'now'))) ?></td>
                    <td><?= htmlspecialchars($h['pan_number'] ?? '—') ?></td>
                    <td><span class="aps-cp-badge aps-cp-badge-<?= $hc ?>"><?= htmlspecialchars(ucfirst($h['status'] ?? 'unknown')) ?></span></td>
                    <td><?= htmlspecialchars($h['reason'] ?? '—') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.getElementById('kycForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('kycSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
    try {
        const fd = new FormData(this);
        const r = await fetch(this.action, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await r.json();
        if (data.success) {
            APS.toast(data.message || 'KYC submitted', 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            APS.toast(data.error || 'Submission failed', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit for Verification';
        }
    } catch (err) {
        APS.toast('Network error: ' + err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit for Verification';
    }
});
</script>

<?php
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/customer.php';
