<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="font-weight-bold text-gray-900">मेरा प्रोफाइल (My Profile)</h2>
            <p class="text-muted">अपनी व्यक्तिगत जानकारी और अकाउंट सेटिंग्स यहाँ मैनेज करें।</p>
        </div>
    </div>

    <div class="row">
        <!-- Profile Sidebar -->
        <div class="col-lg-4 mb-4">
            <div class="card shadow">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-4">
                        <img src="<?= $customer['profile_image'] ?? '/assets/img/user-placeholder.jpg' ?>"
                             class="rounded-circle border shadow-sm" style="width: 150px; height: 150px; object-fit: cover;" alt="Profile Picture">
                        <button class="btn btn-sm btn-primary position-absolute" style="bottom: 5px; right: 5px; border-radius: 50%;">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <h4 class="font-weight-bold mb-1"><?= h($customer['name']) ?></h4>
                    <p class="text-muted small mb-3"><?= h($customer['email']) ?></p>
                    <div class="badge badge-success px-3 py-2 mb-4">एक्टिव ग्राहक</div>

                    <div class="row text-center border-top pt-4">
                        <div class="col-4">
                            <div class="text-xs font-weight-bold text-primary text-uppercase">बुकिंग</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_bookings'] ?? 0 ?></div>
                        </div>
                        <div class="col-4 border-left border-right">
                            <div class="text-xs font-weight-bold text-success text-uppercase">पसंद</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_favorites'] ?? 0 ?></div>
                        </div>
                        <div class="col-4">
                            <div class="text-xs font-weight-bold text-info text-uppercase">रिव्यू</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total_reviews'] ?? 0 ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Card -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">पासवर्ड बदलें</h6>
                </div>
                <div class="card-body">
                    <form action="/customer/change-password" method="POST">
                        <?php echo getCsrfField(); ?>
                        <div class="form-group">
                            <label class="small">पुराना पासवर्ड</label>
                            <input type="password" name="old_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="small">नया पासवर्ड</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="small">नया पासवर्ड कन्फर्म करें</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">पासवर्ड अपडेट करें</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Profile Details Form -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">व्यक्तिगत जानकारी (Personal Information)</h6>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="editProfileBtn">
                        <i class="fas fa-edit mr-1"></i> एडिट करें
                    </button>
                </div>
                <div class="card-body">
                    <form action="/customer/update-profile" method="POST" id="profileForm">
                        <?php echo getCsrfField(); ?>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold">पूरा नाम</label>
                                <input type="text" name="name" class="form-control" value="<?= h($customer['name']) ?>" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold">ईमेल एड्रेस</label>
                                <input type="email" class="form-control" value="<?= h($customer['email']) ?>" readonly disabled>
                                <small class="text-muted">ईमेल बदला नहीं जा सकता।</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold">मोबाइल नंबर</label>
                                <input type="text" name="phone" class="form-control" value="<?= h($customer['phone'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small font-weight-bold">जन्म तिथि (DOB)</label>
                                <input type="date" name="date_of_birth" class="form-control" value="<?= h($customer['date_of_birth'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold">पूरा पता</label>
                            <textarea name="address" class="form-control" rows="3" readonly><?= h($customer['address'] ?? '') ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold">शहर</label>
                                <input type="text" name="city" class="form-control" value="<?= h($customer['city'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold">राज्य</label>
                                <input type="text" name="state" class="form-control" value="<?= h($customer['state'] ?? '') ?>" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold">पिनकोड</label>
                                <input type="text" name="pincode" class="form-control" value="<?= h($customer['pincode'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold">व्यवसाय (Occupation)</label>
                                <select name="occupation" class="form-control" disabled>
                                    <option value="">चुनें...</option>
                                    <option value="Salaried" <?= ($customer['occupation'] ?? '') == 'Salaried' ? 'selected' : '' ?>>सैलरीड (Salaried)</option>
                                    <option value="Self-Employed" <?= ($customer['occupation'] ?? '') == 'Self-Employed' ? 'selected' : '' ?>>स्व-रोजगार (Self-Employed)</option>
                                    <option value="Business" <?= ($customer['occupation'] ?? '') == 'Business' ? 'selected' : '' ?>>बिज़नेस (Business)</option>
                                    <option value="Student" <?= ($customer['occupation'] ?? '') == 'Student' ? 'selected' : '' ?>>छात्र (Student)</option>
                                    <option value="Other" <?= ($customer['occupation'] ?? '') == 'Other' ? 'selected' : '' ?>>अन्य (Other)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold">वैवाहिक स्थिति</label>
                                <select name="marital_status" class="form-control" disabled>
                                    <option value="">चुनें...</option>
                                    <option value="Single" <?= ($customer['marital_status'] ?? '') == 'Single' ? 'selected' : '' ?>>अविवाहित (Single)</option>
                                    <option value="Married" <?= ($customer['marital_status'] ?? '') == 'Married' ? 'selected' : '' ?>>विवाहित (Married)</option>
                                    <option value="Other" <?= ($customer['marital_status'] ?? '') == 'Other' ? 'selected' : '' ?>>अन्य</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="small font-weight-bold">शादी की सालगिरह</label>
                                <input type="date" name="anniversary_date" class="form-control" value="<?= h($customer['anniversary_date'] ?? '') ?>" readonly>
                            </div>
                        </div>

                        <div id="formActions" class="text-right mt-4" style="display: none;">
                            <button type="button" class="btn btn-secondary mr-2" id="cancelEditBtn">कैंसिल</button>
                            <button type="submit" class="btn btn-success px-4">बदलाव सुरक्षित करें</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- KYC Status -->
            <div class="card shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">KYC जानकारी</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="font-weight-bold mb-0">आधार कार्ड</h6>
                                    <span class="badge badge-<?= $customer['aadhaar_verified'] ? 'success' : 'warning' ?>">
                                        <?= $customer['aadhaar_verified'] ? 'वेरिफाइड' : 'लंबित' ?>
                                    </span>
                                </div>
                                <p class="text-xs text-muted mb-0">नंबर: <?= $customer['aadhaar_number'] ? 'XXXX-XXXX-' . substr($customer['aadhaar_number'], -4) : 'उपलब्ध नहीं' ?></p>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="p-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="font-weight-bold mb-0">PAN कार्ड</h6>
                                    <span class="badge badge-<?= $customer['pan_verified'] ? 'success' : 'warning' ?>">
                                        <?= $customer['pan_verified'] ? 'वेरिफाइड' : 'लंबित' ?>
                                    </span>
                                </div>
                                <p class="text-xs text-muted mb-0">नंबर: <?= $customer['pan_number'] ? 'XXXXX' . substr($customer['pan_number'], -4) : 'उपलब्ध नहीं' ?></p>
                            </div>
                        </div>
                    </div>
                    <?php if (!$customer['aadhaar_verified'] || !$customer['pan_verified']): ?>
                        <div class="mt-2 text-center">
                            <a href="/customer/kyc-upload" class="btn btn-sm btn-outline-info">दस्तावेज अपलोड करें</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$extra_js = "
<script>
    $(document).ready(function() {
        $('#editProfileBtn').click(function() {
            $('#profileForm input, #profileForm textarea, #profileForm select').not('[disabled]').removeAttr('readonly').removeAttr('disabled');
            $('#formActions').show();
            $(this).hide();
        });

        $('#cancelEditBtn').click(function() {
            location.reload();
        });
    });
</script>
";
?>
