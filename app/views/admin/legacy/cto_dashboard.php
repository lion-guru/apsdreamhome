<?php
// CTO Dashboard
require_once __DIR__ . '/core/init.php';

if (!isAuthenticated() || !in_array(getAuthSubRole(), ['cto', 'super_admin', 'superadmin'])) {
    header('Location: login.php?error=unauthorized');
    exit();
}
$cto = getAuthUsername() ?? 'CTO';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CTO Dashboard</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-end mb-3">
            <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <h1 class="mb-4 text-center text-dark">Welcome, <?php echo h($cto); ?>!</h1>
        <p class="lead text-center">This is your CTO Dashboard. Here you can manage IT infrastructure and projects.</p>
        <!-- Add CTO-specific dashboard widgets here -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
