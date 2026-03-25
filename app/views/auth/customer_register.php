

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-lg border-0">
                <div class="card-body p-5">
                    <div class="text-center mb-5">
                        <div class="mb-3">
                            <i class="fas fa-home fa-3x text-primary"></i>
                        </div>
                        <h2 class="card-title fw-bold mb-3">Customer Registration</h2>
                        <p class="text-muted lead">Join APS Dream Home and discover your dream property</p>
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

                    <form method="POST" action="<?php echo BASE_URL; ?>/register" id="customerRegistrationForm" class="registration-form">
                        <?php
                        // Generate CSRF token if not exists
                        if (!isset($_SESSION['csrf_token'])) {
                            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                        }
                        $csrf_token = $_SESSION['csrf_token'];
                        ?>
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        <input type="hidden" name="user_type" value="customer">

                        <!-- Personal Information -->
                        <div class="registration-section mb-4">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-user me-2 text-primary"></i>Personal Information
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
                                    <label for="referral_code" class="form-label">
                                        <i class="fas fa-gift me-1"></i> Referral Code (Optional)
                                    </label>
                                    <input type="text" class="form-control" id="referral_code" name="referral_code"
                                        placeholder="Enter referral code if you have one"
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['referral_code'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Address Information -->
                        <div class="registration-section mb-4">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-map-marker-alt me-2 text-primary"></i>Address Information
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
                                <i class="fas fa-lock me-2 text-primary"></i>Account Information
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

                        <!-- Preferences -->
                        <div class="registration-section mb-4">
                            <h4 class="section-title mb-4">
                                <i class="fas fa-heart me-2 text-primary"></i>Property Preferences
                            </h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="property_type" class="form-label">
                                        <i class="fas fa-building me-1"></i> Preferred Property Type
                                    </label>
                                    <select class="form-select" id="property_type" name="property_type">
                                        <option value="">Select Property Type</option>
                                        <option value="apartment" <?php echo ($_SESSION['old_input']['property_type'] ?? '') === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                        <option value="villa" <?php echo ($_SESSION['old_input']['property_type'] ?? '') === 'villa' ? 'selected' : ''; ?>>Villa</option>
                                        <option value="house" <?php echo ($_SESSION['old_input']['property_type'] ?? '') === 'house' ? 'selected' : ''; ?>>House</option>
                                        <option value="plot" <?php echo ($_SESSION['old_input']['property_type'] ?? '') === 'plot' ? 'selected' : ''; ?>>Plot</option>
                                        <option value="commercial" <?php echo ($_SESSION['old_input']['property_type'] ?? '') === 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="budget_range" class="form-label">
                                        <i class="fas fa-rupee-sign me-1"></i> Budget Range
                                    </label>
                                    <select class="form-select" id="budget_range" name="budget_range">
                                        <option value="">Select Budget Range</option>
                                        <option value="10-25" <?php echo ($_SESSION['old_input']['budget_range'] ?? '') === '10-25' ? 'selected' : ''; ?>>₹10-25 Lakhs</option>
                                        <option value="25-50" <?php echo ($_SESSION['old_input']['budget_range'] ?? '') === '25-50' ? 'selected' : ''; ?>>₹25-50 Lakhs</option>
                                        <option value="50-75" <?php echo ($_SESSION['old_input']['budget_range'] ?? '') === '50-75' ? 'selected' : ''; ?>>₹50-75 Lakhs</option>
                                        <option value="75-100" <?php echo ($_SESSION['old_input']['budget_range'] ?? '') === '75-100' ? 'selected' : ''; ?>>₹75-100 Lakhs</option>
                                        <option value="100+" <?php echo ($_SESSION['old_input']['budget_range'] ?? '') === '100+' ? 'selected' : ''; ?>>₹1 Crore+</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="preferred_location" class="form-label">
                                        <i class="fas fa-map-pin me-1"></i> Preferred Location
                                    </label>
                                    <input type="text" class="form-control" id="preferred_location" name="preferred_location"
                                        placeholder="Enter your preferred locations (comma separated)"
                                        value="<?php echo htmlspecialchars($_SESSION['old_input']['preferred_location'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="registration-section mb-4">
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="#" class="text-primary">Terms and Conditions</a> and <a href="#" class="text-primary">Privacy Policy</a> *
                                </label>
                                <div class="invalid-feedback">You must agree to the terms and conditions</div>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="newsletter" name="newsletter">
                                <label class="form-check-label" for="newsletter">
                                    Send me property updates and exclusive offers via email
                                </label>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <small class="text-muted">
                            Already have an account?
                            <a href="<?php echo BASE_URL; ?>/login" class="text-decoration-none">
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
        border-bottom: 2px solid #3498db;
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

    .btn-primary {
        background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, #2980b9 0%, #3498db 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(52, 152, 219, 0.3);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }

    .fa-3x {
        color: #3498db;
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
        border-color: #3498db;
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
