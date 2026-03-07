<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'APS Dream Home'; ?></title>
    <meta name="description" content="<?php echo $page_description ?? 'APS Dream Home - Premium Real Estate in Gorakhpur'; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?php echo BASE_URL; ?>/assets/css/header.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/animations.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/loading.css" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/header_new.php'; ?>
    
    <main>
        <?php echo $content ?? ''; ?>
    </main>
    
    <?php include __DIR__ . '/footer_new.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?php echo BASE_URL; ?>/assets/js/layout.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/premium-header.js"></script>
    <script src="<?php echo BASE_URL; ?>/assets/js/lead-capture.js"></script>
</body>
</html>
