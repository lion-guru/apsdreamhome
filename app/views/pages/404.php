<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body class="bg-light">
    <div class="container min-vh-100 d-flex align-items-center justify-content-center">
        <div class="text-center">
            <div class="display-1 text-muted mb-3">404</div>
            <h2 class="mb-3">Page Not Found</h2>
            <p class="text-muted mb-4">The page you are looking for does not exist or has been moved.</p>
            <a href="<?= BASE_URL ?? '/' ?>" class="btn btn-primary"><i class="fas fa-home me-2"></i>Go Home</a>
        </div>
    </div>
</body>
</html>