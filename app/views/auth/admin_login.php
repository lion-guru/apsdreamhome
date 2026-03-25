<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg border-0 login-card">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="mb-3">
                            <div class="admin-icon-wrapper">
                                <i class="fas fa-user-shield"></i>
                            </div>
                        </div>
                        <h2 class="card-title fw-bold gradient-text">Admin Login</h2>
                        <p class="text-muted">Welcome back! Access your admin dashboard</p>
                        <div class="divider">
                            <span>Secure Access Portal</span>
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

                    <form method="POST" action="<?php echo BASE_URL; ?>/admin/login" id="adminLoginForm" class="admin-login-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <div class="mb-4">
                            <label for="username" class="form-label fw-semibold">
                                <i class="fas fa-user me-2 text-primary"></i> Username / Email
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-user text-primary"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="username" name="username"
                                    placeholder="Enter username or email (admin@apsdreamhome.com)" required
                                    value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">
                                <i class="fas fa-lock me-2 text-primary"></i> Password
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-lock text-primary"></i>
                                </span>
                                <input type="password" class="form-control border-start-0 border-end-0" id="password" name="password"
                                    placeholder="Enter your secure password" required>
                                <button class="btn btn-outline-secondary border-start-0" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="toggleIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="captcha" class="form-label fw-semibold">
                                <i class="fas fa-robot me-2 text-primary"></i> <?php echo $captcha_question; ?>
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="fas fa-question-circle text-primary"></i>
                                </span>
                                <input type="text" class="form-control border-start-0" id="captcha" name="captcha"
                                    placeholder="Your answer" required>
                            </div>
                        </div>

                        <div class="d-grid mb-4">
                            <button type="submit" class="btn btn-primary btn-lg login-btn">
                                <i class="fas fa-sign-in-alt me-2"></i> Login to Dashboard
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>