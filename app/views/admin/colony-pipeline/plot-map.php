<?php
$page_title = $page_title ?? 'Plot Map';
$colony = $colony ?? [];
$plots = $plots ?? [];
$cid = (int)($colony['id'] ?? 0);
$byBlock = [];
foreach ($plots as $p) { $byBlock[$p['block'] ?? '—'][] = $p; }
ksort($byBlock);
$statusColor = ['available' => 'success', 'booked' => 'warning', 'sold' => 'primary', 'hold' => 'secondary', 'reserved' => 'info'];
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-map me-2"></i>Plot Map — <?= htmlspecialchars($colony['name'] ?? '') ?></h4>
    <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
</div>
<?php if (empty($plots)): ?>
<div class="alert alert-info">No plots in this colony yet. <a href="<?= BASE_URL ?>/admin/colony-pipeline/<?= $cid ?>/layout">Generate a layout</a> first.</div>
<?php endif; ?>
<?php foreach ($byBlock as $block => $bplots): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-header bg-white fw-bold">Block <?= htmlspecialchars($block) ?> <span class="badge bg-light text-dark border"><?= count($bplots) ?> plots</span></div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($bplots as $p): $st = $p['status'] ?? 'available'; ?>
            <div class="border rounded px-2 py-1 text-center bg-<?= $statusColor[$st] ?? 'light' ?> <?= in_array($st, ['sold','booked']) ? 'text-white' : '' ?>" style="min-width:92px" title="Plot <?= htmlspecialchars($p['plot_number'] ?? '') ?> · <?= htmlspecialchars($p['area_sqft'] ?? '') ?> sqft · ₹<?= number_format($p['total_price'] ?? 0, 0) ?><?= !empty($p['corner_plot']) ? ' · Corner' : '' ?><?= !empty($p['park_facing']) ? ' · Park facing' : '' ?>">
                <div class="fw-bold small"><?= htmlspecialchars($p['plot_number'] ?? '') ?></div>
                <div style="font-size:11px"><?= htmlspecialchars($p['area_sqft'] ?? '') ?> sqft</div>
                <div style="font-size:11px">₹<?= number_format($p['total_price'] ?? 0, 0) ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endforeach; ?>
<div class="d-flex gap-3 small text-muted">
    <span><span class="badge bg-success">A</span> Available</span>
    <span><span class="badge bg-warning">B</span> Booked</span>
    <span><span class="badge bg-primary">S</span> Sold</span>
    <span><span class="badge bg-secondary">H</span> Hold</span>
</div>
