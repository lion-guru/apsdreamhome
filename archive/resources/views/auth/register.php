<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: /dashboard');
    exit();
}

// Set page title and layout
$title = 'Register - APS Dream Home';
$layout = 'modern';

// Capture the content for layout injection
ob_start();
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-lg border-0 rounded-lg">
            <div class="card-header bg-success text-white text-center py-4">
                <h2 class="mb-0">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </h2>
                <p class="mb-0 mt-2 opacity-75">Join APS Dream Home today</p>
            </div>
            <div class="card-body p-5">
                <?php if(isset($_GET['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?php echo htmlspecialchars($_GET['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if(isset($_GET['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?php echo htmlspecialchars($_GET['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <form action="/auth/process-registration" method="POST" id="registrationForm">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="first_name" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-success"></i>First Name
                            </label>
                            <input type="text" class="form-control form-control-lg" id="first_name" name="first_name"
                                   placeholder="Enter your first name" required>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <label for="last_name" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-success"></i>Last Name
                            </label>
                            <input type="text" class="form-control form-control-lg" id="last_name" name="last_name"
                                   placeholder="Enter your last name" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">
                            <i class="fas fa-envelope me-2 text-success"></i>Email Address
                        </label>
                        <input type="email" class="form-control form-control-lg" id="email" name="email"
                               placeholder="Enter your email address" required>
                        <div class="form-text">We'll never share your email with anyone else.</div>
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="form-label fw-semibold">
                            <i class="fas fa-phone me-2 text-success"></i>Phone Number
                        </label>
                        <input type="tel" class="form-control form-control-lg" id="phone" name="phone"
                               placeholder="Enter your phone number" required pattern="[0-9]{10}">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-success"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="password" name="password"
                                       placeholder="Create a strong password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="form-text">Password must be at least 6 characters long.</div>
                        </div>

                        <div class="col-md-6 mb-4">
                            <label for="confirm_password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-success"></i>Confirm Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg" id="confirm_password" name="confirm_password"
                                       placeholder="Confirm your password" required minlength="6">
                                <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="user_type" class="form-label fw-semibold">
                            <i class="fas fa-users me-2 text-success"></i>I am a
                        </label>
                        <select class="form-select form-select-lg" id="user_type" name="user_type" required>
                            <option value="">Select user type</option>
                            <option value="buyer">Buyer</option>
                            <option value="seller">Seller</option>
                            <option value="investor">Investor</option>
                            <option value="tenant">Tenant</option>
                        </select>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="/terms" target="_blank" class="text-decoration-none text-success">Terms & Conditions</a>
                            and <a href="/privacy" target="_blank" class="text-decoration-none text-success">Privacy Policy</a>
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Create Account
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <div class="text-center">
                    <p class="mb-0">Already have an account?</p>
                    <a href="/auth/login" class="btn btn-outline-success mt-2">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();

// Custom styles for this page
ob_start();
?>
<style>
    .card {
        border-radius: 15px;
        overflow: hidden;
    }
    
    .card-header {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%) !important;
    }
    
    .form-control-lg, .form-select-lg {
        border-radius: 10px;
        padding: 12px 16px;
        font-size: 16px;
    }
    
    .btn-lg {
        padding: 12px 24px;
        font-size: 16px;
        border-radius: 10px;
    }
    
    .btn-outline-success {
        border-width: 2px;
    }
    
    .btn-outline-success:hover {
        background-color: #059669;
        border-color: #059669;
    }
    
    .form-check-input:checked {
        background-color: #10b981;
        border-color: #10b981;
    }
</style>
<?php
$styles = ob_get_clean();

// Custom scripts for this page
ob_start();
?>
<script>
    // Password toggle functionality
    document.getElementById('togglePassword').addEventListener('click', function() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = this.querySelector('i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    });
    
    // Confirm password toggle functionality
    document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
        const confirmPasswordInput = document.getElementById('confirm_password');
        const toggleIcon = this.querySelector('i');
        
        if (confirmPasswordInput.type === 'password') {
            confirmPasswordInput.type = 'text';
            toggleIcon.classList.remove('fa-eye');
            toggleIcon.classList.add('fa-eye-slash');
        } else {
            confirmPasswordInput.type = 'password';
            toggleIcon.classList.remove('fa-eye-slash');
            toggleIcon.classList.add('fa-eye');
        }
    });
    
    // Password confirmation validation
    document.getElementById('confirm_password').addEventListener('input', function() {
        const password = document.getElementById('password').value;
        const confirmPassword = this.value;
        
        if (password !== confirmPassword) {
            this.setCustomValidity('Passwords do not match');
        } else {
            this.setCustomValidity('');
        }
    });
    
    // Phone number validation (only digits)
    document.getElementById('phone').addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 10) {
            this.value = this.value.slice(0, 10);
        }
    });
    
    // Form validation
    document.getElementById('registrationForm').addEventListener('submit', function(e) {
        const firstName = document.getElementById('first_name').value.trim();
        const lastName = document.getElementById('last_name').value.trim();
        const email = document.getElementById('email').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const userType = document.getElementById('user_type').value;
        const terms = document.getElementById('terms').checked;
        
        if (!firstName || !lastName || !email || !phone || !password || !confirmPassword || !userType) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return;
        }
        
        // Basic email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            alert('Please enter a valid email address.');
            return;
        }
        
        // Phone validation
        if (phone.length !== 10) {
            e.preventDefault();
            alert('Please enter a valid 10-digit phone number.');
            return;
        }
        
        if (password.length < 6) {
            e.preventDefault();
            alert('Password must be at least 6 characters long.');
            return;
        }
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match.');
            return;
        }
        
        if (!terms) {
            e.preventDefault();
            alert('You must agree to the Terms & Conditions and Privacy Policy.');
            return;
        }
    });
</script>
<?php
$scripts = ob_get_clean();

// Include the layout
require_once __DIR__ . '/../layouts/' . $layout . '.php';
?>