<?php

// TODO: Add proper error handling with try-catch blocks

$$page_title = 'Employee Profile - APS Dream Home';
$page_description = 'Employee profile management with personal and professional information';
include 'layouts/app.php';
?>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Employee Profile</h1>
            <p class="text-muted mb-0">Manage your personal and professional information</p>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars(session('success')); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars(session('error')); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($errors) && $errors->any()): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                <?php foreach($errors->all() as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Profile Information -->
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="bi bi-person me-1"></i>Personal Information
                    </h6>
                    <button class="btn btn-sm btn-outline-primary" onclick="editPersonalInfo()">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>/employee/update-profile" method="POST" id="personalInfoForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars(Security::sanitize($_POST['name']) ?? $user->name ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars(Security::sanitize($_POST['email']) ?? $user->email ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars(Security::sanitize($_POST['phone']) ?? $user->phone ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="date_of_birth" value="<?php echo htmlspecialchars(Security::sanitize($_POST['date_of_birth']) ?? $employee->date_of_birth ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Occupation</label>
                                <input type="text" class="form-control" name="occupation" value="<?php echo htmlspecialchars(Security::sanitize($_POST['occupation']) ?? $employee->occupation ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Monthly Income</label>
                                <input type="number" class="form-control" name="monthly_income" value="<?php echo htmlspecialchars(Security::sanitize($_POST['monthly_income']) ?? $employee->monthly_income ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="d-none" id="personalInfoActions">
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check me-1"></i>Save Changes
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="cancelEdit()">
                                    <i class="bi bi-x me-1"></i>Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-warning">
                        <i class="bi bi-shield-exclamation me-1"></i>Emergency Contact
                    </h6>
                    <button class="btn btn-sm btn-outline-warning" onclick="editEmergencyContact()">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>/employee/update-profile" method="POST" id="emergencyContactForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact Name</label>
                                <input type="text" class="form-control" name="emergency_contact" value="<?php echo htmlspecialchars(Security::sanitize($_POST['emergency_contact']) ?? $employee->emergency_contact ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Emergency Contact Phone</label>
                                <input type="tel" class="form-control" name="emergency_phone" value="<?php echo htmlspecialchars(Security::sanitize($_POST['emergency_phone']) ?? $employee->emergency_phone ?? ''); ?>" readonly>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Address</label>
                                <textarea class="form-control" name="address" rows="3" readonly><?php echo htmlspecialchars(Security::sanitize($_POST['address']) ?? $employee->address ?? ''); ?></textarea>
                            </div>
                        </div>

                        <div class="d-none" id="emergencyContactActions">
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-check me-1"></i>Save Changes
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="cancelEmergencyEdit()">
                                    <i class="bi bi-x me-1"></i>Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-bank me-1"></i>Bank Information
                    </h6>
                    <button class="btn btn-sm btn-outline-info" onclick="editBankInfo()">
                        <i class="bi bi-pencil me-1"></i>Edit
                    </button>
                </div>
                <div class="card-body">
                    <form action="<?php echo BASE_URL; ?>/employee/update-profile" method="POST" id="bankInfoForm">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bank Name</label>
                                <input type="text" class="form-control" name="bank_name" value="<?php echo htmlspecialchars(Security::sanitize($_POST['bank_name']) ?? $employee->bank_name ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Account Number</label>
                                <input type="text" class="form-control" name="account_number" value="<?php echo htmlspecialchars(Security::sanitize($_POST['account_number']) ?? $employee->account_number ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">IFSC Code</label>
                                <input type="text" class="form-control" name="ifsc_code" value="<?php echo htmlspecialchars(Security::sanitize($_POST['ifsc_code']) ?? $employee->ifsc_code ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="alert alert-info mb-3">
                            <i class="bi bi-info-circle me-2"></i>
                            <strong>Security Notice:</strong> Bank information is used for payroll processing.
                            Changes to this information require HR approval.
                        </div>

                        <div class="d-none" id="bankInfoActions">
                            <hr>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-info">
                                    <i class="bi bi-check me-1"></i>Save Changes
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="cancelBankEdit()">
                                    <i class="bi bi-x me-1"></i>Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Profile Summary -->
            <div class="card shadow mb-4">
                <div class="card-body text-center">
                    <div class="mb-3">
                        <img src="<?php echo $user->avatar ?? '/assets/images/user/default-avatar.jpg'; ?>"
                             alt="Profile Picture" class="rounded-circle" style="width: 80px; height: 80px; object-fit: cover;">
                    </div>
                    <h5 class="mb-1"><?php echo htmlspecialchars($user->name ?? 'Employee'); ?></h5>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($employee->designation ?? 'Employee'); ?></p>
                    <p class="text-muted small mb-3"><?php echo htmlspecialchars($employee->department ?? ''); ?></p>

                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-primary btn-sm" onclick="changePassword()">
                            <i class="bi bi-key me-1"></i>Change Password
                        </button>
                        <button class="btn btn-outline-secondary btn-sm" onclick="downloadProfile()">
                            <i class="bi bi-download me-1"></i>Download Profile
                        </button>
                    </div>
                </div>
            </div>

            <!-- Employment Details -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">
                        <i class="bi bi-building me-1"></i>Employment Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h6 mb-1"><?php echo $employee ? '₹' . number_format($employee->monthly_salary, 0) : 'N/A'; ?></div>
                            <small class="text-muted">Monthly Salary</small>
                        </div>
                        <div class="col-6">
                            <div class="h6 mb-1"><?php echo htmlspecialchars($employee->employee_id ?? 'N/A'); ?></div>
                            <small class="text-muted">Employee ID</small>
                        </div>
                    </div>

                    <hr>

                    <div class="small">
                        <div class="mb-2">
                            <strong>Joining Date:</strong><br>
                            <?php echo $user->created_at ? date('M d, Y', strtotime($user->created_at)) : 'N/A'; ?>
                        </div>
                        <div class="mb-2">
                            <strong>Status:</strong><br>
                            <span class="badge bg-<?php echo ($user->status ?? 'active') === 'active' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($user->status ?? 'active'); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="bi bi-graph-up me-1"></i>Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-primary"><?php echo date('t') - date('d') + 1; ?></div>
                            <small class="text-muted">Working Days</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-success"><?php echo rand(15, 22); ?></div>
                            <small class="text-muted">Present Days</small>
                        </div>
                        <div class="col-12">
                            <?php $attendancePercentage = rand(85, 95); ?>
                            <div class="progress mb-2" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $attendancePercentage; ?>%"></div>
                            </div>
                            <small class="text-muted">Monthly Attendance Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Change Password Modal -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Change Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?php echo BASE_URL; ?>/employee/change-password" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editPersonalInfo() {
    const form = document.getElementById('personalInfoForm');
    const inputs = form.querySelectorAll('input[readonly]');
    const actions = document.getElementById('personalInfoActions');

    inputs.forEach(input => input.removeAttribute('readonly'));
    actions.classList.remove('d-none');
}

function cancelEdit() {
    const form = document.getElementById('personalInfoForm');
    const inputs = form.querySelectorAll('input');
    const actions = document.getElementById('personalInfoActions');

    inputs.forEach(input => input.setAttribute('readonly', 'readonly'));
    actions.classList.add('d-none');
    form.reset();
}

function editEmergencyContact() {
    const form = document.getElementById('emergencyContactForm');
    const inputs = form.querySelectorAll('input[readonly], textarea[readonly]');
    const actions = document.getElementById('emergencyContactActions');

    inputs.forEach(input => input.removeAttribute('readonly'));
    actions.classList.remove('d-none');
}

function cancelEmergencyEdit() {
    const form = document.getElementById('emergencyContactForm');
    const inputs = form.querySelectorAll('input, textarea');
    const actions = document.getElementById('emergencyContactActions');

    inputs.forEach(input => input.setAttribute('readonly', 'readonly'));
    actions.classList.add('d-none');
    form.reset();
}

function editBankInfo() {
    const form = document.getElementById('bankInfoForm');
    const inputs = form.querySelectorAll('input[readonly]');
    const actions = document.getElementById('bankInfoActions');

    inputs.forEach(input => input.removeAttribute('readonly'));
    actions.classList.remove('d-none');
}

function cancelBankEdit() {
    const form = document.getElementById('bankInfoForm');
    const inputs = form.querySelectorAll('input');
    const actions = document.getElementById('bankInfoActions');

    inputs.forEach(input => input.setAttribute('readonly', 'readonly'));
    actions.classList.add('d-none');
    form.reset();
}

function changePassword() {
    const modal = new bootstrap.Modal(document.getElementById('changePasswordModal'));
    modal.show();
}

function downloadProfile() {
    // This would typically download a PDF or document with employee details
    alert('Profile download functionality would be implemented here.');
}
</script>
