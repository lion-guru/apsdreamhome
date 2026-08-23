<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay) ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay) ?>'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title><?= __('terms_page_title') ?> - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/uiux-fixes.css?v=1">
</head>
<body>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <h1 class="h3 mb-4"><?= __('terms_title') ?></h1>
                <div class="card aps-cp-card">
                    <div class="card-body aps-cp-card-body">
                        <h5><?= __('terms_s1_title') ?></h5>
                        <p><?= __('terms_s1_desc') ?></p>
                        
                        <h5><?= __('terms_s2_title') ?></h5>
                        <p><?= __('terms_s2_desc') ?></p>
                        
                        <h5><?= __('terms_s3_title') ?></h5>
                        <p><?= __('terms_s3_desc') ?></p>
                        
                        <h5><?= __('terms_s4_title') ?></h5>
                        <p><?= __('terms_s4_desc') ?></p>
                        
                        <div class="mt-4">
                            <a href="/" class="btn btn-primary"><?= __('terms_back_home') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>