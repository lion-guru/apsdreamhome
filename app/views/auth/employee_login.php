<?php
$layout = 'layouts/base';
$page_title = $page_title ?? 'Login - APS Dream Home';
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
                            <h2 class="fw-bold">Employee Login</h2>
                            <p class="text-muted">Access your employee dashboard</p>
                        </div>

                        <form action="<?php echo BASE_URL; ?>/login" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 mb-3">Login</button>
                            
                            <div class="text-center">
                                <a href="<?php echo BASE_URL; ?>/register" class="text-decoration-none">Don't have an account? Register</a>
                            </div>
                        </form>

                        <hr class="my-4">
                        
                        <div class="text-center">
                            <p class="mb-2">Login as:</p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="<?php echo BASE_URL; ?>/associate/register" class="btn btn-outline-primary btn-sm">Associate</a>
                                <a href="<?php echo BASE_URL; ?>/register" class="btn btn-outline-success btn-sm">Customer</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>