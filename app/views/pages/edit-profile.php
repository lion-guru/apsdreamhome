<?php
/**
 * Modernized Edit Profile
 * Allow users to update their personal information and password at APS Dream Homes
 */

require_once __DIR__ . '/init.php';

// Check if user is logged in
if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

$db = \App\Core\App::database();
$uid = $_SESSION['uid'];
$msg = '';
$error = '';

// Handle Basic Profile Update
if (isset($_POST['update_basic'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');

    if (!empty($name) && !empty($email)) {
        try {
            $success = $db->query("UPDATE user SET uname = ?, uemail = ?, uphone = ? WHERE uid = ?", [$name, $email, $phone, $uid]);
            if ($success) {
                $msg = "Profile updated successfully!";
                $_SESSION['name'] = $name;
                $_SESSION['uemail'] = $email;
            } else {
                $error = "Failed to update profile.";
            }
        } catch (Exception $e) {
            $error = "An error occurred while updating profile.";
        }
    } else {
        $error = "Name and Email are required.";
    }
}

// Handle Password Update
if (isset($_POST['update_password'])) {
    $old_pass = $_POST['old_password'] ?? '';
    $new_pass = $_POST['new_password'] ?? '';
    $conf_pass = $_POST['confirm_password'] ?? '';

    if (!empty($old_pass) && !empty($new_pass) && $new_pass === $conf_pass) {
        try {
            $user = $db->fetch("SELECT upassword FROM user WHERE uid = ?", [$uid]);

            if ($user && password_verify($old_pass, $user['upassword'])) {
                $hashed_pass = password_hash($new_pass, PASSWORD_DEFAULT);
                $success = $db->query("UPDATE user SET upassword = ? WHERE uid = ?", [$hashed_pass, $uid]);
                if ($success) {
                    $msg = "Password changed successfully!";
                } else {
                    $error = "Failed to update password.";
                }
            } else {
                $error = "Incorrect old password.";
            }
        } catch (Exception $e) {
            $error = "An error occurred while updating password.";
        }
    } else {
        $error = "Please ensure all password fields are filled and match.";
    }
}

// Fetch current user data
$user_data = $db->fetch("SELECT * FROM user WHERE uid = ?", [$uid]);

if (!$user_data) {
    header("Location: login.php?error=user_not_found");
    exit;
}

// Page variables
$page_title = 'Edit Profile | APS Dream Homes';
$layout = 'modern';

ob_start();
?>

<div class="container py-5 mt-5">
    <div class="row mb-5 animate-fade-up">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold text-primary">Edit Profile</h1>
            <p class="text-muted">Keep your account information up to date.</p>
        </div>
        <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end mt-4 mt-md-0">
            <a href="profile.php" class="btn btn-outline-primary rounded-pill px-4 shadow-sm">
                <i class="fas fa-arrow-left me-2"></i>Back to Profile
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
        <div class="col-lg-7 animate-fade-up" style="animation-delay: 0.1s;">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white p-4">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user-circle me-2"></i>Basic Information</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Full Name</label>
                            <input type="text" name="name" class="form-control border-0 bg-light rounded-3 p-3" value="<?= h($user_data['uname']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">Email Address</label>
                            <input type="email" name="email" class="form-control border-0 bg-light rounded-3 p-3" value="<?= h($user_data['uemail']) ?>" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small text-muted">Phone Number</label>
                            <input type="text" name="phone" class="form-control border-0 bg-light rounded-3 p-3" value="<?= h($user_data['uphone'] ?? '') ?>">
                        </div>
                        <button type="submit" name="update_basic" class="btn btn-primary rounded-pill px-5 shadow-sm">
                            Save Changes
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security Section -->
        <div class="col-lg-5 animate-fade-up" style="animation-delay: 0.2s;">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4" id="password-section">
                <div class="card-header bg-dark text-white p-4">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-lock me-2"></i>Change Password</h5>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small text-muted">Current Password</label>
                            <input type="password" name="old_password" class="form-control border-0 bg-light rounded-3 p-3" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted">New Password</label>
                            <input type="password" name="new_password" class="form-control border-0 bg-light rounded-3 p-3" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small text-muted">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control border-0 bg-light rounded-3 p-3" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-dark rounded-pill px-5 shadow-sm w-100">
                            Update Password
                        </button>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 bg-light">
                <div class="card-body p-4 text-center">
                    <i class="fas fa-shield-alt text-primary fa-3x mb-3"></i>
                    <h6 class="fw-bold">Security Tip</h6>
                    <p class="small text-muted mb-0">Use a strong password with at least 8 characters, including numbers and symbols, to keep your account secure.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-light { background-color: #f8f9fa !important; }
.form-control:focus {
    box-shadow: none;
    background-color: #f0f7ff !important;
}
.animate-fade-up { animation: fadeUp 0.6s ease forwards; opacity: 0; }
@keyframes fadeUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/modern.php';
?>

