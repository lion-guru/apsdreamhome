<?php
/**
 * Associate Profile View
 * APS Dream Home - Associate Portal
 */
$page_title = $page_title ?? 'My Profile - Associate Portal';
$user = $user ?? [];
$associate = $associate ?? [];
$wallet_balance = $wallet_balance ?? 0.0;
$total_commissions = $total_commissions ?? 0.0;
$pending_commissions = $pending_commissions ?? 0.0;
$property_count = $property_count ?? 0;
$lead_count = $lead_count ?? 0;
$baseUrl = defined('BASE_URL') ? BASE_URL : '';

$userName = $user['name'] ?? $_SESSION['user_name'] ?? 'Associate Partner';
$userEmail = $user['email'] ?? $_SESSION['user_email'] ?? '';
$userPhone = $user['phone'] ?? '';
$memberSince = $user['created_at'] ?? $associate['created_at'] ?? date('Y-m-d');
$associateCode = $associate['associate_code'] ?? $associate['code'] ?? $user['referral_code'] ?? 'AP-' . str_pad($user['id'] ?? 0, 5, '0', STR_PAD_LEFT);
$rank = strtolower($associate['rank'] ?? 'associate');
$kycStatus = strtolower($associate['kyc_status'] ?? $user['kyc_status'] ?? 'pending');

// Rank styling
$rankLabels = [
    'associate' => 'Associate',
    'senior_associate' => 'Senior Associate',
    'bdm' => 'Business Development Manager',
    'sr_bdm' => 'Senior BDM',
    'vice_president' => 'Vice President',
    'president' => 'President',
    'site_manager' => 'Site Manager',
];
$rankDisplay = $rankLabels[$rank] ?? ucfirst(str_replace('_', ' ', $rank));

$rankColors = [
    'associate' => '#64748b',
    'senior_associate' => '#f59e0b',
    'bdm' => '#3b82f6',
    'sr_bdm' => '#06b6d4',
    'vice_president' => '#8b5cf6',
    'president' => '#ef4444',
    'site_manager' => '#10b981'
];
$rankColor = $rankColors[$rank] ?? '#10b981';

$referralLink = (defined('BASE_URL') ? BASE_URL : '') . '/register?ref=' . urlencode($associateCode);

