<?php
$layout = 'layouts/customer';
$page_title = $page_title ?? __('user_kyc_page_title', null, 'KYC Verification - APS Dream Home');
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
    'approved' => __('user_kyc_status_verified', null, 'Verified'),
    'rejected' => __('user_kyc_status_rejected', null, 'Rejected'),
    'pending' => __('user_kyc_status_under_review', null, 'Under Review'),
    default => __('user_kyc_status_not_submitted', null, 'Not Submitted')
};

ob_start();
?>

<div class="aps-cp-page-header">
    <h2><i class="fas fa-id-card"></i> <?= __('user_kyc_heading', null, 'KYC Verification') ?></h2>
    <p><?= __('user_kyc_subtitle', null, 'Complete your KYC to unlock property bookings, loans, and payouts.') ?></p>
</div>

<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h3><?= __('user_kyc_current_status', null, 'Current Status') ?></h3>
        <span class="aps-cp-badge aps-cp-badge-<?= $statusColor ?>"><?= htmlspecialchars($statusLabel ?? '') ?></span>
    </div>
    <div class="aps-cp-card-body">
        <?php if ($existing): ?>
        <div class="aps-cp-info-grid">
            <div><strong><?= __('user_kyc_legal_name', null, 'Legal Name:') ?></strong> <?= htmlspecialchars($existing['legal_name'] ?? '—') ?></div>
            <div><strong><?= __('user_kyc_pan_label', null, 'PAN:') ?></strong> <?= htmlspecialchars($aadhaarMasked ?? '—') === '—' ? '—' : htmlspecialchars($panMasked ?? '') ?></div>
            <div><strong><?= __('user_kyc_aadhaar_label', null, 'Aadhaar:') ?></strong> <?= htmlspecialchars($aadhaarMasked ?? '') ?></div>
            <div><strong><?= __('user_kyc_submitted', null, 'Submitted:') ?></strong> <?= htmlspecialchars(date('M j, Y', strtotime($existing['created_at'] ?? 'now'))) ?></div>
        </div>
        <?php if (!empty($existing['reason'])): ?>
        <div class="aps-cp-alert aps-cp-alert-danger mt-3">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($existing['reason'] ?? '') ?>
        </div>
        <?php endif; ?>
        <?php if ($status === 'approved'): ?>
        <div class="aps-cp-alert aps-cp-alert-success mt-3">
            <i class="fas fa-check-circle"></i> <?= __('user_kyc_approved_msg', null, 'Your KYC is verified. All features are unlocked.') ?>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <p><?= __('user_kyc_not_submitted_msg', null, "You haven't submitted KYC yet. Use the form below to verify your identity.") ?></p>
        <?php endif; ?>
    </div>
</div>

