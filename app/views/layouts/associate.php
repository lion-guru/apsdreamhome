<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Associate Panel - APS Dream Home' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Dashboard CSS -->
    <link href="<?= BASE_URL ?>public/css/dashboard.css" rel="stylesheet">
    <?php if (!empty($extra_css)): ?>
        <?php foreach ($extra_css as $css): ?>
            <link href="<?= BASE_URL ?>public/css/<?= $css ?>" rel="stylesheet">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar associate-sidebar p-3">
            <div class="text-center mb-4">
                <h4><i class="fas fa-handshake me-2"></i>APS Partner</h4>
                <small class="text-light">Associate Panel</small>
            </div>

            <ul class="nav nav-pills flex-column">
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>associate/dashboard" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/associate/dashboard') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>associate/expenses" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/associate/expenses') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-receipt me-2"></i>My Expenses
                    </a>
                </li>
                <li class="nav-item mb-1">
                    <a href="<?= BASE_URL ?>associate/profile" class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], '/associate/profile') !== false) ? 'active' : '' ?>">
                        <i class="fas fa-user me-2"></i>My Profile
                    </a>
                </li>
            </ul>

            <div class="mt-auto">
                <a href="<?= BASE_URL ?>associate/logout" class="nav-link text-danger">
                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="flex-grow-1">
            <!-- Top Navbar -->
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
                <div class="container-fluid">
                    <button class="btn btn-outline-secondary d-md-none me-3" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>

                    <span class="navbar-brand mb-0 h1">
                        <i class="fas fa-user-tie me-2"></i>
                        Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Associate') ?>
                    </span>

                    <div class="d-flex align-items-center ms-auto">
                        <span class="me-3 d-none d-md-inline">
                            <i class="fas fa-clock me-1"></i>
                            <?= date('M d, Y') ?>
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-cog"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>associate/profile">
                                        <i class="fas fa-user me-2"></i>Profile
                                    </a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>associate/logout">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Content -->
            <main class="p-4">
                <!-- Flash Messages -->
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $_SESSION['success'];
                        unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $_SESSION['error'];
                        unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <!-- Content Injection -->
                <?php
                // This is where the view content will be injected
                // If $content variable exists (from Output Buffering), echo it
                if (isset($content)) {
                    echo $content;
                }
                ?>
            </main>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>public/js/dashboard.js"></script>

    <?php if (!empty($extra_js)): ?>
        <?php foreach ($extra_js as $js): ?>
            <script src="<?= BASE_URL ?>public/js/<?= $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            // Sidebar Toggle
            $('#sidebarToggle').on('click', function() {
                $('.sidebar').toggleClass('collapsed');
                $('.main-content').toggleClass('expanded');
            });

            // Mobile responsive check
            if ($(window).width() < 768) {
                $('.sidebar').addClass('collapsed');
            }
        });
    </script>
</body>

</html>