$success = $_SESSION['flash_success'] ?? $_SESSION['success'] ?? null;
$error = $_SESSION['flash_error'] ?? $_SESSION['error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['success'], $_SESSION['flash_error'], $_SESSION['error']);
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.assoc-profile-card {
    border: none;
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    overflow: hidden;
}
.assoc-profile-header-bg {
    background: linear-gradient(135deg, #0f172a 0%, #134e4a 100%);
    height: 100px;
    position: relative;
}
.assoc-avatar-wrap {
    margin-top: -50px;
    margin-bottom: 12px;
}
.assoc-stat-pill {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 12px 14px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.2s ease;
}
.assoc-stat-pill:hover {
    background: #ffffff;
    border-color: #cbd5e1;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}
.assoc-stat-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}
.copy-ref-btn {
    border: 1px dashed #cbd5e1;
    background: #f8fafc;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s;
}
.copy-ref-btn:hover {
    background: #f1f5f9;
    border-color: #94a3b8;
}
</style>

<div class="container-fluid px-4 py-3">
    <!-- Top Bar / Breadcrumb -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-user-circle me-2 text-primary"></i>My Profile</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-breadcrumb-item"><a href="<?= $baseUrl ?>/associate/dashboard" class="text-decoration-none text-muted">Dashboard</a> <span class="mx-1 text-muted">/</span></li>
                    <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">Profile</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $baseUrl ?>/associate/settings" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                <i class="fas fa-cog me-1"></i> Account Settings
            </a>
            <a href="<?= $baseUrl ?>/associate/documents" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                <i class="fas fa-id-card me-1"></i> KYC Documents
            </a>
        </div>
    </div>

    <!-- Flash Notifications -->
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left Sidebar: Profile Summary & Stats -->
        <div class="col-lg-4 col-xl-4">
            <!-- Profile Identity Card -->
            <div class="assoc-profile-card text-center mb-4">
                <div class="assoc-profile-header-bg"></div>
                <div class="card-body pt-0 px-4 pb-4">
                    <div class="assoc-avatar-wrap">
                        <?php
                        $userId = (int)($user['id'] ?? $_SESSION['user_id'] ?? 0);
                        $photoUrl = !empty($user['profile_image']) ? $baseUrl . '/' . htmlspecialchars($user['profile_image']) : null;
                        $size = 'lg';
                        $userNameVal = $userName;
                        include_once __DIR__ . '/../shared/profile_photo_upload.php';
                        ?>
                    </div>

                    <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($userName) ?></h5>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($userEmail) ?></p>

                    <div class="d-flex justify-content-center gap-2 flex-wrap mb-3">
                        <span class="badge" style="background-color: <?= $rankColor ?>; font-size: 0.75rem; padding: 6px 12px;">
                            <i class="fas fa-medal me-1"></i><?= htmlspecialchars($rankDisplay) ?>
                        </span>
                        <?php if ($kycStatus === 'verified' || $kycStatus === 'approved'): ?>
                            <span class="badge bg-success" style="font-size: 0.75rem; padding: 6px 12px;">
                                <i class="fas fa-check-circle me-1"></i>KYC Verified
                            </span>
                        <?php elseif ($kycStatus === 'submitted' || $kycStatus === 'pending'): ?>
                            <span class="badge bg-warning text-dark" style="font-size: 0.75rem; padding: 6px 12px;">
                                <i class="fas fa-clock me-1"></i>KYC Pending
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary" style="font-size: 0.75rem; padding: 6px 12px;">
                                <i class="fas fa-exclamation-circle me-1"></i>KYC Incomplete
                            </span>
                        <?php endif; ?>
                    </div>

                    <!-- Associate ID / Referral Code Box -->
                    <button type="button" class="copy-ref-btn w-100 d-flex align-items-center justify-content-between my-3 text-start" onclick="copyReferralCode('<?= htmlspecialchars($associateCode) ?>')">
                        <div>
                            <span class="text-muted small d-block">Associate ID / Ref Code</span>
                            <span class="fw-bold font-monospace text-primary" id="refCodeText"><?= htmlspecialchars($associateCode) ?></span>
                        </div>
                        <span class="badge bg-light text-dark border"><i class="fas fa-copy me-1"></i>Copy</span>
                    </button>

                    <div class="text-start border-top pt-3 small text-muted">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-phone me-2"></i>Phone:</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($userPhone ?: 'Not set') ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="fas fa-calendar-alt me-2"></i>Joined:</span>
                            <span class="fw-semibold text-dark"><?= date('d M Y', strtotime($memberSince)) ?></span>
                        </div>
                        <?php if (!empty($associate['sponsor_code']) || !empty($user['sponsor_id'])): ?>
                        <div class="d-flex justify-content-between mb-0">
                            <span><i class="fas fa-user-tie me-2"></i>Sponsor:</span>
                            <span class="fw-semibold text-dark"><?= htmlspecialchars($associate['sponsor_code'] ?? ('ID: ' . $user['sponsor_id'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quick Performance Metrics -->
            <div class="assoc-profile-card p-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-chart-pie me-2 text-primary"></i>Business Overview</h6>
                
                <div class="vstack gap-2">
                    <div class="assoc-stat-pill">
                        <div class="assoc-stat-icon bg-primary bg-opacity-10 text-primary">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small">Wallet Balance</div>
                            <div class="fw-bold fs-6">₹<?= number_format($wallet_balance, 2) ?></div>
                        </div>
                        <a href="<?= $baseUrl ?>/associate/wallet" class="btn btn-sm btn-light border"><i class="fas fa-chevron-right"></i></a>
                    </div>

                    <div class="assoc-stat-pill">
                        <div class="assoc-stat-icon bg-success bg-opacity-10 text-success">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small">Total Commissions</div>
                            <div class="fw-bold fs-6">₹<?= number_format($total_commissions, 2) ?></div>
                        </div>
                        <a href="<?= $baseUrl ?>/associate/commissions" class="btn btn-sm btn-light border"><i class="fas fa-chevron-right"></i></a>
                    </div>

                    <div class="assoc-stat-pill">
                        <div class="assoc-stat-icon bg-warning bg-opacity-10 text-warning">
                            <i class="fas fa-hourglass-half"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small">Pending Payouts</div>
                            <div class="fw-bold fs-6">₹<?= number_format($pending_commissions, 2) ?></div>
                        </div>
                        <a href="<?= $baseUrl ?>/associate/commissions" class="btn btn-sm btn-light border"><i class="fas fa-chevron-right"></i></a>
                    </div>

                    <div class="assoc-stat-pill">
                        <div class="assoc-stat-icon bg-info bg-opacity-10 text-info">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small">Active Leads</div>
                            <div class="fw-bold fs-6"><?= (int)$lead_count ?> Leads</div>
                        </div>
                        <a href="<?= $baseUrl ?>/associate/leads" class="btn btn-sm btn-light border"><i class="fas fa-chevron-right"></i></a>
                    </div>

                    <div class="assoc-stat-pill">
                        <div class="assoc-stat-icon bg-secondary bg-opacity-10 text-secondary">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="text-muted small">Properties Listed</div>
                            <div class="fw-bold fs-6"><?= (int)$property_count ?> Properties</div>
                        </div>
                        <a href="<?= $baseUrl ?>/associate/properties" class="btn btn-sm btn-light border"><i class="fas fa-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content: Editable Information & Details -->
        <div class="col-lg-8 col-xl-8">
            <!-- Edit Personal Profile Form -->
            <div class="assoc-profile-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="fas fa-user-edit me-2 text-primary"></i>Personal Details</h5>
                        <p class="text-muted small mb-0">Update your contact and identification details</p>
                    </div>
                    <span class="badge bg-light text-muted border">Associate Partner</span>
                </div>

                <form action="<?= $baseUrl ?>/associate/profile" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="action" value="update_profile">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="profile_name" class="form-label small fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" id="profile_name" name="name" class="form-control" value="<?= htmlspecialchars($user['name'] ?? $userName) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="profile_email" class="form-label small fw-semibold">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="profile_email" name="email" class="form-control bg-light" value="<?= htmlspecialchars($user['email'] ?? $userEmail) ?>" readonly title="Email cannot be changed directly">
                            <div class="form-text small">Contact support to update your registered email</div>
                        </div>

                        <div class="col-md-6">
                            <label for="profile_phone" class="form-label small fw-semibold">Primary Phone Number <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-phone text-muted"></i></span>
                                <input type="tel" id="profile_phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? $userPhone) ?>" placeholder="10-digit mobile number" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="profile_alt_phone" class="form-label small fw-semibold">Alternate Phone / WhatsApp</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fab fa-whatsapp text-muted"></i></span>
                                <input type="tel" id="profile_alt_phone" name="alternate_phone" class="form-control" value="<?= htmlspecialchars($associate['alternate_phone'] ?? $user['alternate_phone'] ?? '') ?>" placeholder="WhatsApp number">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="profile_dob" class="form-label small fw-semibold">Date of Birth</label>
                            <input type="date" id="profile_dob" name="dob" class="form-control" value="<?= htmlspecialchars($associate['dob'] ?? $user['dob'] ?? '') ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="profile_gender" class="form-label small fw-semibold">Gender</label>
                            <select id="profile_gender" name="gender" class="form-select">
                                <?php $g = strtolower($associate['gender'] ?? $user['gender'] ?? ''); ?>
                                <option value="">Select Gender</option>
                                <option value="male" <?= $g === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $g === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= $g === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="profile_occupation" class="form-label small fw-semibold">Occupation</label>
                            <input type="text" id="profile_occupation" name="occupation" class="form-control" value="<?= htmlspecialchars($associate['occupation'] ?? '') ?>" placeholder="e.g. Real Estate Consultant">
                        </div>

                        <div class="col-12">
                            <label for="profile_address" class="form-label small fw-semibold">Postal Address</label>
                            <textarea id="profile_address" name="address" class="form-control" rows="2" placeholder="Street address, colony, landmark..."><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                        </div>

                        <div class="col-md-4">
                            <label for="profile_city" class="form-label small fw-semibold">City</label>
                            <input type="text" id="profile_city" name="city" class="form-control" value="<?= htmlspecialchars($user['city'] ?? '') ?>" placeholder="e.g. Gorakhpur">
                        </div>
                        <div class="col-md-4">
                            <label for="profile_state" class="form-label small fw-semibold">State</label>
                            <input type="text" id="profile_state" name="state" class="form-control" value="<?= htmlspecialchars($user['state'] ?? 'Uttar Pradesh') ?>" placeholder="e.g. Uttar Pradesh">
                        </div>
                        <div class="col-md-4">
                            <label for="profile_pincode" class="form-label small fw-semibold">Pincode</label>
                            <input type="text" id="profile_pincode" name="pincode" class="form-control" maxlength="6" value="<?= htmlspecialchars($user['pincode'] ?? '') ?>" placeholder="6-digit PIN">
                        </div>

                        <div class="col-12 text-end pt-3">
                            <button type="submit" class="btn btn-primary px-4 rounded-pill shadow-sm">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Business & Referral Links Card -->
            <div class="assoc-profile-card p-4 mb-4">
                <h5 class="fw-bold mb-3"><i class="fas fa-bullhorn me-2 text-primary"></i>My Referral & Sharing Tools</h5>
                <p class="text-muted small mb-3">Share your unique associate registration link to expand your network and earn multi-tier commissions.</p>

                <label for="directRefLink" class="visually-hidden">Direct Referral Link</label>
                <div class="input-group mb-3">
                    <span class="input-group-text bg-light"><i class="fas fa-link text-muted"></i></span>
                    <input type="text" class="form-control font-monospace small" id="directRefLink" value="<?= htmlspecialchars($referralLink) ?>" readonly>
                    <button class="btn btn-outline-primary" type="button" onclick="copyDirectLink()">
                        <i class="fas fa-copy me-1"></i>Copy Link
                    </button>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="https://api.whatsapp.com/send?text=<?= urlencode('Join APS Dream Home network with me! Register here: ' . $referralLink) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3">
                        <i class="fab fa-whatsapp me-1"></i>Share via WhatsApp
                    </a>
                    <a href="<?= $baseUrl ?>/associate/referral" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fas fa-share-alt me-1"></i>Advanced Referral Tools
                    </a>
                    <a href="<?= $baseUrl ?>/associate/network/tree" class="btn btn-sm btn-outline-info rounded-pill px-3">
                        <i class="fas fa-project-diagram me-1"></i>View Network Tree
                    </a>
                </div>
            </div>

            <!-- KYC & Bank Account Overview -->
            <div class="assoc-profile-card p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fas fa-university me-2 text-primary"></i>Banking & Tax Information</h5>
                    <a href="<?= $baseUrl ?>/associate/bank-details" class="btn btn-sm btn-outline-primary rounded-pill">
                        <i class="fas fa-external-link-alt me-1"></i>Manage Accounts
                    </a>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small mb-1">PAN Card Number</div>
                            <div class="fw-bold font-monospace"><?= htmlspecialchars($associate['pan_number'] ?? $user['pan_number'] ?? 'Not submitted') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small mb-1">Aadhaar Card Number</div>
                            <div class="fw-bold font-monospace"><?= !empty($associate['aadhaar_number'] ?? $user['aadhaar_number']) ? 'XXXX-XXXX-' . substr($associate['aadhaar_number'] ?? $user['aadhaar_number'], -4) : 'Not submitted' ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small mb-1">Primary Bank</div>
                            <div class="fw-bold"><?= htmlspecialchars($associate['bank_name'] ?? 'Not linked') ?></div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="text-muted small mb-1">Account Number & IFSC</div>
                            <div class="fw-bold font-monospace"><?= htmlspecialchars($associate['bank_account_no'] ?? '—') ?> / <?= htmlspecialchars($associate['bank_ifsc'] ?? '—') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
function copyReferralCode(code) {
    navigator.clipboard.writeText(code).then(() => {
        alert('Associate Code copied to clipboard: ' + code);
    }).catch(() => {
        const temp = document.createElement('input');
        temp.value = code;
        document.body.appendChild(temp);
        temp.select();
        document.execCommand('copy');
        document.body.removeChild(temp);
        alert('Associate Code copied to clipboard: ' + code);
    });
}

function copyDirectLink() {
    const input = document.getElementById('directRefLink');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value).then(() => {
        alert('Referral link copied to clipboard!');
    }).catch(() => {
        document.execCommand('copy');
        alert('Referral link copied to clipboard!');
    });
}
</script>
