<?php
$user = $user ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$activeTab = $_GET['tab'] ?? 'overview';
$csrf = $_SESSION['csrf_token'] ?? '';

$roleColors = ['admin'=>'danger','super_admin'=>'danger','manager'=>'primary','employee'=>'secondary','telecaller'=>'info','associate'=>'warning','agent'=>'success','customer'=>'dark','user'=>'secondary'];
$roleColor = $roleColors[$user['role'] ?? ''] ?? 'secondary';
?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center">
        <a href="<?= $base ?>/admin/users" class="text-decoration-none text-muted me-3"><i class="fas fa-arrow-left fa-lg"></i></a>
        <div class="style-91226" class="me-3">
            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
        </div>
        <div>
            <h4 class="mb-0"><?= htmlspecialchars($user['name'] ?? 'Unknown') ?></h4>
            <small class="text-muted"><?= htmlspecialchars($user['email'] ?? '') ?> &middot; <?= htmlspecialchars($user['customer_id'] ?? 'ID: '.$user['id'] ?? '') ?></small>
        </div>
        <span class="badge bg-<?= $roleColor ?> ms-3 fs-6"><?= ucfirst($user['role'] ?? '') ?></span>
        <span class="badge bg-<?= ($user['status'] ?? '') === 'active' ? 'success' : (($user['status'] ?? '') === 'suspended' ? 'danger' : 'secondary') ?> ms-1 fs-6"><?= ucfirst($user['status'] ?? '') ?></span>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/admin/users/<?= $user['id'] ?>/edit" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
        <a href="<?= $base ?>/admin/users/<?= $user['id'] ?>/wallet" class="btn btn-outline-success btn-sm"><i class="fas fa-wallet me-1"></i>Wallet</a>
        <a href="<?= $base ?>/admin/users/<?= $user['id'] ?>/commissions" class="btn btn-outline-info btn-sm"><i class="fas fa-coins me-1"></i>Commissions</a>
        <a href="<?= $base ?>/admin/users/<?= $user['id'] ?>/team" class="btn btn-outline-warning btn-sm"><i class="fas fa-users me-1"></i>Team</a>
        <a href="<?= $base ?>/admin/users/<?= $user['id'] ?>/activity-log" class="btn btn-outline-secondary btn-sm"><i class="fas fa-history me-1"></i>Activity</a>
    </div>
</div>

<!-- Tabs -->
<?php $tabs = ['overview'=>'Overview','profile'=>'Profile','mlm'=>'MLM & Sponsor','quick-actions'=>'Actions'];
if (in_array($user['role'] ?? '', ['associate','agent','telecaller'])) $tabs['mlm'] = 'MLM & Sponsor';
?>
<ul class="nav nav-tabs mb-4">
    <?php foreach ($tabs as $key => $label): ?>
    <li class="nav-item">
        <a class="nav-link <?= $activeTab === $key ? 'active' : '' ?>" href="<?= $base ?>/admin/users/<?= $user['id'] ?>?tab=<?= $key ?>"><?= $label ?></a>
    </li>
    <?php endforeach; ?>
</ul>

