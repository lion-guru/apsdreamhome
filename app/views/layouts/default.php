<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'APS Dream Home'; ?> | <?php echo config('app.name'); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Styles -->
    <link href="<?php echo ASSET_URL; ?>css/style.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="bg-primary text-white py-3">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1 class="h4 mb-0">
                        <i class="fas fa-home me-2"></i>
                        <?php echo config('app.name'); ?>
                    </h1>
                </div>
                <div class="col-md-6 text-end">
                    <nav class="navbar navbar-expand-sm justify-content-end">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>">
                                    <i class="fas fa-home me-1"></i>Home
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>about">
                                    <i class="fas fa-info-circle me-1"></i>About
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>contact">
                                    <i class="fas fa-phone me-1"></i>Contact
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="py-5">
        <div class="container">
            <?php if (isset($breadcrumbs) && !empty($breadcrumbs)): ?>
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <?php foreach ($breadcrumbs as $breadcrumb): ?>
                    <li class="breadcrumb-item">
                        <a href="<?php echo $breadcrumb['url']; ?>"><?php echo $breadcrumb['title']; ?></a>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
            <?php endif; ?>

            <!-- Page Content -->
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h2 class="card-title text-center mb-4"><?php echo $page_title ?? 'Welcome'; ?></h2>

                            <?php if (isset($content)): ?>
                                <div class="content">
                                    <?php echo $content; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="fas fa-construction fa-3x mb-3 text-warning"></i>
                                    <p>This page is being organized with the new MVC structure.</p>
                                    <p>Content will be available soon!</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">
                        &copy; <?php echo date('Y'); ?> <?php echo config('app.name'); ?>.
                        All rights reserved.
                    </p>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-0">
                        Organized with <i class="fas fa-heart text-danger"></i> using MVC structure
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Scripts -->
    <script src="<?php echo ASSET_URL; ?>js/script.js"></script>
</body>
</html>
