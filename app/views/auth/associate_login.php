<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }
    .card {
        border-radius: 15px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    .card:hover {
        transform: translateY(-5px);
    }
    .btn-primary {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    .btn-primary:hover {
        background: linear-gradient(135deg, #20c997 0%, #28a745 100%);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
    }
    .form-control:focus {
        border-color: #28a745;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }
    .fa-3x {
        color: #28a745;
        margin-bottom: 1rem;
        animation: pulse 2s infinite;
    }
    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
</style>
<section class="py-5 bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                            <h2 class="fw-bold">Associate Login</h2>
                            <p class="text-muted">Access your associate dashboard</p>
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

                        <form action="<?php echo BASE_URL; ?>/associate/login" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email Address *</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Password *</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="row">
                                <div class="col-6 mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                                        <label class="form-check-label" for="remember">
                                            Remember me
                                        </label>
                                    </div>
                                </div>
                                <div class="col-6 mb-3 text-end">
                                    <a href="#" class="text-primary">Forgot Password?</a>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary btn-lg w-100">
                                        <i class="fas fa-sign-in-alt me-2"></i>Login as Associate
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">Don't have an account? 
                                <a href="<?php echo BASE_URL; ?>/associate/register" class="text-primary">Register here</a>
                            </p>
                            <p class="mb-0">Are you a customer? 
                                <a href="<?php echo BASE_URL; ?>/login" class="text-primary">Customer Login</a>
                            </p>
                            <p class="mb-0">Are you an agent? 
                                <a href="<?php echo BASE_URL; ?>/agent/login" class="text-primary">Agent Login</a>
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
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    
    form.addEventListener('submit', function(e) {
        if (!email.value || !password.value) {
            e.preventDefault();
            alert('Please fill in all fields');
            return false;
        }
        
        if (!email.value.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
            e.preventDefault();
            alert('Please enter a valid email address');
            email.focus();
            return false;
        }
    });
    
    // Auto-focus on email field
    email.focus();
});
</script>