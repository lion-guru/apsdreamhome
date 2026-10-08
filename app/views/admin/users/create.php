<!-- Page Header -->
<div class="mb-4">
    <a href="<?php echo BASE_URL; ?>/admin/users" class="text-decoration-none text-muted">
        <i class="fas fa-arrow-left me-2"></i>Back to Users
    </a>
    <h1 class="h3 mt-2 mb-1">Add New Team Member</h1>
    <p class="text-muted">Create a new user account with proper role setup</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?php echo BASE_URL; ?>/admin/users">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token'] ?? ''; ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" class="form-control" name="phone" maxlength="10">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="password" required value="Aps@2026">
                            <div class="form-text">Default: Aps@2026</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Role <span class="text-danger">*</span></label>
                            <select class="form-select" name="role" id="roleSelect" required>
                                <?php if (!empty($roleGroups) && is_array($roleGroups)): ?>
                                    <?php foreach ($roleGroups as $groupLabel => $groupRoles): ?>
                                        <?php
                                        // Super-admins are never minted from the create form
                                        $groupRoles = array_values(array_diff($groupRoles, ['super_admin']));
                                        if (empty($groupRoles)) continue;
                                        ?>
                                        <optgroup label="<?= htmlspecialchars($groupLabel ?? '') ?>">
                                            <?php foreach ($groupRoles as $r): ?>
                                                <option value="<?= htmlspecialchars($r ?? '') ?>"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $r ?? ''))) ?></option>
                                            <?php endforeach; ?>
                                        </optgroup>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <optgroup label="Office Staff">
                                        <option value="employee">Employee</option>
                                        <option value="telecaller">Telecaller</option>
                                        <option value="manager">Manager</option>
                                    </optgroup>
                                    <optgroup label="Field Team">
                                        <option value="associate">Associate (MLM)</option>
                                        <option value="agent">Agent</option>
                                    </optgroup>
                                    <optgroup label="Customers">
                                        <option value="customer">Customer</option>
                                    </optgroup>
                                    <optgroup label="System">
                                        <option value="admin">Admin</option>
                                    </optgroup>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded mt-2">
                                <h6 class="fw-semibold mb-3"><i class="fas fa-map-marker-alt me-2"></i>Address</h6>
                                <?php
                                $addressPrefix = 'user';
                                $addressValues = [];
                                $addressShowMap = false;
                                $addressRequired = false;
                                include __DIR__ . '/../../components/address-form.php';
                                ?>
                            </div>
                        </div>

                        <!-- Employee-specific fields (shown for employee/telecaller/manager) -->
                        <div id="employeeFields" >
                            <hr class="my-3">
                            <h6 class="text-primary mb-3"><i class="fas fa-building me-2"></i>Employment Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Department</label>
                                    <select class="form-select" name="department">
                                        <option value="General">General</option>
                                        <option value="HR">HR</option>
                                        <option value="IT">IT</option>
                                        <option value="Sales">Sales</option>
                                        <option value="Marketing">Marketing</option>
                                        <option value="Operations">Operations</option>
                                        <option value="Finance">Finance</option>
                                        <option value="Legal">Legal</option>
                                        <option value="Customer Support">Customer Support</option>
                                        <option value="Telecalling">Telecalling</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Designation</label>
                                    <input type="text" class="form-control" name="designation" placeholder="e.g. Executive, Sr. Executive, Team Lead">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Salary (₹/month)</label>
                                    <input type="number" class="form-control" name="salary" step="100" min="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Joining Date</label>
                                    <input type="date" class="form-control" name="join_date" value="<?= date('Y-m-d') ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Associate/Agent-specific fields -->
                        <div id="mlmFields" >
                            <hr class="my-3">
                            <h6 class="text-success mb-3"><i class="fas fa-handshake me-2"></i>MLM / Agent Details</h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Occupation</label>
                                    <input type="text" class="form-control" name="occupation" placeholder="e.g. Real Estate Advisor">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Sponsor/Referral Code <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="referral_code" id="adminReferralCode" placeholder="Enter sponsor's referral code" required>
                                </div>
                                <div class="col-12">
                                    <div id="adminReferralNameDisplay" class="mt-2"></div>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Required for Associate/Agent to join the MLM network tree.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 d-flex gap-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Create User</button>
                        <a href="<?php echo BASE_URL; ?>/admin/users" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm bg-light">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Role Information</h6>
                <div id="roleInfo">
                    <p class="text-muted small mb-0">Select a role to see details.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('roleSelect').addEventListener('change', function() {
    const role = this.value;
    const empFields = document.getElementById('employeeFields');
    const mlmFields = document.getElementById('mlmFields');
    const roleInfo = document.getElementById('roleInfo');

    // Toggle fields
    empFields.style.display = ['employee', 'telecaller', 'manager'].includes(role) ? 'block' : 'none';
    mlmFields.style.display = ['associate', 'agent'].includes(role) ? 'block' : 'none';

    // Role info
    const info = {
        employee: '<strong>Employee</strong><br><small class="text-muted">Office staff with department/designation/salary. Can login to admin panel. Gets user + employees records.</small>',
        telecaller: '<strong>Telecaller</strong><br><small class="text-muted">Telesales staff. Part of MLM system with salary + incentives. Gets user + employees + MLM records.</small>',
        manager: '<strong>Manager</strong><br><small class="text-muted">Team manager with admin panel access. Can manage employees, view reports, approve requests.</small>',
        associate: '<strong>Associate (MLM)</strong><br><small class="text-muted">Field associate in MLM network. Earns commissions from sales. Gets user + wallet + MLM profile + network tree.</small>',
        agent: '<strong>Agent</strong><br><small class="text-muted">Independent real estate agent. Earns brokerage on deals. Gets user + wallet + MLM profile + network tree.</small>',
        customer: '<strong>Customer</strong><br><small class="text-muted">Property buyer. Can browse properties, make bookings, track EMI. Gets user + wallet.</small>',
        admin: '<strong>Admin</strong><br><small class="text-muted">Full system access. Can manage all modules, users, settings. Gets user record only.</small>'
    };
    roleInfo.innerHTML = info[role] || '<p class="text-muted small mb-0">Select a role to see details.</p>';
});

// Trigger on load
document.getElementById('roleSelect').dispatchEvent(new Event('change'));

// Referral name resolution for admin create
function resolveAdminReferralName() {
    var referralInput = document.getElementById('adminReferralCode');
    var referralCode = referralInput ? referralInput.value.trim() : '';
    var display = document.getElementById('adminReferralNameDisplay');
    if (!referralCode) {
        display.innerHTML = '';
        return;
    }
    display.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Resolving...';
    fetch('<?= BASE_URL ?>/api/user/resolve-sponsor?code=' + encodeURIComponent(referralCode))
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                var name = data.name || 'Unknown';
                var role = data.role || '';
                var displayText = '<strong class="text-success">' + name + '</strong> <span class="text-muted">(' + role + ')</span>';
                display.innerHTML = displayText;
            } else {
                display.innerHTML = '<span class="text-danger">Invalid referral code</span>';
            }
        })
        .catch(() => {
            display.innerHTML = '<span class="text-danger">Error validating referral</span>';
        });
}

var adminReferralInput = document.getElementById('adminReferralCode');
if (adminReferralInput) {
    adminReferralInput.addEventListener('input', resolveAdminReferralName);
    adminReferralInput.addEventListener('blur', resolveAdminReferralName);
}
</script>
