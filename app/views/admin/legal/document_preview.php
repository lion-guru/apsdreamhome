<?php
$doc = $doc ?? null;
if (!$doc) { echo '<div class="container-fluid py-4"><div class="alert alert-danger">Document not found</div></div>'; return; }
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview: <?= htmlspecialchars($doc['document_number'] ?? $doc['title'] ?? 'Document') ?></title>
    <link href="<?= BASE_URL ?>/assets/admin/css/admin.css" rel="stylesheet">
    <style>
        @media print { .no-print { display: none !important; } body { background: #fff; } .print-container { box-shadow: none; margin: 0; padding: 20px; } }
        body { background: #f5f5f5; font-family: 'Times New Roman', Times, serif; }
        .print-container { max-width: 900px; margin: 20px auto; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.1); padding: 40px; min-height: 297mm; }
        .status-badge { position: fixed; top: 10px; right: 10px; z-index: 1000; }
    </style>
</head>
<body>
    <div class="no-print text-center py-2 bg-light border-bottom">
        <a href="<?= BASE_URL ?>/admin/legal/documents/<?= $doc['id'] ?>" class="btn btn-sm btn-outline-secondary me-2"><i class="fas fa-arrow-left me-1"></i>Back</a>
        <button class="btn btn-sm btn-primary" onclick="window.print()"><i class="fas fa-print me-1"></i>Print</button>
        <span class="ms-3 text-muted small"><?= htmlspecialchars($doc['document_number'] ?? 'Draft') ?> | <?= htmlspecialchars($doc['title'] ?? '') ?></span>
    </div>
    <div class="status-badge no-print">
        <span class="badge bg-<?= match($doc['status']) { 'signed' => 'success', 'final' => 'info', 'draft' => 'secondary', 'expired' => 'warning', 'cancelled' => 'danger', default => 'secondary' } ?> fs-6"><?= $doc['status'] ?></span>
        <?php if (!empty($doc['kyc_verified'])): ?><span class="badge bg-info fs-6 ms-1">KYC ✓</span><?php endif; ?>
    </div>
    <div class="print-container">
        <?= $doc['content'] ?? '<p class="text-muted text-center py-5">No content</p>' ?>
        <?php if (!empty($doc['notes'])): ?>
            <div style="margin-top:30px;padding:10px;background:#f9f9f9;border:1px solid #eee;font-size:12px;color:#666;">
                <strong>Internal Notes:</strong> <?= nl2br(htmlspecialchars($doc['notes'])) ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
