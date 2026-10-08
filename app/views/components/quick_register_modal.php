<!-- Quick Register Modal -->
<div class="modal fade" id="quickRegisterModal" tabindex="-1" aria-labelledby="quickRegisterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="quickRegisterModalLabel">
                    <i class="fas fa-user-plus me-2 text-primary"></i><?= __('component_quick_register', 'Quick Register') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4"><?= __('component_join_aps_seconds', 'Join APS Dream Home in seconds! Choose your role and register.') ?></p>
                
                <form id="quickRegisterForm">
    <?php echo CSRFProtection::csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('component_role', 'Role') ?> *</label>
                        <div class="d-flex gap-2 flex-wrap" id="qrRoleSelector">
                            <button type="button" class="btn btn-outline-primary flex-fill role-btn-qr active" data-role="customer" onclick="selectQrRole(this,'customer')">
                                <i class="fas fa-user me-1"></i> Customer
                            </button>
                            <button type="button" class="btn btn-outline-warning flex-fill role-btn-qr" data-role="associate" onclick="selectQrRole(this,'associate')">
                                <i class="fas fa-handshake me-1"></i> Associate
                            </button>
                            <button type="button" class="btn btn-outline-primary flex-fill role-btn-qr" data-role="agent" onclick="selectQrRole(this,'agent')">
                                <i class="fas fa-star me-1"></i> Agent
                            </button>
                            <button type="button" class="btn btn-outline-purple flex-fill role-btn-qr" data-role="employee" onclick="selectQrRole(this,'employee')">
                                <i class="fas fa-id-badge me-1"></i> Employee
                            </button>
                            <button type="button" class="btn btn-outline-pink flex-fill role-btn-qr" data-role="telecaller" onclick="selectQrRole(this,'telecaller')">
                                <i class="fas fa-headset me-1"></i> Telecaller
                            </button>
                        </div>
                        <input type="hidden" name="role" id="qrSelectedRole" value="customer">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('component_full_name', 'Full Name') ?> *</label>
                        <input type="text" class="form-control" id="qrName" name="name" required placeholder="<?= htmlspecialchars(__('component_enter_full_name', 'Enter your full name')) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('component_email', 'Email') ?> *</label>
                        <input type="email" class="form-control" id="qrEmail" name="email" required placeholder="<?= htmlspecialchars(__('component_enter_email', 'Enter your email')) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('component_phone_number', 'Phone Number') ?> *</label>
                        <input type="tel" class="form-control" id="qrPhone" name="phone" required placeholder="<?= htmlspecialchars(__('component_enter_phone_10', 'Enter 10-digit phone number')) ?>" pattern="[0-9]{10}">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('component_referral_code_optional', 'Referral Code (Optional)') ?></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="qrReferralCode" name="referral_code" placeholder="<?= htmlspecialchars(__('component_enter_referral_placeholder', 'Enter referral code for 5% discount')) ?>">
                            <button type="button" class="btn btn-outline-primary" onclick="requestReferralCode()">
                                <i class="fas fa-ticket-alt me-1"></i>Request Code
                            </button>
                        </div>
                        <small class="text-muted"><?= __('component_get_referral_small', 'Get referral code if you want to join as Associate/Agent') ?></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('component_security_code', 'Security Code') ?> *</label>
                        <?php echo \App\Helpers\SimpleCaptcha::renderField("Enter Security Code"); ?>
                    </div>

                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold" onclick="submitQuickRegister()">
                        <i class="fas fa-check-circle me-2"></i><?= __('component_register_now', 'Register Now') ?>
                    </button>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            By registering, you agree to our
                            <a href="<?= BASE_URL ?>/terms" class="text-primary">Terms</a> and
                            <a href="<?= BASE_URL ?>/privacy" class="text-primary">Privacy Policy</a>
                        </small>
                    </div>
                </form>
                
                <div id="qrLoading" style="display:none">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3"><?= __('component_creating_account', 'Creating your account...') ?></p>
                    </div>
                </div>
                <div id="qrError" class="alert alert-danger mt-3" style="display:none" role="alert"></div>
            </div>
        </div>
    </div>
</div>

