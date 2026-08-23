<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>400 - Bad Request | APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#0d9488 0%,#0f766e 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{background:#fff;border-radius:16px;padding:40px;text-align:center;max-width:500px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.15)}
        .icon{width:80px;height:80px;background:#fef2f2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:2rem;color:#ef4444}
        h1{font-size:3.5rem;font-weight:700;color:#1e293b;line-height:1}
        h2{font-size:1.1rem;color:#64748b;font-weight:400;margin:8px 0 16px}
        p{color:#94a3b8;font-size:.9rem;margin-bottom:24px;line-height:1.6}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#0d9488;color:#fff;text-decoration:none;border-radius:8px;font-weight:500;font-size:.9rem;transition:all .2s;margin:4px}
        .btn:hover{background:#4338ca;transform:translateY(-1px)}
        .logo-text{font-size:.8rem;color:#c7d2fe;margin-top:24px}
    </style>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <div class="card aps-cp-card">
        <div class="icon"><i class="fas fa-exclamation-circle"></i></div>
        <h1>400</h1>
        <h2>Bad Request</h2>
        <p>The request could not be processed. Please check your input and try again.</p>
        <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>" class="btn"><i class="fas fa-home"></i> Homepage</a>
        <div class="logo-text">APS Dream Home</div>
    </div>
</body>
</html>