<?php
$page_title = $page_title ?? '';
if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}
if (!function_exists('__')) {
    require_once __DIR__ . '/../../Helpers/TranslationHelper.php';
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$page_title = $page_title ?? __('forgot_password_page_title') . ' - APS Dream Home';
$page_description = __('forgot_password_page_description');

?>

<!-- Forgot Password Section -->
<div class="forgot-password-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="forgot-password-card">
                    <div class="forgot-header">
                        <div class="forgot-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <h2 class="forgot-title"><?= __('forgot_password_title') ?></h2>
                        <p class="forgot-subtitle"><?= __('forgot_password_subtitle') ?></p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['error'] ?? '');
                            unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['success'] ?? '');
                            unset($_SESSION['success']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Reset Method Tabs -->
                    <ul class="nav nav-tabs forgot-tabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#email-reset-tab" type="button" role="tab">
                                <i class="fas fa-envelope me-2"></i><?= __('forgot_password_tab_email') ?>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#mobile-reset-tab" type="button" role="tab">
                                <i class="fas fa-mobile-alt me-2"></i><?= __('forgot_password_tab_mobile') ?>
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content forgot-tab-content">
                        <!-- Email Reset Tab -->
                        <div class="tab-pane fade show active" id="email-reset-tab" role="tabpanel">
                            <form action="<?= BASE_URL ?>/forgot-password" method="POST" class="forgot-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="reset_type" value="email">

                                <div class="mb-4">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-2"></i><?= __('forgot_password_label_email') ?>
                                    </label>
                                    <input type="email" class="form-control form-control-lg" id="email" name="email" required
                                        placeholder="<?= __('forgot_password_ph_email') ?>" autocomplete="email">
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <?= __('forgot_password_hint_email') ?>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i><?= __('forgot_password_button_send_link') ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" onclick="switchToResetTab('mobile-reset-tab')">
                                        <i class="fas fa-mobile-alt me-2"></i><?= __('forgot_password_button_try_mobile') ?>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Mobile Reset Tab -->
                        <div class="tab-pane fade" id="mobile-reset-tab" role="tabpanel">
                            <form action="<?= BASE_URL ?>/forgot-password" method="POST" class="forgot-form">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="reset_type" value="mobile">

                                <div class="mb-4">
                                    <label for="mobile" class="form-label">
                                        <i class="fas fa-mobile-alt me-2"></i><?= __('forgot_password_label_mobile') ?>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">+91</span>
                                        <input type="tel" class="form-control form-control-lg" id="mobile" name="mobile" required
                                            placeholder="9876543210" pattern="[0-9]{10}" maxlength="10">
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        <?= __('forgot_password_hint_mobile') ?>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="send-otp-mobile" name="send_otp">
                                        <label class="form-check-label" for="send-otp-mobile">
                                            <i class="fas fa-key me-2"></i><?= __('forgot_password_label_otp_instead') ?>
                                        </label>
                                    </div>
                                </div>

                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-paper-plane me-2"></i><?= __('forgot_password_button_send_link') ?>
                                    </button>
                                    <button type="button" class="btn btn-outline-info" onclick="requestMobileOTP()">
                                        <i class="fas fa-key me-2"></i><?= __('forgot_password_button_send_otp') ?>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Help Information -->
                    <div class="help-info">
                        <h6><i class="fas fa-question-circle me-2"></i><?= __('forgot_password_help_heading') ?></h6>
                        <div class="help-content">
                            <div class="help-item">
                                <i class="fas fa-envelope text-primary me-2"></i>
                                <div>
                                    <strong><?= __('forgot_password_help_email_label') ?>:</strong> <?= __('forgot_password_help_email_desc') ?>
                                </div>
                            </div>
                            <div class="help-item">
                                <i class="fas fa-mobile-alt text-success me-2"></i>
                                <div>
                                    <strong><?= __('forgot_password_help_mobile_label') ?>:</strong> <?= __('forgot_password_help_mobile_desc') ?>
                                </div>
                            </div>
                            <div class="help-item">
                                <i class="fas fa-clock text-warning me-2"></i>
                                <div>
                                    <strong><?= __('forgot_password_help_time_label') ?>:</strong> <?= __('forgot_password_help_time_desc') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Links -->
                    <div class="quick-links">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="<?= BASE_URL ?>auth/universal_login" class="quick-link">
                                    <i class="fas fa-sign-in-alt me-2"></i><?= __('forgot_password_link_login') ?>
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="<?= BASE_URL ?>auth/register" class="quick-link">
                                    <i class="fas fa-user-plus me-2"></i><?= __('forgot_password_link_register') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .forgot-password-section {
        min-height: 100vh;
        background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
        padding: 2rem 0;
        display: flex;
        align-items: center;
    }

    .forgot-password-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        overflow: hidden;
    }

    .forgot-header {
        background: linear-gradient(135deg, #0f766e 0%, #f093fb 100%);
        color: white;
        padding: 2rem;
        text-align: center;
        margin-bottom: 2rem;
    }

    .forgot-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        font-size: 1.5rem;
    }

    .forgot-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .forgot-subtitle {
        color: rgba(255, 255, 255, 0.8);
        margin-bottom: 0;
    }

    .forgot-tabs {
        border-bottom: 2px solid rgba(255, 255, 255, 0.1);
        margin-bottom: 2rem;
    }

    .forgot-tabs .nav-link {
        color: rgba(255, 255, 255, 0.7);
        border: none;
        border-radius: 10px 10px 0 0;
        padding: 1rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .forgot-tabs .nav-link:hover {
        color: white;
        background: rgba(255, 255, 255, 0.1);
    }

    .forgot-tabs .nav-link.active {
        color: white;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px 10px 0 0;
    }

    .forgot-tab-content {
        padding: 0 2rem 2rem 2rem;
    }

    .help-info {
        background: #f8f9fa;
        padding: 1.5rem 2rem;
        border-radius: 10px;
        margin-top: 1rem;
    }

    .help-content {
        display: grid;
        gap: 1rem;
    }

    .help-item {
        display: flex;
        align-items: flex-start;
        padding: 0.5rem 0;
    }

    .help-item strong {
        color: #495057;
    }

    .quick-links {
        background: #f8f9fa;
        padding: 1.5rem 2rem;
        border-top: 1px solid #e9ecef;
    }

    .quick-link {
        display: block;
        color: #6c757d;
        text-decoration: none;
        padding: 0.5rem 0;
        border-radius: 8px;
        transition: all 0.3s ease;
    }

    .quick-link:hover {
        color: #0f766e;
        background: rgba(118, 75, 162, 0.1);
        text-decoration: none;
    }

    .input-group-text {
        background: #e9ecef;
        border: 1px solid #ced4da;
        border-radius: 0.375rem 0 0 0.375rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .forgot-password-section {
            padding: 1rem 0;
        }

        .forgot-tabs .nav-link {
            padding: 0.75rem 1rem;
            font-size: 0.9rem;
        }

        .forgot-tab-content {
            padding: 0 1rem 1rem 1rem;
        }
    }
</style>

<script>
    // Tab switching
    function switchToResetTab(tabId) {
        const tab = new bootstrap.Tab(document.getElementById(tabId));
        tab.show();

        // Update nav links
        document.querySelectorAll('.forgot-tabs .nav-link').forEach(link => {
            link.classList.remove('active');
        });
        document.querySelector(`[data-bs-target="${tabId}"]`).classList.add('active');
    }

    // Request Mobile OTP
    function requestMobileOTP() {
        const mobile = document.getElementById('mobile').value;
        if (!mobile || mobile.length !== 10) {
            alert('Please enter a valid 10-digit mobile number');
            return;
        }

        // Check the OTP checkbox
        document.getElementById('send-otp-mobile').checked = true;

        // Simulate OTP request
        alert(`OTP sent to +91${mobile}`);
    }

    // Form validation
    document.querySelectorAll('.forgot-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            const resetType = form.querySelector('input[name="reset_type"]').value;

            if (resetType === 'mobile') {
                const mobile = form.querySelector('input[name="mobile"]').value;

                if (mobile.length !== 10) {
                    e.preventDefault();
                    alert('Please enter a valid 10-digit mobile number');
                    return;
                }
            }

            if (resetType === 'email') {
                const email = form.querySelector('input[name="email"]').value;

                if (!email.includes('@') || !email.includes('.')) {
                    e.preventDefault();
                    alert('Please enter a valid email address');
                    return;
                }
            }
        });
    });

    // Auto-focus first input
    document.addEventListener('DOMContentLoaded', function() {
        const firstInput = document.querySelector('.tab-pane.active input');
        if (firstInput) {
            firstInput.focus();
        }
    });
</script>
<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
</body></html>

