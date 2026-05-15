
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Associate Dashboard - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Include sidebar functions
    require_once __DIR__ . '/sidebar_functions.php';
    
    // Check user role
    $userRole = $_SESSION['user_role'] ?? 'customer';
    ?>
    
    <div class="d-flex">
        <!-- Sidebar -->
        <?php renderSidebar($userRole); ?>
        
        <!-- Main Content -->
        <div class="main-content flex-grow-1">
            <?php include $content; ?>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
    