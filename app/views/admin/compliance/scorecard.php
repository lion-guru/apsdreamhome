<?php
$page_title = $page_title ?? 'Compliance Scorecard';
$overall = $overall ?? 0;
$areas = $areas ?? [];
$last_checked = $last_checked ?? '';
$recommendations = $recommendations ?? [];
$area_labels = $area_labels ?? [];
$area_icons = $area_icons ?? [];
$weights = $weights ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';

function complianceColor($score) {
    if ($score >= 80) return '#28a745';
    if ($score >= 50) return '#ffc107';
    return '#dc3545';
}

function complianceLabel($status) {
    switch ($status) {
        case 'compliant':     return 'Compliant';
        case 'partial':       return 'Partial';
        case 'non_compliant': return 'Non-Compliant';
        default:              return 'Unknown';
    }
}

function complianceBadgeClass($status) {
    switch ($status) {
        case 'compliant':     return 'bg-success';
        case 'partial':       return 'bg-warning text-dark';
        case 'non_compliant': return 'bg-danger';
        default:              return 'bg-secondary';
    }
}
?>

<style>
.compliance-header{background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);color:#fff;border-radius:0 0 24px 24px;padding:32px 0;margin-bottom:24px;position:relative;overflow:hidden}
.compliance-header::before{content:'';position:absolute;top:-50%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(40,167,69,.15) 0%,transparent 70%);border-radius:50%}
.compliance-header::after{content:'';position:absolute;bottom:-30%;left:10%;width:300px;height:300px;background:radial-gradient(circle,rgba(255,193,7,.1) 0%,transparent 70%);border-radius:50%}
.overall-score-ring{width:180px;height:180px;position:relative;margin:0 auto}
.overall-score-ring svg{width:100%;height:100%;transform:rotate(-90deg)}
.overall-score-ring .ring-bg{fill:none;stroke:#e9ecef;stroke-width:12}
.overall-score-ring .ring-fill{fill:none;stroke-width:12;stroke-linecap:round;transition:stroke-dashoffset 1.5s ease}
.overall-score-value{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center}
.overall-score-value .score-num{font-size:42px;font-weight:800;line-height:1}
.overall-score-value .score-label{font-size:12px;text-transform:uppercase;letter-spacing:1.5px;opacity:.7;margin-top:4px}
.area-card{background:#fff;border-radius:16px;border:1px solid #f0f0f5;padding:20px;transition:.3s;height:100%;cursor:pointer;text-decoration:none;color:inherit;display:block}
.area-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.1);text-decoration:none;color:inherit}
.area-card .area-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.area-card .area-score{font-size:32px;font-weight:800;line-height:1}
.area-card .area-name{font-size:13px;color:#666;text-transform:uppercase;letter-spacing:.8px;margin-top:4px}
.area-card .area-weight{font-size:11px;color:#aaa;margin-top:2px}
.area-card .progress-track{height:6px;border-radius:3px;background:#f0f0f5;margin-top:12px;overflow:hidden}
.area-card .progress-fill{height:100%;border-radius:3px;transition:width 1s ease}
.rec-item{padding:12px 16px;border-left:3px solid #e9ecef;margin-bottom:8px;border-radius:0 8px 8px 0;background:#fafbfc;transition:.2s}
.rec-item:hover{background:#f0f4ff}
.rec-item.critical{border-left-color:#dc3545}
.rec-item.high{border-left-color:#ffc107}
.rec-item.medium{border-left-color:#28a745}
.rec-item .rec-area{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px}
.rec-item .rec-text{font-size:13px;color:#444;line-height:1.5}
.rec-item .rec-priority{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;padding:2px 8px;border-radius:10px;display:inline-block}
</style>

<div class="compliance-header">
    <div class="container-fluid px-4" style="position:relative;z-index:1">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h2 class="mb-1 fw-bold"><i class="fas fa-shield-alt me-2"></i>Compliance Scorecard</h2>
                <p class="mb-0 opacity-75" style="font-size:14px">Data security, KYC, payment compliance, and regulatory status</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?= $base ?>/admin/compliance-scorecard/recommendations" class="btn btn-light"><i class="fas fa-list-check me-1"></i>All Recommendations</a>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid px-4" style="margin-top:-12px">

    <!-- Overall Score + Last Checked -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="text-center p-4" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                <div class="overall-score-ring">
                    <?php $circ = 2 * M_PI * 78; $offset = $circ * (1 - $overall / 100); ?>
                    <svg viewBox="0 0 180 180">
                        <circle class="ring-bg" cx="90" cy="90" r="78"/>
                        <circle class="ring-fill" cx="90" cy="90" r="78"
                            stroke="<?= complianceColor($overall) ?>"
                            stroke-dasharray="<?= $circ ?>"
                            stroke-dashoffset="<?= $offset ?>"/>
                    </svg>
                    <div class="overall-score-value">
                        <div class="score-num" style="color:<?= complianceColor($overall) ?>"><?= $overall ?></div>
                        <div class="score-label">Overall Score</div>
                    </div>
                </div>
                <div class="mt-3">
                    <span class="badge <?= complianceBadgeClass($areas[array_key_first($areas)]['status'] ?? '') ?>" style="font-size:12px;padding:6px 14px">
                        <?= $overall >= 80 ? 'COMPLIANT' : ($overall >= 50 ? 'PARTIAL' : 'NON-COMPLIANT') ?>
                    </span>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="p-4" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5;height:100%">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0" style="font-size:14px"><i class="fas fa-clock me-2 text-muted"></i>Last Checked</h6>
                    <span class="text-muted" style="font-size:13px"><?= $last_checked ? date('d M Y, h:i A', strtotime($last_checked)) : 'Never' ?></span>
                </div>
                <div class="row g-2">
                    <?php foreach ($areas as $key => $area): ?>
                        <div class="col-6 col-md-4">
                            <div class="d-flex align-items-center gap-2 p-2" style="background:#f8f9fa;border-radius:10px">
                                <div style="width:10px;height:10px;border-radius:50%;background:<?= complianceColor($area['score']) ?>;flex-shrink:0"></div>
                                <div>
                                    <div style="font-size:11px;color:#888"><?= $area_labels[$key] ?? $key ?></div>
                                    <div style="font-size:16px;font-weight:700;color:<?= complianceColor($area['score']) ?>"><?= $area['score'] ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- 6 Area Cards -->
    <div class="row g-3 mb-4">
        <?php foreach ($areas as $key => $area): ?>
            <?php
                $weight = $weights[$key] ?? 0;
                $icon = $area_icons[$key] ?? 'fas fa-check-circle';
                $color = complianceColor($area['score']);
                $circ2 = 2 * M_PI * 22;
                $offset2 = $circ2 * (1 - $area['score'] / 100);
            ?>
            <div class="col-md-4 col-lg-2">
                <a href="<?= $base ?>/admin/compliance-scorecard/area/<?= $key ?>" class="area-card">
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="area-icon" style="background:<?= $color ?>15;color:<?= $color ?>">
                            <i class="<?= $icon ?>"></i>
                        </div>
                        <svg width="52" height="52" viewBox="0 0 52 52" style="transform:rotate(-90deg)">
                            <circle cx="26" cy="26" r="22" fill="none" stroke="#f0f0f5" stroke-width="4"/>
                            <circle cx="26" cy="26" r="22" fill="none" stroke="<?= $color ?>" stroke-width="4"
                                stroke-linecap="round" stroke-dasharray="<?= $circ2 ?>" stroke-dashoffset="<?= $offset2 ?>"/>
                        </svg>
                    </div>
                    <div class="area-score" style="color:<?= $color ?>"><?= $area['score'] ?></div>
                    <div class="area-name"><?= $area_labels[$key] ?? $key ?></div>
                    <div class="area-weight">Weight: <?= round($weight * 100) ?>%</div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width:<?= $area['score'] ?>%;background:<?= $color ?>"></div>
                    </div>
                    <div class="mt-2">
                        <span class="badge <?= complianceBadgeClass($area['status']) ?>" style="font-size:10px;padding:3px 8px"><?= complianceLabel($area['status']) ?></span>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Top 5 Recommendations -->
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="p-4" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0" style="font-size:15px"><i class="fas fa-lightbulb me-2 text-warning"></i>Top Recommendations</h6>
                    <a href="<?= $base ?>/admin/compliance-scorecard/recommendations" style="font-size:12px;color:#667eea">View All →</a>
                </div>
                <?php if (empty($recommendations)): ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-check-circle fa-2x mb-2 text-success"></i>
                        <p class="mb-0">All areas compliant — no critical recommendations</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($recommendations as $rec): ?>
                        <div class="rec-item <?= $rec['priority'] ?>">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <div class="rec-area" style="color:<?= complianceColor(100 - $rec['impact']) ?>"><?= $rec['area'] ?></div>
                                    <div class="rec-text"><?= htmlspecialchars($rec['recommendation']) ?></div>
                                </div>
                                <span class="rec-priority bg-<?= $rec['priority'] === 'critical' ? 'danger' : ($rec['priority'] === 'high' ? 'warning' : 'success') ?> text-white ms-2"><?= $rec['priority'] ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="p-4" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                <h6 class="fw-bold mb-3" style="font-size:15px"><i class="fas fa-chart-line me-2 text-info"></i>Compliance Trend</h6>
                <?php if (empty($trend)): ?>
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-chart-area fa-2x mb-2"></i>
                        <p class="mb-0" style="font-size:12px">Run compliance checks to see trend data</p>
                    </div>
                <?php else: ?>
                    <canvas id="trendChart" height="200"></canvas>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
                    <script>
                        const trendData = <?= json_encode(array_reverse($trend)) ?>;
                        const ctx = document.getElementById('trendChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: trendData.map(d => { const dt = new Date(d.checked_at); return dt.toLocaleDateString('en-IN', {day:'2-digit',month:'short'}); }),
                                datasets: [{
                                    label: 'Overall Score',
                                    data: trendData.map(d => d.overall_score),
                                    borderColor: '#667eea',
                                    backgroundColor: 'rgba(102,126,234,.1)',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 4,
                                    pointBackgroundColor: '#667eea',
                                }]
                            },
                            options: {
                                responsive: true,
                                plugins: { legend: { display: false } },
                                scales: {
                                    y: { min: 0, max: 100, ticks: { stepSize: 25 } },
                                    x: { ticks: { font: { size: 10 } } }
                                }
                            }
                        });
                    </script>
                <?php endif; ?>
            </div>
            <div class="p-3 mt-3" style="background:#fff;border-radius:16px;border:1px solid #f0f0f5">
                <h6 class="fw-bold mb-2" style="font-size:13px"><i class="fas fa-info-circle me-1 text-muted"></i>Scoring Weights</h6>
                <?php foreach ($weights as $k => $w): ?>
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size:12px">
                        <span class="text-muted"><?= $area_labels[$k] ?? $k ?></span>
                        <span class="fw-bold"><?= round($w * 100) ?>%</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
