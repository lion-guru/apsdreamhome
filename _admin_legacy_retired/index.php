<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__) . '/admin_error.log');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Security Headers (added from login.php)
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// Include necessary files
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/../includes/db_connection.php';
require_once __DIR__ . '/includes/csrf_protection.php';
require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/security_functions.php';

// Generate or refresh captcha question
$shouldGenerateCaptcha = !isset($_SESSION['captcha_question'])
    || !isset($_SESSION['captcha_answer'])
    || isset($_GET['refresh']);

if ($shouldGenerateCaptcha) {
    $numOne = random_int(2, 9);
    $numTwo = random_int(1, 8);

    $_SESSION['captcha_question'] = sprintf('%d + %d', $numOne, $numTwo);
    $_SESSION['captcha_answer'] = $numOne + $numTwo;
}

$captcha_question = $_SESSION['captcha_question'] ?? '';

// Remember me functionality
if (isset($_COOKIE['remember_me'])) {
    $token = $_COOKIE['remember_me'];
    
    try {
        require_once __DIR__ . '/../config/database.php';
        global $con;

        $stmt = $con->prepare("SELECT admin_id, expires_at FROM remember_me_tokens WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();
        $token_data = $result->fetch_assoc();

        if ($token_data && strtotime($token_data['expires_at']) > time()) {
            // Token is valid, log the user in
            $_SESSION['admin_id'] = $token_data['admin_id'];
            $_SESSION['admin_logged_in'] = true;

            // You might want to fetch admin details and set other session variables here
            $stmt = $con->prepare("SELECT role FROM admins WHERE id = ?");
            $stmt->bind_param("i", $token_data['admin_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            $admin = $result->fetch_assoc();

            if ($admin) {
                $_SESSION['admin_role'] = $admin['role'];
                // Redirect based on role
                $redirect_url = 'enhanced_dashboard.php'; // Default
                if ($admin['role'] === 'superadmin') {
                    $redirect_url = 'superadmin_dashboard.php';
                } elseif ($admin['role'] === 'admin') {
                    $redirect_url = 'admin_dashboard.php';
                }
                header('Location: ' . $redirect_url);
                exit();
            }
        }
    } catch (Exception $e) {
        // Log error, but don't expose details
        error_log('Remember me check failed: ' . $e->getMessage());
    }
}

// If already logged in, redirect to the appropriate dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $admin_role = $_SESSION['admin_role'] ?? 'admin';

    // Get appropriate dashboard for role
    $dashboard_map = [
        'superadmin' => 'superadmin_dashboard.php',
        'admin' => 'admin_dashboard.php',
        'manager' => 'manager_dashboard.php',
        'director' => 'director_dashboard.php',
        'office_admin' => 'office_admin_dashboard.php',
        'ceo' => 'ceo_dashboard.php',
        'cfo' => 'cfo_dashboard.php',
        'coo' => 'coo_dashboard.php',
        'cto' => 'cto_dashboard.php',
        'cm' => 'cm_dashboard.php',
        'sales' => 'sales_dashboard.php',
        'employee' => 'employee_dashboard.php',
        'legal' => 'legal_dashboard.php',
        'marketing' => 'marketing_dashboard.php',
        'finance' => 'finance_dashboard.php',
        'hr' => 'hr_dashboard.php',
        'it' => 'it_dashboard.php',
        'operations' => 'operations_dashboard.php',
        'support' => 'support_dashboard.php',
        'builder' => 'builder_management_dashboard.php',
        'agent' => 'agent_dashboard.php',
        'associate' => 'associate_dashboard.php'
    ];

    $redirect_dashboard = $dashboard_map[$admin_role] ?? 'enhanced_dashboard.php';
    header('Location: ' . $redirect_dashboard);
    exit();
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/modern-ui.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --login-bg: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --glass-bg: rgba(255, 255, 255, 0.1);
            --glass-border: rgba(255, 255, 255, 0.2);

            /* Core color palette */
            --text-primary: #1f2933;
            --text-secondary: #6b7280;
            --success-color: #22c55e;
            --error-color: #ef4444;

            /* Spacing scale */
            --space-xs: 0.25rem;
            --space-sm: 0.5rem;
            --space-md: 0.75rem;
            --space-lg: 1.25rem;
            --space-xl: 1.75rem;
            --space-2xl: 2.5rem;

            /* Radius scale */
            --radius-lg: 1rem;
            --radius-xl: 1.5rem;
            --radius-full: 999px;

            /* Typography scale */
            --font-size-xs: 0.75rem;
            --font-size-sm: 0.875rem;
            --font-size-base: 1rem;

            /* Motion */
            --transition-fast: 0.15s ease;
            --transition-normal: 0.3s ease;
        }

        body {
            background: var(--login-bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            overflow-x: hidden;
            overflow-y: auto;
            padding: clamp(1.5rem, 4vw, 2.5rem) var(--space-lg);
            position: relative;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 1000"><defs><radialGradient id="a"><stop offset="0" stop-color="%23ffffff" stop-opacity=".1"/><stop offset="1" stop-color="%23ffffff" stop-opacity="0"/></radialGradient></defs><circle cx="500" cy="500" r="400" fill="url(%23a)"/></svg>');
            animation: float 20s ease-in-out infinite;
            pointer-events: none;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            padding: clamp(2.75rem, 6vw, 3.5rem) clamp(1.5rem, 4vw, 2.5rem);
            width: 100%;
            max-width: 520px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
            animation: slideUp 0.8s ease-out;
            max-height: calc(100vh - 3rem);
            overflow-y: auto;
        }

        .environment-badge {
            position: absolute;
            top: var(--space-lg);
            right: var(--space-lg);
            padding: var(--space-xs) var(--space-sm);
            border-radius: var(--radius-full);
            background: rgba(102, 126, 234, 0.08);
            color: #4338ca;
            font-size: var(--font-size-xs);
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            letter-spacing: 0.03em;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            animation: shimmer 2s infinite;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .login-header {
            text-align: center;
            margin-bottom: var(--space-2xl);
        }

        .login-logo {
            margin-bottom: var(--space-lg);
        }

        .login-logo i {
            font-size: 3.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--space-sm);
            letter-spacing: -0.025em;
        }

        .login-subtitle {
            color: var(--text-secondary);
            font-size: var(--font-size-sm);
            font-weight: 500;
            margin-bottom: var(--space-lg);
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: var(--space-sm);
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: var(--space-xs) var(--space-md);
            border-radius: var(--radius-full);
            font-size: var(--font-size-xs);
            font-weight: 600;
            margin-bottom: var(--space-lg);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .login-meta {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: var(--space-md);
            background: rgba(255, 255, 255, 0.65);
            border-radius: var(--radius-lg);
            border: 1px solid rgba(102, 126, 234, 0.12);
            padding: var(--space-md);
            margin-bottom: var(--space-xl);
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .meta-item i {
            font-size: 1.1rem;
            color: #6366f1;
        }

        .meta-text {
            display: flex;
            flex-direction: column;
        }

        .meta-title {
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--text-primary);
        }

        .meta-subtitle {
            font-size: 0.7rem;
            color: var(--text-secondary);
        }

        .form-floating-modern {
            display: flex;
            flex-direction: column;
            gap: var(--space-xs);
            margin-bottom: var(--space-lg);
        }

        .form-floating-modern label {
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: var(--text-primary);
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-wrapper input {
            width: 100%;
            height: 56px;
            padding: 0 calc(var(--space-xl) + 0.75rem) 0 var(--space-md);
            border: 2px solid #e1e8ed;
            border-radius: var(--radius-lg);
            font-size: var(--font-size-base);
            transition: all var(--transition-normal);
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
        }

        .input-wrapper input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
        }

        .input-wrapper .input-icon,
        .input-wrapper .password-toggle {
            position: absolute;
            top: 0;
            right: var(--space-md);
            bottom: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--text-secondary);
        }

        .input-wrapper .input-icon i {
            font-size: 1rem;
        }

        .password-toggle {
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
            transition: color var(--transition-fast);
        }

        .password-toggle:hover,
        .password-toggle:focus {
            color: #667eea;
            outline: none;
        }

        .form-floating-modern.error .input-wrapper input {
            border-color: var(--error-color);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        .form-floating-modern.error label,
        .form-floating-modern.error .input-icon,
        .form-floating-modern.error .password-toggle {
            color: var(--error-color);
        }

        .btn-login {
            height: 55px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: var(--radius-lg);
            font-size: var(--font-size-base);
            font-weight: 600;
            color: white;
            transition: all var(--transition-normal);
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-login:hover::before {
            left: 100%;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none !important;
        }

        .login-footer {
            text-align: center;
            margin-top: var(--space-xl);
            padding-top: var(--space-xl);
            border-top: 1px solid #e9ecef;
        }

        .login-links {
            display: flex;
            justify-content: center;
            gap: var(--space-lg);
            margin-top: var(--space-md);
        }

        .login-links a {
            color: var(--text-secondary);
            text-decoration: none;
            font-size: var(--font-size-sm);
            transition: color var(--transition-fast);
            display: flex;
            align-items: center;
            gap: var(--space-xs);
        }

        .login-links a:hover {
            color: #667eea;
        }

        .support-card {
            background: rgba(102, 126, 234, 0.08);
            border-radius: var(--radius-lg);
            padding: var(--space-md);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: var(--space-sm);
            color: #4338ca;
            font-size: var(--font-size-sm);
            margin-bottom: var(--space-md);
        }

        .support-card strong {
            font-weight: 600;
        }

        .contextual-banner {
            background: rgba(59, 130, 246, 0.12);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: var(--radius-lg);
            padding: var(--space-md);
            margin-bottom: var(--space-lg);
            font-size: var(--font-size-sm);
            color: #1d4ed8;
            display: flex;
            align-items: flex-start;
            gap: var(--space-sm);
        }

        .contextual-banner i {
            font-size: 1.2rem;
        }

        .inline-error {
            display: none;
            font-size: 0.75rem;
            color: var(--error-color);
            margin-top: var(--space-xs);
            font-weight: 500;
            margin-left: var(--space-sm);
        }

        .form-floating-modern.error .inline-error {
            display: block;
        }

        .alert-modern {
            border: none;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-bottom: var(--space-lg);
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: rgba(76, 175, 80, 0.1);
            color: var(--success-color);
            border-left: 4px solid var(--success-color);
        }

        .alert-danger {
            background: rgba(244, 67, 54, 0.1);
            color: var(--error-color);
            border-left: 4px solid var(--error-color);
        }

        .security-info {
            background: rgba(255, 152, 0, 0.1);
            border: 1px solid rgba(255, 152, 0, 0.3);
            border-radius: var(--radius-lg);
            padding: var(--space-md);
            margin-top: var(--space-lg);
            font-size: var(--font-size-sm);
            color: #856404;
        }

        .captcha-refresh {
            display: inline-flex;
            align-items: center;
            gap: var(--space-xs);
            font-size: var(--font-size-sm);
            font-weight: 600;
            color: #4338ca;
            cursor: pointer;
            text-decoration: none;
        }

        .captcha-refresh:hover {
            color: #312e81;
        }

        .captcha-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: var(--space-sm);
        }

        .toast-container {
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 1055;
        }

        .captcha-section {
            background: #f8f9fa;
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            margin-top: var(--space-lg);
            border: 2px dashed #dee2e6;
            display: flex;
            flex-direction: column;
            gap: var(--space-sm);
        }

        .captcha-question {
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            color: #312e81;
            background: rgba(102, 126, 234, 0.12);
            border-radius: var(--radius-full);
            padding: 0.4rem 1rem;
            width: fit-content;
        }

        .captcha-question .caption {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #4338ca;
        }

        .captcha-expression {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1f2937;
        }

        .back-home-btn {
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            backdrop-filter: blur(10px);
        }

        .back-home-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            color: white;
        }

        /* Loading animation for form submission */
        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: var(--radius-xl);
            z-index: 1000;
            opacity: 0;
            visibility: hidden;
            transition: all var(--transition-normal);
        }

        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive improvements */
        @media (max-width: 576px) {
            body {
                padding: var(--space-lg);
                align-items: flex-start;
            }

            .login-container {
                margin: var(--space-lg) auto;
                padding: var(--space-xl) var(--space-lg);
                max-height: none;
                overflow-y: visible;
            }

            .login-logo i {
                font-size: 2.5rem;
            }

            .login-title {
                font-size: 1.5rem;
            }
        }

        /* Enhanced form validation */
        .form-floating-modern input:invalid {
            border-color: var(--error-color);
        }

        .form-floating-modern input:invalid:focus {
            border-color: var(--error-color);
            box-shadow: 0 0 0 4px rgba(244, 67, 54, 0.1);
        }

        /* Password visibility toggle */
        .password-toggle {
            cursor: pointer;
            user-select: none;
        }

        /* Focus trap for better accessibility */
        .login-container:focus-within {
            outline: 2px solid rgba(102, 126, 234, 0.5);
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <div class="login-container" tabindex="0">
        <div class="loading-overlay" id="loadingOverlay">
            <div class="spinner"></div>
        </div>

        <div class="environment-badge">
            <i class="fas fa-globe"></i>
            <?php echo htmlspecialchars(strtoupper($_ENV['APP_ENV'] ?? 'Development')); ?> MODE
        </div>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>

        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="login-title">APS Dream Homes</h1>
            <p class="login-subtitle">Secure Admin Access</p>
            <div class="login-badge">
                <i class="fas fa-lock"></i>
                Admin Panel Login
            </div>
        </div>

        <div class="login-meta">
            <div class="meta-item">
                <i class="fas fa-fingerprint"></i>
                <div class="meta-text">
                    <span class="meta-title">MFA Ready</span>
                    <span class="meta-subtitle">Supports two-factor auth</span>
                </div>
            </div>
            <div class="meta-item">
                <i class="fas fa-database"></i>
                <div class="meta-text">
                    <span class="meta-title">Encrypted Data</span>
                    <span class="meta-subtitle">AES-256 at rest & in transit</span>
                </div>
            </div>
            <div class="meta-item">
                <i class="fas fa-headset"></i>
                <div class="meta-text">
                    <span class="meta-title">24/7 Support</span>
                    <span class="meta-subtitle">ops@apsdreamhome.com</span>
                </div>
            </div>
        </div>

        <div class="contextual-banner">
            <i class="fas fa-shield-check"></i>
            <div>
                <strong>Security First:</strong> Your login is protected with rate limiting, CSRF protection, and session hardening.
                Please ensure you are accessing the panel from a trusted network.
            </div>
        </div>

        <?php if (!empty($login_error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <?php echo htmlspecialchars($login_error); ?>
        </div>
        <?php endif; ?>

        <form action="process_login.php" method="post" autocomplete="off" novalidate id="loginForm">
            <div class="form-floating-modern">
                <label for="username"><i class="fas fa-user"></i> Username</label>
                <div class="input-wrapper">
                    <input type="text" id="username" name="username" required
                           placeholder="Enter your username" autocomplete="username">
                    <span class="input-icon" aria-hidden="true"><i class="fas fa-user"></i></span>
                </div>
                <p class="inline-error" id="usernameError">Please enter a valid username.</p>
            </div>

            <div class="form-floating-modern">
                <label for="password"><i class="fas fa-lock"></i> Password</label>
                <div class="input-wrapper">
                    <input type="password" id="password" name="password" required
                           placeholder="Enter your password" autocomplete="current-password">
                    <button type="button" class="password-toggle fas fa-lock" id="passwordToggle" title="Show Password" aria-label="Show password"></button>
                </div>
                <p class="inline-error" id="passwordError">Password must be at least 6 characters long.</p>
            </div>

            <div class="captcha-section">
                <div class="captcha-question" aria-live="polite">
                    <i class="fas fa-calculator"></i>
                    <span class="caption">Security Question</span>
                    <span class="captcha-expression"><?php echo htmlspecialchars($captcha_question, ENT_QUOTES, 'UTF-8'); ?> = ?</span>
                </div>
                <div class="form-floating-modern mb-0">
                    <label for="captcha_answer"><i class="fas fa-shield-alt"></i> Enter answer</label>
                    <div class="input-wrapper">
                        <input type="number" id="captcha_answer" name="captcha_answer" required
                               placeholder="Type the result" min="1" max="999">
                        <span class="input-icon" aria-hidden="true"><i class="fas fa-check-circle"></i></span>
                    </div>
                    <p class="inline-error" id="captchaError">Please provide the security answer to proceed.</p>
                </div>
                <div class="captcha-actions">
                    <span class="text-muted small">Need a new challenge?</span>
                    <button type="button" class="btn btn-link p-0 captcha-refresh" id="refreshCaptcha">
                        <i class="fas fa-sync-alt"></i> Refresh Question
                    </button>
                </div>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" value="" id="rememberMe" name="remember_me">
                <label class="form-check-label" for="rememberMe">
                    Remember me
                </label>
            </div>

            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

            <button type="submit" class="btn btn-login w-100" id="loginBtn">
                <i class="fas fa-sign-in-alt me-2"></i>
                <span>Login to Dashboard</span>
            </button>

            <div class="security-info">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Security Notice:</strong> This admin panel is restricted and monitored.
                Only authorized personnel should attempt to login.
            </div>
        </form>

        <div class="login-footer">
            <div class="support-card">
                <i class="fas fa-life-ring"></i>
                Need assistance? <strong>Call +91-98765-43210</strong>
            </div>
            <div class="login-links">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">
                    <i class="fas fa-key"></i>
                    Forgot Password?
                </a>
                <a href="../index.php">
                    <i class="fas fa-home"></i>
                    Back to Home
                </a>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">
                        <i class="fas fa-key me-2"></i>Reset Your Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="forgotPasswordForm">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   placeholder="Enter your registered email address">
                            <div class="form-text">
                                Enter the email address associated with your admin account.
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">Send Reset Link</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="toast-container">
        <div id="feedbackToast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMessage">
                    Action completed successfully.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const defaultLoginBtnContent = loginBtn ? loginBtn.innerHTML : '';

        const toastElement = document.getElementById('feedbackToast');
        let toastInstance = null;

        const showToast = (message, variant = 'primary') => {
            if (!toastElement) {
                return;
            }

            const toastBody = document.getElementById('toastMessage');
            if (toastBody) {
                toastBody.textContent = message;
            }

            toastElement.classList.remove('bg-primary', 'bg-success', 'bg-danger', 'bg-warning', 'bg-info', 'bg-secondary');
            toastElement.classList.add(`bg-${variant}`);

            if (!toastInstance) {
                toastInstance = new bootstrap.Toast(toastElement, { delay: 4000 });
            }

            toastInstance.show();
        };

        const resetLoginButton = () => {
            if (loginBtn) {
                loginBtn.disabled = false;
                loginBtn.innerHTML = defaultLoginBtnContent;
            }
            if (loadingOverlay) {
                loadingOverlay.classList.remove('show');
            }
        };

        const fieldConfig = {
            username: {
                input: document.getElementById('username'),
                errorEl: document.getElementById('usernameError'),
                validator: value => value.trim().length >= 3,
                message: 'Username must be at least 3 characters.'
            },
            password: {
                input: document.getElementById('password'),
                errorEl: document.getElementById('passwordError'),
                validator: value => value.trim().length >= 6,
                message: 'Password must be at least 6 characters long.'
            },
            captcha_answer: {
                input: document.getElementById('captcha_answer'),
                errorEl: document.getElementById('captchaError'),
                validator: value => value.trim() !== '' && Number(value) > 0,
                message: 'Please provide the security answer to proceed.'
            }
        };

        Object.values(fieldConfig).forEach(config => {
            if (!config.input) {
                return;
            }

            config.group = config.input.closest('.form-floating-modern');
            if (config.errorEl && config.message) {
                config.errorEl.textContent = config.message;
            }
        });

        const setFieldState = (config, isValid) => {
            if (!config || !config.group) {
                return;
            }

            config.group.classList.toggle('error', !isValid);

            if (config.input) {
                config.input.setAttribute('aria-invalid', (!isValid).toString());
            }
        };

        const validateField = key => {
            const config = fieldConfig[key];
            if (!config || !config.input) {
                return true;
            }

            const isValid = !!config.validator(config.input.value);
            setFieldState(config, isValid);
            return isValid;
        };

        const validateLoginForm = () => {
            let isValid = true;
            let firstInvalid = null;

            Object.keys(fieldConfig).forEach(key => {
                const fieldValid = validateField(key);
                if (!fieldValid) {
                    isValid = false;
                    if (!firstInvalid) {
                        firstInvalid = fieldConfig[key].input;
                    }
                }
            });

            if (!isValid && firstInvalid) {
                firstInvalid.focus();
            }

            return isValid;
        };

        // Password visibility toggle
        const passwordToggle = document.getElementById('passwordToggle');
        if (passwordToggle) {
            passwordToggle.addEventListener('click', function() {
                const passwordInput = fieldConfig.password?.input;
                const icon = this;

                if (!passwordInput) {
                    return;
                }

                if (passwordInput.type === 'password') {
                    passwordInput.type = 'text';
                    icon.classList.remove('fa-lock');
                    icon.classList.add('fa-unlock');
                    icon.title = 'Hide Password';
                } else {
                    passwordInput.type = 'password';
                    icon.classList.remove('fa-unlock');
                    icon.classList.add('fa-lock');
                    icon.title = 'Show Password';
                }
            });
        }

        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const formIsValid = validateLoginForm();
                if (!formIsValid) {
                    e.preventDefault();
                    e.stopPropagation();
                    resetLoginButton();
                    showToast('Please fix the highlighted fields before continuing.', 'warning');
                    return;
                }

                if (loginBtn) {
                    loginBtn.disabled = true;
                    loginBtn.innerHTML = `
                        <div class="spinner" style="width: 20px; height: 20px; margin-right: 8px;"></div>
                        Signing In...
                    `;
                }
                if (loadingOverlay) {
                    loadingOverlay.classList.add('show');
                }
            });
        }

        Object.entries(fieldConfig).forEach(([key, config]) => {
            if (!config.input) {
                return;
            }

            config.input.addEventListener('blur', () => validateField(key));
            config.input.addEventListener('input', () => {
                if (config.group && config.group.classList.contains('error')) {
                    validateField(key);
                }
            });
        });

        const usernameInput = fieldConfig.username?.input;
        if (usernameInput) {
            usernameInput.focus();
        }

        const refreshCaptchaBtn = document.getElementById('refreshCaptcha');
        if (refreshCaptchaBtn) {
            refreshCaptchaBtn.addEventListener('click', function() {
                const originalContent = refreshCaptchaBtn.innerHTML;
                refreshCaptchaBtn.disabled = true;
                refreshCaptchaBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Refreshing...';
                setTimeout(() => {
                    window.location.href = window.location.pathname + '?refresh=' + Date.now();
                }, 150);
            });
        }

        const forgotPasswordForm = document.getElementById('forgotPasswordForm');
        if (forgotPasswordForm) {
            forgotPasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();

                const emailInput = document.getElementById('email');
                const email = emailInput ? emailInput.value.trim() : '';
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : '';

                if (!email) {
                    showToast('Please enter your email address.', 'warning');
                    return;
                }

                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showToast('Please enter a valid email address.', 'warning');
                    return;
                }

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = `
                        <div class="spinner" style="width: 16px; height: 16px; margin-right: 8px;"></div>
                        Sending...
                    `;
                }

                fetch('reset_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(email)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message || 'Reset link sent to your email.', 'success');
                        const forgotPasswordModal = bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal'));
                        if (forgotPasswordModal) {
                            forgotPasswordModal.hide();
                        }
                    } else {
                        showToast(data.message || 'Error sending reset link.', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Reset password error:', error);
                    showToast('An unexpected error occurred. Please try again.', 'danger');
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modals = document.querySelectorAll('.modal');
                modals.forEach(modal => {
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                });
            }

            if (e.key === 'Enter' && document.activeElement && document.activeElement.id === 'password') {
                if (loginForm && typeof loginForm.requestSubmit === 'function') {
                    loginForm.requestSubmit();
                } else if (loginForm) {
                    loginForm.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
                }
            }
        });
    });
    </script>
</body>
</html>