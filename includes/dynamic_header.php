<?php
require_once __DIR__ . '/../admin/config.php';
require_once __DIR__ . '/../includes/db_config.php';

// Fetch header settings from database
$conn = getDbConnection();
$settings = [];
if ($conn) {
    $sql = "SELECT * FROM site_settings WHERE setting_name IN ('header_menu_items', 'site_logo', 'header_styles')";
    try {
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['setting_name']] = $row['value'];
            }
            $result->free();
        }
    } catch (Exception $e) {
        // If site_settings table is missing or another DB error, use safe defaults
        $settings = [];
    }
}

$headerMenuItems = json_decode($settings['header_menu_items'] ?? '[]', true);
$headerStyles = json_decode($settings['header_styles'] ?? '{}', true);
$siteLogo = $settings['site_logo'] ?? get_asset_url('logo.png', 'images');

// Apply default styles if not set
$backgroundColor = $headerStyles['background'] ?? '#1e3c72';
$textColor = $headerStyles['text_color'] ?? '#ffffff';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Real Estate</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    
    <!-- Custom Header Styles -->
    <style>
        .site-header {
            background-color: <?php echo $backgroundColor; ?>;
            color: <?php echo $textColor; ?>;
            padding: 1rem 0;
        }
        .site-header a {
            color: <?php echo $textColor; ?>;
            text-decoration: none;
        }
        .site-header a:hover {
            color: rgba(255, 255, 255, 0.8);
        }
        .site-logo img {
            max-height: 60px;
            width: auto;
        }
        .nav-item {
            margin: 0 10px;
        }
        .nav-item i {
            margin-right: 5px;
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-3">
                <a href="/" class="site-logo">
                    <img src="<?php echo htmlspecialchars($siteLogo); ?>" alt="APS Real Estate Logo">
                </a>
            </div>
            <div class="col-md-9">
                <nav class="navbar navbar-expand-lg">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav ms-auto">
                            <?php foreach ($headerMenuItems as $item): ?>
                                <li class="nav-item">
                                    <a class="nav-link" href="<?php echo htmlspecialchars($item['url']); ?>">
                                        <?php if (!empty($item['icon'])): ?>
                                            <i class="fas <?php echo htmlspecialchars($item['icon']); ?>"></i>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($item['text']); ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>
    </div>
</header>

<!-- Bootstrap JS Bundle with Popper -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>