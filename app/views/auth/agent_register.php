<?php

/**
 * Agent Registration - APS Dream Home
 */

$layout = 'layouts/base';
$page_title = $page_title ?? 'Agent Registration - APS Dream Home';
$page_description = $page_description ?? 'Register as a property agent';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-briefcase fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold">Agent Registration</h2>
                            <p class="text-muted">Register as a property agent to sell properties</p>
                        </div>

                        <?php if (isset($_SESSION['errors'])): ?>
                            <div class="alert alert-danger">
                                <?php foreach ($_SESSION['errors'] as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                            <?php unset($_SESSION['errors']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['success'])): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($_SESSION['success']); ?>
                            </div>
                            <?php unset($_SESSION['success']); ?>
                        <?php endif; ?>

                        <form action="<?php echo BASE_URL; ?>/agent/register" method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="name" class="form-label">Full Name *</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($_SESSION['old_input']['name'] ?? ''); ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">Email Address *</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_SESSION['old_input']['email'] ?? ''); ?>" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">Phone Number *</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($_SESSION['old_input']['phone'] ?? ''); ?>" 
                                           pattern="[0-9]{10}" maxlength="10" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="license_no" class="form-label">License Number *</label>
                                    <input type="text" class="form-control" id="license_no" name="license_no" 
                                           value="<?php echo htmlspecialchars($_SESSION['old_input']['license_no'] ?? ''); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="experience" class="form-label">Experience (Years)</label>
                                    <select class="form-control" id="experience" name="experience">
                                        <option value="">Select Experience</option>
                                        <option value="0-1" <?php echo (($_SESSION['old_input']['experience'] ?? '') === '0-1') ? 'selected' : ''; ?>>0-1 Year</option>
                                        <option value="1-3" <?php echo (($_SESSION['old_input']['experience'] ?? '') === '1-3') ? 'selected' : ''; ?>>1-3 Years</option>
                                        <option value="3-5" <?php echo (($_SESSION['old_input']['experience'] ?? '') === '3-5') ? 'selected' : ''; ?>>3-5 Years</option>
                                        <option value="5+" <?php echo (($_SESSION['old_input']['experience'] ?? '') === '5+') ? 'selected' : ''; ?>>5+ Years</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="referral_code" class="form-label">Referral Code (Optional)</label>
                                    <input type="text" class="form-control" id="referral_code" name="referral_code" 
                                           value="<?php echo htmlspecialchars($_SESSION['old_input']['referral_code'] ?? ''); ?>"
                                           placeholder="Enter referral code if you have one">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password" class="form-label">Password *</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="6" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="confirm_password" class="form-label">Confirm Password *</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="6" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                        <label class="form-check-label" for="terms">
                                            I agree to the <a href="#" target="_blank">Terms & Conditions</a> and 
                                            <a href="#" target="_blank">Privacy Policy</a>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-briefcase me-2"></i>Register as Agent
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">Already have an account? 
                                <a href="<?php echo BASE_URL; ?>/agent/login" class="text-primary">Login here</a>
                            </p>
                            <p class="mb-0">Want to register as a customer? 
                                <a href="<?php echo BASE_URL; ?>/register" class="text-primary">Register as Customer</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    form.addEventListener('submit', function(e) {
        if (password.value !== confirmPassword.value) {
            e.preventDefault();
            alert('Passwords do not match!');
            confirmPassword.focus();
        }
    });
});
</script>

<?php unset($_SESSION['old_input']); ?>