<!-- Referral Code Request Modal -->
<div class="modal fade" id="referralRequestModal" tabindex="-1" aria-labelledby="referralRequestModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="referralRequestModalLabel">
                    <i class="fas fa-ticket-alt me-2 text-primary"></i><?= __('component_request_referral_code', 'Request Referral Code') ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-4"><?= __('component_get_company_referral', 'Get your company referral code to join as Associate/Agent!') ?></p>
                
                <form id="referralRequestForm">
    <?php echo CSRFProtection::csrfField(); ?>
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('component_full_name', 'Full Name') ?> *</label>
                        <input type="text" class="form-control" id="rrName" name="name" required placeholder="<?= htmlspecialchars(__('component_enter_full_name', 'Enter your full name')) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('component_email', 'Email') ?> *</label>
                        <input type="email" class="form-control" id="rrEmail" name="email" required placeholder="<?= htmlspecialchars(__('component_enter_email', 'Enter your email')) ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold"><?= __('component_phone_number', 'Phone Number') ?> *</label>
                        <input type="tel" class="form-control" id="rrPhone" name="phone" required placeholder="<?= htmlspecialchars(__('component_enter_phone_10', 'Enter 10-digit phone number')) ?>" pattern="[0-9]{10}">
                    </div>
                    
                    <button type="button" class="btn btn-primary w-100 py-3 fw-bold" onclick="submitReferralRequest()">
                        <i class="fas fa-paper-plane me-2"></i><?= __('component_request_code_btn2', 'Request Code') ?>
                    </button>
                </form>
                
                <div id="rrLoading" style="display:none">
                    <div class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3"><?= __('component_processing_request', 'Processing your request...') ?></p>
                    </div>
                </div>

                <div id="rrResult" style="display:none">
                    <div class="alert alert-success mt-3">
                        <h6 class="fw-bold"><i class="fas fa-check-circle me-2"></i><?= __('component_referral_code_sent', 'Referral Code Sent!') ?></h6>
                        <p class="mb-2"><?= __('component_your_referral_code', 'Your company referral code:') ?></p>
                        <div class="fs-3 fw-bold text-center py-2" id="rrReferralCode"></div>
                        <p class="mb-0 small"><?= __('component_use_code_to_join', 'Use this code to join as Associate/Agent') ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Ensure referral modal is always hidden on page load (prevents bfcache/restore showing stale "Referral Code Sent" state)
document.addEventListener('DOMContentLoaded', function () {
    var rrModalEl = document.getElementById('referralRequestModal');
    if (rrModalEl) {
        // Force hide via Bootstrap if shown
        var rrModal = bootstrap.Modal.getInstance(rrModalEl);
        if (rrModal) rrModal.hide();
        // Reset internal state
        var rrForm = document.getElementById('referralRequestForm');
        if (rrForm) rrForm.style.display = 'block';
        var rrLoading = document.getElementById('rrLoading');
        if (rrLoading) rrLoading.style.display = 'none';
        var rrResult = document.getElementById('rrResult');
        if (rrResult) rrResult.style.display = 'none';
        var rrCode = document.getElementById('rrReferralCode');
        if (rrCode) rrCode.textContent = '';
    }
});

// Quick Register Functions
function showQuickRegisterModal() {
    resetQuickRegisterModal();
    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('quickRegisterModal'));
    modal.show();
}

function resetQuickRegisterModal() {
    const form = document.getElementById('quickRegisterForm');
    if (form) form.style.display = 'block';
    const loading = document.getElementById('qrLoading');
    if (loading) loading.style.display = 'none';
    const err = document.getElementById('qrError');
    if (err) { err.style.display = 'none'; err.innerHTML = ''; }
    // Reset referral sub-modal state so a previous "code sent" result never lingers
    const rrForm = document.getElementById('referralRequestForm');
    if (rrForm) rrForm.style.display = 'block';
    const rrLoading = document.getElementById('rrLoading');
    if (rrLoading) rrLoading.style.display = 'none';
    const rrResult = document.getElementById('rrResult');
    if (rrResult) rrResult.style.display = 'none';
}

