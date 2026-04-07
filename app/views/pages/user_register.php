<?php
/**
 * User Registration Page
 */
$error = isset($error) ? $error : '';
$success = isset($success) ? $success : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .register-card { border: none; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .form-control { border-radius: 10px; padding: 12px 15px; }
        .form-control:focus { border-color: #667eea; box-shadow: 0 0 0 0.2rem rgba(102,126,234,0.25); }
        .btn-register { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 10px; padding: 12px; font-weight: 600; }
        .btn-register:hover { opacity: 0.9; transform: translateY(-2px); }
        .brand-logo { height: 60px; border-radius: 12px; }
        .input-group-text { border-radius: 10px 0 0 10px; border-right: none; }
    </style>
</head>
<body>
    <div class="container min-vh-100 d-flex align-items-center justify-content-center py-5">
        <div class="row w-100 justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card register-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <img src="<?php echo BASE_URL; ?>/assets/images/logo/apslogonew.jpg" alt="APS Dream Home" class="brand-logo mb-3">
                            <h4 class="fw-bold">Create Account</h4>
                            <p class="text-muted">Register to post properties and track inquiries</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo BASE_URL; ?>/user/register">
                            <div class="mb-3">
                                <label class="form-label">Full Name *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-user text-muted"></i>
                                    </span>
                                    <input type="text" name="name" class="form-control border-start-0" placeholder="Enter your full name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email Address *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-envelope text-muted"></i>
                                    </span>
                                    <input type="email" name="email" class="form-control border-start-0" placeholder="Enter your email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Phone Number *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-phone text-muted"></i>
                                    </span>
                                    <input type="tel" name="phone" class="form-control border-start-0" placeholder="Enter your phone number" required pattern="[0-9]{10}" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                                </div>
                                <small class="text-muted">10 digit mobile number</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" name="password" class="form-control border-start-0" placeholder="Create a password (min 6 characters)" required minlength="6">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Confirm Password *</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-lock text-muted"></i>
                                    </span>
                                    <input type="password" name="confirm_password" class="form-control border-start-0" placeholder="Confirm your password" required>
                                </div>
                            </div>

                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="terms" required>
                                <label class="form-check-label" for="terms">
                                    I agree to the <a href="<?php echo BASE_URL; ?>/terms" target="_blank">Terms of Service</a> and <a href="<?php echo BASE_URL; ?>/privacy" target="_blank">Privacy Policy</a>
                                </label>
                            </div>

                            <button type="submit" class="btn btn-register text-white w-100 mb-3">
                                <i class="fas fa-user-plus me-2"></i>Create Account
                            </button>
                        </form>

                        <hr class="my-4">

                        <p class="text-center text-muted mb-0">
                            Already have an account? 
                            <a href="<?php echo BASE_URL; ?>/user/login" class="fw-bold text-decoration-none">Login Now</a>
                        </p>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="<?php echo BASE_URL; ?>" class="text-white text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Back to Home
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
