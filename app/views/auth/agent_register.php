<?php
// Start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set page variables
$page_title = 'Agent Registration - APS Dream Home';
$page_description = 'Join as an agent and build your real estate business';

// Content for base layout
ob_start();
?>

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <div class="mb-3">
                            <i class="fas fa-briefcase fa-3x text-warning"></i>
                        </div>
                        <h2 class="card-title fw-bold mb-3">Agent Registration</h2>
                        <p class="text-muted lead">Build your real estate career with APS Dream Home</p>
                        <div class="badge bg-warning text-dark fs-6 mb-3">
                            <i class="fas fa-trophy me-1"></i>Top Commission Structure
                        </div>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php
                            echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php
                            echo htmlspecialchars($_SESSION['success']);
                            unset($_SESSION['success']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['errors'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <div><?php echo htmlspecialchars($error); ?></div>
                            <?php endforeach; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['errors']); ?>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo BASE_URL; ?>/agent/register" id="agentRegistrationForm" class="registration-form">
                        <?php
                        // Generate CSRF token if not exists
                        if (!isset($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                        $csrf_token = $_SESSION['csrf_token'];
                        ?>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="user_type" value="agent">

                        <!-- Personal Information -->
                        <div class="registration-section mb-4">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-user me-2 text-warning"></i>Personal Information
                            </h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-user me-1"></i> Full Name *
                                    </label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="Enter your full name" required
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['name'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please provide your full name</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        <i class="fas fa-envelope me-1"></i> Email Address *
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email"
                                        placeholder="Enter your email address" required
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['email'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please provide a valid email address</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i> Phone Number *
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone"
                                        placeholder="Enter your phone number" pattern="[0-9]{10}" maxlength="10" required
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['phone'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please provide a valid 10-digit phone number</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="license_no" class="form-label">
                                        <i class="fas fa-id-card me-1"></i> RERA License Number *
                                    </label>
                                    <input type="text" class="form-control" id="license_no" name="license_no"
                                        placeholder="Enter your RERA license number" required
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['license_no'] ?? ''); ?>">
                                    <div class="form-text">Required for real estate agents</div>
                                    <div class="invalid-feedback">Please provide your license number</div>
                                </div>
                            </div>
                        </div>

                        <!-- Professional Information -->
                        <div class="registration-section mb-4">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-briefcase me-2 text-warning"></i>Professional Information
                            </h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="experience" class="form-label">
                                        <i class="fas fa-chart-line me-1"></i> Years of Experience *
                                    </label>
                                    <select class="form-select" id="experience" name="experience" required>
                                        <option value="">Select Experience</option>
                                        <option value="0-1" <?php echo (($_SESSION['old_input']['experience'] ?? '') === '0-1') ? 'selected' : ''; ?>>0-1 Year</option>
                                        <option value="1-3" <?php echo (($_SESSION['old_input']['experience'] ?? '') === '1-3') ? 'selected' : ''; ?>>1-3 Years</option>
                                        <option value="3-5" <?php echo (($_SESSION['old_input']['experience'] ?? '') === '3-5') ? 'selected' : ''; ?>>3-5 Years</option>
                                        <option value="5+" <?php echo (($_SESSION['old_input']['experience'] ?? '') === '5+') ? 'selected' : ''; ?>>5+ Years</option>
                                    </select>
                                    <div class="invalid-feedback">Please select your experience level</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="specialization" class="form-label">
                                        <i class="fas fa-star me-1"></i> Specialization
                                    </label>
                                    <select class="form-select" id="specialization" name="specialization">
                                        <option value="">Select Specialization</option>
                                        <option value="residential" <?php echo ($_SESSION['old_input']['specialization'] ?? '') === 'residential' ? 'selected' : ''; ?>>Residential Properties</option>
                                        <option value="commercial" <?php echo ($_SESSION['old_input']['specialization'] ?? '') === 'commercial' ? 'selected' : ''; ?>>Commercial Properties</option>
                                        <option value="plots" <?php echo ($_SESSION['old_input']['specialization'] ?? '') === 'plots' ? 'selected' : ''; ?>>Plots & Land</option>
                                        <option value="rental" <?php echo ($_SESSION['old_input']['specialization'] ?? '') === 'rental' ? 'selected' : ''; ?>>Rental Properties</option>
                                        <option value="investment" <?php echo ($_SESSION['old_input']['specialization'] ?? '') === 'investment' ? 'selected' : ''; ?>>Investment Properties</option>
                                        <option value="luxury" <?php echo ($_SESSION['old_input']['specialization'] ?? '') === 'luxury' ? 'selected' : ''; ?>>Luxury Properties</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="bio" class="form-label">
                                        <i class="fas fa-info-circle me-1"></i> Professional Bio
                                    </label>
                                    <textarea class="form-control" id="bio" name="bio" rows="3"
                                        placeholder="Tell us about your experience and expertise..."
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['bio'] ?? ''); ?>"></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="registration-section mb-4">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-map-marker-alt me-2 text-warning"></i>Address Information
                            </h4>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-home me-1"></i> Street Address *
                                    </label>
                                    <input type="text" class="form-control" id="address" name="address"
                                        placeholder="Enter your street address" required
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['address'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please provide your address</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label">
                                        <i class="fas fa-city me-1"></i> City *
                                    </label>
                                    <input type="text" class="form-control" id="city" name="city"
                                        placeholder="Enter your city" required
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['city'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please provide your city</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label">
                                        <i class="fas fa-map me-1"></i> State *
                                    </label>
                                    <select class="form-select" id="state" name="state" required>
                                        <option value="">Select State</option>
                                        <option value="Andhra Pradesh" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'Andhra Pradesh' ? 'selected' : ''; ?>>Andhra Pradesh</option>
                                        <option value="Arunachal Pradesh" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'Arunachal Pradesh' ? 'selected' : ''; ?>>Arunachal Pradesh</option>
                                        <option value="Assam" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'Assam' ? 'selected' : ''; ?>>Assam</option>
                                        <option value="Bihar" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'Bihar' ? 'selected' : ''; ?>>Bihar</option>
                                        <option value="Gujarat" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'Gujarat' ? 'selected' : ''; ?>>Gujarat</option>
                                        <option value="Karnataka" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'Karnataka' ? 'selected' : ''; ?>>Karnataka</option>
                                        <option value="Kerala" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'Kerala' ? 'selected' : ''; ?>>Kerala</option>
                                        <option value="Maharashtra" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'Maharashtra' ? 'selected' : ''; ?>>Maharashtra</option>
                                        <option value="Tamil Nadu" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'Tamil Nadu' ? 'selected' : ''; ?>>Tamil Nadu</option>
                                        <option value="Uttar Pradesh" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'Uttar Pradesh' ? 'selected' : ''; ?>>Uttar Pradesh</option>
                                        <option value="West Bengal" <?php echo ($_SESSION['old_input']['state'] ?? '') === 'West Bengal' ? 'selected' : ''; ?>>West Bengal</option>
                                    </select>
                                    <div class="invalid-feedback">Please select your state</div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="pincode" class="form-label">
                                        <i class="fas fa-mail-bulk me-1"></i> PIN Code *
                                    </label>
                                    <input type="text" class="form-control" id="pincode" name="pincode"
                                        placeholder="Enter PIN code" pattern="[0-9]{6}" required
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['pincode'] ?? ''); ?>">
                                    <div class="invalid-feedback">Please provide a valid 6-digit PIN code</div>
                                </div>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="registration-section mb-4">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-lock me-2 text-warning"></i>Account Information
                            </h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">
                                        <i class="fas fa-lock me-1"></i> Password *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="password" name="password"
                                            placeholder="Create a strong password" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text">Password must be at least 6 characters long</div>
                                    <div class="invalid-feedback">Please provide a strong password</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">
                                        <i class="fas fa-lock me-1"></i> Confirm Password *
                                    </label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                            placeholder="Confirm your password" required minlength="6">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">Passwords do not match</div>
                                </div>
                            </div>
                        </div>

                        <!-- Business Information -->
                        <div class="registration-section mb-4">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-chart-line me-2 text-warning"></i>Business Information
                            </h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="referral_code" class="form-label">
                                        <i class="fas fa-gift me-1"></i> Referral Code (Optional)
                                    </label>
                                    <input type="text" class="form-control" id="referral_code" name="referral_code"
                                        placeholder="Enter referral code if you have one"
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['referral_code'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <div class="alert alert-info small">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Agents earn up to 10% commission on sales
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="registration-section mb-4">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to <a href="<?= BASE_URL ?>/terms" class="text-warning">Terms and Conditions</a> and <a href="<?= BASE_URL ?>/privacy" class="text-warning">Privacy Policy</a> *
                                </label>
                                <div class="invalid-feedback">You must agree to the terms and conditions</div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="commission_terms" name="commission_terms" required>
                                <label class="form-check-label" for="commission_terms">
                                    I understand the commission structure and payment terms *
                                </label>
                                <div class="invalid-feedback">You must agree to commission terms</div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Send me business opportunities and training updates via email
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-warning btn-lg px-5">
                                <i class="fas fa-user-plus me-2"></i>Join as Agent
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Already have an account?
                            <a href="<?php echo BASE_URL; ?>/agent/login" class="text-decoration-none">
                                <i class="fas fa-sign-in-alt me-1"></i>Sign In
                            </a>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .registration-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 25px;
        margin-bottom: 20px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .registration-section:hover {
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .section-title {
        color: #2c3e50;
        font-weight: 600;
        border-bottom: 2px solid #ffc107;
        padding-bottom: 10px;
        margin-bottom: 20px;
    }

    .card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        color: #000;
    }

    .btn-warning:hover {
        background: linear-gradient(135deg, #fd7e14 0%, #ffc107 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(255, 193, 7, 0.3);
        color: #000;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #ffc107;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    }

    .fa-3x {
        color: #ffc107;
        margin-bottom: 1rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
        }

        50% {
            transform: scale(1.05);
        }

        100% {
            transform: scale(1);
        }
    }

    .alert {
        border-radius: 8px;
        border: none;
        animation: slideIn 0.3s ease;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .input-group .btn:hover {
        border-color: #ffc107;
        background-color: #f8f9fa;
    }

    .form-text {
        color: #6c757d;
        font-size: 0.875rem;
    }

    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
    }

    .badge {
        animation: bounce 2s infinite;
    }

    @keyframes bounce {

        0%,
        20%,
        50%,
        80%,
        100% {
            transform: translateY(0);
        }

        40% {
            transform: translateY(-10px);
        }

        60% {
            transform: translateY(-5px);
        }
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

                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }

        // Toggle confirm password visibility
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirm_password');

        if (toggleConfirmPassword && confirmPasswordInput) {
            toggleConfirmPassword.addEventListener('click', function() {
                const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                confirmPasswordInput.setAttribute('type', type);

                const icon = this.querySelector('i');
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            });
        }

        // Form validation
        const form = document.querySelector('form');
        const inputs = form.querySelectorAll('input[required], select[required]');

        // Real-time validation
        inputs.forEach(input => {
            input.addEventListener('blur', function() {
                validateField(this);
            });

            input.addEventListener('input', function() {
                if (this.classList.contains('is-invalid')) {
                    validateField(this);
                }
            });
        });

        function validateField(field) {
            let isValid = true;

            if (field.hasAttribute('required') && !field.value.trim()) {
                isValid = false;
            }

            if (field.type === 'email' && field.value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(field.value)) {
                    isValid = false;
                }
            }

            if (field.id === 'pincode' && field.value) {
                const pincodeRegex = /^[0-9]{6}$/;
                if (!pincodeRegex.test(field.value)) {
                    isValid = false;
                }
            }

            if (field.id === 'confirm_password' && field.value) {
                const password = document.getElementById('password').value;
                if (field.value !== password) {
                    isValid = false;
                    field.setCustomValidity('Passwords do not match');
                } else {
                    field.setCustomValidity('');
                }
            }

            if (field.id === 'password' && field.value) {
                if (field.value.length < 6) {
                    isValid = false;
                    field.setCustomValidity('Password must be at least 6 characters');
                } else {
                    field.setCustomValidity('');
                }
            }

            if (isValid) {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            } else {
                field.classList.remove('is-valid');
                field.classList.add('is-invalid');
            }

            return isValid;
        }

        // Form submission validation
        form.addEventListener('submit', function(e) {
            let isFormValid = true;

            inputs.forEach(input => {
                if (!validateField(input)) {
                    isFormValid = false;
                }
            });

            if (!isFormValid) {
                e.preventDefault();

                // Show error message
                const alertDiv = document.createElement('div');
                alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                alertDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle me-2"></i>
                Please fill in all required fields correctly.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;

                const existingAlert = form.querySelector('.alert');
                if (existingAlert) {
                    existingAlert.remove();
                }

                form.insertBefore(alertDiv, form.firstChild);

                // Scroll to top
                form.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });

        // Auto-focus first field
        document.getElementById('name').focus();
    });
</script>

<?php
$content = ob_get_clean();
require_once __DIR__ . '/../layouts/base.php';
echo $content;
?>
<?php unset($_SESSION['old_input']); ?>