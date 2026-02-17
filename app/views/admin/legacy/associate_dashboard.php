<?php
// Associate Dashboard
require_once __DIR__ . '/core/init.php';

if (!isAuthenticated() || !in_array(getAuthSubRole(), ['associate', 'super_admin', 'superadmin'])) {
    header('Location: login.php?error=unauthorized');
    exit();
}

$associate = getAuthUsername() ?? 'Associate';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Associate Dashboard</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-end mb-3">
            <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <h1 class="mb-4 text-center text-info">Welcome, <?php echo h($associate); ?>!</h1>
        <p class="lead text-center">This is your Associate Dashboard. Here you can view your assigned tasks and leads.</p>
        <!-- Add associate-specific dashboard widgets here -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
