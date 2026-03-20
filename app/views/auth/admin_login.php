<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page variables
$page_title = 'Admin Login - APS Dream Home';
$page_description = 'Secure admin access to APS Dream Home dashboard';

// Content for base layout
ob_start();
?>

<main class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-4">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <i class="fas fa-user-shield fa-3x text-primary"></i>
                        </div>
                        <h2 class="card-title fw-bold">Admin Login</h2>
                        <p class="text-muted">Access admin dashboard</p>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php
                            echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?php
                            echo htmlspecialchars($_SESSION['success']);
                            unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/login" id="adminLoginForm" class="admin-login-form">
                        <?php
                        // Generate CSRF token if not exists
                        if (!isset($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                        $csrf_token = $_SESSION['csrf_token'];
                        ?>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <div class="mb-3">
                            <label for="username" class="form-label">
                                <i class="fas fa-user me-1"></i> Username
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input type="text" class="form-control" id="username" name="username"
                                    placeholder="Enter admin username" required
                                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock me-1"></i> Password
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input type="password" class="form-control" id="password" name="password"
                                    placeholder="Enter password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember">
                                Remember me
                            </label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-sign-in-alt me-2"></i> Login to Admin
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            <a href="<?php echo BASE_URL; ?>/forgot-password" class="text-decoration-none me-3">
                                <i class="fas fa-question-circle me-1"></i> Forgot Password?
                            </a>
                            <span class="mx-2">|</span>
                            <a href="<?php echo BASE_URL; ?>/" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i> Back to Home
                            </a>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Security Info -->
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Secure admin access • Encrypted connection
                </small>
            </div>
        </div>
    </div>
</main>

<style>
    .card {
        border-radius: 15px;
        transition: transform 0.3s ease;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .input-group .btn {
        border-color: #ced4da;
    }

    .input-group .btn:hover {
        border-color: #667eea;
        background-color: #f8f9fa;
    }

    .fa-3x {
        color: #667eea;
        margin-bottom: 1rem;
    }

    .alert {
        border-radius: 8px;
        border: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if (togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);

                // Toggle icon
                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }

        // Form validation - disabled for testing
        // const form = document.querySelector('form');
        // form.addEventListener('submit', function(e) {
        //     // Basic validation - just ensure fields are not empty
        //     const username = document.getElementById('username').value;
        //     const password = document.getElementById('password').value;
        //     
        //     if (!username || !password) {
        //         e.preventDefault();
        //         alert('Please fill in all fields');
        //         return false;
        //     }
        // });

        // Auto-focus username field
        document.getElementById('username').focus();
    });
</script>

<?php
$content = ob_get_clean();

// Include admin header and display content
require_once __DIR__ . '/../admin/layouts/header.php';

// Display the login form content
echo $content;
?>