<?php require_once 'app/views/layouts/header.php'; ?>

<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-success text-white text-center py-4">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-user-plus mr-2"></i>Customer Registration
                    </h3>
                    <p class="mb-0">Join APS Dream Home - Your Property Journey Starts Here</p>
                </div>
                <div class="card-body p-5">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?= $error ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/customer/register">
                        <?php echo csrf_field(); ?>
                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label for="sponsor_id" class="form-label">
                                    <i class="fas fa-id-badge mr-2"></i>Sponsor ID (Optional)
                                </label>
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="sponsor_id"
                                       name="sponsor_id"
                                       placeholder="Enter Sponsor ID if you have one"
                                       value="<?= $_GET['ref'] ?? '' ?>">
                                <small class="text-muted">Enter the ID of the agent who referred you.</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-4">
                                <label for="name" class="form-label">
                                    <i class="fas fa-user mr-2"></i>Full Name
                                </label>
                                <input type="text"
                                       class="form-control form-control-lg"
                                       id="name"
                                       name="name"
                                       placeholder="Enter your full name"
                                       required
                                       autofocus>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="email" class="form-label">
                                    <i class="fas fa-envelope mr-2"></i>Email Address
                                </label>
                                <input type="email"
                                       class="form-control form-control-lg"
                                       id="email"
                                       name="email"
                                       placeholder="Enter your email"
                                       required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="phone" class="form-label">
                                    <i class="fas fa-phone mr-2"></i>Phone Number
                                </label>
                                <input type="tel"
                                       class="form-control form-control-lg"
                                       id="phone"
                                       name="phone"
                                       placeholder="Enter your phone number"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="password" class="form-label">
                                    <i class="fas fa-lock mr-2"></i>Password
                                </label>
                                <input type="password"
                                       class="form-control form-control-lg"
                                       id="password"
                                       name="password"
                                       placeholder="Create a password"
                                       required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="confirm_password" class="form-label">
                                    <i class="fas fa-lock mr-2"></i>Confirm Password
                                </label>
                                <input type="password"
                                       class="form-control form-control-lg"
                                       id="confirm_password"
                                       name="confirm_password"
                                       placeholder="Confirm your password"
                                       required>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="terms" name="terms" required>
                                <label class="custom-control-label" for="terms">
                                    I agree to the <a href="/terms" target="_blank">Terms and Conditions</a>
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success btn-lg btn-block mb-3">
                            <i class="fas fa-user-check mr-2"></i>Register Now
                        </button>
                    </form>
                </div>
                <div class="card-footer text-center bg-light">
                    <small class="text-muted">
                        Already have an account?
                        <a href="/customer/login" class="text-primary font-weight-bold">Login here</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
body {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    min-height: 100vh;
}

.card {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    background: linear-gradient(135deg, #28a745 0%, #218838 100%) !important;
}

.form-control {
    border-radius: 10px;
    border: 2px solid #e3e6f0;
    padding: 0.75rem 1rem;
    font-size: 1rem;
}

.form-control:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.btn-success {
    background: linear-gradient(135deg, #28a745 0%, #218838 100%);
    border: none;
    border-radius: 10px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-success:hover {
    background: linear-gradient(135deg, #218838 0%, #28a745 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
}

.alert {
    border-radius: 10px;
    border: none;
}

.text-primary {
    color: #667eea !important;
}
</style>

<?php require_once 'app/views/layouts/footer.php'; ?>