function qrShowError(msg, loginUrl) {
    const err = document.getElementById('qrError');
    if (!err) { alert(msg); return; }
    err.innerHTML = msg + (loginUrl ? ' <a href="' + loginUrl + '" class="alert-link">Login here</a>' : '');
    err.style.display = 'block';
    err.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

// Normalize Indian mobile numbers: strips spaces/+91/leading 0 -> 10 digits
function qrNormalizePhone(raw) {
    let d = String(raw || '').replace(/\D/g, '');
    if (d.length === 12 && d.indexOf('91') === 0) d = d.slice(2);
    if (d.length === 11 && d.charAt(0) === '0') d = d.slice(1);
    return d;
}

function qrCsrfToken() {
    const input = document.querySelector('#quickRegisterForm input[name="csrf_token"]');
    return input ? input.value : '';
}

function selectQrRole(btn, role) {
    document.querySelectorAll('.role-btn-qr').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('qrSelectedRole').value = role;
}

function submitQuickRegister() {
    const name = document.getElementById('qrName').value.trim();
    const email = document.getElementById('qrEmail').value.trim();
    const phone = qrNormalizePhone(document.getElementById('qrPhone').value);
    const referralCode = document.getElementById('qrReferralCode').value.trim();
    const role = document.getElementById('qrSelectedRole').value;

    if (!name || !email || !phone) {
        qrShowError('Please fill all required fields');
        return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        qrShowError('Please enter a valid email address');
        return;
    }

    if (!/^[6-9]\d{9}$/.test(phone)) {
        qrShowError('Please enter a valid 10-digit mobile number');
        return;
    }

    // Show loading
    document.getElementById('quickRegisterForm').style.display = 'none';
    document.getElementById('qrLoading').style.display = 'block';
    const errBox = document.getElementById('qrError');
    if (errBox) errBox.style.display = 'none';

    const captchaInput = document.querySelector('#quickRegisterForm input[name="captcha_code"]');
    const captchaCode = captchaInput ? captchaInput.value.trim() : '';
    if (!captchaCode) {
        qrShowError('Please enter the security code');
        return;
    }

    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone);
    formData.append('referral_code', referralCode);
    formData.append('role', role);
    formData.append('captcha_code', captchaCode);
    formData.append('csrf_token', qrCsrfToken());

    fetch((window.BASE_URL || '<?= BASE_URL ?>') + '/auth/quick-register', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Page-specific continuation (e.g. list-property auto-submits the listing)
            if (typeof window.__qrAfterSuccess === 'function') {
                try { window.__qrAfterSuccess(data); return; } catch (e) { console.error(e); }
            }
            let target = data.redirect || '/user/dashboard';
            if (target.charAt(0) === '/' && window.BASE_URL) target = window.BASE_URL + target;
            window.location.href = target;
        } else {
            qrShowError('Registration failed: ' + (data.message || 'Unknown error'), data.login_url || null);
            document.getElementById('quickRegisterForm').style.display = 'block';
            document.getElementById('qrLoading').style.display = 'none';
        }
    })
    .catch(error => {
        qrShowError('Network error, please try again.');
        document.getElementById('quickRegisterForm').style.display = 'block';
        document.getElementById('qrLoading').style.display = 'none';
    });
}

function requestReferralCode() {
    // Hide quick register modal
    const qrEl = document.getElementById('quickRegisterModal');
    const qrInstance = bootstrap.Modal.getInstance(qrEl);
    if (qrInstance) qrInstance.hide();

    // Reset + show referral request modal
    document.getElementById('referralRequestForm').style.display = 'block';
    document.getElementById('rrLoading').style.display = 'none';
    document.getElementById('rrResult').style.display = 'none';
    const modal = new bootstrap.Modal(document.getElementById('referralRequestModal'));
    modal.show();
}

function submitReferralRequest() {
    const name = document.getElementById('rrName').value.trim();
    const email = document.getElementById('rrEmail').value.trim();
    const phone = qrNormalizePhone(document.getElementById('rrPhone').value);

    if (!name || !email || !phone) {
        alert('Please fill all required fields');
        return;
    }

    if (!/^[6-9]\d{9}$/.test(phone)) {
        alert('Please enter a valid 10-digit mobile number');
        return;
    }

    // Show loading
    document.getElementById('referralRequestForm').style.display = 'none';
    document.getElementById('rrLoading').style.display = 'block';

    const formData = new FormData();
    formData.append('name', name);
    formData.append('email', email);
    formData.append('phone', phone);
    formData.append('csrf_token', qrCsrfToken());

    fetch((window.BASE_URL || '<?= BASE_URL ?>') + '/auth/request-referral-code', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('rrLoading').style.display = 'none';
            document.getElementById('rrResult').style.display = 'block';
            document.getElementById('rrReferralCode').textContent = data.referral_code;
        } else {
            alert('Request failed: ' + (data.message || 'Unknown error'));
            document.getElementById('referralRequestForm').style.display = 'block';
            document.getElementById('rrLoading').style.display = 'none';
        }
    })
    .catch(error => {
        alert('Network error, please try again.');
        document.getElementById('referralRequestForm').style.display = 'block';
        document.getElementById('rrLoading').style.display = 'none';
    });
}
</script>

<style>
    .role-btn-qr { transition: all 0.2s; flex: 1; min-width: 100px; }
    .role-btn-qr.active { background: var(--bs-btn-active-bg); color: var(--bs-btn-active-color); border-color: var(--bs-btn-active-border-color); }
    .btn-outline-purple { color: #6366f1; border-color: #6366f1; }
    .btn-outline-purple.active, .btn-outline-purple:hover { background: #6366f1; color: white; }
    .btn-outline-pink { color: #ec4899; border-color: #ec4899; }
    .btn-outline-pink.active, .btn-outline-pink:hover { background: #ec4899; color: white; }
    @media (max-width: 576px) {
        #qrRoleSelector { flex-direction: column; }
        .role-btn-qr { width: 100%; }
    }
</style>
