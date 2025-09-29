<?php
/**
 * Customer Registration System - APS Dream Homes
 * Handles customer registration with optional referral code
 */

session_start();
require_once 'includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $mobile = trim($_POST['mobile']);
    $email = trim($_POST['email']);
    $referrer_code = trim($_POST['referrer_code']) ?: null;
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

    // Validation
    if (empty($full_name) || empty($mobile) || empty($email) || empty($password)) {
        $message = "Please fill all required fields!";
        $message_type = "danger";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match!";
        $message_type = "danger";
    } elseif (strlen($mobile) != 10) {
        $message = "Mobile number must be 10 digits!";
        $message_type = "danger";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter a valid email address!";
        $message_type = "danger";
    } else {
        // Check if mobile or email already exists
        $check_stmt = $conn->prepare("SELECT id FROM customers WHERE phone = ? OR email = ?");
        $check_stmt->bind_param("ss", $mobile, $email);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $message = "Mobile number or email already registered!";
            $message_type = "danger";
        } else {
            // Verify referral code if provided
            $referrer_id = null;
            if ($referrer_code) {
                $ref_stmt = $conn->prepare("SELECT id FROM mlm_agents WHERE referral_code = ? AND status = 'active'");
                $ref_stmt->bind_param("s", $referrer_code);
                $ref_stmt->execute();
                $ref_result = $ref_stmt->get_result()->fetch_assoc();
                if (!$ref_result) {
                    $message = "Invalid referral code!";
                    $message_type = "danger";
                } else {
                    $referrer_id = $ref_result['id'];
                }
            }

            if (!$message) {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Generate customer ID
                $customer_id = 'CUST-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

                // Insert new customer
                $stmt = $conn->prepare("INSERT INTO customers (id, name, email, phone, password, referred_by, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
                $stmt->bind_param("sssssi", $customer_id, $full_name, $email, $mobile, $hashed_password, $referrer_id);

                if ($stmt->execute()) {
                    // Send welcome email/notification (placeholder)
                    // sendCustomerWelcomeEmail($email, $full_name, $customer_id, $password);

                    $message = "Registration successful! Your Customer ID is: <strong>$customer_id</strong>. Please save it for login. You can now login to your dashboard.";
                    $message_type = "success";

                    // Clear form data
                    $_POST = array();
                } else {
                    $message = "Registration failed. Please try again!";
                    $message_type = "danger";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Registration - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .registration-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 600px;
        }
        .header-section {
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
        .btn-register {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-register:hover {
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
        .benefits-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .feature-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-right: 1rem;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="registration-container">
                    <!-- Header Section -->
                    <div class="header-section">
                        <div class="logo-section d-inline-block">
                            <h3 class="mb-0">
                                <i class="fas fa-home me-2"></i>
                                APS DREAM HOMES
                            </h3>
                            <p class="mb-0">Customer Portal</p>
                        </div>
                        <h1 class="mb-2">Customer Registration</h1>
                        <p class="mb-0">Create your account to track bookings and properties</p>
                    </div>

                    <div class="p-4">
                        <!-- Messages -->
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Information Box -->
                        <div class="info-box">
                            <h6><i class="fas fa-info-circle me-2"></i>Registration Information</h6>
                            <small>
                                📝 Register to get personalized property recommendations<br>
                                📊 Track your property visits and bookings<br>
                                💰 Get exclusive offers and discounts<br>
                                🔐 Secure access to your account details
                            </small>
                        </div>

                        <!-- Benefits Section -->
                        <div class="benefits-section">
                            <h6 class="mb-3"><i class="fas fa-star me-2"></i>Customer Benefits</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-home"></i>
                                        </div>
                                        <small>Property Tracking</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-calendar"></i>
                                        </div>
                                        <small>Visit Scheduling</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <small>Market Insights</small>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="feature-icon">
                                            <i class="fas fa-gift"></i>
                                        </div>
                                        <small>Exclusive Offers</small>
                            </div>
                        </div>

                        <!-- Google Login Option -->
                        <div class="mb-3">
                            <div class="d-grid">
                                <button type="button" class="btn btn-outline-danger" onclick="googleLogin()">
                                    <i class="fab fa-google me-2"></i>Sign up with Google
                                </button>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">or register with email</small>
                            </div>
                        </div>

                        <!-- Registration Form -->
                        <form method="POST" id="customerRegisterForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-user me-1"></i>Full Name *
                                        </label>
                                        <input type="text" class="form-control" name="full_name" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-mobile-alt me-1"></i>Mobile Number *
                                        </label>
                                        <input type="tel" class="form-control" name="mobile" pattern="[0-9]{10}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-envelope me-1"></i>Email Address *
                                </label>
                                <input type="email" class="form-control" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-gift me-1"></i>Referral Code (Optional)
                                </label>
                                <input type="text" class="form-control" name="referrer_code"
                                       placeholder="Enter associate's referral code for benefits">
                                <small class="text-muted">Get special benefits if referred by an APS Associate</small>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-lock me-1"></i>Password *
                                        </label>
                                        <input type="password" class="form-control" name="password" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-lock me-1"></i>Confirm Password *
                                        </label>
                                        <input type="password" class="form-control" name="confirm_password" required>
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-register">
                                    <i class="fas fa-user-plus me-2"></i>Create Customer Account
                                </button>
                            </div>

                            <div class="text-center mt-3">
                                <a href="customer_login.php">
                                    <i class="fas fa-sign-in-alt me-1"></i>Already have an account? Login here
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

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

    <!-- Google Sign-In API -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>

    <script>
        // Google Login Function
        function googleLogin() {
            if (typeof google !== 'undefined') {
                google.accounts.id.initialize({
                    client_id: 'YOUR_GOOGLE_CLIENT_ID', // Replace with actual Google Client ID
                    callback: handleGoogleCredentialResponse
                });

                google.accounts.id.prompt();
            } else {
                alert('Google Sign-In is not available. Please register with email instead.');
            }
        }

        function handleGoogleCredentialResponse(response) {
            // This function will handle the Google credential response
            // In a real implementation, you would send this to your server for verification
            console.log('Google credential response:', response);

            // For demo purposes, show a message
            alert('Google Sign-In successful! In a real implementation, this would auto-fill the form with your Google account details.');

            // You can extract user information from the JWT token here
            // The response.credential contains a JWT token with user information
        }

        // Initialize Google Sign-In when page loads
        window.onload = function () {
            // Check if Google Sign-In script is loaded
            if (typeof google !== 'undefined') {
                google.accounts.id.initialize({
                    client_id: 'YOUR_GOOGLE_CLIENT_ID', // Replace with actual Google Client ID
                    callback: handleGoogleCredentialResponse
                });
            }
        };
    </script>
</body>
</html>
