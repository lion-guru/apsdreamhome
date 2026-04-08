<?php
/**
 * Change Password Form
 */
$user = $user ?? [];
$action = $action ?? '/users/update-password/' . ($user['id'] ?? 0);
$page_title = $page_title ?? 'Change Password - APS Dream Home';
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-warning text-dark">
                        <h4 class="mb-0"><i class="fas fa-key me-2"></i>Change Password</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php if (!empty($user)): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-user me-2"></i>Changing password for: <strong><?php echo htmlspecialchars($user['name'] ?? ''); ?></strong>
                            </div>
                            
                            <form action="<?php echo $base . $action; ?>" method="POST" onsubmit="return validateForm()">
                                <div class="mb-3">
                                    <label class="form-label">New Password *</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Confirm Password *</label>
                                    <div class="input-group">
                                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('confirm_password')">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="alert alert-danger d-none" id="errorAlert">
                                    Passwords do not match!
                                </div>

                                <div class="d-flex justify-content-between">
                                    <a href="<?php echo $base; ?>/users/profile/<?php echo $user['id']; ?>" class="btn btn-outline-secondary">Cancel</a>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save me-2"></i>Update Password
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-danger">User not found</div>
                            <a href="<?php echo $base; ?>/users" class="btn btn-outline-secondary">Back to Users</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            field.type = field.type === 'password' ? 'text' : 'password';
        }

        function validateForm() {
            const password = document.getElementById('password').value;
            const confirm = document.getElementById('confirm_password').value;
            const errorAlert = document.getElementById('errorAlert');

            if (password !== confirm) {
                errorAlert.classList.remove('d-none');
                return false;
            }
            errorAlert.classList.add('d-none');
            return true;
        }
    </script>
</body>
</html>
