<?php

// TODO: Add proper error handling with try-catch blocks

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Admin Login - APS Dream Homes'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/css/admin-login.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            background-color: #f8f9fa;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            box-sizing: border-box;
        }

        .error {
            color: red;
            margin-bottom: 10px;
        }

        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body>
    <div class="login-container">
        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo h($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="login-header" style="display:flex;flex-direction:column;align-items:center;gap:0.4rem;margin-bottom:1.2rem;">
            <div class="panel-title" style="font-size:1.45rem;font-weight:700;color:#0d6efd;letter-spacing:1px;">APS Dream Homes</div>
            <div style="font-size:1.05rem;color:#444;font-weight:500;">Admin Panel Login</div>
            <div class="panel-desc" style="font-size:0.98rem;color:#666;">Welcome! Only authorized personnel may proceed.</div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/admin/login" method="post" id="adminLoginForm" class="admin-login-form" autocomplete="off" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" class="form-control" id="username" name="username" required autofocus autocomplete="username">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
            </div>
            <?php if (isset($captcha_question)): ?>
                <div class="mb-3">
                    <label for="captcha_answer" class="form-label">Security Question: <?php echo htmlspecialchars($captcha_question); ?></label>
                    <input type="number" class="form-control" id="captcha_answer" name="captcha_answer" required>
                </div>
            <?php endif; ?>

            <?php if (isset($csrf_token)): ?>
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            <?php endif; ?>

            <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
            <div class="text-center">
                <a href="#" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Forgot Password?</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required autocomplete="email">
    </div>
    </div>

    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <label for="password" class="form-label fw-semibold text-secondary">Password</label>
            <a href="<?php echo BASE_URL; ?>forgot-password" class="small text-decoration-none">Forgot password?</a>
        </div>
        <div class="input-group input-group-lg position-relative">
            <span class="input-group-text bg-primary-subtle text-primary-emphasis border-0">
                <i class="fas fa-lock"></i>
            </span>
            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required autocomplete="current-password">
            <button type="button" class="btn btn-link text-decoration-none position-absolute top-50 end-0 translate-middle-y me-3" onclick="togglePassword()">
                <i class="fas fa-eye" id="passwordToggle"></i>
            </button>
        </div>
    </div>

    <div class="col-12">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="remember" name="remember">
            <label class="form-check-label" for="remember">Remember me for 30 days</label>
        </div>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary btn-lg w-100">Sign In</button>
    </div>

    <div class="col-12 text-center">
        <p class="mb-0 text-secondary">Don’t have an account?
            <a href="<?php echo BASE_URL; ?>register" class="fw-semibold text-decoration-none">Create one now</a>
        </p>
    </div>
    </form>
    </div>
    </div>

    <div class="row g-4 mt-4">
        <div class="col-md-4 text-center">
            <div class="p-3 bg-white border rounded-4 shadow-sm h-100">
                <i class="fas fa-shield-alt fa-2x text-primary mb-3"></i>
                <h6 class="fw-semibold">Secure Access</h6>
                <p class="small text-muted mb-0">Bank-grade encryption for every session.</p>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <div class="p-3 bg-white border rounded-4 shadow-sm h-100">
                <i class="fas fa-home fa-2x text-success mb-3"></i>
                <h6 class="fw-semibold">Exclusive Listings</h6>
                <p class="small text-muted mb-0">Access 500+ curated properties.</p>
            </div>
        </div>
        <div class="col-md-4 text-center">
            <div class="p-3 bg-white border rounded-4 shadow-sm h-100">
                <i class="fas fa-headset fa-2x text-warning mb-3"></i>
                <h6 class="fw-semibold">24/7 Support</h6>
                <p class="small text-muted mb-0">Dedicated advisors at every step.</p>
            </div>
        </div>
    </div>
    </div>
    </div>
    </div>
    </section
        </body>

</html>


// Merged from: C:\xampp\htdocs\apsdreamhome\app\Controllers/..\views\auth\login.php

function togglePassword() {
const passwordInput = document.getElementById('password');
const toggleIcon = document.getElementById('passwordToggle');

if (passwordInput.type === 'password') {
passwordInput.type = 'text';
toggleIcon.classList.remove('fa-eye');
toggleIcon.classList.add('fa-eye-slash');
}