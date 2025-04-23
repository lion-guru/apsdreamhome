<?php
/**
 * Modern, dynamic header with fallback for APS Dream Homes
 * Tries to load menu, logo, styles from DB; on error, shows static trusted header
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!function_exists('get_asset_url')) {
    require_once __DIR__ . '/../includes/functions/common-functions.php';
}

// --- Dynamic logic ---
$dynamicError = false;
$headerMenuItems = $headerStyles = [];
$siteLogo = get_asset_url('logo/aps-logo.png', 'images'); // Use logo/aps-logo.png if it exists
$backgroundColor = '#1e3c72';
$textColor = '#ffffff';
try {
    require_once __DIR__ . '/../admin/config.php';
    require_once __DIR__ . '/../includes/db_config.php';
    $conn = function_exists('getDbConnection') ? getDbConnection() : null;
    $settings = [];
    if ($conn) {
        $sql = "SELECT * FROM site_settings WHERE setting_name IN ('header_menu_items', 'site_logo', 'header_styles')";
        $result = $conn->query($sql);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $settings[$row['setting_name']] = $row['value'];
            }
            $result->free();
        }
    }
    $headerMenuItems = json_decode($settings['header_menu_items'] ?? '[]', true);
    $headerStyles = json_decode($settings['header_styles'] ?? '{}', true);
    $siteLogo = $settings['site_logo'] ?? get_asset_url('logo/aps-logo.png', 'images');
    $backgroundColor = $headerStyles['background'] ?? '#1e3c72';
    $textColor = $headerStyles['text_color'] ?? '#ffffff';
} catch (Throwable $e) {
    $dynamicError = true;
}
?>
<?php if (!$dynamicError && !empty($headerMenuItems)): ?>
    <!-- DYNAMIC HEADER -->
    <header class="site-header">
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="<?php echo BASE_URL; ?>">
                    <img src="<?php echo $siteLogo; ?>" alt="APS Dream Homes Logo" style="height:40px;">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <?php foreach ($headerMenuItems as $item): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="<?php echo htmlspecialchars($item['url']); ?>" aria-label="<?php echo htmlspecialchars($item['text']); ?>">
                                    <?php echo htmlspecialchars($item['text']); ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <ul class="navbar-nav ms-auto">
                        <?php if(isset($_SESSION['uemail'])) { ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/profile.php">Profile</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/logout.php">Logout</a></li>
                        <?php } else { ?>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/login.php">Login</a></li>
                            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>/register.php">Register</a></li>
                        <?php } ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
<?php else: ?>
    <!-- STATIC FALLBACK HEADER -->
    <header class="header">
        <div class="top-header bg-secondary">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-6">
                        <span class="text-white small">7007444842 &nbsp; apsdreamhomes44@gmail.com</span>
                    </div>
                    <div class="col-lg-6 col-md-6 text-end">
                        <a href="<?php echo BASE_URL; ?>/login.php" class="text-white">Login</a> /
                        <a href="<?php echo BASE_URL; ?>/register.php" class="text-white">Register</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="main-header">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-3 col-md-3 col-9">
                        <div class="logo">
                            <a href="<?php echo BASE_URL; ?>/index.php">
                                <img src="<?php echo get_asset_url('aps-logo.png', 'images'); ?>" alt="APS Dream Homes Logo" class="img-fluid" style="max-height: 60px;">
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-9 col-md-9 col-3">
                        <div class="nav-outer">
                            <div class="mobile-nav-toggler"><span class="icon fas fa-bars"></span></div>
                            <nav class="main-menu navbar-expand-md navbar-light">
                                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                                    <ul class="navigation">
                                        <li><a href="<?php echo BASE_URL; ?>/index.php" aria-label="Home">Home</a></li>
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Project">Project</a>
                                            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                                                <li><a class="dropdown-item" href="#">Gorakhpur</a>
                                                    <ul class="submenu dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/gorakhpur-suryoday-colony.php">Suryoday Colony</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/gorakhpur-raghunath-nagri.php">Raghunath Nagri</a></li>
                                                    </ul>
                                                </li>
                                                <li><a class="dropdown-item" href="#">Lucknow</a>
                                                    <ul class="submenu dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/lucknow-ram-nagri.php">Ram Nagri</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/lucknow-nawab-city.php">Nawab City</a></li>
                                                    </ul>
                                                </li>
                                                <li><a class="dropdown-item" href="#">Kusinagar</a>
                                                    <ul class="submenu dropdown-menu">
                                                        <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/kusinagar-budha-city.php">Budha City</a></li>
                                                    </ul>
                                                </li>
                                            </ul>
                                        </li>
                                        <li><a href="<?php echo BASE_URL; ?>/gallery.php" aria-label="Gallery">Gallery</a></li>
                                        <li><a href="<?php echo BASE_URL; ?>/legal.php" aria-label="Legal">Legal</a></li>
                                        <li><a href="<?php echo BASE_URL; ?>/career.php" aria-label="Career">Career</a></li>
                                        <li><a href="<?php echo BASE_URL; ?>/about.php" aria-label="About Us">About Us</a></li>
                                        <li><a href="<?php echo BASE_URL; ?>/bank.php" aria-label="Bank">Bank</a></li>
                                        <li class="nav-item dropdown">
                                            <a class="nav-link dropdown-toggle" href="#" id="resellDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="Resell">Resell</a>
                                            <ul class="dropdown-menu" aria-labelledby="resellDropdown">
                                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/resell-properties.php">View Resell Properties</a></li>
                                                <li><a class="dropdown-item" href="<?php echo BASE_URL; ?>/add-property.php">Add Your Properties</a></li>
                                            </ul>
                                        </li>
                                        <?php if(isset($_SESSION['uemail'])) { ?>
                                            <li class="nav-item dropdown">
                                                <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="My Account">My Account</a>
                                                <ul class="dropdown-menu">
                                                    <li><a class="nav-link" href="profile.php" aria-label="Profile">Profile</a></li>
                                                    <li><a class="nav-link" href="logout.php" aria-label="Logout">Logout</a></li>
                                                </ul>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                </div>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
<?php endif; ?>