<?php require_once 'app/views/layouts/header.php'; ?>

<div class="container-fluid py-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-lg border-0 rounded-lg overflow-hidden">
                <div class="card-header bg-gradient-primary text-white text-center py-5" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                    <h2 class="mb-2">
                        <i class="fas fa-home me-2"></i>APS DREAM HOMES
                    </h2>
                    <p class="mb-0 lead">Associate Registration</p>
                    <p class="small text-white-50">Join India's Premier Real Estate Network</p>
                </div>

                <div class="card-body p-5">
                    <!-- Alerts -->
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Referrer Info -->
                    <?php if ($referrer_info): ?>
                    <div class="alert alert-info border-0 shadow-sm rounded-lg mb-4" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white;">
                        <h5 class="alert-heading mb-3"><i class="fas fa-user-friends me-2"></i>Referred by Sponsor</h5>
                        <div class="row">
                            <div class="col-md-6"><strong>Name:</strong> <?= htmlspecialchars($referrer_info['full_name']) ?></div>
                            <div class="col-md-6"><strong>Level:</strong> <?= htmlspecialchars($referrer_info['current_level']) ?></div>
                            <div class="col-md-6"><strong>Mobile:</strong> <?= htmlspecialchars($referrer_info['mobile']) ?></div>
                            <div class="col-md-6"><strong>Team Size:</strong> <?= $referrer_info['total_team_size'] ?> Members</div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Information Column -->
                        <div class="col-lg-4 mb-4 mb-lg-0">
                            <div class="p-4 bg-light rounded-lg h-100">
                                <h4 class="text-primary mb-4"><i class="fas fa-star me-2"></i>Why Join Us?</h4>
                                
                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="icon-circle bg-primary text-white me-3 p-2 rounded-circle">
                                            <i class="fas fa-percentage"></i>
                                        </div>
                                        <h6 class="mb-0">High Commissions</h6>
                                    </div>
                                    <p class="text-muted small ms-5">Earn up to 20% commission on every sale.</p>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="icon-circle bg-success text-white me-3 p-2 rounded-circle">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <h6 class="mb-0">Team Building</h6>
                                    </div>
                                    <p class="text-muted small ms-5">Build your own network and earn override commissions.</p>
                                </div>

                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="icon-circle bg-warning text-white me-3 p-2 rounded-circle">
                                            <i class="fas fa-gift"></i>
                                        </div>
                                        <h6 class="mb-0">Rewards & Recognition</h6>
                                    </div>
                                    <p class="text-muted small ms-5">Win gadgets, trips, and vehicles on achieving targets.</p>
                                </div>

                                <div class="mt-5 text-center">
                                    <img src="/assets/images/logo.png" alt="APS Dream Home" class="img-fluid" style="max-height: 100px; opacity: 0.8;">
                                </div>
                            </div>
                        </div>

                        <!-- Registration Form Column -->
                        <div class="col-lg-8">
                            <form method="POST" action="/associate/store" class="needs-validation" novalidate>
                                <h4 class="text-primary mb-4 border-bottom pb-2">Personal Information</h4>
                                
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="full_name" value="<?= htmlspecialchars($old['full_name'] ?? '') ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="form-label">Mobile Number <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" name="mobile" value="<?= htmlspecialchars($old['mobile'] ?? '') ?>" pattern="[0-9]{10}" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>" required>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Referral Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="referrer_code" value="<?= htmlspecialchars($referrer_code ?: ($old['referrer_code'] ?? '')) ?>" <?= $referrer_code ? 'readonly' : '' ?> required placeholder="Enter Sponsor Code">
                                        <div class="form-text">This is mandatory. Ask your sponsor.</div>
                                    </div>
                                </div>

                                <h4 class="text-primary mt-4 mb-4 border-bottom pb-2">Security</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                </div>

                                <h4 class="text-primary mt-4 mb-4 border-bottom pb-2">Additional Details (Optional)</h4>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Aadhar Number</label>
                                        <input type="text" class="form-control" name="aadhar_number" value="<?= htmlspecialchars($old['aadhar_number'] ?? '') ?>" pattern="[0-9]{12}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">PAN Number</label>
                                        <input type="text" class="form-control" name="pan_number" value="<?= htmlspecialchars($old['pan_number'] ?? '') ?>" style="text-transform: uppercase;">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($old['address'] ?? '') ?></textarea>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">State</label>
                                        <input type="text" class="form-control" name="state" value="<?= htmlspecialchars($old['state'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">District/City</label>
                                        <input type="text" class="form-control" name="district" value="<?= htmlspecialchars($old['district'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Pin Code</label>
                                        <input type="text" class="form-control" name="pin_code" value="<?= htmlspecialchars($old['pin_code'] ?? '') ?>">
                                    </div>
                                </div>

                                <div class="mt-4 pt-3 text-center">
                                    <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill shadow-sm hover-lift">
                                        <i class="fas fa-user-plus me-2"></i>Register as Associate
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light text-center py-3">
                    <p class="mb-0 text-muted">Already have an account? <a href="/associate/login" class="text-primary fw-bold text-decoration-none">Login Here</a></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
body {
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
.form-control:focus {
    border-color: #4e73df;
    box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
}
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>
