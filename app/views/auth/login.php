<?php
/**
 * Generic login view ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬ï¿½ redirects to role-specific login pages.
 * Used by AuthenticationController as a unified entry point.
 */
$role = $_GET['role'] ?? '';
$redirectTo = $_GET['redirect'] ?? '';
$redirects = [
    'admin'     => '/admin/login',
    'customer'  => '/login',
    'associate' => '/associate/login',
    'agent'     => '/agent/login',
    'employee'  => '/employee/login',
];
if (isset($redirects[$role])) {
    $url = BASE_URL . $redirects[$role];
    if ($redirectTo) {
        $url .= '?redirect=' . urlencode($redirectTo);
    }
    header('Location: ' . $url);
    exit;
}
$contextMessages = [
    'mlm-dashboard'     => 'Login to view your MLM dashboard and track commissions',
    'user-dashboard'    => 'Login to access your account dashboard',
    'user-properties'   => 'Login to view your saved properties',
    'user-inquiries'    => 'Login to manage your property inquiries',
    'user-profile'      => 'Login to update your profile',
    'property-detail'   => 'Login to save this property to your favorites',
    'associate-network' => 'Login to view your MLM network tree',
    'associate-commissions' => 'Login to check your commission earnings',
    'checkout'          => 'Login to complete your booking',
    'booking'           => 'Login to proceed with booking',
];
$context = $contextMessages[$redirectTo] ?? '';
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <h4 class="text-center mb-4"><i class="fas fa-sign-in-alt me-2"></i>Login</h4>
                    <?php if ($context): ?>
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($context) ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" action="<?= BASE_URL ?>/login">
                        <input type="hidden" name="csrf_token" value="<?= $csrf_token ?? $_SESSION['csrf_token'] ?? '' ?>">
                        <?php if ($redirectTo): ?>
                            <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirectTo) ?>">
                        <?php endif; ?>
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
                        
<?php echo SimpleCaptcha::renderField("Enter Security Code"); ?>
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
