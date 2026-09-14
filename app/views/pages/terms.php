<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '919277121112'); $emailDisplay = $sc('contact_email', 'support@apsdreamhome.com'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Terms & Conditions') ?></title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; color: #1a1a2e; line-height: 1.8; }
        .legal-container { max-width: 900px; margin: 0 auto; padding: 40px 20px; }
        .legal-container h1 { font-size: 2rem; color: #0a192f; margin-bottom: 10px; }
        .legal-container h2 { font-size: 1.5rem; color: #1e3a5f; margin-top: 30px; margin-bottom: 12px; border-bottom: 2px solid #d4af37; padding-bottom: 6px; }
        .legal-container h3 { font-size: 1.25rem; color: #334155; margin-top: 20px; margin-bottom: 10px; }
        .legal-container p, .legal-container li { font-size: 1rem; color: #334155; }
        .legal-container ul { padding-left: 20px; margin-bottom: 12px; }
        .legal-container li { margin-bottom: 6px; }
        .legal-header { background: linear-gradient(135deg, #0a192f 0%, #1e3a5f 100%); color: #fff; padding: 40px; border-radius: 12px; margin-bottom: 30px; }
        .legal-header h1 { color: #fff; }
        .legal-header p { color: #cbd5e1; margin: 0; }
        .legal-content { background: #fff; border-radius: 12px; padding: 40px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .back-link { display: inline-block; margin-top: 30px; color: #0a192f; text-decoration: none; font-weight: 600; }
        .back-link:hover { color: #d4af37; }
    </style>
</head>
<body>
    <div class="legal-container">
        <div class="legal-header">
            <h1><?= htmlspecialchars($page_title ?? 'Terms & Conditions') ?></h1>
            <p>Last Updated: <?= date('F Y') ?></p>
        </div>
        <div class="legal-content">
            <?php if (!empty($pageContent)): ?>
                <?= $pageContent ?>
            <?php else: ?>
                <p>Terms and conditions content is being updated. Please check back later.</p>
            <?php endif; ?>
        </div>
        <a href="/" class="back-link"><i class="fas fa-arrow-left me-1"></i> Back to Home</a>
    </div>
</body>
</html>