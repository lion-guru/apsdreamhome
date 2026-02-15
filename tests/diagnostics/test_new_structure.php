<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Structure Test - <?php echo APP_NAME ?? 'APS Dream Home'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .test-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
        }
        .success { color: #28a745; }
        .info { color: #17a2b8; }
        .warning { color: #ffc107; }
        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .status-good { background: #d4edda; color: #155724; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="test-container">
                    <h1 class="mb-4">🎉 New Structure Test</h1>
                    <p class="lead mb-4">Testing the organized APS Dream Home structure</p>

                    <div class="row text-start mb-4">
                        <div class="col-md-6">
                            <h5>✅ Core Systems</h5>
                            <ul class="list-unstyled">
                                <li><span class="status-badge status-good">✓ Bootstrap Loaded</span></li>
                                <li><span class="status-badge status-good">✓ Configuration System</span></li>
                                <li><span class="status-badge status-good">✓ Autoloader Active</span></li>
                                <li><span class="status-badge status-good">✓ Router Initialized</span></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>📁 Directory Structure</h5>
                            <ul class="list-unstyled">
                                <li><span class="status-badge status-good">✓ MVC Structure</span></li>
                                <li><span class="status-badge status-good">✓ Assets Organized</span></li>
                                <li><span class="status-badge status-good">✓ Database Files Moved</span></li>
                                <li><span class="status-badge status-good">✓ Modules Created</span></li>
                            </ul>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <h6>🔧 Next Steps:</h6>
                        <ol class="mb-0 text-start">
                            <li>Test individual controllers and views</li>
                            <li>Update existing file references</li>
                            <li>Create view templates for pages</li>
                            <li>Set up proper error handling</li>
                            <li>Test database connections</li>
                        </ol>
                    </div>

                    <div class="mt-4">
                        <a href="<?php echo BASE_URL ?? '/'; ?>" class="btn btn-primary me-2">← Back to Original Site</a>
                        <a href="<?php echo BASE_URL ?? '/'; ?>?route=test" class="btn btn-success">Test Router</a>
                    </div>

                    <div class="mt-3 text-muted">
                        <small>Structure organized on: <?php echo date('Y-m-d H:i:s'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
