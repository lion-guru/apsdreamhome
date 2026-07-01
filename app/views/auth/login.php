<?php
/**
 * Generic login view — redirects to role-specific login pages.
 * Used by AuthenticationController as a unified entry point.
 */
$role = $_GET['role'] ?? '';
$redirects = [
    'admin'     => '/admin/login',
    'customer'  => '/login',
    'associate' => '/associate/login',
    'agent'     => '/agent/login',
    'employee'  => '/employee/login',
];
if (isset($redirects[$role])) {
    header('Location: ' . BASE_URL . $redirects[$role]);
    exit;
}
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4"><i class="fas fa-sign-in-alt me-2"></i>Login</h4>
                    <form method="POST" action="<?= BASE_URL ?>/login">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($old['email'] ?? $_GET['email'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" name="remember" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                        <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $err): ?>
                            <div><?= htmlspecialchars($err) ?></div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="<?= BASE_URL ?>/forgot-password" class="text-decoration-none">Forgot Password?</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
