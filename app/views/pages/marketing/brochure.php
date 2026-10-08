<?php
$listings = $listings ?? [];
$summary = $summary ?? ['count' => 0, 'min_price' => 0, 'max_price' => 0, 'avg_price' => 0];
$branding = $branding ?? ['display_name' => 'APS Dream Home', 'phone' => '+91 73092 68077', 'referral_code' => ''];
$base = defined('BASE_URL') ? BASE_URL : '';
$refLink = $base . '/register' . (!empty($branding['referral_code']) ? '?ref=' . urlencode($branding['referral_code']) : '');
$colony_name = $colony_name ?? 'All Colonies';
?>
<!DOCTYPE html>
<html lang="hi">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Colony Brochure - APS Dream Home</title>
<style>
  body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #111; }
  .header { text-align: center; border-bottom: 3px solid #FACC15; padding-bottom: 12px; margin-bottom: 16px; }
  .header h1 { margin: 0; font-size: 26px; }
  .header p { margin: 4px 0; color: #444; }
  .summary { display: flex; gap: 12px; margin-bottom: 16px; }
  .summary div { flex: 1; border: 1px solid #ddd; border-radius: 8px; padding: 10px; text-align: center; }
  table { width: 100%; border-collapse: collapse; font-size: 13px; }
  th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
  th { background: #070C18; color: #FACC15; }
  .footer { margin-top: 16px; text-align: center; border-top: 3px solid #FACC15; padding-top: 12px; }
  .no-print { margin-bottom: 16px; text-align: center; }
  .property-row:hover { background: #f5f5f5; }
  .qr-cell { text-align: center; }
  .qr-img { width: 60px; height: 60px; }
  @media print {
    .no-print { display: none !important; }
    body { padding: 0; }
    @page { size: A4 portrait; margin: 10mm; }
  }
</style>
</head>
<body>
<div class="no-print">
    <button onclick="window.print()" style="padding:10px 24px;background:#059669;color:#fff;border:none;border-radius:8px;font-size:15px;cursor:pointer">🖨️ PDF / Print Karo</button>
    <a href="<?= $base ?>/marketing/toolkit" style="margin-left:10px">← Toolkit</a>
</div>

<div class="header">
    <h1>🏡 APS DREAM HOMES PVT. LTD.</h1>
    <p>Colony Property Brochure — <?= htmlspecialchars($colony_name) ?></p>
    <p><strong><?= (int)$summary['count'] ?> listings</strong>
    <?php if ($summary['min_price'] > 0): ?>
    | Min ₹<?= number_format($summary['min_price']) ?> | Max ₹<?= number_format($summary['max_price']) ?> | Avg ₹<?= number_format($summary['avg_price']) ?>
    <?php endif; ?></p>
</div>

<div class="summary">
    <div><strong><?= (int)$summary['count'] ?></strong><br>Total Plots</div>
    <div><strong>₹<?= $summary['min_price'] ? number_format($summary['min_price']) : '-' ?></strong><br>Starting Price</div>
    <div><strong>0% EMI</strong><br>36 Months</div>
    <div><strong>Turant</strong><br>Registry + Kabza</div>
</div>

<table>
    <thead><tr><th>#</th><th>Property</th><th>Type</th><th>Area</th><th>Price</th><th>Location</th><th>QR</th></tr></thead>
    <tbody>
    <?php foreach ($listings as $i => $p): ?>
        <tr class="property-row">
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars(mb_substr($p['name'] ?? '', 0, 40)) ?></td>
            <td><?= htmlspecialchars($p['property_type'] ?? '') ?></td>
            <td><?= number_format($p['area_sqft'] ?? 0) ?> sqft</td>
            <td><strong>₹<?= number_format($p['price'] ?? 0) ?></strong></td>
            <td><?= htmlspecialchars(mb_substr($p['location'] ?? $p['city_name'] ?? '', 0, 30)) ?></td>
            <td class="qr-cell">
                <?php if (!empty($p['qr_code'])): ?>
                    <img class="qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=60x60&data=<?= urlencode($p['qr_code'] ?? '') ?>" alt="QR" loading="lazy">
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($listings)): ?>
        <tr><td colspan="7" style="text-align:center">Koi approved listing nahi mili.</td></tr>
    <?php endif; ?>
    </tbody>
</table>

<div class="footer">
    <p><strong><?= htmlspecialchars($branding['display_name']) ?></strong> | <?= htmlspecialchars($branding['phone']) ?></p>
    <p>Join: <?= htmlspecialchars($refLink) ?> | Helpline: +91 73092 68077</p>
    <p style="font-size:11px;color:#666">© APS Dream Homes Pvt. Ltd. | Gorakhpur</p>
</div>
</body>
</html>
