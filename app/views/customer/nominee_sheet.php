<?php
/** Printable family asset succession sheet (customer self-serve). */
/** @var array $booking */
/** @var array $me */
$booking = $booking ?? [];
$me = $me ?? [];
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Family Asset Succession Sheet - APS Dream Home</title>
    <link href="<?= BASE_URL ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .sheet { max-width: 720px; margin: 0 auto; border: 2px solid #111; padding: 26px 30px; background: #fff; color: #111; }
        table.kv { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table.kv td { border: 1px solid #999; padding: 6px 9px; }
        table.kv td.k { width: 36%; background: #f4f4f4; font-weight: 600; }
        .sigrow { display: flex; gap: 24px; margin-top: 30px; }
        .sigbox { flex: 1; border-top: 1px solid #111; padding-top: 4px; font-size: 12px; }
        @media print { .toolbar { display: none !important; } body { background: #fff !important; } }
    </style>
</head>
<body>
<div class="container py-4">
    <div class="toolbar mb-3 d-flex gap-2">
        <a href="<?= BASE_URL ?>/customer/documents" class="btn btn-outline-secondary btn-sm">← Documents</a>
        <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fas fa-print me-1"></i>Print Sheet</button>
    </div>
    <div class="sheet">
        <h4 class="text-center">Family Asset Succession Sheet (पारिवारिक उत्तराधिकार पत्र)</h4>
        <p class="text-center text-muted small">APS Dream Homes Pvt. Ltd. — record of nominee for plot booking</p>
        <table class="kv">
            <tr><td class="k">Booking No.</td><td><?= $e($booking['booking_number'] ?? '') ?></td><td class="k">Booking Date</td><td><?= $e($booking['booking_date'] ?? $booking['created_at'] ?? '') ?></td></tr>
            <tr><td class="k">Owner</td><td><?= $e($me['name'] ?? '') ?> (<?= $e($me['phone'] ?? '') ?>)</td><td class="k">Plot</td><td><?= $e($booking['plot_number'] ?? '') ?>, <?= $e($booking['colony_name'] ?? '') ?> (<?= $e($booking['area_sqft'] ?? '') ?> sqft)</td></tr>
            <tr><td class="k">Nominee</td><td><strong><?= $e($booking['nominee_name'] ?? '—') ?></strong></td><td class="k">Relation</td><td><?= $e($booking['nominee_relation'] ?? '—') ?></td></tr>
            <tr><td class="k">Nominee DOB</td><td><?= $e($booking['nominee_dob'] ?? '—') ?></td><td class="k">Aadhaar</td><td><?= $e($booking['nominee_aadhaar'] ?? '—') ?></td></tr>
            <tr><td class="k">Nominee Phone</td><td><?= $e($booking['nominee_phone'] ?? '—') ?></td><td class="k">Guardian (minor)</td><td><?= $e($booking['guardian_name'] ?? '—') ?></td></tr>
            <tr><td class="k">Agreement Value</td><td colspan="3">₹<?= number_format((float)($booking['agreement_value'] ?? $booking['total_plot_value'] ?? 0)) ?></td></tr>
        </table>
        <p class="small">Declaration: I nominate the above person to receive all rights in this booking on my behalf as per applicable succession law. This sheet is a record, not a registered will — get a registered will for full legal effect.</p>
        <div class="sigrow"><div class="sigbox">Owner signature + date</div><div class="sigbox">Nominee signature + date</div><div class="sigbox">Witness + seal</div></div>
    </div>
</div>
</body>
</html>
