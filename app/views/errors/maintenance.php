<?php
/**
 * Generic maintenance page (used by MaintenanceModeMiddleware directly,
 * but also available for /maintenance direct link).
 */
$message = $message ?? "We're performing scheduled maintenance. We'll be back soon. Thanks for your patience!";
$eta = $eta ?? '';
$logo = defined('BASE_URL') ? BASE_URL . '/assets/images/logo/apslogonew.jpg' : '/assets/images/logo/apslogonew.jpg';
$contact = 'info@apsdreamhome.com';
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
http_response_code(503);
header('Retry-After: 3600');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Be right back — APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); min-height: 100vh; display: flex; align-items: center; }
        .card { border: none; box-shadow: 0 20px 60px rgba(0,0,0,0.2); }
        .icon-circle { width: 100px; height: 100px; border-radius: 50%; background: #f8f9fa; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem; }
        .icon-circle i { font-size: 3rem; color: #0f766e; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-6">
                <div class="card p-5 text-center">
                    <div class="icon-circle"><i class="fas fa-cog fa-spin"></i></div>
                    <h1 class="h2 mb-3">Be right back</h1>
                    <p class="lead text-muted mb-4"><?= htmlspecialchars($message) ?></p>
                    <?php if ($eta): ?>
                        <p class="mb-4"><i class="far fa-clock text-primary me-2"></i>Estimated return: <strong><?= htmlspecialchars($eta) ?></strong></p>
                    <?php endif; ?>
                    <hr>
                    <p class="small text-muted mb-0">
                        Need help? <a href="mailto:<?= htmlspecialchars($contact) ?>"><?= htmlspecialchars($contact) ?></a>
                    </p>
                    <div class="mt-3">
                        <a href="<?= $base ?>" class="text-muted small"><i class="fas fa-sync-alt me-1"></i>Try again</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
