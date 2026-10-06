<?php
/**
 * System Configuration Import View
 */
$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/css/style.css?v=7" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/../layouts/admin.php'; ?>
    
    <main class="main-content">
        <header class="top-header">
            <div>
                <h1 class="page-title"><?= htmlspecialchars($page_title) ?></h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?= $base ?>/admin/erp">Admin</a></li>
                        <li class="breadcrumb-item"><a href="<?= $base ?>/admin/system-config">System Config</a></li>
                        <li class="breadcrumb-item active">Import</li>
                    </ol>
                </nav>
            </div>
            <div class="header-actions">
                <a href="<?= $base ?>/admin/system-config" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </header>

        <div class="content-wrapper">
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($_SESSION['error']) ?>
                    <?php unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($_SESSION['success']) ?>
                    <?php unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card aps-cp-card">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Import Configuration</h5>
                        </div>
                        <div class="card-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Import Format:</strong> JSON file with configuration key-value pairs.
                                Each config should have: <code>value</code>, <code>type</code>, <code>group</code> (optional), <code>description</code> (optional).
                            </div>

                            <form method="POST" action="<?= $base ?>/admin/system-config/import" enctype="multipart/form-data">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                
                                <div class="mb-4">
                                    <label class="form-label">Configuration File</label>
                                    <input type="file" name="config_file" class="form-control" accept=".json" required>
                                    <div class="form-text">Select a JSON configuration file to import</div>
                                </div>

                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="overwrite" id="overwrite" value="1" checked>
                                        <label class="form-check-label" for="overwrite">
                                            Overwrite existing configurations (uncheck to skip existing)
                                        </label>
                                    </div>
                                </div>

                                <hr>

                                <h6>Example JSON Format:</h6>
                                <pre class="bg-light p-3 rounded small"><code>{
  "app_name": {
    "value": "APS Dream Home",
    "type": "string",
    "group": "general",
    "description": "Application name"
  },
  "maintenance_mode": {
    "value": false,
    "type": "boolean",
    "group": "system",
    "description": "Enable maintenance mode"
  },
  "max_upload_size": {
    "value": 10,
    "type": "integer",
    "group": "uploads",
    "description": "Max upload size in MB"
  }
}</code></pre>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-1"></i> Import Configuration
                                    </button>
                                    <a href="<?= $base ?>/admin/system-config" class="btn btn-outline-secondary">
                                        <i class="fas fa-times me-1"></i> Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/../layouts/admin.php'; ?>
</body>
</html>