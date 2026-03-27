<?php
/**
 * Admin Profile View
 */
if (!defined('BASE_PATH')) exit;

$user = $user ?? [];
$success = $_SESSION['success'] ?? null;
$error = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'My Profile'; ?> | APS Dream Home</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #4338ca;
            --sidebar-bg: #1e1b4b;
            --sidebar-hover: #312e81;
            --sidebar-active: #4f46e5;
            --sidebar-text: #e0e7ff;
            --sidebar-icon: #a5b4fc;
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
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .sidebar-nav { padding: 15px 10px; }
        
        .nav-item { margin-bottom: 2px; }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.2s;
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
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        
        .card-header {
            padding: 16px 20px;
            border-bottom: 1px solid var(--card-border);
            background: transparent;
        }
        
        .card-title { font-size: 1rem; font-weight: 600; margin: 0; }
        
        .card-body { padding: 20px; }
        
        .avatar-lg {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: var(--primary);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 600;
        }
        
        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
        }
        
        .form-control {
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 0.875rem;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .btn-primary {
            background: var(--primary);
            border-color: var(--primary);
            padding: 10px 20px;
            border-radius: 8px;
        }
        
        .btn-primary:hover {
            background: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        
        .role-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .role-super_admin { background: #7c3aed; color: #fff; }
        .role-admin { background: #dc2626; color: #fff; }
        .role-manager { background: #ea580c; color: #fff; }
        .role-employee { background: #0891b2; color: #fff; }
        
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
                <i class="fas fa-home"></i>
                <span>APS Dream Home</span>
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
                    <a href="<?php echo BASE_URL; ?>/admin/profile" class="nav-link active">
                        <i class="fas fa-user"></i> My Profile
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo BASE_URL; ?>/admin/profile/security" class="nav-link">
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
            <a href="<?php echo BASE_URL; ?>/admin/dashboard" class="text-decoration-none text-muted">
                <i class="fas fa-arrow-left me-2"></i> Back to Dashboard
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
            
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <div class="avatar-lg mx-auto mb-3">
                                <?php echo strtoupper(substr($user['name'] ?? $user['username'] ?? 'U', 0, 1)); ?>
                            </div>
                            <h5 class="mb-1"><?php echo htmlspecialchars($user['name'] ?? $user['username'] ?? 'User'); ?></h5>
                            <p class="text-muted mb-3"><?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
                            <span class="role-badge role-<?php echo $user['role'] ?? 'admin'; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $user['role'] ?? 'Admin')); ?>
                            </span>
                            
                            <hr class="my-4">
                            
                            <div class="text-start">
                                <p class="mb-2">
                                    <i class="fas fa-calendar me-2 text-muted"></i>
                                    <small class="text-muted">Member since</small><br>
                                    <strong><?php echo date('F Y', strtotime($user['created_at'] ?? date('Y-m-d'))); ?></strong>
                                </p>
                                <?php if (!empty($user['phone'])): ?>
                                <p class="mb-0">
                                    <i class="fas fa-phone me-2 text-muted"></i>
                                    <strong><?php echo htmlspecialchars($user['phone']); ?></strong>
                                </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-user me-2"></i>Profile Information</h5>
                        </div>
                        <div class="card-body">
                            <form action="<?php echo BASE_URL; ?>/admin/profile" method="POST">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Full Name</label>
                                        <input type="text" name="name" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" name="email" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Phone Number</label>
                                        <input type="text" name="phone" class="form-control" 
                                               value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Address</label>
                                        <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i> Save Changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="card-title"><i class="fas fa-shield-alt me-2"></i>Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <a href="<?php echo BASE_URL; ?>/admin/profile/security" class="btn btn-outline-primary">
                                <i class="fas fa-key me-2"></i> Change Password
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
