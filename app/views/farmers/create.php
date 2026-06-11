<?php $pageTitle = 'Add Farmer'; ?>
<?php $states = $states ?? []; $districts = $districts ?? []; $success = $_SESSION['success'] ?? null; $error = $_SESSION['error'] ?? null; unset($_SESSION['success'], $_SESSION['error']); ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb"><ol class="breadcrumb"><li class="breadcrumb-item"><a href="<?= BASE_URL ?>admin/dashboard"><i class="fas fa-home"></i> Dashboard</a></li><li class="breadcrumb-item"><a href="<?= BASE_URL ?>farmers/list">Farmers</a></li><li class="breadcrumb-item active">Add Farmer</li></ol></nav>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-user-plus me-2"></i>Register New Farmer</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if ($success): ?>
                    <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($success) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>
                    <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($error) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    <?php endif; ?>
                    <form method="post" action="<?= BASE_URL ?>farmers">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-user me-2"></i>Personal Information</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required placeholder="Farmer's full name">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="farmer@example.com">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" name="phone" required placeholder="+91 9XXXXXXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Address <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="address" required placeholder="Full address">
                            </div>
                        </div>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">State <span class="text-danger">*</span></label>
                                <select class="form-select" name="state_id" required>
                                    <option value="">Select State</option>
                                    <?php foreach ($states as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">District <span class="text-danger">*</span></label>
                                <select class="form-select" name="district_id" required>
                                    <option value="">Select District</option>
                                    <?php foreach ($districts as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-id-card me-2"></i>KYC Details</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Aadhar Number</label>
                                <input type="text" class="form-control" name="aadhar_number" placeholder="XXXX XXXX XXXX">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">PAN Number</label>
                                <input type="text" class="form-control" name="pan_number" placeholder="ABCDE1234F">
                            </div>
                        </div>

                        <h6 class="border-bottom pb-2 mb-3"><i class="fas fa-university me-2"></i>Bank Details</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label">Bank Account Number</label>
                                <input type="text" class="form-control" name="bank_account" placeholder="Account number">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">IFSC Code</label>
                                <input type="text" class="form-control" name="ifsc_code" placeholder="SBIN0001234">
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Farmer</button>
                            <a href="<?= BASE_URL ?>farmers/list" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
