<?php
// Clean Customer Login using Universal Template

require_once __DIR__ . '/includes/universal_template.php';
require_once __DIR__ . '/../../app/helpers.php';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Please fill in all fields!</div>';
    } else {
        // Simple demo authentication (replace with real logic)
        if ($login === 'admin@example.com' && $password === 'password') {
            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_name'] = 'Admin User';
            header('Location: customer_dashboard.php');
            exit();
        } else {
            $message = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Invalid credentials!</div>';
        }
    }
}

$content = '
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="login-container mx-auto">
                <!-- Header -->
                <div class="login-header">
                    <div class="logo-section d-inline-block mb-3">
                        <h3 class="mb-0">
                            <i class="fas fa-home me-2"></i>
                            APS DREAM HOMES
                        </h3>
                    </div>
                    <h2 class="mb-2">Customer Login</h2>
                    <p class="mb-0">Access your property details and bookings</p>
                </div>

                <div class="card-body p-4">
                    ' . $message . '

                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-envelope me-1"></i>Email or Phone *
                            </label>
                            <input type="text" class="form-control" name="login"
                                   value="' . h($_POST['login'] ?? '') . '" required>
                            <div class="form-text">Enter your registered email or phone</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-lock me-1"></i>Password *
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" name="password" required>
                                <button class="btn btn-outline-secondary toggle-password" type="button">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i>Login
                            </button>
                        </div>

                        <div class="row mt-3">
                            <div class="col-6">
                                <a href="forgot-password" class="text-decoration-none">
                                    <i class="fas fa-key me-1"></i>Forgot Password?
                                </a>
                            </div>
                            <div class="col-6 text-end">
                                <a href="customer_registration" class="text-decoration-none">
                                    <i class="fas fa-user-plus me-1"></i>Register
                                </a>
                            </div>
                        </div>
                    </form>

                    <div class="demo-credentials mt-4 p-3 bg-light rounded">
                        <h6 class="text-muted">Demo Credentials:</h6>
                        <small>
                            <strong>Email:</strong> admin@example.com<br>
                            <strong>Password:</strong> password
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>';

login_page($content, 'Customer Login - APS Dream Home');
?>
