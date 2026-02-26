<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0f2b66 0%, #1b5fd0 50%, #0f2b66 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .login-header h1 {
            color: #0f2b66;
            font-size: 2rem;
            margin-bottom: 10px;
        }
        .login-header p {
            color: #666;
            margin-bottom: 0;
        }
        .form-control:focus {
            border-color: #1b5fd0;
            box-shadow: 0 0 0 0.2rem rgba(27, 95, 208, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #1b5fd0, #0f2b66);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #0f2b66, #1b5fd0);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        .back-link a {
            color: white;
            text-decoration: none;
            font-weight: 500;
        }
        .alert {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1><i class="fas fa-shield-alt me-2"></i>Admin Login</h1>
            <p>APS Dream Home Admin Panel</p>
        </div>
        
        <?php
        // Handle login form submission
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Simple authentication (in production, use proper database authentication)
            if ($username === 'admin' && $password === 'admin123') {
                // Set session
                session_start();
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $username;
                
                // Redirect to admin dashboard
                header('Location: ' . BASE_URL . 'admin/dashboard');
                exit;
            } else {
                $error = 'Invalid username or password';
            }
        }
        ?>
        
        <?php if ($error): ?>
        <div class="alert alert-danger" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">
                    <i class="fas fa-user me-2"></i>Username
                </label>
                <input type="text" class="form-control" id="username" name="username" 
                       placeholder="Enter username" required>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-2"></i>Password
                </label>
                <input type="password" class="form-control" id="password" name="password" 
                       placeholder="Enter password" required>
            </div>
            
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember">
                <label class="form-check-label" for="remember">
                    Remember me
                </label>
            </div>
            
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt me-2"></i>Login
            </button>
        </form>
        
        <div class="back-link">
            <a href="<?php echo BASE_URL; ?>">
                <i class="fas fa-arrow-left me-2"></i>Back to Website
            </a>
        </div>
        
        <div class="mt-4 p-3 bg-light rounded">
            <h6><i class="fas fa-info-circle me-2"></i>Default Login Credentials:</h6>
            <p class="mb-0"><strong>Username:</strong> admin</p>
            <p class="mb-0"><strong>Password:</strong> admin123</p>
            <small class="text-muted">Please change these credentials in production</small>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
