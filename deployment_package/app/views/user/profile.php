<?php
/**
 * User Profile View
 * Migrated from legacy profile.php
 */
?>
<main class="user-main-content">
    <div class="container-fluid">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <h2 class="h3 mb-0 text-gray-800"><?= $title ?? 'My Profile' ?></h2>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="/dashboard" class="text-decoration-none">Dashboard</a></li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Profile Information -->
            <div class="col-lg-7">
                <div class="card shadow-sm mb-4 border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary fw-bold">Personal Information</h5>
                    </div>
                    <div class="card-body">
                        <?php if($user): ?>
                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <img src="<?= !empty($user->uimage) ? '/images/user/'.$user->uimage : '/images/user/default.png'; ?>"
                                     class="img-fluid rounded-circle shadow-sm" style="width: 150px; height: 150px; object-fit: cover;" alt="Profile Picture">
                                <div class="mt-3">
                                    <h5 class="mb-1 text-capitalize"><?= h($user->uname); ?></h5>
                                    <span class="badge bg-primary rounded-pill"><?= h($user->job_role ?? 'Customer'); ?></span>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">Sponsor ID</div>
                                    <div class="col-sm-8"><?= h($user->sponsor_id ?? 'N/A'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">Sponsored By</div>
                                    <div class="col-sm-8 text-capitalize"><?= h($user->sponsored_by ?? 'N/A'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">Email Address</div>
                                    <div class="col-sm-8"><?= h($user->uemail); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">Contact Number</div>
                                    <div class="col-sm-8"><?= h($user->uphone); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">Date of Birth</div>
                                    <div class="col-sm-8"><?= h($user->date_of_birth ?? 'N/A'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">Address</div>
                                    <div class="col-sm-8"><?= h($user->address ?? 'N/A'); ?></div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">Bank Name</div>
                                    <div class="col-sm-8"><?= h($user->bank_name ?? 'N/A'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">Account No.</div>
                                    <div class="col-sm-8"><?= h($user->account_number ?? 'N/A'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">IFSC Code</div>
                                    <div class="col-sm-8"><?= h($user->ifsc_code ?? 'N/A'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">PAN Card</div>
                                    <div class="col-sm-8"><?= h($user->pan ?? 'N/A'); ?></div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">Aadhar Card</div>
                                    <div class="col-sm-8"><?= h($user->adhaar ?? 'N/A'); ?></div>
                                </div>
                                <hr>
                                <div class="row mb-3">
                                    <div class="col-sm-4 fw-bold text-muted">Join Date</div>
                                    <div class="col-sm-8"><?= h($user->join_date ?? 'N/A'); ?></div>
                                </div>

                                <div class="mt-4">
                                    <button type="button" class="btn btn-primary shadow-sm px-4" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                                        <i class="fas fa-edit me-2"></i>Edit Profile
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php else: ?>
                            <div class="alert alert-warning">User details not found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Feedback Form -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 text-primary fw-bold">Send Feedback</h5>
                    </div>
                    <div class="card-body">
                        <form action="/profile/feedback" method="post" class="needs-validation" novalidate>
                            <?= csrf_field(); ?>
                            <div class="mb-3">
                                <label class="form-label text-muted">Full Name</label>
                                <input type="text" name="name" class="form-control" value="<?= h($user->uname ?? ''); ?>" placeholder="Enter Full Name" required>
                                <div class="invalid-feedback">Please enter your name.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Contact Number</label>
                                <input type="text" name="phone" class="form-control" value="<?= h($user->uphone ?? ''); ?>" placeholder="Enter Phone" maxlength="10" required>
                                <div class="invalid-feedback">Please enter your phone number.</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Your Feedback</label>
                                <textarea class="form-control" name="content" rows="6" placeholder="Share your experience or suggestions..." required></textarea>
                                <div class="invalid-feedback">Please share your feedback.</div>
                            </div>
                            <button type="submit" name="insert" class="btn btn-primary w-100 shadow-sm py-2">
                                <i class="fas fa-paper-plane me-2"></i>Submit Feedback
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-labelledby="editProfileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/profile/update" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    <?= csrf_field(); ?>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Full Name *</label>
                                <input type="text" class="form-control" id="username" name="username" required
                                       value="<?= h($user->uname); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="mobile" class="form-label">Mobile Number *</label>
                                <input type="tel" class="form-control" id="mobile" name="mobile" required
                                       value="<?= h($user->uphone); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?= h($user->address ?? ''); ?></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city" value="<?= h($user->city ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state" value="<?= h($user->state ?? ''); ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="pincode" name="pincode" value="<?= h($user->pincode ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="country_id" class="form-label">Country ID</label>
                                <input type="text" class="form-control" id="country_id" name="country_id" value="<?= h($user->country_id ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                            <div class="form-text">Upload a new profile picture (JPG, PNG, GIF)</div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
// Example starter JavaScript for disabling form submissions if there are invalid fields
(function () {
  'use strict'

  // Fetch all the forms we want to apply custom Bootstrap validation styles to
  var forms = document.querySelectorAll('.needs-validation')

  // Loop over them and prevent submission
  Array.prototype.slice.call(forms)
    .forEach(function (form) {
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault()
          event.stopPropagation()
        }

        form.classList.add('was-validated')
      }, false)
    })
})()
</script>
