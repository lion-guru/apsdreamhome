<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | APS Dream Home</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Inter',sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
        .card{background:#fff;border-radius:16px;padding:40px;text-align:center;max-width:500px;width:100%;box-shadow:0 20px 60px rgba(0,0,0,.15)}
        .icon{width:80px;height:80px;background:#fffbeb;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;font-size:2rem;color:#f59e0b}
        h1{font-size:3.5rem;font-weight:700;color:#1e293b;line-height:1}
        h2{font-size:1.1rem;color:#64748b;font-weight:400;margin:8px 0 16px}
        p{color:#94a3b8;font-size:.9rem;margin-bottom:24px;line-height:1.6}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;background:#4f46e5;color:#fff;text-decoration:none;border-radius:8px;font-weight:500;font-size:.9rem;transition:all .2s;margin:4px}
        .btn:hover{background:#4338ca;transform:translateY(-1px)}
        .btn-outline{background:transparent;color:#64748b;border:1px solid #e2e8f0}
        .btn-outline:hover{background:#f8fafc;color:#1e293b}
        .logo-text{font-size:.8rem;color:#c7d2fe;margin-top:24px}
        .logo-text a{color:#a5b4fc;text-decoration:none}
        <?php if (defined('SHOW_ERRORS') && SHOW_ERRORS): ?>
        .debug{text-align:left;background:#f8fafc;padding:12px;border-radius:8px;font-family:monospace;font-size:.8rem;color:#dc2626;max-height:150px;overflow:auto;margin-bottom:16px;border:1px solid #e2e8f0}
        <?php endif; ?>
    </style>
</head>
<body>
    <div class="card">
        <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        <h1>500</h1>
        <h2>Something went wrong</h2>
        <p>Our team has been notified. Please try again in a moment or contact us directly.</p>
        <?php if (defined('SHOW_ERRORS') && SHOW_ERRORS && !empty($error)): ?>
            <div class="debug"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div>
            <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/'; ?>" class="btn"><i class="fas fa-home"></i> Homepage</a>
            <button onclick="window.history.back()" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Go Back</button>
        </div>
        <div class="logo-text">APS Dream Home</div>
    </div>
</body>
</html>