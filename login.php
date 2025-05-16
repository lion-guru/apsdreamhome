<?php
session_start();
include 'config.php';
include 'includes/base_template.php';

// Prevent session fixation
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Rate limiting
    if ($_SESSION['login_attempts'] >= 5) {
        $error = "बहुत अधिक असफल प्रयास। कृपया कुछ समय बाद फिर से प्रयास करें।";
    } else {
        $username = filter_var($_POST['username'], FILTER_SANITIZE_STRING);
        $password = $_POST['password'];
        
        $stmt = $con->prepare("SELECT id, username, password, role, last_login FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Reset login attempts
                $_SESSION['login_attempts'] = 0;
                
                // सेशन वेरिएबल्स सेट करें
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['last_activity'] = time();
                
                // CSRF टोकन जेनरेट करें
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                
                // Update last login
                $update_stmt = $con->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $user['id']);
                $update_stmt->execute();
                
                header("Location: dashboard.php");
                exit;
            } else {
                $_SESSION['login_attempts']++;
                $error = "अमान्य पासवर्ड";
            }
        } else {
            $_SESSION['login_attempts']++;
            $error = "अमान्य उपयोगकर्ता नाम";
        }
    }
}

// Prepare content for base template
ob_start();
?>

<div class="login-container">
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h2>APS Dream Homes</h2>
                <p>अपने खाते में लॉगिन करें</p>
            </div>
            
            <?php if(!empty($error)): ?>
                <div class="alert alert-danger animate-fade-in">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <form method="post" action="" id="loginForm" class="login-form">
                <div class="form-group">
                    <label for="username">उपयोगकर्ता नाम</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-user"></i></span>
                        <input type="text" class="form-control" id="username" name="username" required placeholder="अपना उपयोगकर्ता नाम दर्ज करें">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">पासवर्ड</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required placeholder="अपना पासवर्ड दर्ज करें">
                        <span class="input-group-text toggle-password"><i class="fa fa-eye"></i></span>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-block">लॉगिन</button>
                    <a href="forgot-password.php" class="forgot-password">पासवर्ड भूल गए?</a>
                </div>
            </form>
            
            <div class="login-footer">
                <p>नया उपयोगकर्ता? <a href="register.php">खाता बनाएं</a></p>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const togglePasswordBtn = document.querySelector('.toggle-password');
    
    // Password visibility toggle
    togglePasswordBtn.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.querySelector('i').classList.toggle('fa-eye');
        this.querySelector('i').classList.toggle('fa-eye-slash');
    });
    
    // Form validation
    loginForm.addEventListener('submit', function(e) {
        if (!usernameInput.value.trim() || !passwordInput.value.trim()) {
            e.preventDefault();
            showToast('कृपया सभी फ़ील्ड भरें', 'error');
        }
    });
});
</script>
<?php 
$content = ob_get_clean();
render_base_template('लॉगिन - APS Dream Homes', $content, ['modern-ui.css'], ['performance-optimizer.js']);
?>