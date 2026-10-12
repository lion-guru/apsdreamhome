<?php
/** Public document verification card (QR landing, no login). */
/** @var string $doc_type */
/** @var string $ref_no */
/** @var array|null $doc */
$doc = $doc ?? null;
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<div class="container py-5" style="max-width:640px">
    <?php if ($doc): ?>
        <div class="card shadow border-success">
            <div class="card-body text-center p-4">
                <div style="font-size:52px;">✅</div>
                <h4 class="text-success fw-bold">OFFICIALLY VERIFIED DOCUMENT</h4>
                <p class="text-muted">Issued by APS Dream Home ERP — verified live from database records.</p>
                <table class="table table-sm text-start mt-3">
                    <tr><th style="width:40%">Document Type</th><td><?= $e($doc['label'] ?? $doc_type) ?></td></tr>
                    <tr><th>Reference No.</th><td class="fw-bold"><?= $e($doc['ref'] ?? $ref_no) ?></td></tr>
                    <tr><th>Issued To</th><td><?= $e($doc['who'] ?? '') ?></td></tr>
                    <tr><th>Plot / Colony</th><td><?= $e($doc['plot'] ?? '') ?></td></tr>
                    <tr><th>Value / Status</th><td><?= $e($doc['amount'] ?? '') ?></td></tr>
                    <tr><th>Issue Date</th><td><?= $e($doc['date'] ?? '') ?></td></tr>
                </table>
                <p class="small text-muted mt-3">If anyone asks for money against this document, call our office first — verified amount above is final.</p>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow border-danger">
            <div class="card-body text-center p-4">
                <div style="font-size:52px;">⚠️</div>
                <h4 class="text-danger fw-bold">Invalid Reference / Unverified Document</h4>
                <p class="text-muted">No <?= $e($doc_type) ?> found for reference <strong><?= $e($ref_no) ?></strong> in APS Dream Home records. Do not pay anyone against it — contact our office.</p>
                <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/">Go to Homepage</a>
            </div>
        </div>
    <?php endif; ?>
</div>
