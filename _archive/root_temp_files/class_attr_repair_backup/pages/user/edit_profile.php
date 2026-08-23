<?php

/**
 * Modernized Edit Profile
 * Allow users to update their personal information and password at APS Dream Homes
 */

$db = \App\Core\Database\Database::getInstance();
$msg = '';
$error = '';

// Handle Basic Profile Update
if (isset($_POST['update_basic'])) {
    // Get user ID from session properly
    $uid = $_SESSION['user_id'] ?? $_SESSION['uid'] ?? $_SESSION['auser'] ?? 0;
    $uid = is_numeric($uid) ? (int)$uid : 0;

    if ($uid <= 0) {
        $msg = __('user_edit_invalid_session', null, 'Invalid user session!');
        $msg_type = "error";
    } else {
        $name = trim(\App\Core\Security::sanitize($_POST['name'] ?? ''));
        $email = trim(\App\Core\Security::sanitize($_POST['email'] ?? ''));
        $phone = trim(\App\Core\Security::sanitize($_POST['phone'] ?? ''));

        if (!empty($name) && !empty($email)) {
            try {
                // Using 'users' table with correct column names: name, email, phone
                $success = $db->query("UPDATE users SET name = :name, email = :email, phone = :phone WHERE id = :uid", [
                    'name' => $name,
                    'email' => $email,
                    'phone' => $phone,
                    'uid' => (int)$uid
                ]);
                if ($success) {
                    $msg = __('user_edit_profile_updated', null, 'Profile updated successfully!');
                    $_SESSION['name'] = $name;
                    $_SESSION['uemail'] = $email;
                } else {
                    $error = __('user_edit_profile_failed', null, 'Failed to update profile.');
                }
            } catch (\Exception $e) {
                $error = __('user_edit_profile_error', null, 'An error occurred while updating profile: ') . $e->getMessage();
            }
        } else {
            $error = __('user_edit_name_email_required', null, 'Name and Email are required.');
        }
    }
}

// Handle Password Update
if (isset($_POST['update_password'])) {
    $old_pass = \App\Core\Security::sanitize($_POST['old_password'] ?? '');
    $new_pass = \App\Core\Security::sanitize($_POST['new_password'] ?? '');
    $conf_pass = \App\Core\Security::sanitize($_POST['confirm_password'] ?? '');

    if (!empty($old_pass) && !empty($new_pass) && $new_pass === $conf_pass) {
        try {
            // Check 'users' table password column
            $user = $db->fetch("SELECT password FROM users WHERE id = :uid", ['uid' => $uid]);

            if ($user && password_verify($old_pass, $user['password'])) {
                $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                $success = $db->query("UPDATE users SET password = :password WHERE id = :uid", [
                    'password' => $hashed_pass,
                    'uid' => (int)$uid
                ]);
                if ($success) {
                    $msg = __('user_edit_password_changed', null, 'Password changed successfully!');
                } else {
                    $error = __('user_edit_password_failed', null, 'Failed to update password.');
                }
            } else {
                $error = __('user_edit_old_password_incorrect', null, 'Incorrect old password.');
            }
        } catch (\Exception $e) {
            $error = __('user_edit_password_error', null, 'An error occurred while updating password: ') . $e->getMessage();
        }
    } else {
        $error = __('user_edit_password_fields_required', null, 'Please ensure all password fields are filled and match.');
    }
}

// Fetch current user data from 'users' table
$user_data = $db->fetch("SELECT * FROM users WHERE id = :uid", ['uid' => $uid]);

if (!$user_data) {
    header("Location: " . BASE_URL . "/login?error=user_not_found");
    exit;
}

// Map modern columns to legacy variables for view compatibility
$user_data['uid'] = $user_data['id'];
$user_data['uname'] = $user_data['name'];
$user_data['utype'] = $user_data['role'];
$user_data['uemail'] = $user_data['email'];
$user_data['uphone'] = $user_data['phone'];

?>

<div class="container py-5 mt-5">
    <div class="row mb-5 animate-fade-up">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold text-primary"><?= __('user_edit_title', null, 'Edit Profile') ?></h1>
            <p class="text-muted"><?= __('user_edit_subtitle', null, 'Keep your account information up to date.') ?></p>
        </div>
        <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end mt-4 mt-md-0">
            <a href="profile.php" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-arrow-left me-2"></i><?= __('user_edit_back', null, 'Back to Profile') ?>
            </a>
        </div>
    </div>

    <?php if ($msg): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $msg ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Basic Info Form -->
        <div class="col-lg-7 animate-fade-up" class="style-44791">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white p-4">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-circle me-2"></i><?= __('user_edit_basic_info', null, 'Basic Information') ?></h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label small text-muted"><?= __('user_edit_full_name', null, 'Full Name') ?></label>
                            <input type="text" name="name" class="form-control border-0 bg-light rounded-3 p-3" value="<?= h($user_data['uname']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted"><?= __('user_edit_email', null, 'Email Address') ?></label>
                            <input type="email" name="email" class="form-control border-0 bg-light rounded-3 p-3" value="<?= h($user_data['uemail']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small text-muted"><?= __('user_edit_phone', null, 'Phone Number') ?></label>
                            <input type="text" name="phone" class="form-control border-0 bg-light rounded-3 p-3" value="<?= h($user_data['uphone'] ?? '') ?>">
                        </div>
                        <button type="submit" name="update_basic" class="btn btn-primary rounded-pill px-5 shadow-sm">
                            <?= __('user_edit_save_changes', null, 'Save Changes') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Section -->
        <div class="col-lg-5 animate-fade-up" class="style-75842">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="password-section">
                <div class="card-header bg-dark text-white p-4">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-lock me-2"></i><?= __('user_edit_change_password', null, 'Change Password') ?></h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label small text-muted"><?= __('user_edit_current_password', null, 'Current Password') ?></label>
                            <input type="password" name="old_password" class="form-control border-0 bg-light rounded-3 p-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted"><?= __('user_edit_new_password', null, 'New Password') ?></label>
                            <input type="password" name="new_password" class="form-control border-0 bg-light rounded-3 p-3" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small text-muted"><?= __('user_edit_confirm_password', null, 'Confirm New Password') ?></label>
                            <input type="password" name="confirm_password" class="form-control border-0 bg-light rounded-3 p-3" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-dark rounded-pill px-5 shadow-sm w-100">
                            <?= __('user_edit_update_password', null, 'Update Password') ?>
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-light">
                <div class="card-body p-4 text-center">
                    <i class="fas fa-shield-alt text-primary fa-3x mb-3"></i>
                    <h6 class="fw-bold"><?= __('user_edit_security_tip', null, 'Security Tip') ?></h6>
                    <p class="small text-muted mb-0"><?= __('user_edit_security_tip_desc', null, 'Use a strong password with at least 8 characters, including numbers and symbols, to keep your account secure.') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .bg-light {
        background-color: #f8f9fa !important;
    }

    .form-control:focus {
        box-shadow: none;
        background-color: #f0f7ff !important;
    }

    .animate-fade-up {
        animation: fadeUp 0.6s ease forwards;
        opacity: 0;
    }

    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

