<?php
/**
 * Associate ID Card View
 * Branded ID Card for Associate Portal
 */
$base = BASE_URL ?? '';
$qrCodeUrl = $qrCodeUrl ?? '';
$referralCode = $referralCode ?? 'APS' . str_pad($user['id'] ?? 0, 6, '0', STR_PAD_LEFT);
$rankBadge = $rankBadge ?? ['label' => 'Bronze', 'class' => 'bg-secondary'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'My ID Card') ?> - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?= BASE_URL ?>/assets/fonts/fontawesome/css/all.min.css" rel="stylesheet">
    <style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        @media print {
            .no-print { display: none !important; }
            .id-card { box-shadow: none !important; border: 1px solid #dee2e6 !important; }
            body { background: #fff !important; padding: 0; }
            .no-print { display: none !important; }
        }
        .id-card {
            background: linear-gradient(135deg, #0d9488 0%, #14b8a6 100%);
            border-radius: 16px;
            max-width: 420px;
            margin: 20px auto;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(13, 148, 136, 0.3);
        }
        .id-card::before {
            content: '';
            position: absolute;
            top: -50%; right: -50%;
            width: 200%; height: 200%;
            background: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Cpattern id='grid' width='10' height='10' patternUnits='userSpaceOnUse'%3E%3Cpath d='M 10 0 L 0 0 0 10' fill='none' stroke='%23ffffff10' stroke-width='0.5'/%3E%3C/pattern%3E%3Crect width='100' height='100' fill='url(%23grid)'/%3E%3C/svg%3E") center/20px;
            opacity: 0.15;
        }
        .id-card-header {
            padding: 20px 24px 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .logo-box {
            width: 56px; height: 56px;
            border-radius: 12px;
            background: rgba(255,255,255,0.2);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .id-body { padding: 0 24px 20px; position: relative; z-index: 1; }
        .id-title { font-size: 0.75rem; font-weight: 600; color: rgba(255,255,255,0.8); text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px; }
        .id-name { font-size: 1.5rem; font-weight: 700; color: #fff; margin: 2px 0 2px; line-height: 1.2; }
        .id-role { font-size: 0.85rem; opacity: 0.9; display: flex; align-items: center; gap: 6px; }
        .rank-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .id-details { margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.15); }
        .detail-row { display: flex; justify-content: space-between; padding: 8px 0; border-top: 1px solid rgba(255,255,255,0.1); }
        .detail-label { font-size: 0.7rem; color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 0.03em; }
        .detail-value { font-weight: 600; font-size: 0.85rem; color: #fff; font-family: monospace; }
        .qr-section { margin-top: 20px; text-align: center; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.15); }
        .qr-code { width: 96px; height: 96px; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 8px; background: #fff; }
        .qr-label { font-size: 0.65rem; color: rgba(255,255,255,0.6); margin-top: 6px; text-transform: uppercase; letter-spacing: 0.05em; }
        .card-footer { margin-top: 20px; padding-top: 16px; border-top: 1px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: space-between; font-size: 0.7rem; opacity: 0.7; }
        .no-print { margin-top: 20px; text-align: center; }
        .btn-print { background: #fff; color: #0d9488; border: none; padding: 12px 28px; border-radius: 10px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 14px rgba(13,148,136,0.3); transition: all 0.2s; }
        .btn-print:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(13,148,136,0.4); }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; padding: 0; }
            .id-card { box-shadow: none; border: 1px solid #dee2e6; }
        }
    </style>
</head>
<body>
    <div class="no-print text-center mb-3">
        <button class="btn btn-outline-primary btn-sm" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print ID Card
        </button>
    </div>

    <div class="id-card" role="img" aria-label="Associate ID Card for <?= htmlspecialchars($user['name'] ?? 'Associate') ?>">
        <div class="id-card-header">
            <div class="logo-box">
                <img src="<?= BASE_URL ?>/assets/images/logo/apslogonew.jpg" alt="APS Logo" style="width: 32px; height: 32px; object-fit: contain;">
            </div>
            <div>
                <div class="id-title">APS Dream Home</div>
                <span class="text-white-50 small">Associate Identity Card</span>
            </div>
        </div>

        <div class="id-body">
            <div class="id-title">Associate Identity Card</div>
            <div class="id-name"><?= htmlspecialchars($user['name'] ?? 'Associate') ?></div>
            <div class="id-role">
                <span class="rank-badge <?= $rankBadge['class'] ?>"><?= htmlspecialchars($rankBadge['label']) ?></span>
                <span class="text-white-50">Associate</span>
            </div>

            <div class="id-details">
                <div class="detail-row">
                    <span class="detail-label">Associate Code</span>
                    <span class="detail-value"><?= htmlspecialchars($user['associate_code'] ?? 'APS' . str_pad($user['id'] ?? 0, 6, '0', STR_PAD_LEFT)) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Referral Code</span>
                    <span class="detail-value"><?= htmlspecialchars($referralCode) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Phone</span>
                    <span class="detail-value"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></span>
                </div>
                <?php if (!empty($associate['joining_date'])): ?>
                <div class="detail-row">
                    <span class="detail-label">Joined</span>
                    <span class="detail-value"><?= date('M Y', strtotime($associate['joining_date'])) ?></span>
                </div>
                <?php endif; ?>
            </div>

            <div class="qr-section">
                <div class="qr-code">
                    <img src="<?= htmlspecialchars($qrCodeUrl) ?>" alt="Referral QR Code" width="96" height="96">
                </div>
                <div class="qr-label">Scan to Refer</div>
                <div class="mt-2 small text-white-50"><?= htmlspecialchars($referralCode) ?></div>
            </div>

            <div class="card-footer">
                <span>APS Dream Home &copy; <?= date('Y') ?></span>
                <span>Associate ID: <?= htmlspecialchars($user['id'] ?? '0') ?></span>
            </div>
        </div>

    <div class="no-print text-center mt-3">
        <button class="btn-print" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Print ID Card
        </button>
        <span class="text-muted ms-3 small">Press Ctrl+P to print or save as PDF</span>
    </div>

    <script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
        // Auto-focus for better UX
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                window.print();
            }
        });
    </script>
</body>
</html>