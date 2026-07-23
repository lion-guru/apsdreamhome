<?php
$page_title = $page_title ?? 'Compliance Area';
$area_key = $area_key ?? '';
$area_label = $area_label ?? '';
$area_icon = $area_icon ?? 'fas fa-check-circle';
$area_weight = $area_weight ?? 0;
$result = $result ?? ['score' => 0, 'status' => 'non_compliant', 'details' => '', 'findings' => [], 'recommendations' => []];
$overall = $overall ?? 0;
$area_labels = $area_labels ?? [];
$all_areas = $all_areas ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

function areaColor($score) {
    if ($score >= 80) return '#28a745';
    if ($score >= 50) return '#ffc107';
    return '#dc3545';
}

function areaBadgeClass($status) {
    switch ($status) {
        case 'compliant':     return 'bg-success';
        case 'partial':       return 'bg-warning text-dark';
        case 'non_compliant': return 'bg-danger';
        default:              return 'bg-secondary';
    }
}

function areaStatusLabel($status) {
    switch ($status) {
        case 'compliant':     return 'Compliant';
        case 'partial':       return 'Partial';
        case 'non_compliant': return 'Non-Compliant';
        default:              return 'Unknown';
    }
}
?>

<style>
.area-detail-header{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);color:#fff;border-radius:0 0 24px 24px;padding:32px 0;margin-bottom:24px;position:relative;overflow:hidden}
.finding-item{padding:12px 16px;background:#fff8f8;border-radius:10px;border-left:3px solid #dc3545;margin-bottom:8px;font-size:13px;color:#555;line-height:1.5}
.finding-item:empty{display:none}
.rec-item-sm{padding:10px 14px;background:#f0fff4;border-radius:10px;border-left:3px solid #28a745;margin-bottom:8px;font-size:13px;color:#444;line-height:1.5}
.rec-item-sm:empty{display:none}
</style>

<div class="area-detail-header">
    <div class="container-fluid px-4" style="position:relative;z-index:1">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <a href="<?= $base ?>/admin/compliance-scorecard" class="text-white-50 text-decoration-none" style="font-size:13px"><i class="fas fa-arrow-left me-1"></i>Back to Scorecard</a>
                <h2 class="mb-1 fw-bold mt-1"><i class="<?= $area_icon ?> me-2"></i><?= htmlspecialchars($area_label) ?></h2>
                <p class="mb-0 opacity-75" style="font-size:14px">Weight: <?= round($area_weight * 100) ?>% of overall compliance</p>
            </div>
            <div class="text-end">
                <div style="font-size:56px;font-weight:800;line-height:1;color:<?= areaColor($result['score']) ?>"><?= $result['score'] ?></div>
                <span class="badge <?= areaBadgeClass($result['status']) ?>" style="font-size:12px;padding:5px 14px"><?= areaStatusLabel($result['status']) ?></span>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4" style="margin-top:-12px">
    <div class="row g-4">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Details -->
            <div class="p-4 mb-4" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                <h6 class="fw-bold mb-3" style="font-size:15px"><i class="fas fa-info-circle me-2 text-primary"></i>Check Summary</h6>
                <p class="mb-0" style="font-size:14px;color:#555;line-height:1.6"><?= htmlspecialchars($result['details']) ?></p>
            </div>

            <!-- Findings -->
            <div class="p-4 mb-4" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                <h6 class="fw-bold mb-3" style="font-size:15px">
                    <i class="fas fa-exclamation-triangle me-2 text-danger"></i>Findings
                    <span class="badge bg-danger ms-2" style="font-size:11px"><?= count($result['findings']) ?></span>
                </h6>
                <?php if (empty($result['findings'])): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                        <p class="mb-0">No issues found — area is compliant</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($result['findings'] as $finding): ?>
                        <div class="finding-item"><?= htmlspecialchars($finding) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Recommendations -->
            <div class="p-4" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                <h6 class="fw-bold mb-3" style="font-size:15px">
                    <i class="fas fa-lightbulb me-2 text-warning"></i>Recommendations
                    <span class="badge bg-success ms-2" style="font-size:11px"><?= count($result['recommendations']) ?></span>
                </h6>
                <?php if (empty($result['recommendations'])): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                        <p class="mb-0">No recommendations — area fully compliant</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($result['recommendations'] as $rec): ?>
                        <div class="rec-item-sm"><?= htmlspecialchars($rec) ?></div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar: Other Areas -->
        <div class="col-lg-4">
            <div class="p-4" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                <h6 class="fw-bold mb-3" style="font-size:14px"><i class="fas fa-th-large me-2 text-muted"></i>All Compliance Areas</h6>
                <?php foreach ($all_areas as $key => $area): ?>
                    <?php $color = areaColor($area['score']); $active = $key === $area_key; ?>
                    <a href="<?= $base ?>/admin/compliance-scorecard/area/<?= $key ?>"
                       class="d-flex align-items-center gap-3 p-2 mb-1 rounded text-decoration-none <?= $active ? 'bg-light' : '' ?>"
                       style="border: <?= $active ? '2px solid ' . $color : '2px solid transparent' ?>;transition:.2s">
                        <div style="width:10px;height:10px;border-radius:50%;background:<?= $color ?>;flex-shrink:0"></div>
                        <div class="flex-grow-1">
                            <div style="font-size:12px;font-weight:<?= $active ? '700' : '500' ?>;color:#333"><?= $area_labels[$key] ?? $key ?></div>
                        </div>
                        <div style="font-size:18px;font-weight:800;color:<?= $color ?>"><?= $area['score'] ?></div>
                    </a>
                <?php endforeach; ?>

                <hr class="my-3">

                <div class="text-center">
                    <div style="font-size:12px;color:#888;margin-bottom:4px">Overall Score</div>
                    <div style="font-size:28px;font-weight:800;color:<?= areaColor($overall) ?>"><?= $overall ?></div>
                    <a href="<?= $base ?>/admin/compliance-scorecard" class="btn btn-sm btn-outline-primary mt-2" style="font-size:12px">View Full Scorecard</a>
                </div>
            </div>
        </div>
    </div>
</div>
