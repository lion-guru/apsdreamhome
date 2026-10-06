<?php
/**
 * My Profile
*/

$page_title = $page_title ?? 'My Profile';
$user = $user ?? [];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="fas fa-user me-2"></i>My Profile</h4>
                </div>
                <div class="card-body">
                    <!-- Profile Header -->
                    <div class="row mb-4">
                        <div class="col-md-3 text-center">
                            <div class="avatar-circle bg-primary text-white mx-auto mb-3" style="width: 100px; height: 100px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
                                <?= strtoupper(substr($user['name'] ?? 'E', 0, 1)) ?>
                            </div>
                            <h4><?= htmlspecialchars($user['name'] ?? 'Employee') ?></h4>
                            <p class="text-muted mb-0"><?= htmlspecialchars($user['email'] ?? '') ?></p>
                            <small class="text-muted"><?= htmlspecialchars($user['role'] ?? 'Employee') ?> | <?= htmlspecialchars($user['department'] ?? 'General') ?></small>
                        </div>
                        <div class="col-md-9">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Employee ID</label>
                                    <div class="fw-bold"><?= htmlspecialchars($user['employee_code'] ?? $user['id'] ?? '-') ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Joining Date</label>
                                    <div class="fw-bold"><?= $user['joining_date'] ? date('d M Y', strtotime($user['joining_date'])) : '-' ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Designation</label>
                                    <div class="fw-bold"><?= htmlspecialchars($user['designation'] ?? '-') ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label text-muted">Phone</label>
                                    <div class="fw-bold"><?= htmlspecialchars($user['phone'] ?? '-') ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Profile Form -->
                    <form method="POST" class="row g-3" id="profileForm">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="col-12">
                            <h5 class="mb-3">Personal Information</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" name="date_of_birth" class="form-control" value="<?= htmlspecialchars($user['date_of_birth'] ?? '') ?>">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Emergency Contact</label>
                            <input type="text" name="emergency_contact" class="form-control" value="<?= htmlspecialchars($user['emergency_contact'] ?? '') ?>" placeholder="Name & Phone">
                        </div>

                        <div class="col-12 mt-4">
                            <h5 class="mb-3">Bank Details</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Bank Account Number</label>
                            <input type="text" name="bank_account" class="form-control" value="<?= htmlspecialchars($user['bank_account'] ?? '') ?>" placeholder="Account Number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">IFSC Code</label>
                            <input type="text" name="bank_ifsc" class="form-control" value="<?= htmlspecialchars($user['bank_ifsc'] ?? '') ?>" placeholder="IFSC Code" maxlength="11">
                        </div>

                        <div class="col-12 mt-4">
                            <h5 class="mb-3">Tax Documents</h5>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">PAN Number</label>
                            <input type="text" name="pan_number" class="form-control" value="<?= htmlspecialchars($user['pan_number'] ?? '') ?>" placeholder="ABCDE1234F" maxlength="10" style="text-transform: uppercase;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Aadhaar Number</label>
                            <input type="text" name="aadhaar_number" class="form-control" value="<?= htmlspecialchars($user['aadhaar_number'] ?? '') ?>" placeholder="1234 5678 9012" maxlength="14">
                        </div>

                        <div class="col-12 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                            <a href="<?= BASE_URL ?>/employee/self-service/change-password" class="btn btn-outline-secondary ms-2">
                                <i class="fas fa-key me-1"></i> Change Password
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card { border-radius: 0.75rem; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
.avatar-circle { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.15); }
</style>

<script>
document.getElementById('profileForm').addEventListener('submit', function(e) {
    const pan = this.querySelector('input[name="pan_number"]').value;
    if (pan && !/^[A-Z]{5}[0-9]{4}[A-Z]$/i.test(pan)) {
        e.preventDefault();
        alert('Please enter a valid PAN number (e.g., ABCDE1234F)');
        return false;
    }
    
    const aadhaar = this.querySelector('input[name="aadhaar_number"]').value;
    if (aadhaar && !/^\d{4}\s?\d{4}\s?\d{4}$/.test(aadhaar.replace(/\s/g, ''))) {
        e.preventDefault();
        alert('Please enter a valid 12-digit Aadhaar number');
        return false;
    }
    
    const ifsc = this.querySelector('input[name="bank_ifsc"]').value;
    if (ifsc && !/^[A-Z]{4}0[A-Z0-9]{6}$/i.test(ifsc)) {
        e.preventDefault();
        alert('Please enter a valid IFSC code (e.g., SBIN0001234)');
        return false;
    }
});
</script>