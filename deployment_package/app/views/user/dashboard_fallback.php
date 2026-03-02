<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/apsdreamhome/');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title ?? 'Dashboard - APS Dream Home'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
        }
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 1rem;
        }
        .quick-action-btn {
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1><i class="fas fa-tachometer-alt me-3"></i>Dashboard</h1>
                    <p class="mb-0">Welcome back! Here's your personal dashboard overview.</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-outline-light">
                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-4">
        <div class="row">
            <div class="col-md-3 mb-4">
                <div class="stats-card text-center">
                    <i class="fas fa-calendar-check fa-2x text-primary mb-3"></i>
                    <h4>0</h4>
                    <p class="text-muted mb-0">My Bookings</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card text-center">
                    <i class="fas fa-heart fa-2x text-danger mb-3"></i>
                    <h4>0</h4>
                    <p class="text-muted mb-0">Favorites</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card text-center">
                    <i class="fas fa-comments fa-2x text-info mb-3"></i>
                    <h4>0</h4>
                    <p class="text-muted mb-0">Messages</p>
                </div>
            </div>
            <div class="col-md-3 mb-4">
                <div class="stats-card text-center">
                    <i class="fas fa-bell fa-2x text-warning mb-3"></i>
                    <h4>0</h4>
                    <p class="text-muted mb-0">Notifications</p>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>Quick Search</h5>
                    </div>
                    <div class="card-body">
                        <a href="<?php echo BASE_URL; ?>properties" class="btn btn-primary quick-action-btn w-100">
                            <i class="fas fa-home me-2"></i>Browse Properties
                        </a>
                        <a href="<?php echo BASE_URL; ?>projects" class="btn btn-success quick-action-btn w-100">
                            <i class="fas fa-building me-2"></i>View Projects
                        </a>
                        <a href="<?php echo BASE_URL; ?>contact" class="btn btn-info quick-action-btn w-100">
                            <i class="fas fa-phone me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-user me-2"></i>My Account</h5>
                    </div>
                    <div class="card-body">
                        <a href="<?php echo BASE_URL; ?>dashboard/profile" class="btn btn-outline-primary quick-action-btn w-100">
                            <i class="fas fa-user-edit me-2"></i>Edit Profile
                        </a>
                        <a href="<?php echo BASE_URL; ?>dashboard/favorites" class="btn btn-outline-danger quick-action-btn w-100">
                            <i class="fas fa-heart me-2"></i>My Favorites
                        </a>
                        <a href="<?php echo BASE_URL; ?>dashboard/bookings" class="btn btn-outline-success quick-action-btn w-100">
                            <i class="fas fa-calendar me-2"></i>My Bookings
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
