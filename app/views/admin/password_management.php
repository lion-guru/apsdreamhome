<?php
require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../../../includes/password_utils.php';

// Check if user is logged in and has admin privileges
adminAccessControl(['superadmin', 'admin']);

// Handle password reset actions
$resetMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset_single_user'])) {
        // Single user password reset
        $username = $_POST['username'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        if (!empty($username) && !empty($new_password)) {
            $result = resetUserPassword($username, $new_password, $_SESSION['admin_session']['role']);
            $resetMessage = $result['message'];
        }
    } elseif (isset($_POST['bulk_reset'])) {
        // Bulk password reset
        $usernames = $_POST['usernames'] ?? [];
        $bulk_password = $_POST['bulk_password'] ?? '';

        if (!empty($usernames) && !empty($bulk_password)) {
            $bulkResults = bulkResetPasswords($usernames, $bulk_password, $_SESSION['admin_session']['role']);
            $resetMessage = "Bulk Reset Results:<br>" . implode('<br>', array_map(function($username, $result) {
                return "$username: " . $result['message'];
            }, array_keys($bulkResults), $bulkResults));
        }
    }
}

// Fetch all admin users
$users = \App\Core\App::database()->fetchAll("SELECT id, auser, role, status FROM admin");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Password Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .password-strength-bar {
            height: 5px;
            background: #ddd;
            margin-top: 5px;
        }
        .password-strength-bar div {
            height: 100%;
            width: 0;
            transition: width 0.5s;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>Admin Password Management</h2>

    <?php if (!empty($resetMessage)): ?>
    <div class="alert <?php echo strpos($resetMessage, 'success') !== false ? 'alert-success' : 'alert-danger'; ?>">
        <?php echo h($resetMessage); ?>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Single User Password Reset</div>
                <div class="card-body">
                    <form method="POST">
                        <?php echo getCsrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label">Select User</label>
                            <select name="username" class="form-control" required>
                                <?php foreach ($users as $user): ?>
                                <option value="<?php echo h($user['auser']); ?>">
                                    <?php echo h($user['auser'] . " (" . $user['role'] . ")"); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password" class="form-control password-input" required
                                   minlength="12" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$">
                            <div class="password-strength-bar">
                                <div class="strength-very-weak"></div>
                                <div class="strength-weak"></div>
                                <div class="strength-medium"></div>
                                <div class="strength-strong"></div>
                                <div class="strength-very-strong"></div>
                            </div>
                            <small class="form-text text-muted">
                                Password must be at least 12 characters long, include uppercase, lowercase, number, and special character.
                            </small>
                        </div>
                        <input type="hidden" name="reset_single_user" value="1">
                        <button type="submit" class="btn btn-primary">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Bulk Password Reset</div>
                <div class="card-body">
                    <form method="POST">
                        <?php echo getCsrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label">Select Users</label>
                            <select name="usernames[]" class="form-control" multiple required>
                                <?php foreach ($users as $user): ?>
                                <option value="<?php echo h($user['auser']); ?>">
                                    <?php echo h($user['auser'] . " (" . $user['role'] . ")"); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Bulk Password</label>
                            <input type="password" name="bulk_password" class="form-control password-input" required
                                   minlength="12" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{12,}$">
                            <div class="password-strength-bar">
                                <div class="strength-very-weak"></div>
                                <div class="strength-weak"></div>
                                <div class="strength-medium"></div>
                                <div class="strength-strong"></div>
                                <div class="strength-very-strong"></div>
                            </div>
                            <small class="form-text text-muted">
                                Password must be at least 12 characters long, include uppercase, lowercase, number, and special character.
                            </small>
                        </div>
                        <input type="hidden" name="bulk_reset" value="1">
                        <button type="submit" class="btn btn-warning">Bulk Reset Passwords</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInputs = document.querySelectorAll('.password-input');

    passwordInputs.forEach(input => {
        input.addEventListener('input', function() {
            const password = this.value;
            const strengthBar = this.nextElementSibling;
            const strengths = strengthBar.querySelectorAll('div');

            const checks = [
                password.length >= 12,
                /[a-z]/.test(password),
                /[A-Z]/.test(password),
                /\d/.test(password),
                /[@$!%*?&]/.test(password)
            ];

            const strengthLevel = checks.filter(Boolean).length;

            strengths.forEach((strength, index) => {
                strength.style.backgroundColor = index < strengthLevel ?
                    ['red', 'orange', 'yellow', 'green', 'darkgreen'][index] : '#ddd';
                strength.style.width = strengthLevel > index ? '20%' : '0';
            });
        });
    });
});
</script>
</body>
</html>
