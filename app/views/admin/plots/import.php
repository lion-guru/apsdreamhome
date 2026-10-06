<?php
/**
 * Import Plots View
 */
$page_title = $page_title ?? 'Import Plots';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link href="http://localhost/apsdreamhome/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="http://localhost/apsdreamhome/assets/css/premium-theme.css?v=12" rel="stylesheet">
    <link href="http://localhost/apsdreamhome/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
</head>
<body class="admin-body">
    <?php include __DIR__ . '/../layouts/admin_header.php'; ?>
    
    <main class="admin-main">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0"><?= htmlspecialchars($page_title) ?></h1>
                <div>
                    <a href="<?= BASE_URL ?>/admin/plots/import/template" class="btn btn-outline-secondary">
                        <i class="fas fa-download me-1"></i>Download Template
                    </a>
                    <a href="<?= BASE_URL ?>/admin/plots" class="btn btn-primary ms-2">
                        <i class="fas fa-arrow-left me-1"></i>Back to Plots
                    </a>
                </div>
            </div>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?= $_SESSION['success'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?= $_SESSION['error'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['warning'])): ?>
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <?= $_SESSION['warning'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['warning']); ?>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Import Plots from CSV</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Required Columns</h6>
                            <ul class="mb-0">
                                <li><strong>plot_number</strong> - Unique plot identifier</li>
                                <li><strong>colony_id</strong> - ID of the colony (must exist)</li>
                                <li><strong>area_sqft</strong> - Area in square feet</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Optional Columns</h6>
                            <ul class="mb-0 small">
                                <li>block, sector, plot_type, area_sqm, frontage_ft, depth_ft</li>
                                <li>price_per_sqft, total_price, status, description, features</li>
                                <li>facing, corner_plot, park_facing, road_width_ft</li>
                                <li>latitude, longitude, is_featured, is_active</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Notes:</strong>
                        <ul class="mb-0 mt-2">
                            <li>CSV file must have headers in the first row</li>
                            <li>Plot numbers must be unique within each colony</li>
                            <li>Colony IDs must exist in the system</li>
                            <li>Maximum file size: 5MB</li>
                            <li>Download the template below for the exact format</li>
                        </ul>
                    </div>
                    
                    <form method="POST" action="<?= BASE_URL ?>/admin/plots/import" enctype="multipart/form-data" class="mt-4">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        
                        <div class="mb-3">
                            <label for="import_file" class="form-label">Select CSV File</label>
                            <input type="file" class="form-control" id="import_file" name="import_file" accept=".csv" required>
                            <div class="form-text">Only CSV files accepted (max 5MB)</div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload me-1"></i>Import Plots
                            </button>
                            <a href="<?= BASE_URL ?>/admin/plots/import/template" class="btn btn-outline-secondary">
                                <i class="fas fa-download me-1"></i>Download Template
                            </a>
                            <a href="<?= BASE_URL ?>/admin/plots" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/../layouts/admin_footer.php'; ?>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
</body>
</html>