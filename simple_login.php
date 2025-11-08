<?php
// Simple Login Page Example

require_once __DIR__ . '/includes/simple_template.php';

// Handle form submission
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $message = simple_alert('Please fill in all fields!', 'danger');
    } else {
        // Simple authentication check (replace with real logic)
        if ($email === 'admin@example.com' && $password === 'password') {
            $_SESSION['customer_logged_in'] = true;
            $_SESSION['customer_name'] = 'Admin User';
            header('Location: customer_dashboard.php');
            exit();
        } else {
            $message = simple_alert('Invalid credentials!', 'danger');
        }
    }
}

$content = "
<div class='container py-5'>
    <div class='row justify-content-center'>
        <div class='col-md-6'>
            " . simple_card('Login to APS Dream Home', '
                ' . $message . '

                <form method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            <i class="fas fa-envelope me-2"></i>Email Address
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="' . htmlspecialchars($_POST['email'] ?? '') . '" required>
                        <div class="form-text">Enter your registered email</div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">
                            <i class="fas fa-lock me-2"></i>Password
                        </label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <div class="form-text">Enter your password</div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </button>
                        <a href="customer_registration.php" class="btn btn-outline-secondary">
                            <i class="fas fa-user-plus me-2"></i>Create New Account
                        </a>
                    </div>
                </form>

                <div class="mt-4 p-3 bg-light rounded">
                    <h6 class="text-muted">Demo Credentials:</h6>
                    <small>
                        <strong>Email:</strong> admin@example.com<br>
                        <strong>Password:</strong> password
                    </small>
                </div>
            ') . "
        </div>
    </div>
</div>";

simple_page($content, 'Customer Login');
?>
