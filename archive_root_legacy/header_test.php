<?php
// Simple test page to verify header is working
$page_title = 'Header Test - APS Dream Home';

$content = '
<div class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="text-center mb-4">Header Functionality Test</h1>

            <div class="alert alert-info">
                <h4><i class="fas fa-info-circle me-2"></i>Test Instructions:</h4>
                <ul>
                    <li>Check if the header navbar is visible and fits within the screen</li>
                    <li>Click on each menu item to ensure they respond</li>
                    <li>Test the dropdown menus by hovering/clicking</li>
                    <li>Try the responsive mobile menu (resize window)</li>
                    <li>Test the Login/Register buttons</li>
                </ul>
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-check-circle me-2"></i>Expected Features</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i>Responsive navbar</li>
                                <li><i class="fas fa-check text-success me-2"></i>Working dropdown menus</li>
                                <li><i class="fas fa-check text-success me-2"></i>Clickable menu items</li>
                                <li><i class="fas fa-check text-success me-2"></i>Mobile hamburger menu</li>
                                <li><i class="fas fa-check text-success me-2"></i>Login/Register buttons</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-bug me-2"></i>Previously Fixed Issues</h5>
                        </div>
                        <div class="card-body">
                            <ul class="list-unstyled">
                                <li><i class="fas fa-times text-danger me-2"></i>Header going outside screen</li>
                                <li><i class="fas fa-times text-danger me-2"></i>Menu items not responding to clicks</li>
                                <li><i class="fas fa-times text-danger me-2"></i>CSS overflow issues</li>
                                <li><i class="fas fa-times text-danger me-2"></i>Bootstrap dropdown conflicts</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="/" class="btn btn-primary me-2">
                    <i class="fas fa-home me-2"></i>Go to Homepage
                </a>
                <a href="properties" class="btn btn-success me-2">
                    <i class="fas fa-building me-2"></i>Test Properties
                </a>
                <a href="about" class="btn btn-info">
                    <i class="fas fa-info-circle me-2"></i>Test About
                </a>
            </div>
        </div>
    </div>
</div>
';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Include the header -->
    <?php require_once 'includes/templates/header.php'; ?>

    <!-- Main Content -->
    <main>
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> APS Dream Home. All rights reserved.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
