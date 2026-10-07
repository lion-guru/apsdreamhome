<?php
/**
 * Generic register view — redirects to unified register with role selection.
 * Used by AuthenticationController as a unified entry point.
 */
$csrf_token = $csrf_token ?? '';
$errors = $errors ?? [];
$old = $old ?? [];
$success = $success ?? null;
$ref = trim($_GET['ref'] ?? $old['referral_code'] ?? $_COOKIE['aps_ref'] ?? $_SESSION['aps_ref'] ?? '');
$selectedRole = trim($_GET['role'] ?? $old['role'] ?? 'customer');
$base = BASE_URL;
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5">
                    <div class="row">
                        <!-- Left Side - Benefits/Info -->
                        <div class="col-lg-6 d-none d-lg-block">
                            <div class="p-4 h-100">
                                <div class="text-center mb-4">
                                    <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-primary bg-opacity-10 text-primary mb-3" style="width: 80px; height: 80px;">
                                        <i class="fas fa-user-plus fa-2x"></i>
                                    </div>
                                    <h3 class="fw-bold">Create Your Account</h3>
                                    <p class="text-muted">Join 5000+ families and professionals</p>
                                </div>
                                <div class="mb-4">
                                    <h5 class="fw-bold text-primary mb-3"><i class="fas fa-shield-halved me-2"></i>Why Register?</h5>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Access to 204+ premium plots</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>EMI from ₹8,333/month</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>Refer & earn rewards</li>
                                        <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i>RERA approved properties</li>
                                    </ul>
                                </div>
                                <div>
                                    <h5 class="fw-bold text-primary mb-3"><i class="fas fa-users me-2"></i>Join As</h5>
                                    <div class="row g-2">
                                        <div class="col-6"><span class="badge bg-success-subtle text-success p-2 w-100"><i class="fas fa-user me-1"></i> Customer/Buyer</span></div>
                                        <div class="col-6"><span class="badge bg-warning-subtle text-warning p-2 w-100"><i class="fas fa-handshake me-1"></i> Associate</span></div>
                                        <div class="col-6"><span class="badge bg-primary-subtle text-primary p-2 w-100"><i class="fas fa-star me-1"></i> Agent</span></div>
                                        <div class="col-6"><span class="badge bg-purple-subtle text-purple p-2 w-100"><i class="fas fa-id-badge me-1"></i> Employee</span></div>
                                        <div class="col-6"><span class="badge bg-pink-subtle text-pink p-2 w-100"><i class="fas fa-headset me-1"></i> Telecaller</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Right Side - Registration Form -->
                        <div class="col-lg-6">
                            <div class="bg-light p-4 rounded">
                                <h4 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i>Create Account</h4>
                                <div class="d-flex gap-2 mb-3">
                                    <a href="<?= BASE_URL ?>/auth/google" class="btn btn-outline-secondary flex-fill">
                                        <i class="fab fa-google me-1" style="color:#ea4335"></i> Google
                                    </a>
                                    <a href="<?= BASE_URL ?>/auth/facebook" class="btn btn-outline-secondary flex-fill">
                                        <i class="fab fa-facebook-f me-1" style="color:#1877f2"></i> Facebook
                                    </a>
                                </div>
                                <div class="text-center text-muted small mb-3">— OR —</div>
                                <form method="POST" action="<?= BASE_URL ?>/register">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                                    <input type="hidden" name="role" id="selectedRole" value="<?= htmlspecialchars($selectedRole ?? '') ?>">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">Role <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-2 flex-wrap" id="roleSelector">
                                                <button type="button" class="btn btn-outline-primary flex-fill role-btn active" data-role="customer" onclick="selectRegRole(this,'customer')">
                                                    <i class="fas fa-user me-1"></i> Customer
                                                </button>
                                                <button type="button" class="btn btn-outline-warning flex-fill role-btn" data-role="associate" onclick="selectRegRole(this,'associate')">
                                                    <i class="fas fa-handshake me-1"></i> Associate
                                                </button>
                                                <button type="button" class="btn btn-outline-primary flex-fill role-btn" data-role="agent" onclick="selectRegRole(this,'agent')">
                                                    <i class="fas fa-star me-1"></i> Agent
                                                </button>
                                                <button type="button" class="btn btn-outline-purple flex-fill role-btn" data-role="employee" onclick="selectRegRole(this,'employee')">
                                                    <i class="fas fa-id-badge me-1"></i> Employee
                                                </button>
                                                <button type="button" class="btn btn-outline-pink flex-fill role-btn" data-role="telecaller" onclick="selectRegRole(this,'telecaller')">
                                                    <i class="fas fa-headset me-1"></i> Telecaller
                                                </button>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($old['name'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Email <span class="text-danger">*</span></label>
                                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($old['email'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                                            <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($old['phone'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Password <span class="text-danger">*</span></label>
                                            <input type="password" name="password" class="form-control" required minlength="6">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                            <input type="password" name="password_confirmation" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">Referral Code</label>
                                            <input type="text" name="referral_code" class="form-control" value="<?= htmlspecialchars($_GET['ref'] ?? $old['referral_code'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger mt-3">
                                        <?php foreach ($errors as $err): ?>
                                        <div><?= htmlspecialchars($err ?? '') ?></div>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php if (!empty($success)): ?>
                                    <div class="alert alert-success mt-3">
                                        <?= htmlspecialchars($success ?? '') ?>
                                    </div>
                                    <?php endif; ?>
                                    <?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
                                    <button type="submit" class="btn btn-primary w-100 mt-4">Register</button>
                                </form>
                                <div class="text-center mt-3">
                                    Already have an account? <a href="<?= BASE_URL ?>/login" class="text-decoration-none">Login</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <style>
        .role-btn { transition: all 0.2s; }
        .role-btn.active { background: var(--bs-btn-active-bg); color: var(--bs-btn-active-color); border-color: var(--bs-btn-active-border-color); }
        .btn-outline-purple { color: #6366f1; border-color: #6366f1; }
        .btn-outline-purple.active, .btn-outline-purple:hover { background: #6366f1; color: white; }
        .btn-outline-pink { color: #ec4899; border-color: #ec4899; }
        .btn-outline-pink.active, .btn-outline-pink:hover { background: #ec4899; color: white; }
        @media (max-width: 576px) {
            #roleSelector { flex-direction: column; }
            .role-btn { width: 100%; }
        }
    </style>
    <script>
        function selectRegRole(btn, role) {
            document.querySelectorAll('.role-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById('selectedRole').value = role;
        }
    </script>
