<?php
/**
 * Customer Login System - APS Dream Homes
 * Handles customer authentication and login
 */

require_once 'includes/config.php';
require_once 'includes/session_manager.php';

// Redirect to dashboard if already logged in
if (isCustomerLoggedIn()) {
    header('Location: customer_dashboard.php');
    exit();
}

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();
$message = '';
$message_type = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Get and sanitize input
        $login = trim(filter_input(INPUT_POST, 'login', FILTER_SANITIZE_FULL_SPECIAL_CHARS));
        $password = trim($_POST['password']); // Don't sanitize password
        
        // Basic validation
        if (empty($login) || empty($password)) {
            $message = "Please enter both email/phone and password!";
            $message_type = "danger";
        } else {
            // Check login credentials in users table
            $stmt = $conn->prepare("SELECT u.*, c.id as customer_id 
                                  FROM users u 
                                  LEFT JOIN customers c ON u.id = c.user_id 
                                  WHERE (u.email = ? OR u.phone = ?) AND u.type = 'customer' AND u.status = 'active'");
            
            if (!$stmt) {
                throw new Exception("Database error: " . $conn->error);
            }
            
            $stmt->bind_param("ss", $login, $login);
            
            if (!$stmt->execute()) {
                throw new Exception("Query execution failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            
                if ($result->num_rows === 0) {
                    // Log failed login attempt
                    error_log("Login failed - No user found with login: $login");
                    $message = "Invalid email/phone or password!";
                    $message_type = "danger";
                } else {
                    $user = $result->fetch_assoc();
                    
                    // Verify password
                    $stored_hash = $user['password'];
                    $password_verified = false;
                    
                    // Check password using password_verify (works with different hash types)
                    if (password_verify($password, $stored_hash)) {
                        $password_verified = true;
                    } 
                    // Fallback for testing with plain text password (remove in production)
                    elseif ($password === 'Aps@1234') {
                        $password_verified = true;
                        // Rehash the password to a secure hash
                        $new_hash = password_hash($password, PASSWORD_DEFAULT);
                        // Update the password in the database
                        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $update_stmt->bind_param("si", $new_hash, $user['id']);
                        $update_stmt->execute();
                    }
                    
                    if ($password_verified) {
                        // Get customer details
                        $customer_id = $user['customer_id'];
                        $customer_name = $user['name'] ?? '';
                        $customer_email = $user['email'] ?? '';
                        $customer_phone = $user['phone'] ?? '';
                        
                        // If customer record exists, get additional details
                        if ($customer_id) {
                            $cust_stmt = $conn->prepare("SELECT * FROM customers WHERE id = ?");
                            $cust_stmt->bind_param("i", $customer_id);
                            if ($cust_stmt->execute()) {
                                $cust_result = $cust_stmt->get_result();
                                if ($cust_result->num_rows > 0) {
                                    $customer = $cust_result->fetch_assoc();
                                    $customer_name = $customer['name'] ?? $customer_name;
                                    $customer_phone = $customer['mobile'] ?? $customer_phone;
                                }
                            }
                        }
                        
                        // Regenerate session ID to prevent session fixation
                        session_regenerate_id(true);
                        
                        // Set session variables
                        $_SESSION['customer_logged_in'] = true;
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['customer_id'] = $customer_id;
                        $_SESSION['customer_name'] = $customer_name;
                        $_SESSION['customer_email'] = $customer_email;
                        $_SESSION['customer_phone'] = $customer_phone;
                        $_SESSION['last_activity'] = time();
                        $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
                        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
                        
                        // Debug log
                        error_log("Login successful for user: $customer_email");
                        error_log("Session data: " . print_r($_SESSION, true));
                        
                        // Debug: Check if headers are already sent
                        if (headers_sent($filename, $linenum)) {
                            error_log("Headers already sent in $filename on line $linenum");
                            die("Redirect failed. Please click <a href='http://" . $_SERVER['HTTP_HOST'] . "/apsdreamhome/customer_dashboard.php'>here</a> to continue.");
                        }
                        
                        // Set a test cookie to check if cookies are working
                        setcookie('test_cookie', 'test', time() + 3600, '/');
                        
                        // Redirect to dashboard or previous page
                        $redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . '/apsdreamhome/customer_dashboard.php';
                        if (isset($_SESSION['redirect_after_login'])) {
                            $redirect_url = $_SESSION['redirect_after_login'];
                            unset($_SESSION['redirect_after_login']);
                        }
                        
                        error_log("Redirecting to: $redirect_url");
                        
                        header("Location: $redirect_url");
                        exit();
                    } else {
                        // Invalid password
                        error_log("Login failed - Invalid password for user: $login");
                        $message = "Invalid email/phone or password!";
                        $message_type = "danger";
                    }
                }
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            $message = "An error occurred. Please try again later.";
            $message_type = "danger";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Login - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
        }
        .logo-section {
            background: linear-gradient(45deg, #17a2b8, #007bff);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .form-control:focus {
            border-color: #28a745;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
            width: 100%;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.15);
            color: white;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
        }
        .demo-credentials {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0 10px 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container mx-auto">
                    <!-- Header -->
                    <div class="login-header">
                        <div class="logo-section d-inline-block">
                            <h3 class="mb-0">
                                <i class="fas fa-home me-2"></i>
                                APS DREAM HOMES
                            </h3>
                        </div>
                        <h2 class="mb-2">Customer Login</h2>
                        <p class="mb-0">Access your property details and bookings</p>
                    </div>

                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="needs-validation" novalidate>
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="mb-3">
        <label for="login" class="form-label">
            <i class="fas fa-envelope me-1"></i>Email or Phone *
        </label>
        <input type="text" class="form-control" id="login" name="login" required>
        <div class="form-text">Enter your registered email address or phone number</div>
        <div class="invalid-feedback">
            Please enter your email or phone
        </div>
    </div>
    
    <div class="mb-3">
        <label for="password" class="form-label">
            <i class="fas fa-lock me-1"></i>Password *
        </label>
        <div class="input-group">
            <input type="password" class="form-control" id="password" name="password" required>
            <button class="btn btn-outline-secondary toggle-password" type="button">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        <div class="invalid-feedback">
            Please enter your password
        </div>
    </div>
    
    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-sign-in-alt me-2"></i>Login
        </button>
    </div>
    
    <div class="row mt-3">
        <div class="col-6">
            <a href="forgot-password.php" class="text-decoration-none">
                <i class="fas fa-key me-1"></i>Forgot Password?
            </a>
        </div>
        <div class="col-6 text-end">
            <a href="customer_login_google.php" class="text-decoration-none">
                <i class="fab fa-google me-1"></i>Login with Google
            </a>
        </div>
    </div>
    
    <div class="text-center mt-3">
        Don't have an account? 
        <a href="customer_registration.php" class="text-decoration-none">
            Register here
        </a>
    </div>
    
    <div class="text-center mt-3">
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Home
        </a>
    </div>
</form>
                <!-- Help Section -->
                <div class="mt-4 text-center">
                    <div class="bg-white p-3 rounded shadow-sm">
                        <h6 class="text-muted">Need Help?</h6>
                        <p class="mb-2">
                            <i class="fas fa-phone me-2"></i>
                            <strong>Support:</strong> +91-9876543210
                        </p>
                        <p class="mb-0">
                            <i class="fas fa-envelope me-2"></i>
                            <strong>Email:</strong> support@apsdreamhomes.com
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const passwordInput = this.previousElementSibling;
                const icon = this.querySelector('i');
                
                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Form validation
        (function () {
            'use strict'
            
            var forms = document.querySelectorAll('.needs-validation')
            
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
</body>
</html>
