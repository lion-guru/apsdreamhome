<?php
$layout = 'layouts/base';
$page_title = $page_title ?? __('auth_employee_login') . ' - APS Dream Home';
$page_description = $page_description ?? 'Login to your APS Dream Home account';
?>

<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-building fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold"><?= __('auth_employee_login') ?></h2>
                            <p class="text-muted"><?= __('auth_employee_login_subtitle') ?></p>
                        </div>

                        <form action="<?php echo BASE_URL; ?>/login" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                            <div class="mb-3">
                                <label for="email" class="form-label"><?= __('email_address') ?></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label"><?= __('password') ?></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember"><?= __('remember_me') ?></label>
                            </div>
                            <?php echo \App\Helpers\SimpleCaptcha::renderField(); ?>
<button type="submit" class="btn btn-primary w-100 mb-3"><?= __('login') ?></button>

                            <!-- Google Login Button -->
                            <div class="text-center mb-3" class="style-98881">
                                <span class="style-4805"><?= __('or_continue_with') ?></span>
                                <div class="style-53230"></div>
                            </div>
                            <a href="<?php echo BASE_URL; ?>/auth/google" class="btn btn-outline-secondary w-100 mb-3" class="style-94542">
                                <svg width="18" height="18" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4" />
                                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853" />
                                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05" />
                                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335" />
                                </svg>
                                Continue with Google
                            </a>

                            <div class="text-center">
                                <a href="<?php echo BASE_URL; ?>/register" class="text-decoration-none"><?= __('auth_dont_have_account') ?> <?= __('register') ?></a>
                            </div>
                        </form>

                        <hr class="my-4">

                        <div class="text-center">
                            <p class="mb-2"><?= __('auth_login_as_text') ?></p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="<?php echo BASE_URL; ?>/associate/register" class="btn btn-outline-primary btn-sm"><?= __('register_associate') ?></a>
                                <a href="<?php echo BASE_URL; ?>/register" class="btn btn-outline-success btn-sm"><?= __('auth_register_as_customer_text') ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php include __DIR__ . '/../partials/_chatbot_icons.php'; ?>
</body></html>