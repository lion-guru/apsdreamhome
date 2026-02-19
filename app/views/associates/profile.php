<?php require_once 'app/views/layouts/associate_header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Breadcrumb -->
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/associate/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Profile Management</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Profile Information -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user mr-2"></i>Profile Information
                    </h6>
                    <button type="button" class="btn btn-primary btn-sm" onclick="editProfile()">
                        <i class="fas fa-edit mr-1"></i>Edit Profile
                    </button>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="profile-info-item mb-3">
                                <label class="font-weight-bold text-muted">Full Name:</label>
                                <div class="profile-value">
                                    <?= htmlspecialchars($associate['name'] ?? '') ?>
                                </div>
                            </div>

                            <div class="profile-info-item mb-3">
                                <label class="font-weight-bold text-muted">Associate Code:</label>
                                <div class="profile-value">
                                    <span class="badge badge-primary">
                                        <?= htmlspecialchars($associate['associate_code'] ?? '') ?>
                                    </span>
                                </div>
                            </div>

                            <div class="profile-info-item mb-3">
                                <label class="font-weight-bold text-muted">Level:</label>
                                <div class="profile-value">
                                    <span class="badge badge-info">Level <?= $associate['level'] ?? 1 ?></span>
                                </div>
                            </div>

                            <div class="profile-info-item mb-3">
                                <label class="font-weight-bold text-muted">Joining Date:</label>
                                <div class="profile-value">
                                    <?= date('d M Y', strtotime($associate['joining_date'] ?? '')) ?>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="profile-info-item mb-3">
                                <label class="font-weight-bold text-muted">Email:</label>
                                <div class="profile-value">
                                    <?= htmlspecialchars($associate['email'] ?? '') ?>
                                </div>
                            </div>

                            <div class="profile-info-item mb-3">
                                <label class="font-weight-bold text-muted">Phone:</label>
                                <div class="profile-value">
                                    <?= htmlspecialchars($associate['phone'] ?? '') ?>
                                </div>
                            </div>

                            <div class="profile-info-item mb-3">
                                <label class="font-weight-bold text-muted">City:</label>
                                <div class="profile-value">
                                    <?= htmlspecialchars($associate['city'] ?? '') ?>
                                </div>
                            </div>

                            <div class="profile-info-item mb-3">
                                <label class="font-weight-bold text-muted">State:</label>
                                <div class="profile-value">
                                    <?= htmlspecialchars($associate['state'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="profile-info-item">
                                <label class="font-weight-bold text-muted">Address:</label>
                                <div class="profile-value">
                                    <?= htmlspecialchars($associate['address'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Stats -->
        <div class="col-lg-4">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar mr-2"></i>प्रोफाइल स्टैट्स
                    </h6>
                </div>
                <div class="card-body">
                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>डाउनलाइन मेंबर्स:</span>
                            <span class="font-weight-bold text-primary">
                                <?= $associate['downline_count'] ?? 0 ?>
                            </span>
                        </div>
                    </div>

                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>टोटल अर्निंग्स:</span>
                            <span class="font-weight-bold text-success">
                                ₹<?= number_format($associate['total_earnings'] ?? 0) ?>
                            </span>
                        </div>
                    </div>

                    <div class="stats-item mb-3">
                        <div class="d-flex justify-content-between">
                            <span>टोटल सेल्स:</span>
                            <span class="font-weight-bold text-info">
                                <?= $associate['total_sales'] ?? 0 ?>
                            </span>
                        </div>
                    </div>

                    <div class="stats-item">
                        <div class="d-flex justify-content-between">
                            <span>KYC स्टेटस:</span>
                            <?php
                            $kycStatus = $associate['kyc_status'] ?? 'pending';
                            $kycClass = 'warning';
                            if ($kycStatus === 'verified') $kycClass = 'success';
                            if ($kycStatus === 'rejected') $kycClass = 'danger';
                            ?>
                            <span class="badge badge-<?= $kycClass ?>">
                                <?= ucfirst($kycStatus) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-bolt mr-2"></i>क्विक एक्शन
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="/associate/kyc" class="btn btn-outline-warning">
                            <i class="fas fa-id-card mr-2"></i>KYC अपडेट करें
                        </a>
                        <a href="/associate/change-password" class="btn btn-outline-info">
                            <i class="fas fa-key mr-2"></i>पासवर्ड चेंज करें
                        </a>
                        <a href="/associate/bank-details" class="btn btn-outline-success">
                            <i class="fas fa-university mr-2"></i>बैंक डिटेल्स अपडेट करें
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Form (Initially Hidden) -->
    <div class="row" id="editProfileForm" style="display: none;">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-edit mr-2"></i>प्रोफाइल एडिट करें
                    </h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="/associate/update-profile">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="phone" class="form-label">फोन नंबर</label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                           value="<?= htmlspecialchars($associate['phone'] ?? '') ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="city" class="form-label">सिटी</label>
                                    <input type="text" class="form-control" id="city" name="city"
                                           value="<?= htmlspecialchars($associate['city'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="state" class="form-label">स्टेट</label>
                                    <select class="form-control" id="state" name="state">
                                        <option value="">चुनें</option>
                                        <option value="Uttar Pradesh" <?= ($associate['state'] ?? '') === 'Uttar Pradesh' ? 'selected' : '' ?>>उत्तर प्रदेश</option>
                                        <option value="Bihar" <?= ($associate['state'] ?? '') === 'Bihar' ? 'selected' : '' ?>>बिहार</option>
                                        <option value="Delhi" <?= ($associate['state'] ?? '') === 'Delhi' ? 'selected' : '' ?>>दिल्ली</option>
                                        <option value="Haryana" <?= ($associate['state'] ?? '') === 'Haryana' ? 'selected' : '' ?>>हरियाणा</option>
                                        <!-- Add more states as needed -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="pincode" class="form-label">पिनकोड</label>
                                    <input type="text" class="form-control" id="pincode" name="pincode"
                                           value="<?= htmlspecialchars($associate['pincode'] ?? '') ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label for="address" class="form-label">कंप्लीट एड्रेस</label>
                            <textarea class="form-control" id="address" name="address" rows="3"
                                      placeholder="अपना कंप्लीट एड्रेस लिखें"><?= htmlspecialchars($associate['address'] ?? '') ?></textarea>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save mr-2"></i>चेंजेस सेव करें
                            </button>
                            <button type="button" class="btn btn-secondary ml-2" onclick="cancelEdit()">
                                <i class="fas fa-times mr-2"></i>कैंसल करें
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editProfile() {
    document.getElementById('editProfileForm').style.display = 'block';
    document.querySelector('.card-body').scrollIntoView({ behavior: 'smooth' });
}

function cancelEdit() {
    document.getElementById('editProfileForm').style.display = 'none';
}
</script>

<style>
.card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 0 20px rgba(0,0,0,0.08);
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 2px solid rgba(0,0,0,0.1);
}

.profile-info-item {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.profile-info-item:last-child {
    border-bottom: none;
}

.profile-value {
    font-size: 1.1em;
    font-weight: 500;
    margin-top: 5px;
}

.stats-item {
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.stats-item:last-child {
    border-bottom: none;
}

.badge {
    font-size: 0.9em;
    padding: 0.5em 1em;
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e3e6f0;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-outline-warning:hover,
.btn-outline-info:hover,
.btn-outline-success:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.d-grid .btn {
    margin-bottom: 0.5rem;
}

@media (max-width: 768px) {
    .d-grid {
        display: block !important;
    }

    .d-grid .btn {
        display: block;
        width: 100%;
    }
}
</style>

<?php require_once 'app/views/layouts/associate_footer.php'; ?>
