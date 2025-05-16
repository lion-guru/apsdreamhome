<?php
// CFO Dashboard
require_once(__DIR__ . '/includes/session_manager.php');
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || $_SESSION['admin_role'] !== 'cfo') {
    header('Location: login.php?error=unauthorized');
    exit();
}
$cfo = $_SESSION['admin_username'] ?? 'CFO';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CFO Dashboard</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="d-flex justify-content-end mb-3">
            <a href="logout.php" class="btn btn-outline-danger btn-sm"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <h1 class="mb-4 text-center text-warning">Welcome, <?php echo htmlspecialchars($cfo); ?>!</h1>
        <p class="lead text-center">This is your CFO Dashboard. Here you can manage financial reports and analytics.</p>
        <!-- Add CFO-specific dashboard widgets here -->
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