<?php if ($activeTab === 'overview'): ?>
<!-- OVERVIEW TAB -->
<div class="row g-4">
    <!-- Stats -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-primary text-white">
            <div class="card-body text-center"><i class="fas fa-building fa-2x mb-2 opacity-75"></i><h3 class="mb-0"><?= $user['property_count'] ?? 0 ?></h3><small>Properties</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-success text-white">
            <div class="card-body text-center"><i class="fas fa-calendar-check fa-2x mb-2 opacity-75"></i><h3 class="mb-0"><?= $user['booking_count'] ?? 0 ?></h3><small>Bookings</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-info text-white">
            <div class="card-body text-center"><i class="fas fa-wallet fa-2x mb-2 opacity-75"></i><h3 class="mb-0">₹<?= number_format($user['wallet_balance'] ?? 0) ?></h3><small>Wallet</small></div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm bg-warning text-white">
            <div class="card-body text-center"><i class="fas fa-clock fa-2x mb-2 opacity-75"></i><h3 class="mb-0"><?= isset($user['last_login_at']) ? date('M d', strtotime($user['last_login_at'])) : 'Never' ?></h3><small>Last Login</small></div>
        </div>
    </div>

    <!-- Info Cards -->
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Info</h6></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" class="style-19131">Phone</td><td><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></td></tr>
                    <tr><td class="text-muted">City</td><td><?= htmlspecialchars($user['city'] ?? 'N/A') ?></td></tr>
                    <tr><td class="text-muted">Address</td><td><?= htmlspecialchars($user['address'] ?? 'N/A') ?></td></tr>
                    <tr><td class="text-muted">Registered</td><td><?= isset($user['created_at']) ? date('M d, Y h:i A', strtotime($user['created_at'])) : 'N/A' ?></td></tr>
                    <tr><td class="text-muted">Method</td><td><?= ucfirst($user['registration_method'] ?? 'N/A') ?></td></tr>
                    <tr><td class="text-muted">KYC Status</td><td><span class="badge bg-<?= ($user['kyc_status'] ?? '') === 'verified' ? 'success' : 'warning' ?>"><?= ucfirst($user['kyc_status'] ?? 'pending') ?></span></td></tr>
                    <tr><td class="text-muted">Last Login</td><td><?= $user['last_login_at'] ?? 'Never' ?></td></tr>
                    <tr><td class="text-muted">Login Count</td><td><?= $user['login_count'] ?? 0 ?></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-link me-2"></i>Referral & Sponsor</h6></div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr><td class="text-muted" class="style-19131">Referral Code</td><td><code class="fs-6"><?= htmlspecialchars($user['referral_code'] ?? 'N/A') ?></code></td></tr>
                    <tr><td class="text-muted">Referred By</td><td><?= $user['referred_by_name'] ?? ($user['referred_by'] ?? 'None') ?></td></tr>
                    <tr><td class="text-muted">Sponsor</td><td><?= $user['sponsor_name'] ?? ($user['sponsor_id'] ?? 'None') ?></td></tr>
                    <tr><td class="text-muted">MLM Position</td><td><?= ucfirst($user['mlm_position'] ?? 'N/A') ?></td></tr>
                    <tr><td class="text-muted">MLM Rank</td><td><?= ucfirst($user['mlm_rank'] ?? 'Associate') ?></td></tr>
                    <tr><td class="text-muted">Onboarding Track</td><td><?= ucfirst($user['onboarding_track'] ?? 'N/A') ?></td></tr>
                </table>
            </div>
        </div>
    </div>
</div>

<?php elseif ($activeTab === 'profile'): ?>
<!-- PROFILE TAB -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="mb-3"><i class="fas fa-user-edit me-2"></i>Edit Profile</h5>
        <form id="profileForm">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <div class="row g-3">
                <div class="col-md-6"><label class="form-label">Name</label><input type="text" class="form-control" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">Phone</label><input type="text" class="form-control" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"></div>
                <div class="col-md-6"><label class="form-label">City</label><input type="text" class="form-control" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>"></div>
                <div class="col-md-6">
                    <label class="form-label">Role</label>
                    <select class="form-select" name="role">
                        <?php foreach (['admin','super_admin','manager','employee','telecaller','associate','agent','customer','user'] as $r): ?>
                        <option value="<?= $r ?>" <?= ($user['role'] ?? '') === $r ? 'selected' : '' ?>><?= ucfirst($r) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <?php foreach (['active','inactive','suspended'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($user['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6"><label class="form-label">New Password (leave blank to keep)</label><input type="text" class="form-control" name="password" placeholder="Min 6 chars"></div>
                <div class="col-12"><label class="form-label">Address</label><textarea class="form-control" name="address" rows="2"><?= htmlspecialchars($user['address'] ?? '') ?></textarea></div>
            </div>
            <div class="mt-3"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Changes</button></div>
        </form>
    </div>
</div>
<script>
document.getElementById('profileForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showLoader();
    const fd = new FormData(this);
    fetch('<?= $base ?>/admin/users/<?= $user['id'] ?>/update', {
        method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: new URLSearchParams(fd)
    }).then(r => r.json()).then(d => {
        if (d.success) { showToast('Updated!', 'success'); location.reload(); } else { showToast(d.message || 'Failed', 'danger'); }
    }).catch(() => showToast('Network error', 'danger')).finally(() => hideLoader());
});
</script>

<?php elseif ($activeTab === 'mlm'): ?>
<!-- MLM & SPONSOR TAB -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Change Sponsor / Referrer</h6></div>
            <div class="card-body">
                <p class="text-muted small">Changes sponsor in: users, mlm_profiles, associates, mlm_network_tree, network_tree (5 tables)</p>
                <form id="sponsorForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <div class="mb-3">
                        <label class="form-label">New Sponsor ID</label>
                        <input type="number" class="form-control" name="new_sponsor_id" required min="1" placeholder="Enter user ID">
                    </div>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-exchange-alt me-2"></i>Change Sponsor</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-code me-2"></i>Change Referral Code</h6></div>
            <div class="card-body">
                <p class="text-muted small">Updates: users.referral_code + mlm_profiles.referral_code</p>
                <form id="referralForm">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <div class="mb-3">
                        <label class="form-label">New Referral Code</label>
                        <input type="text" class="form-control" name="new_referral_code" required minlength="3" maxlength="20" class="style-36130" value="<?= htmlspecialchars($user['referral_code'] ?? '') ?>">
                    </div>
                    <button type="submit" class="btn btn-info"><i class="fas fa-code me-2"></i>Change Code</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('sponsorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    apsConfirm('Change sponsor? This updates 5 tables.').then(function(ok) {
        if (!ok) return;
    showLoader();
    fetch('<?= $base ?>/admin/users/<?= $user['id'] ?>/change-sponsor', {
        method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: new URLSearchParams(new FormData(this))
    });
    }).then(r => r.json()).then(d => { showToast(d.message || d.error, d.success ? 'success' : 'danger'); if (d.success) location.reload(); }).catch(() => showToast('Error', 'danger')).finally(() => hideLoader());
});
document.getElementById('referralForm').addEventListener('submit', function(e) {
    e.preventDefault();
    showLoader();
    fetch('<?= $base ?>/admin/users/<?= $user['id'] ?>/change-referral', {
        method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'}, body: new URLSearchParams(new FormData(this))
    }).then(r => r.json()).then(d => { showToast(d.message || d.error, d.success ? 'success' : 'danger'); if (d.success) location.reload(); }).catch(() => showToast('Error', 'danger')).finally(() => hideLoader());
});
</script>

