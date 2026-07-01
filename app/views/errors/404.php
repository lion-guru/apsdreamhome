<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Inter', sans-serif; margin: 0; }
        .error-card { text-align: center; padding: 60px 40px; max-width: 500px; }
        .error-code { font-size: 120px; font-weight: 800; background: linear-gradient(135deg, #0d9488, #0f766e); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; margin-bottom: 10px; }
        .error-title { font-size: 24px; font-weight: 600; color: #1e293b; margin-bottom: 12px; }
        .error-desc { color: #64748b; margin-bottom: 30px; font-size: 15px; line-height: 1.6; }
        .error-btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: linear-gradient(135deg, #0d9488, #0f766e); color: #fff; border-radius: 8px; text-decoration: none; font-weight: 500; transition: all 0.3s; }
        .error-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(13,148,136,0.4); color: #fff; }
        .error-icon { font-size: 48px; color: #94a3b8; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="error-icon"><i class="fas fa-map-signs"></i></div>
        <div class="error-code">404</div>
        <div class="error-title">Page Not Found</div>
        <div class="error-desc">The page you're looking for doesn't exist or has been moved. Let us help you find your dream property!</div>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/apsdreamhome'; ?>" class="error-btn">
            <i class="fas fa-home"></i> Back to Home
        </a>
    </div>
</body>
</html>