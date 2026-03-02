<div class="container-fluid mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-primary text-white text-center py-4">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-users mr-2"></i>Associate Login
                    </h3>
                    <p class="mb-0">APS Dream Home - MLM Partner Portal</p>
                </div>
                <div class="card-body p-5">
                    <?php if (isset($_SESSION['login_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            <?= $_SESSION['login_error'] ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/associate/authenticate">
                        <div class="form-group mb-4">
                            <label for="login_id" class="form-label">
                                <i class="fas fa-user mr-2"></i>Email or Mobile
                            </label>
                            <input type="text"
                                   class="form-control form-control-lg"
                                   id="login_id"
                                   name="login_id"
                                   placeholder="Enter your email or mobile"
                                   required
                                   autofocus>
                        </div>

                        <div class="form-group mb-4">
                            <label for="password" class="form-label">
                                <i class="fas fa-lock mr-2"></i>Password
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control form-control-lg"
                                       id="password"
                                       name="password"
                                       placeholder="Enter your password"
                                       required>
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                        <i class="fas fa-eye" id="passwordToggle"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="remember" name="remember">
                                <label class="custom-control-label" for="remember">
                                    Remember me
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg btn-block mb-3">
                            <i class="fas fa-sign-in-alt mr-2"></i>Login to Dashboard
                        </button>

                        <div class="text-center">
                            <a href="/associate/forgot-password" class="text-muted">
                                <i class="fas fa-question-circle mr-1"></i>Forgot Password?
                            </a>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center bg-light">
                    <small class="text-muted">
                        Don't have an account?
                        <a href="/associate/register" class="text-primary font-weight-bold">Register here</a>
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var passwordField = document.getElementById('password');
    var toggleIcon = document.getElementById('passwordToggle');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
}

// Auto-focus on email field
document.getElementById('email').focus();
</script>

<style>
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
}

.card {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}

.card-header {
    border-radius: 15px 15px 0 0 !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
}

.form-control {
    border-radius: 10px;
    border: 2px solid #e3e6f0;
    padding: 0.75rem 1rem;
    font-size: 1rem;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    border-radius: 10px;
    padding: 0.75rem 2rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary:hover {
    background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.input-group-append .btn {
    border-radius: 0 10px 10px 0;
    border-left: none;
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
