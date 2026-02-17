<?php
/**
 * Unified Admin Registration - APS Dream Homes
 * RESTRICTED ACCESS - Only existing admins can register new admins
 */

require_once __DIR__ . '/core/init.php';
$db = \App\Core\App::database();

$page_title = "Admin Registration - APS Dream Homes";
$error = '';
$success = '';

// SECURITY: Only logged-in admins can access this page
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit();
}

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = trim($_POST['phone'] ?? '');
    $csrf_token = $_POST['csrf_token'] ?? '';
    $admin_code = trim($_POST['admin_code'] ?? '');

    // Validate CSRF token
    if (!isset($_SESSION['csrf_token']) || $csrf_token !== $_SESSION['csrf_token']) {
        $error = "Invalid request. Please try again.";
    } else {
        // SECURITY: Verify admin registration code
        $required_admin_code = 'APS@ADMIN2024!SECURE';
        if ($admin_code !== $required_admin_code) {
            $error = "Invalid admin registration code. Access denied.";
        } else {
            // Validation
            $errors = [];

            if (empty($name)) $errors[] = "Name is required";
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
            if (empty($password) || strlen($password) < 12) $errors[] = "Password must be at least 12 characters";
            if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $password)) {
                $errors[] = "Password must contain uppercase, lowercase, number, and special character";
            }
            if ($password !== $confirm_password) $errors[] = "Passwords do not match";
            if (empty($phone) || !preg_match('/^[6-9]\d{9}$/', $phone)) $errors[] = "Valid 10-digit mobile number is required";

            // Check if email already exists
            $row = $db->fetchOne("SELECT id FROM admin WHERE email = :email", ['email' => $email]);
            if ($row) {
                $errors[] = "Email already registered";
            }

            if (!empty($errors)) {
                $error = implode(', ', $errors);
            } else {
                try {
                    // Hash password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert admin with restricted status
                    $created_by = $_SESSION['admin_name'] ?? 'System';
                    $sql = "
                        INSERT INTO admin (auser, email, apass, phone, status, created_at)
                        VALUES (:name, :email, :password, :phone, 'pending_approval', NOW())
                    ";

                    if ($db->execute($sql, [
                        'name' => $name,
                        'email' => $email,
                        'password' => $hashed_password,
                        'phone' => $phone
                    ])) {
                        $success = "Admin registration request submitted! Awaiting super admin approval.";

                        // Log the registration with security details
                        error_log("Admin registration requested by " . ($_SESSION['admin_name'] ?? 'Unknown') . " (" . ($_SESSION['admin_email'] ?? 'Unknown') . ") for: $email");

                        // Clear form
                        $_POST = [];
                    } else {
                        $error = "Registration failed. Please try again.";
                    }

                } catch (Exception $e) {
                    $error = "Registration failed: " . $e->getMessage();
                    error_log("Admin registration error: " . $e->getMessage());
                }
            }
        }
    }
}

