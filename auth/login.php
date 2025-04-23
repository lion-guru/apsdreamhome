<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use App\Helpers\Helpers;
require_once __DIR__ . '/../src/bootstrap.php';
include_once __DIR__ . '/../includes/csrf.php';

// Initialize variables
$email = '';
$password = '';
$errors = [];

// Start session
session_start();

// Database connection
$dbConfig = require APP_ROOT . '/src/config/database.php';
$conn = new mysqli(
    $dbConfig['host'],
    $dbConfig['username'],
    $dbConfig['password'],
    $dbConfig['database']
);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

if (!isset($_SESSION['captcha_num1_login'])) {
    $_SESSION['captcha_num1_login'] = rand(1, 10);
    $_SESSION['captcha_num2_login'] = rand(1, 10);
}
$captcha_question_login = $_SESSION['captcha_num1_login'] . ' + ' . $_SESSION['captcha_num2_login'];

// Rate limiting: track failed attempts in session
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_blocked_until'] = 0;
}
// If blocked, show error and block login
if (time() < ($_SESSION['login_blocked_until'] ?? 0)) {
    $errors['login'] = 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['login_blocked_until']) . '.';
} else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || !CSRFProtection::validateToken($_POST['csrf_token'], 'associate_login')) {
        $errors['login'] = 'Security error: Invalid or missing CSRF token.';
    } else if (!isset($_POST['captcha_answer']) || intval($_POST['captcha_answer']) !== ($_SESSION['captcha_num1_login'] + $_SESSION['captcha_num2_login'])) {
        $errors['login'] = 'Security error: Invalid CAPTCHA answer.';
        // Reset CAPTCHA for next attempt
        $_SESSION['captcha_num1_login'] = rand(1, 10);
        $_SESSION['captcha_num2_login'] = rand(1, 10);
        $captcha_question_login = $_SESSION['captcha_num1_login'] . ' + ' . $_SESSION['captcha_num2_login'];
    } else {
        // Reset CAPTCHA for next login
        $_SESSION['captcha_num1_login'] = rand(1, 10);
        $_SESSION['captcha_num2_login'] = rand(1, 10);
        $captcha_question_login = $_SESSION['captcha_num1_login'] . ' + ' . $_SESSION['captcha_num2_login'];
        $email = Helpers::sanitizeInput($_POST['email']);
        $password = $_POST['password'];
        
        // Validate input
        if (empty($email)) {
            $errors['email'] = 'Email is required';
        } elseif (!Helpers::isValidEmail($email)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($password)) {
            $errors['password'] = 'Password is required';
        }
        
        // If no errors, attempt login
        if (empty($errors)) {
            $query = "SELECT id, email, password, role FROM users WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                if (password_verify($password, $user['password'])) {
                    session_regenerate_id(true); // Prevent session fixation
                    // Start session and store user data
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role'];
                    // Reset failed attempts
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['login_blocked_until'] = 0;
                    // Redirect based on role
                    if ($user['role'] === 'admin') {
                        header('Location: /march2025apssite/admin/dashboard');
                    } else {
                        header('Location: /march2025apssite/dashboard');
                    }
                    exit;
                } else {
                    $errors['login'] = 'Invalid email or password';
                    $_SESSION['login_attempts'] += 1;
                }
            } else {
                $errors['login'] = 'Invalid email or password';
                $_SESSION['login_attempts'] += 1;
            }
            // Lockout after 5 failed attempts for 10 minutes
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['login_blocked_until'] = time() + 600;
                $errors['login'] = 'Too many failed login attempts. Please try again after ' . date('H:i:s', $_SESSION['login_blocked_until']) . '.';
            }
        }
    }
}

// Set page title
$page_title = 'Login';

// Start output buffering
ob_start();
?>

<div class="auth-container">
    <div class="auth-box">
        <h2>Login</h2>
        
        <?php if (isset($errors['login'])): ?>
            <div class="alert alert-error">
                <?php echo $errors['login']; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" class="auth-form">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(CSRFProtection::generateToken('associate_login')); ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <span class="error"><?php echo $errors['email']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
                <?php if (isset($errors['password'])): ?>
                    <span class="error"><?php echo $errors['password']; ?></span>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="captcha_answer">What is <?php echo $captcha_question_login; ?>?</label>
                <input type="number" class="form-control" name="captcha_answer" id="captcha_answer" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="btn-primary">Login</button>
            </div>
            
            <div class="auth-links">
                <a href="/march2025apssite/auth/forgot-password">Forgot Password?</a>
                <span>|</span>
                <a href="/march2025apssite/auth/register">Create Account</a>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require APP_ROOT . '/app/views/layouts/base.php';
?>