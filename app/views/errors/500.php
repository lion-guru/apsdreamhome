<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; min-height: 100vh; display: flex; align-items: center; justify-content: center; margin: 0; padding: 20px; }
        .error-container { text-align: center; max-width: 600px; padding: 40px; background: white; border-radius: 16px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
        .error-icon { font-size: 80px; color: #dc3545; margin-bottom: 20px; }
        .error-code { font-size: 6rem; font-weight: 700; color: #2c3e50; margin-bottom: 10px; }
        .error-title { font-size: 1.5rem; color: #495057; margin-bottom: 15px; }
        .error-message { color: #6c757d; margin-bottom: 30px; line-height: 1.6; }
        .btn-home { background: linear-gradient(135deg, #6B4EE6 0%, #8B5CF6 100%); border: none; border-radius: 25px; padding: 12px 30px; color: white; font-weight: 600; text-decoration: none; display: inline-block; margin: 5px; transition: transform 0.2s; }
        .btn-home:hover { transform: translateY(-2px); color: white; text-decoration: none; }
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <div class="error-container">
        <div class="error-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="error-code">500</div>
        <h1 class="error-title">Something Went Wrong</h1>
        <p class="error-message">We're experiencing a technical issue. Please try again later. Our team has been notified.</p>
        <div>
            <a href="<?= BASE_URL ?>/" class="btn-home"><i class="fas fa-home me-2"></i>Go Home</a>
            <a href="javascript:location.reload()" class="btn-home"><i class="fas fa-redo me-2"></i>Try Again</a>
        </div>
    </div>
</body>
</html>
