<?php
$page_title = $page_title ?? 'Compliance Recommendations';
$recommendations = $recommendations ?? [];
$grouped = $grouped ?? [];
$area_labels = $area_labels ?? [];
$area_icons = $area_icons ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');

function recColor($priority) {
    switch ($priority) {
        case 'critical': return '#dc3545';
        case 'high':     return '#ffc107';
        case 'medium':   return '#28a745';
        default:         return '#6c757d';
    }
}

function recBadgeClass($priority) {
    switch ($priority) {
        case 'critical': return 'bg-danger';
        case 'high':     return 'bg-warning text-dark';
        case 'medium':   return 'bg-success';
        default:         return 'bg-secondary';
    }
}
?>

<style>
.recs-header{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);color:#fff;border-radius:0 0 24px 24px;padding:32px 0;margin-bottom:24px;position:relative;overflow:hidden}
.recs-header::before{content:'';position:absolute;top:-50%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(255,193,7,.12) 0%,transparent 70%);border-radius:50%}
.group-section{margin-bottom:24px}
.group-title{font-size:15px;font-weight:700;color:#333;margin-bottom:12px;display:flex;align-items:center;gap:8px}
.group-title i{width:32px;height:32px;border-radius:10px;display:inline-flex;align-items:center;justify-content:center;font-size:14px}
.rec-card{background:#fff;border-radius:12px;border:1px solid #f0f0f5;padding:16px 20px;margin-bottom:10px;transition:.2s;display:flex;align-items:flex-start;gap:14px}
.rec-card:hover{box-shadow:0 4px 16px rgba(0,0,0,.06);transform:translateX(2px)}
.rec-card .rec-indicator{width:4px;border-radius:2px;align-self:stretch;flex-shrink:0}
.rec-card .rec-body{flex-grow:1}
.rec-card .rec-text{font-size:13px;color:#444;line-height:1.6}
.rec-card .rec-meta{font-size:11px;color:#aaa;margin-top:6px;display:flex;gap:12px}
.stat-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600}
</style>

<div class="recs-header">
    <div class="container-fluid px-4" style="position:relative;z-index:1">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <a href="<?= $base ?>/admin/compliance-scorecard" class="text-white-50 text-decoration-none" style="font-size:13px"><i class="fas fa-arrow-left me-1"></i>Back to Scorecard</a>
                <h2 class="mb-1 fw-bold mt-1"><i class="fas fa-list-check me-2"></i>All Recommendations</h2>
                <p class="mb-0 opacity-75" style="font-size:14px">Sorted by impact — highest impact first</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <?php
                    $critCount = 0; $highCount = 0; $medCount = 0;
                    foreach ($recommendations as $r) {
                        if ($r['priority'] === 'critical') $critCount++;
                        elseif ($r['priority'] === 'high') $highCount++;
                        else $medCount++;
                    }
                ?>
                <span class="stat-pill" style="background:rgba(220,53,69,.15);color:#dc3545"><i class="fas fa-circle" style="font-size:6px"></i> <?= $critCount ?> Critical</span>
                <span class="stat-pill" style="background:rgba(255,193,7,.15);color:#d4a017"><i class="fas fa-circle" style="font-size:6px"></i> <?= $highCount ?> High</span>
                <span class="stat-pill" style="background:rgba(40,167,69,.15);color:#28a745"><i class="fas fa-circle" style="font-size:6px"></i> <?= $medCount ?> Medium</span>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4" style="margin-top:-12px">
    <div class="row g-4">
        <div class="col-lg-9">
            <?php if (empty($recommendations)): ?>
                <div class="text-center py-5" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                    <i class="fas fa-check-circle fa-3x mb-3 text-success"></i>
                    <h5 class="fw-bold">All Clear!</h5>
                    <p class="text-muted mb-0">No compliance recommendations — all areas are fully compliant.</p>
                </div>
            <?php else: ?>
                <?php foreach ($grouped as $areaKey => $recs): ?>
                    <?php
                        $icon = $area_icons[$areaKey] ?? 'fas fa-check-circle';
                        $worst = 'medium';
                        foreach ($recs as $r) {
                            if ($r['priority'] === 'critical') { $worst = 'critical'; break; }
                            if ($r['priority'] === 'high' && $worst !== 'critical') { $worst = 'high'; }
                        }
                        $color = recColor($worst);
                    ?>
                    <div class="group-section">
                        <div class="group-title">
                            <i style="background:<?= $color ?>15;color:<?= $color ?>"><i class="<?= $icon ?>"></i></i>
                            <?= $area_labels[$areaKey] ?? $areaKey ?>
                            <span class="badge bg-secondary" style="font-size:10px"><?= count($recs) ?> items</span>
                        </div>
                        <?php foreach ($recs as $rec): ?>
                            <div class="rec-card">
                                <div class="rec-indicator" style="background:<?= recColor($rec['priority']) ?>"></div>
                                <div class="rec-body">
                                    <div class="rec-text"><?= htmlspecialchars($rec['recommendation']) ?></div>
                                    <div class="rec-meta">
                                        <span>Impact: <strong><?= $rec['impact'] ?></strong></span>
                                        <span class="badge <?= recBadgeClass($rec['priority']) ?>" style="font-size:9px;padding:2px 8px"><?= strtoupper($rec['priority']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Sidebar Summary -->
        <div class="col-lg-3">
            <div class="p-4 mb-3" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                <h6 class="fw-bold mb-3" style="font-size:14px"><i class="fas fa-chart-pie me-2 text-muted"></i>Summary</h6>
                <div class="d-flex justify-content-between mb-2" style="font-size:13px">
                    <span class="text-muted">Total Recommendations</span>
                    <span class="fw-bold"><?= count($recommendations) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:13px">
                    <span class="text-muted">Areas Affected</span>
                    <span class="fw-bold"><?= count($grouped) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2" style="font-size:13px">
                    <span class="text-muted">Avg Impact</span>
                    <span class="fw-bold"><?= count($recommendations) > 0 ? round(array_sum(array_column($recommendations, 'impact')) / count($recommendations), 1) : 0 ?></span>
                </div>
                <hr>
                <div class="text-center">
                    <a href="<?= $base ?>/admin/compliance-scorecard" class="btn btn-sm btn-primary" style="font-size:12px"><i class="fas fa-shield-alt me-1"></i>Re-check Now</a>
                </div>
            </div>

            <div class="p-4" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                <h6 class="fw-bold mb-3" style="font-size:14px"><i class="fas fa-sort-amount-down me-2 text-muted"></i>By Area</h6>
                <?php foreach ($grouped as $areaKey => $recs): ?>
                    <div class="d-flex justify-content-between align-items-center mb-2" style="font-size:12px">
                        <span class="text-muted"><?= $area_labels[$areaKey] ?? $areaKey ?></span>
                        <span class="fw-bold"><?= count($recs) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