// Generate new CSRF token
$_SESSION['csrf_token'] = \bin2hex(\App\Helpers\SecurityHelper::secureRandomBytes(32));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- AOS Animation -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #1e40af;
            --secondary-color: #3b82f6;
            --accent-color: #f59e0b;
            --success-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --info-color: #3b82f6;
            --dark-color: #1f2937;
            --light-color: #f8fafc;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }

        .registration-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            margin: 20px;
        }

        .registration-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .registration-form {
            padding: 30px;
        }

        .form-floating label {
            color: var(--dark-color);
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(30, 64, 175, 0.25);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(30, 64, 175, 0.3);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .password-strength {
            height: 5px;
            border-radius: 3px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }

        .strength-weak { background: var(--danger-color); width: 33%; }
        .strength-medium { background: var(--warning-color); width: 66%; }
        .strength-strong { background: var(--success-color); width: 100%; }

        .admin-badge {
            background: linear-gradient(135deg, var(--accent-color) 0%, var(--warning-color) 100%);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }

        .security-features {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(59, 130, 246, 0.1) 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .security-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .security-icon {
            color: var(--success-color);
            margin-right: 8px;
            width: 16px;
        }
    </style>
</head>
<body>
    <div class="registration-container" data-aos="fade-up">
        <!-- Header -->
        <div class="registration-header">
            <div class="admin-badge">
                <i class="fas fa-shield-alt me-1"></i>ADMIN ACCESS
            </div>
            <h2 class="mb-3">
                <i class="fas fa-user-shield me-2"></i>Admin Registration
            </h2>
            <p class="mb-0">Create admin account for APS Dream Homes</p>
        </div>

        <!-- Registration Form -->
        <div class="registration-form">
            <!-- Alert Container -->
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <span><?php echo h($error); ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <span><?php echo h($success); ?></span>
                </div>
            <?php endif; ?>

            <!-- Security Features -->
            <div class="security-features" data-aos="fade-down">
                <h6 class="mb-3">
                    <i class="fas fa-lock me-2"></i>Security Features
                </h6>
                <div class="security-item">
                    <i class="fas fa-check security-icon"></i>
                    <span>CSRF Protection</span>
                </div>
                <div class="security-item">
                    <i class="fas fa-check security-icon"></i>
                    <span>Password Hashing</span>
                </div>
                <div class="security-item">
                    <i class="fas fa-check security-icon"></i>
                    <span>Input Validation</span>
                </div>
                <div class="security-item">
                    <i class="fas fa-check security-icon"></i>
                    <span>SQL Injection Protection</span>
                </div>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <!-- Name -->
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="name" name="name" placeholder="Full Name" required value="<?php echo h($_POST['name'] ?? ''); ?>">
                    <label for="name">
                        <i class="fas fa-user me-2"></i>Full Name
                    </label>
                </div>

                <!-- Email -->
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" required value="<?php echo h($_POST['email'] ?? ''); ?>">
                    <label for="email">
                        <i class="fas fa-envelope me-2"></i>Email Address
                    </label>
                </div>

                <!-- Phone -->
                <div class="form-floating mb-3">
                    <input type="tel" class="form-control" id="phone" name="phone" placeholder="Mobile Number" pattern="[6-9][0-9]{9}" maxlength="10" required value="<?php echo h($_POST['phone'] ?? ''); ?>">
                    <label for="phone">
                        <i class="fas fa-phone me-2"></i>Mobile Number
                    </label>
                </div>

                <!-- Password -->
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <div class="password-strength" id="passwordStrength"></div>
                </div>

                <!-- Confirm Password -->
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="confirmPassword" name="confirm_password" placeholder="Confirm Password" required>
                    <label for="confirmPassword">
                        <i class="fas fa-lock me-2"></i>Confirm Password
                    </label>
                </div>

                <!-- ADMIN REGISTRATION CODE -->
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="adminCode" name="admin_code" placeholder="Admin Registration Code" required>
                    <label for="adminCode">
                        <i class="fas fa-shield-alt me-2"></i>Admin Registration Code
                    </label>
                    <div class="form-text">
                        <i class="fas fa-info-circle me-1"></i>
                        Required security code for admin registration
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="text-center mb-3">
                    <button type="submit" class="btn btn-primary btn-lg px-5">
                        <i class="fas fa-user-plus me-2"></i>Create Admin Account
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="mb-0">Already have an admin account? <a href="index.php" class="text-primary">Sign In</a></p>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 800,
            once: true
        });

        // Password strength checker
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('passwordStrength');

            let strength = 0;
            if (password.length >= 12) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[@$!%*?&]/.test(password)) strength++;

            strengthBar.className = 'password-strength';
            if (strength <= 2) strengthBar.classList.add('strength-weak');
            else if (strength <= 4) strengthBar.classList.add('strength-medium');
            else strengthBar.classList.add('strength-strong');
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const adminCode = document.getElementById('adminCode').value;

            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match!');
                return false;
            }

            if (password.length < 12) {
                e.preventDefault();
                alert('Password must be at least 12 characters long!');
                return false;
            }

            if (!adminCode) {
                e.preventDefault();
                alert('Admin registration code is required!');
                return false;
            }
        });
    </script>
</body>
</html>