<?php elseif ($activeTab === 'quick-actions'): ?>
<!-- QUICK ACTIONS TAB -->
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-key fa-3x text-warning mb-3"></i>
                <h5>Reset Password</h5>
                <p class="text-muted small">Set a new password for this user</p>
                <button onclick="resetPassword()" class="btn btn-warning">Reset Password</button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-<?= ($user['status'] ?? '') === 'active' ? 'ban' : 'check' ?> fa-3x text-<?= ($user['status'] ?? '') === 'active' ? 'secondary' : 'success' ?> mb-3"></i>
                <h5><?= ($user['status'] ?? '') === 'active' ? 'Deactivate' : 'Activate' ?></h5>
                <p class="text-muted small">Toggle user account status</p>
                <button onclick="toggleStatus()" class="btn btn-<?= ($user['status'] ?? '') === 'active' ? 'secondary' : 'success' ?>"><?= ($user['status'] ?? '') === 'active' ? 'Deactivate' : 'Activate' ?></button>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                <h5>Deactivate (Soft Delete)</h5>
                <p class="text-muted small">Sets status to inactive. Data preserved.</p>
                <button onclick="softDelete()" class="btn btn-danger">Soft Delete</button>
            </div>
        </div>
    </div>
</div>
<script>
function resetPassword() {
    const pw = prompt('Enter new password (min 6 chars):');
    if (!pw || pw.length < 6) { showToast('Password too short', 'info'); return; }
    fetch('<?= $base ?>/admin/users/<?= $user['id'] ?>/update', {
        method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=<?= $csrf ?>&password=' + encodeURIComponent(pw)
    }).then(r => r.json()).then(d => { showToast(d.message, 'info'); if (d.success) location.reload(); }).catch(() => showToast('Error', 'danger'));
}
function toggleStatus() {
    const newStatus = '<?= ($user['status'] ?? '') === 'active' ? 'inactive' : 'active' ?>';
    fetch('<?= $base ?>/admin/users/<?= $user['id'] ?>/update', {
        method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=<?= $csrf ?>&status=' + newStatus
    }).then(r => r.json()).then(d => { showToast(d.message, 'info'); if (d.success) location.reload(); }).catch(() => showToast('Error', 'danger'));
}
function softDelete() {
    apsConfirm('Deactivate this user? They will no longer be able to login.').then(function(ok) {
        if (!ok) return;
    fetch('<?= $base ?>/admin/users/<?= $user['id'] ?>/soft-delete', {
        method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=<?= $csrf ?>'
    });
    }).then(r => r.json()).then(d => { showToast(d.message, 'info'); if (d.success) location.reload(); }).catch(() => showToast('Error', 'danger'));
}
</script>
<?php endif; ?>