<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header">
        <h3><?= __('user_kyc_submit_update', null, 'Submit / Update KYC') ?></h3>
    </div>
    <div class="aps-cp-card-body">
        <form id="kycForm" class="aps-cp-form" data-ajax="true" action="<?= BASE_URL ?>/user/kyc/submit" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="legal_name"><?= __('user_kyc_legal_name_label', null, 'Legal Name (as on PAN)') ?></label>
                <input type="text" id="legal_name" name="legal_name" class="aps-cp-input" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
            </div>

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="pan_number"><?= __('user_kyc_pan_number', null, 'PAN Number') ?></label>
                <input type="text" id="pan_number" name="pan_number" class="aps-cp-input" pattern="[A-Z]{5}[0-9]{4}[A-Z]{1}" placeholder="ABCDE1234F" maxlength="10" required>
                <small class="aps-cp-help"><?= __('user_kyc_pan_help', null, '10-character PAN. Example: ABCDE1234F') ?></small>
            </div>

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="pan_document"><?= __('user_kyc_pan_document', null, 'PAN Card Document') ?> <span class="text-danger">*</span></label>
                <input type="file" id="pan_document" name="pan_document" class="aps-cp-input" accept="image/*,application/pdf" required>
                <small class="aps-cp-help"><?= __('user_kyc_pan_upload_help', null, 'Upload PAN card image (JPG, PNG, PDF). Max 5MB.') ?></small>
            </div>

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="aadhaar_number"><?= __('user_kyc_aadhaar_number', null, 'Aadhaar Number') ?></label>
                <input type="text" id="aadhaar_number" name="aadhaar_number" class="aps-cp-input" pattern="[0-9]{12}" placeholder="123412341234" maxlength="12" required>
                <small class="aps-cp-help"><?= __('user_kyc_aadhaar_help', null, '12-digit Aadhaar. Will be verified via UIDAI.') ?></small>
            </div>

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="aadhaar_front_document"><?= __('user_kyc_aadhaar_front', null, 'Aadhaar Front') ?> <span class="text-danger">*</span></label>
                <input type="file" id="aadhaar_front_document" name="aadhaar_front_document" class="aps-cp-input" accept="image/*,application/pdf" required>
                <small class="aps-cp-help"><?= __('user_kyc_aadhaar_front_help', null, 'Upload Aadhaar front side (JPG, PNG, PDF). Max 5MB.') ?></small>
            </div>

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="aadhaar_back_document"><?= __('user_kyc_aadhaar_back', null, 'Aadhaar Back') ?> <span class="text-danger">*</span></label>
                <input type="file" id="aadhaar_back_document" name="aadhaar_back_document" class="aps-cp-input" accept="image/*,application/pdf" required>
                <small class="aps-cp-help"><?= __('user_kyc_aadhaar_back_help', null, 'Upload Aadhaar back side (JPG, PNG, PDF). Max 5MB.') ?></small>
            </div>

            <div class="aps-cp-form-section">
                <label class="aps-cp-label" for="dob"><?= __('user_kyc_dob', null, 'Date of Birth') ?></label>
                <input type="date" id="dob" name="dob" class="aps-cp-input" required>
            </div>

            <div class="aps-cp-form-actions">
                <button type="submit" class="aps-cp-btn aps-cp-btn-primary" id="kycSubmitBtn">
                    <i class="fas fa-paper-plane"></i> <?= __('user_kyc_submit_btn', null, 'Submit for Verification') ?>
                </button>
            </div>
        </form>
    </div>
</div>

<?php if (!empty($history)): ?>
<div class="aps-cp-card mt-3">
    <div class="aps-cp-card-header">
        <h3><?= __('user_kyc_submission_history', null, 'Submission History') ?></h3>
    </div>
    <div class="aps-cp-card-body">
        <div class="aps-cp-table-wrap">
        <table class="aps-cp-table">
            <thead>
                <tr>
                    <th><?= __('user_kyc_history_date', null, 'Date') ?></th>
                    <th><?= __('user_kyc_history_pan', null, 'PAN') ?></th>
                    <th><?= __('user_kyc_history_status', null, 'Status') ?></th>
                    <th><?= __('user_kyc_history_reason', null, 'Reason') ?></th>
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
var KYC_STRINGS = {
    submitting: <?= json_encode(__('user_kyc_submitting', null, 'Submitting...')) ?>,
    submitted: <?= json_encode(__('user_kyc_submitted', null, 'KYC submitted')) ?>,
    submitFailed: <?= json_encode(__('user_kyc_submit_failed', null, 'Submission failed')) ?>,
    networkError: <?= json_encode(__('user_kyc_network_error', null, 'Network error')) ?>,
    submitBtn: <?= json_encode(__('user_kyc_submit_btn', null, 'Submit for Verification')) ?>
};

document.getElementById('kycForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('kycSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + KYC_STRINGS.submitting;
    try {
        const fd = new FormData(this);
        const r = await fetch(this.action, {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await r.json();
        if (data.success) {
            APS.toast(data.message || KYC_STRINGS.submitted, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            APS.toast(data.error || KYC_STRINGS.submitFailed, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> ' + KYC_STRINGS.submitBtn;
        }
    } catch (err) {
        APS.toast(KYC_STRINGS.networkError + ': ' + err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> ' + KYC_STRINGS.submitBtn;
    }
});
</script>

<?php
$content = ob_get_clean();
include APP_ROOT . '/app/views/layouts/customer.php';
