<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <h4 class="fw-bold"><i class="fas fa-user-plus me-2"></i><?= ($page_title ?? 'Registration') ?></h4>
                        <p class="text-muted">Create your account to get started</p>
                    </div>

                    <?php if (!empty($referrerInfo ?? [])): ?>
                    <div class="alert alert-info py-2">
                        <i class="fas fa-user-friends me-1"></i> Referred by: <strong><?= htmlspecialchars($referrerInfo['name'] ?? '') ?></strong>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="<?= ($base ?? BASE_URL) ?>register" class="needs-validation" novalidate>
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Phone <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" class="form-control" placeholder="10-digit mobile" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" class="form-control" minlength="8" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">State <span class="text-danger">*</span></label>
                                <select name="state" class="form-select" required>
                                    <option value="">Select State</option>
                                    <?php foreach (($indianStates ?? []) as $st): ?>
                                    <option value="<?= htmlspecialchars($st ?? '') ?>"><?= htmlspecialchars($st ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">City <span class="text-danger">*</span></label>
                                <input type="text" name="city" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label small">Address <span class="text-danger">*</span></label>
                                <textarea name="address" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Pincode <span class="text-danger">*</span></label>
                                <input type="text" name="pincode" class="form-control" maxlength="6" pattern="\d{6}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Referral Code</label>
                                <input type="text" name="referral_code" class="form-control" value="<?= htmlspecialchars($referralCode ?? '') ?>" readonly>
                            </div>
                        </div>

                        <div class="form-check mt-3">
                            <input type="checkbox" name="terms" class="form-check-input" id="terms" required>
                            <label class="form-check-label small" for="terms">I agree to the <a href="<?= ($base ?? BASE_URL) ?>terms" target="_blank">Terms & Conditions</a></label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-3"><i class="fas fa-user-plus me-1"></i>Register</button>
                    </form>

                    <p class="text-center mt-3 mb-0 small">
                        Already have an account? <a href="<?= ($base ?? BASE_URL) ?>login">Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
