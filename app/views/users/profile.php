<?php include '../app/views/includes/header.php'; ?>

<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">My Profile</h1>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                    <?php endif; ?>

                    <form action="/profile/update" method="POST" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">Username *</label>
                                <input type="text" class="form-control" id="username" name="username" required
                                       value="<?php echo htmlspecialchars($user['username']); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                <div class="form-text">Email cannot be changed</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mobile" class="form-label">Mobile Number</label>
                                <input type="tel" class="form-control" id="mobile" name="mobile"
                                       value="<?php echo htmlspecialchars($user['mobile']); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="address" name="address"
                                   value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>"
                                   placeholder="Your full address">
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control" id="city" name="city"
                                       value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control" id="state" name="state"
                                       value="<?php echo htmlspecialchars($user['state'] ?? ''); ?>">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="pincode" class="form-label">Pincode</label>
                                <input type="text" class="form-control" id="pincode" name="pincode"
                                       value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="profile_picture" class="form-label">Profile Picture</label>
                            <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*">
                            <div class="form-text">Upload a new profile picture (JPG, PNG, GIF)</div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Account Status</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Account Type:</strong>
                        <span class="badge bg-primary"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                    </div>

                    <div class="mb-3">
                        <strong>Status:</strong>
                        <span class="badge bg-<?php echo ($user['status'] == 'active') ? 'success' : 'warning'; ?>">
                            <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                        </span>
                    </div>

                    <?php if ($user['email_verified_at']): ?>
                        <div class="mb-3">
                            <strong>Email Verified:</strong>
                            <span class="badge bg-success">
                                <i class="fas fa-check"></i> Verified
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="mb-3">
                            <strong>Email Verified:</strong>
                            <span class="badge bg-warning">
                                <i class="fas fa-exclamation-triangle"></i> Not Verified
                            </span>
                        </div>
                        <div class="mb-3">
                            <a href="/auth/resend-verification" class="btn btn-sm btn-outline-primary">Resend Verification Email</a>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <strong>Member Since:</strong>
                        <br><?php echo date('M d, Y', strtotime($user['created_at'])); ?>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <a href="/properties/create" class="btn btn-outline-primary w-100 mb-2">Add New Property</a>
                    <a href="/saved-searches" class="btn btn-outline-secondary w-100 mb-2">Saved Searches</a>
                    <a href="/change-password" class="btn btn-outline-warning w-100">Change Password</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../app/views/includes/footer.php'; ?>
