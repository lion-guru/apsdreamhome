<?php
/**
 * APS Dream Home - Admin Login Page
 * Professional admin login with enhanced security
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/includes/csrf_protection.php';
require_once __DIR__ . '/includes/session_security_manager.php';

$sessionSecurity = initializeSecureAdminSession();

// Redirect if already logged in
if (isAuthenticated()) {
    $role = getAuthSubRole();
    if ($role === 'superadmin') {
        header('Location: superadmin_dashboard.php');
    } elseif ($role === 'manager') {
        header('Location: manager_dashboard.php');
    } else {
        header('Location: dashboard.php');
    }
    exit();
}

$error = '';
$success = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Validate session security before processing login
    if (!$sessionSecurity->validateSessionSecurity()) {
        $error = 'Session security validation failed. Please refresh and try again.';
        // Log event internally via SessionSecurityManager if needed, or use a public method
        // For now, we'll rely on the internal logging of validateSessionSecurity if it fails
        $sessionSecurity->recordFailedAttempt(); // Increment failed attempts for security
    }
    // Verify CSRF token
    elseif (!csrf_check()) {
        $error = 'Invalid security token. Please refresh the page and try again.';
        $sessionSecurity->recordFailedAttempt(); // Log failed attempt
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $captcha = $_POST['captcha'] ?? '';

        // Validate captcha
        if ($captcha !== $_SESSION['captcha_answer']) {
            $error = 'Incorrect captcha answer';
        } elseif (empty($username) || empty($password)) {
            $error = 'Please fill all fields';
        } else {
        try {
            $adminModel = new \App\Models\Admin();
            $admin = $adminModel->authenticateAdmin($username, $password);

            if ($admin) {
                // Check for 2FA
                if (!empty($admin['two_factor_secret'])) {
                    $_SESSION['temp_user_id'] = $admin['id'];
                    $_SESSION['temp_user_role'] = $admin['role'];
                    $_SESSION['2fa_pending'] = true;

                    // Generate and send token
                    require_once __DIR__ . '/../includes/two_factor_manager.php';
                    require_once __DIR__ . '/../includes/AdminLogger.php';
                    $twoFactorManager = new TwoFactorManager(\App\Core\App::database());
                    $twoFactorManager->generateToken($admin['id'], TwoFactorManager::METHOD_EMAIL, 'admin');

                    header("Location: ../verify_2fa.php");
                    exit;
                }

                // Login successful - initialize secure admin session
                setAuthSession($admin, 'admin', $admin['role']);

                // Regenerate session ID with enhanced security
                $sessionSecurity->regenerateSessionID();

                // Log successful login
                $sessionSecurity->logSecurityEvent('Admin Login Success', [
                    'admin_id' => $admin['id'],
                    'username' => $admin['username'],
                    'role' => $admin['role'],
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown'
                ]);

                // Redirect based on role
                if ($admin['role'] === 'superadmin') {
                    header('Location: superadmin_dashboard.php');
                } elseif ($admin['role'] === 'manager') {
                    header('Location: manager_dashboard.php');
                } else {
                    header('Location: dashboard.php');
                }
                exit();
            } else {
                $error = 'Invalid username or account not active';
                $sessionSecurity->recordFailedAttempt(); // Log failed attempt
            }
        } catch (Exception $e) {
            $error = 'Login failed. Please try again.';
            // Log event internally via SessionSecurityManager if needed
            $sessionSecurity->recordFailedAttempt(); // Log failed attempt
        }
    }
}

    // Generate new captcha after failed attempt
    $_SESSION['captcha_question'] = \App\Helpers\SecurityHelper::secureRandomInt(5, 15) . ' + ' . \App\Helpers\SecurityHelper::secureRandomInt(1, 10);
    $_SESSION['captcha_answer'] = eval('return ' . str_replace('+', '+', $_SESSION['captcha_question']) . ';');
} else {
    // Generate initial captcha
    $_SESSION['captcha_question'] = \App\Helpers\SecurityHelper::secureRandomInt(5, 15) . ' + ' . \App\Helpers\SecurityHelper::secureRandomInt(1, 10);
    $_SESSION['captcha_answer'] = eval('return ' . str_replace('+', '+', $_SESSION['captcha_question']) . ';');
}

$pageTitle = 'Admin Login - APS Dream Homes';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?></title>

    <!-- Bootstrap CSS -->
    <link href="<?= h(get_admin_asset_url('bootstrap.min.css', 'css')) ?>" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="<?= h(get_admin_asset_url('font-awesome.min.css', 'css')) ?>" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            margin: 20px;
        }

        .login-header {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }

        .login-header h2 {
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
        }

        .login-header .badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem 1rem;
            font-size: 0.9rem;
        }

        .login-body {
            padding: 3rem 2rem;
        }

        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn-login {
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.3);
        }

        .captcha-box {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .captcha-question {
            font-size: 1.2rem;
            font-weight: 600;
            color: #2c3e50;
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
        }

        .form-control.with-icon {
            border-left: none;
        }

        @media (max-width: 768px) {
            .login-container {
                margin: 10px;
            }

            .login-body {
                padding: 2rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="mb-3">
                <i class="fas fa-shield-alt fa-3x mb-3"></i>
            </div>
            <h2>Admin Login</h2>
            <span class="badge">APS Dream Homes</span>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?= h($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?= h($success) ?>
                </div>
            <?php endif; ?>

            <?php
            // Display security recommendations if any
            $recommendations = $sessionSecurity->getSecurityRecommendations();
            if (!empty($recommendations)):
            ?>
                <div class="alert alert-warning" role="alert">
                    <i class="fas fa-shield-alt me-2"></i>
                    <strong>Security Recommendations:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($recommendations as $recommendation): ?>
                            <li><?= h($recommendation) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="username" class="form-label fw-bold">
                        <i class="fas fa-user me-2"></i>Username
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" class="form-control with-icon" id="username" name="username"
                               value="<?= h($_POST['username'] ?? '') ?>"
                               placeholder="Enter your username" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-bold">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" class="form-control with-icon" id="password" name="password"
                               placeholder="Enter your password" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="fas fa-shield-alt me-2"></i>Security Check
                    </label>
                    <div class="captcha-box">
                        <div class="captcha-question mb-2">
                            What is: <?= h($_SESSION['captcha_question']) ?> = ?
                        </div>
                        <input type="text" class="form-control" name="captcha"
                               placeholder="Enter answer" required>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-login btn-lg">
                        <i class="fas fa-sign-in-alt me-2"></i>Login to Admin Panel
                    </button>
                </div>
            </form>

            <div class="text-center mt-4">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>
                    For security, please contact your administrator if you forgot your password
                </small>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="<?= h(get_admin_asset_url('bootstrap.min.js', 'js')) ?>"></script>
</body>
</html>
