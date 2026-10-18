<?php
$base = defined('BASE_URL') ? BASE_URL : '';
$page_title = $page_title ?? 'My ID Card';
$user = $user ?? [];
$rankBadge = $rankBadge ?? ['label' => 'Silver', 'class' => 'bg-secondary'];
$referralCode = $referralCode ?? 'AGT000000';
$qrCodeUrl = $qrCodeUrl ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="id-card mx-auto" style="max-width:420px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border-radius:16px;padding:24px;box-shadow:0 10px 40px rgba(37,99,235,.3);">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <strong>APS Dream Home</strong>
            <span class="badge <?= htmlspecialchars($rankBadge['class']) ?>"><?= htmlspecialchars($rankBadge['label']) ?></span>
        </div>
        <h2 class="h5 mb-1"><?= htmlspecialchars($user['name'] ?? 'Agent') ?></h2>
        <p class="mb-1"><?= htmlspecialchars($user['email'] ?? '') ?></p>
        <p class="mb-3"><?= htmlspecialchars($user['phone'] ?? '') ?></p>
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div class="small">Referral Code</div>
                <strong><?= htmlspecialchars($referralCode) ?></strong>
            </div>
            <img src="<?= htmlspecialchars($qrCodeUrl) ?>" alt="QR Code" width="96" height="96">
        </div>
    </div>
    <div class="text-center mt-3">
        <button class="btn btn-primary" onclick="window.print()">Print ID Card</button>
        <a href="/agent/dashboard" class="btn btn-secondary">Back</a>
    </div>
</div>
</body>
</html>
