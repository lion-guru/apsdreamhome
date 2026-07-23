<?php
/**
 * Feature Disabled / Coming Soon view
 * Used by IoT, Metaverse, Blockchain and other unimplemented controllers
 */
$page_title = $page_title ?? 'Feature Not Available';
$feature_name = $feature_name ?? 'This Feature';
$feature_description = $feature_description ?? 'This feature is under development and will be available soon.';
$icon = $icon ?? 'fas fa-lock';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> — <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%);
            color: #e2e8f0;
        }
        .card {
            max-width: 520px;
            width: 90%;
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(99, 102, 241, 0.2);
            border-radius: 20px;
            padding: 48px 40px;
            text-align: center;
            backdrop-filter: blur(12px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }
        .icon-circle {
            width: 80px; height: 80px;
            margin: 0 auto 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            font-size: 32px; color: #fff;
            box-shadow: 0 0 30px rgba(99, 102, 241, 0.3);
        }
        h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #818cf8, #a78bfa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .desc {
            color: #94a3b8;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .badge {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 20px;
            background: rgba(99, 102, 241, 0.15);
            border: 1px solid rgba(99, 102, 241, 0.3);
            color: #818cf8;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-bottom: 24px;
        }
        .btn-back {
            display: inline-block;
            padding: 12px 28px;
            border-radius: 10px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-circle">
            <i class="<?php echo htmlspecialchars($icon); ?>"></i>
        </div>
        <div class="badge">Coming Soon</div>
        <h1><?php echo htmlspecialchars($feature_name); ?></h1>
        <p class="desc"><?php echo htmlspecialchars($feature_description); ?></p>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>admin/dashboard" class="btn-back">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>
</body>
</html>
