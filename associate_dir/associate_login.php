<?php
/**
 * Associate Login System
 * APS Dream Homes - Associate Portal Access
 */

session_start();
require_once '../includes/config.php';
require_once '../includes/associate_permissions.php';
$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

$message = '';
$message_type = '';

// Redirect if already logged in
if (isset($_SESSION['associate_logged_in']) && $_SESSION['associate_logged_in'] === true) {
    header("Location: associate_dashboard.php");
    exit();
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_id = trim($_POST['login_id']); // Can be mobile or email
    $password = trim($_POST['password']);
    
    if (empty($login_id) || empty($password)) {
        $message = "Please enter both login ID and password!";
        $message_type = "danger";
    } else {
        // Check login credentials
        $stmt = $conn->prepare("SELECT id, full_name, mobile, email, password, status, current_level, total_business, total_team_size FROM mlm_agents WHERE (mobile = ? OR email = ?) AND status IN ('active', 'pending')");
        $stmt->bind_param("ss", $login_id, $login_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $associate = $result->fetch_assoc();
            
            if (password_verify($password, $associate['password'])) {
                if ($associate['status'] === 'active') {
                    // Set session variables
                    $_SESSION['associate_logged_in'] = true;
                    $_SESSION['associate_id'] = $associate['id'];
                    $_SESSION['associate_name'] = $associate['full_name'];
                    $_SESSION['associate_mobile'] = $associate['mobile'];
                    $_SESSION['associate_level'] = $associate['current_level'];
                    $_SESSION['associate_status'] = $associate['status'];
                    
                    // Update last login time
                    $update_stmt = $conn->prepare("UPDATE mlm_agents SET last_login = NOW() WHERE id = ?");
                    $update_stmt->bind_param("i", $associate['id']);
                    $update_stmt->execute();
                    
                    header("Location: associate_dashboard.php");
                    exit();
                } else {
                    $message = "Your account is pending approval. Please contact admin.";
                    $message_type = "warning";
                }
            } else {
                $message = "Invalid password!";
                $message_type = "danger";
            }
        } else {
            $message = "No account found with this mobile/email!";
            $message_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associate Login - APS Dream Homes</title>
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
            max-width: 450px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 3rem 2rem 2rem;
            text-align: center;
        }
        .logo-section {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .form-control {
            border-radius: 25px;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            transition: all 0.3s;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .login-links {
            text-align: center;
            margin-top: 1.5rem;
        }
        .login-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        .login-links a:hover {
            color: #764ba2;
        }
        .input-group-text {
            background: transparent;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 25px 0 0 25px;
        }
        .input-group .form-control {
            border-left: none;
            border-radius: 0 25px 25px 0;
        }
        .input-group:focus-within .input-group-text {
            border-color: #667eea;
        }
        .quick-stats {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 1rem;
            margin: 1rem 0;
            border-radius: 10px;
            text-align: center;
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
                            <p class="mb-0">आपका साथी</p>
                        </div>
                        <h2 class="mb-2">Associate Login</h2>
                        <p class="mb-0">Access your partner dashboard</p>
                    </div>

                    <div class="p-4">
                        <!-- Messages -->
                        <?php if ($message): ?>
                        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                            <?php echo $message; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php endif; ?>

                        <!-- Quick Stats -->
                        <div class="quick-stats">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h6 class="mb-0">500+</h6>
                                    <small>Active Partners</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-0">₹2Cr+</h6>
                                    <small>Paid Commissions</small>
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-0">50+</h6>
                                    <small>Projects</small>
                                </div>
                            </div>
                        </div>

                        <!-- Login Form -->
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Mobile Number or Email</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" name="login_id" 
                                           value="<?php echo htmlspecialchars($_POST['login_id'] ?? ''); ?>" 
                                           placeholder="Enter mobile or email" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" name="password" 
                                           placeholder="Enter your password" required>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">
                                    Remember me
                                </label>
                            </div>

                            <button type="submit" class="btn btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i>
                                Login to Dashboard
                            </button>
                        </form>

                        <!-- Quick Links -->
                        <div class="login-links">
                            <p class="mb-2">
                                <a href="#" onclick="showForgotPassword()">
                                    <i class="fas fa-key me-1"></i>Forgot Password?
                                </a>
                            </p>
                            <p class="mb-2">
                                Don't have an account? 
                                <a href="associate_registration.php">
                                    <i class="fas fa-user-plus me-1"></i>Register Now
                                </a>
                            </p>
                            <p class="mb-0">
                                <a href="index.php">
                                    <i class="fas fa-home me-1"></i>Back to Website
                                </a>
                            </p>
                        </div>

                        <!-- Features -->
                        <div class="mt-4 pt-3 border-top">
                            <h6 class="text-center mb-3">Partner Benefits</h6>
                            <div class="row text-center">
                                <div class="col-6 mb-2">
                                    <i class="fas fa-percentage text-primary"></i>
                                    <small class="d-block">High Commissions</small>
                                </div>
                                <div class="col-6 mb-2">
                                    <i class="fas fa-gift text-success"></i>
                                    <small class="d-block">Amazing Rewards</small>
                                </div>
                                <div class="col-6 mb-2">
                                    <i class="fas fa-users text-info"></i>
                                    <small class="d-block">Team Building</small>
                                </div>
                                <div class="col-6 mb-2">
                                    <i class="fas fa-chart-line text-warning"></i>
                                    <small class="d-block">Career Growth</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Enter your mobile number</label>
                            <input type="tel" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            Send Reset Link
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function showForgotPassword() {
            new bootstrap.Modal(document.getElementById('forgotPasswordModal')).show();
        }

        // Auto-focus on login field
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('input[name="login_id"]').focus();
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const loginId = document.querySelector('input[name="login_id"]').value.trim();
            const password = document.querySelector('input[name="password"]').value;

            if (!loginId || !password) {
                e.preventDefault();
                alert('Please fill in all fields');
                return false;
            }

            // Basic mobile number validation if it's a mobile
            if (/^\d+$/.test(loginId) && loginId.length !== 10) {
                e.preventDefault();
                alert('Please enter a valid 10-digit mobile number');
                return false;
            }
        });
    </script>
</body>
</html>