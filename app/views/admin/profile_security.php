<?php
/**
 * Admin Profile Security View
 */
if (!defined('BASE_PATH')) exit;

$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Security Settings'; ?> | APS Dream Home</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --sidebar-bg: #1e1b4b;
            --main-bg: #f8fafc;
            --card-border: #e2e8f0;
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Inter', sans-serif;
            background: var(--main-bg);
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 260px;
            height: 100vh;
            background: var(--sidebar-bg);
            z-index: 1000;
        }
        
        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-logo {
            color: #fff;
            font-size: 1.25rem;
            font-weight: 700;
            text-decoration: none;
        }
        
        .sidebar-nav { padding: 15px 10px; }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.875rem;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255,255,255,0.1);
            color: #fff;
        }
        
        .nav-link i { width: 24px; margin-right: 10px; }
        
        .main-content { margin-left: 260px; min-height: 100vh; }
        
        .top-navbar {
            background: #fff;
            height: 64px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--card-border);
        }
        
        .page-content { padding: 24px; }
        
        .card {
            background: #fff;
            border: 1px solid var(--card-border);
            border-radius: 12px;
        }
        
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--card-border);
        }
        
        .card-title { font-size: 1rem; font-weight: 600; margin: 0; }
        
        .card-body { padding: 20px; }
        
        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .form-control {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            padding: 10px 20px;
            border-radius: 8px;
        }
        
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
        }
        
        .password-wrapper {
            position: relative;
        }
        
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="sidebar-logo">
                <i class="fas fa-home me-2"></i> APS Dream Home
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="nav-link">
                        <i class="fas fa-chart-pie"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/profile" class="nav-link">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/profile/security" class="nav-link active">
                        <i class="fas fa-shield-alt"></i> Security
                    </a>
                </li>
                <li class="nav-item mt-3">
                    <a href="<?php echo BASE_URL; ?>/admin/logout" class="nav-link text-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </nav>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content">
        <nav class="top-navbar">
            <a href="<?php echo BASE_URL; ?>/admin/profile" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-2"></i> Back to Profile
            </a>
        </nav>
        
        <div class="page-content">
            <?php if ($success): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?php echo $success; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-key me-2"></i>Change Password</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo BASE_URL; ?>/admin/profile/change-password" method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Current Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" name="current_password" class="form-control" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">New Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" name="new_password" class="form-control" required minlength="8">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted">Must be at least 8 characters</small>
                                </div>
                                
                                <div class="mb-4">
                                    <label class="form-label">Confirm New Password</label>
                                    <div class="password-wrapper">
                                        <input type="password" name="confirm_password" class="form-control" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i> Update Password
                                </button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-shield-alt me-2"></i>Security Tips</h5>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li class="mb-2">Use a strong password with at least 8 characters</li>
                                <li class="mb-2">Include uppercase, lowercase, numbers, and symbols</li>
                                <li class="mb-2">Don't reuse passwords from other accounts</li>
                                <li class="mb-2">Change your password periodically</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(btn) {
            const input = btn.parentElement.querySelector('input');
            const icon = btn.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }
    </script>
</body>
</html>
