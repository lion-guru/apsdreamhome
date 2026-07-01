<div class="container-fluid">
    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-map-marked-alt me-2"></i> Plot Layout Map</span>
            <div>
                <button class="btn btn-sm btn-outline-secondary me-2" onclick="resetZoom()"><i class="fas fa-expand"></i> Reset</button>
                <select id="colonyFilter" class="form-select form-select-sm d-inline-block w-auto" onchange="filterByColony(this.value)">
                    <option value="0">All Colonies</option>
                    <?php foreach ($colonies as $c): ?>
                        <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="aps-cp-card-body">
            <div class="row g-2 mb-3" id="statsBar">
                <?php
                $totals = ['total'=>0,'available'=>0,'booked'=>0,'on_emi'=>0,'registered'=>0,'blocked'=>0];
                foreach ($all_plots as $p) { $totals['total']++; $s = $p['status']; if (isset($totals[$s])) $totals[$s]++; }
                $colors = ['total'=>'secondary','available'=>'success','booked'=>'danger','on_emi'=>'warning','registered'=>'primary','blocked'=>'secondary'];
                $labels = ['total'=>'Total','available'=>'Available','booked'=>'Booked','on_emi'=>'On EMI','registered'=>'Registered','blocked'=>'Blocked'];
                foreach ($labels as $k => $l):
                ?>
                <div class="col-4 col-md-2">
                    <div class="border-start border-4 border-<?= $colors[$k] ?> ps-2">
                        <small class="text-muted d-block"><?= $l ?></small>
                        <strong class="h5"><?= $totals[$k] ?></strong>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div id="plotContainer" style="overflow:auto; max-height:75vh;">
                <?php foreach ($colonies as $ci => $colony):
                    $cplots = array_filter($all_plots, fn($p) => $p['colony_id'] == $colony['id']);
                    if (!$cplots) continue;
                    $cplots = array_values($cplots);
                    $cols = 10;
                    $rows = ceil(count($cplots) / $cols);
                    $cw = 90;
                    $rh = 50;
                    $gap = 4;
                    $svgW = $cols * ($cw + $gap) + $gap;
                    $svgH = $rows * ($rh + $gap) + $gap + 20;
                ?>
                <div class="colony-section mb-4" data-colony="<?= $colony['id'] ?>">
                    <h5 class="text-primary mb-2"><?= htmlspecialchars($colony['name']) ?></h5>
                    <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>" class="w-100 border rounded bg-light" style="max-width:100%; height:auto;">
                        <text x="<?= $svgW/2 ?>" y="16" text-anchor="middle" font-size="11" fill="#6c757d"><?= htmlspecialchars($colony['name']) ?> — <?= count($cplots) ?> plots</text>
                        <?php foreach ($cplots as $i => $p):
                            $col = $i % $cols;
                            $row = intdiv($i, $cols);
                            $x = $gap + $col * ($cw + $gap);
                            $y = $gap + 20 + $row * ($rh + $gap);
                            $statusColor = match($p['status']) {
                                'available' => '#10b981',
                                'booked' => '#ef4444',
                                'on_emi' => '#f59e0b',
                                'registered' => '#14b8a6',
                                'blocked' => '#64748b',
                                default => '#94a3b8'
                            };
                            $tooltip = htmlspecialchars("Plot: {$p['plot_number']}\nArea: {$p['area_sqft']} sqft\nDimensions: {$p['width_ft']}x{$p['length_ft']} ft\nFacing: {$p['facing']}\nPrice: ₹".number_format((float)$p['total_price'])."\nStatus: {$p['status']}");
                        ?>
                        <rect x="<?= $x ?>" y="<?= $y ?>" width="<?= $cw ?>" height="<?= $rh ?>"
                              fill="<?= $statusColor ?>" rx="3" class="plot-cell"
                              data-plot="<?= htmlspecialchars($p['plot_number']) ?>" data-status="<?= $p['status'] ?>"
                              data-colony-id="<?= $p['colony_id'] ?>">
                            <title><?= $tooltip ?></title>
                        </rect>
                        <text x="<?= $x + $cw/2 ?>" y="<?= $y + $rh/2 + 4 ?>"
                              text-anchor="middle" font-size="8" fill="#fff" pointer-events="none"
                              style="text-shadow: 0 1px 2px rgba(0,0,0,0.5);"><?= htmlspecialchars($p['plot_number']) ?></text>
                        <?php endforeach; ?>
                    </svg>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<div class="aps-cp-card">
    <div class="aps-cp-card-body">
        <div class="d-flex flex-wrap gap-3 justify-content-center">
            <span><span class="badge" style="background:#10b981; width:16px; height:16px; display:inline-block;"></span> Available</span>
            <span><span class="badge" style="background:#ef4444; width:16px; height:16px; display:inline-block;"></span> Booked</span>
            <span><span class="badge" style="background:#f59e0b; width:16px; height:16px; display:inline-block;"></span> On EMI</span>
            <span><span class="badge" style="background:#14b8a6; width:16px; height:16px; display:inline-block;"></span> Registered</span>
            <span><span class="badge" style="background:#64748b; width:16px; height:16px; display:inline-block;"></span> Blocked</span>
        </div>
    </div>
</div>

<style>
.plot-cell { cursor: pointer; transition: opacity 0.2s, stroke 0.2s; stroke: transparent; stroke-width: 2; }
.plot-cell:hover { opacity: 0.8; stroke: #1e293b; }
</style>

<script>
function filterByColony(colonyId) {
    document.querySelectorAll('.colony-section').forEach(el => {
        el.style.display = (colonyId == 0 || el.dataset.colony == colonyId) ? '' : 'none';
    });
}
function resetZoom() {
    document.getElementById('colonyFilter').value = '0';
    filterByColony('0');
    document.getElementById('plotContainer').scrollTo(0, 0);
}
</script>
