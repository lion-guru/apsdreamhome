<?php
/** @var array $colonies */
/** @var array $all_plots */
$colonies  = $colonies ?? [];
$all_plots = $all_plots ?? [];
$base      = defined('BASE_URL') ? BASE_URL : '';
$csrf      = $csrf_token ?? '';

// Enterprise color code — must match plots.status enum + registry overlay
$statusMeta = [
    'available'          => ['label' => 'Available',          'color' => '#22c55e', 'text' => '#fff'],
    'hold'               => ['label' => '24-Hr Hold',         'color' => '#eab308', 'text' => '#422006'],
    'booked'             => ['label' => 'Booked',             'color' => '#ef4444', 'text' => '#fff'],
    'sold'               => ['label' => 'Sold',               'color' => '#dc2626', 'text' => '#fff'],
    'registry_done'      => ['label' => 'Registry Done',      'color' => '#0d6efd', 'text' => '#fff'],
    'reserved'           => ['label' => 'Reserved',           'color' => '#f97316', 'text' => '#fff'],
    'under_construction' => ['label' => 'Under Construction','color' => '#64748b', 'text' => '#fff'],
];

// Effective status: sold + completed registration => registry_done (blue)
$effective = function ($p) {
    if (($p['status'] ?? '') === 'sold' && !empty($p['registry_done'])) return 'registry_done';
    return $p['status'] ?? 'available';
};

$totals = array_fill_keys(array_keys($statusMeta), 0);
$totals['total'] = 0;
foreach ($all_plots as $p) { $totals['total']++; $s = $effective($p); if (isset($totals[$s])) $totals[$s]++; }

// Group plots by colony -> block for tidy SVG sections
$byColony = [];
foreach ($all_plots as $p) { $byColony[$p['colony_id']][] = $p; }
?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-1"><i class="fas fa-map-marked-alt me-2"></i>Master Plot Map</h4>
            <p class="text-muted mb-0">Click any plot for details & 1-click 24-hour hold — prevents double-booking on site visits</p>
        </div>
        <div class="d-flex gap-2 align-items-center flex-wrap">
            <input type="text" id="plotSearch" class="form-control form-control-sm" style="width:200px" placeholder="Search plot no...">
            <select id="colonyFilter" class="form-select form-select-sm w-auto" onchange="filterByColony(this.value)">
                <option value="0">All Colonies</option>
                <?php foreach ($colonies as $c): ?>
                    <option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option>
                <?php endforeach; ?>
            </select>
            <select id="statusFilter" class="form-select form-select-sm w-auto" onchange="filterByStatus(this.value)">
                <option value="all">All Statuses</option>
                <?php foreach ($statusMeta as $k => $m): ?>
                    <option value="<?= $k ?>"><?= $m['label'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="row g-2 mb-3" id="statsBar">
        <?php
        $statColors = ['total'=>'secondary','available'=>'success','hold'=>'warning','booked'=>'danger','sold'=>'danger','registry_done'=>'primary','reserved'=>'warning','under_construction'=>'secondary'];
        foreach (['total','available','hold','booked','sold','registry_done','reserved'] as $k):
            $lbl = $k === 'total' ? 'Total' : $statusMeta[$k]['label'];
        ?>
        <div class="col-6 col-md">
            <div class="border-start border-4 border-<?= $statColors[$k] ?> ps-2">
                <small class="text-muted d-block"><?= $lbl ?></small>
                <strong class="h5" id="stat-<?= $k ?>"><?= $totals[$k] ?? 0 ?></strong>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="row">
        <div class="col-lg-9">
            <div class="aps-cp-card mb-4">
                <div class="aps-cp-card-body" id="plotContainer">
                    <?php foreach ($colonies as $colony):
                        $cplots = $byColony[$colony['id']] ?? [];
                        if (!$cplots) continue;
                        // Group colony plots by block
                        $byBlock = [];
                        foreach ($cplots as $p) { $byBlock[$p['block'] ?: '—'][] = $p; }
                        ksort($byBlock);
                    ?>
                    <div class="colony-section mb-4" data-colony="<?= $colony['id'] ?>">
                        <h5 class="text-primary mb-2">
                            <?= htmlspecialchars($colony['name'] ?? '') ?>
                            <small class="text-muted">(<?= count($cplots) ?> plots)</small>
                        </h5>
                        <?php foreach ($byBlock as $block => $bplots):
                            $cols = 10;
                            $rows = (int)ceil(count($bplots) / $cols);
                            $cw = 90; $rh = 52; $gap = 4;
                            $svgW = $cols * ($cw + $gap) + $gap;
                            $svgH = $rows * ($rh + $gap) + $gap + 22;
                        ?>
                        <div class="mb-2"><span class="badge bg-light text-dark border">Block <?= htmlspecialchars($block) ?> — <?= count($bplots) ?> plots</span></div>
                        <svg viewBox="0 0 <?= $svgW ?> <?= $svgH ?>" class="w-100 border rounded bg-light mb-3" role="img" aria-label="Plot map <?= htmlspecialchars($colony['name'] ?? '') ?> block <?= htmlspecialchars($block) ?>">
                            <text x="<?= $svgW/2 ?>" y="15" text-anchor="middle" font-size="10" fill="#6c757d"><?= htmlspecialchars($colony['name'] ?? '') ?> · Block <?= htmlspecialchars($block) ?></text>
                            <?php foreach ($bplots as $i => $p):
                                $col = $i % $cols;
                                $row = intdiv($i, $cols);
                                $x = $gap + $col * ($cw + $gap);
                                $y = $gap + 22 + $row * ($rh + $gap);
                                $st = $effective($p);
                                $meta = $statusMeta[$st] ?? $statusMeta['available'];
                                $tip = "Plot: {$p['plot_number']}\nBlock: {$p['block']}\nArea: {$p['area_sqft']} sqft\n"
                                     . "Size: {$p['width_ft']}x{$p['length_ft']} ft\nFacing: {$p['facing']}\n"
                                     . "Price: ₹" . number_format((float)$p['total_price']) . "\nStatus: " . $meta['label']
                                     . (!empty($p['held_by_name']) ? "\nHeld by: {$p['held_by_name']}" : '')
                                     . (!empty($p['hold_remaining']) ? "\nHold: {$p['hold_remaining']}" : '');
                                $attrs = [
                                    'id' => $p['id'], 'number' => $p['plot_number'], 'block' => $p['block'],
                                    'area' => $p['area_sqft'], 'size' => $p['width_ft'] . 'x' . $p['length_ft'],
                                    'facing' => $p['facing'], 'price' => number_format((float)$p['total_price']),
                                    'pps' => number_format((float)($p['price_per_sqft'] ?? 0)),
                                    'status' => $st, 'statuslabel' => $meta['label'],
                                    'holder' => $p['held_by_name'] ?? '', 'holdleft' => $p['hold_remaining'] ?? '',
                                    'corner' => !empty($p['corner_plot']) ? 'Yes' : 'No',
                                ];
                                $dataAttrs = '';
                                foreach ($attrs as $ak => $av) { $dataAttrs .= ' data-' . $ak . '="' . htmlspecialchars((string)$av) . '"'; }
                            ?>
                            <g class="plot-g" data-status="<?= $st ?>" data-number="<?= htmlspecialchars(strtolower($p['plot_number'] ?? '')) ?>">
                                <rect x="<?= $x ?>" y="<?= $y ?>" width="<?= $cw ?>" height="<?= $rh ?>"
                                      fill="<?= $meta['color'] ?>" rx="4" class="plot-cell"<?= $dataAttrs ?>>
                                    <title><?= htmlspecialchars($tip) ?></title>
                                </rect>
                                <text x="<?= $x + $cw/2 ?>" y="<?= $y + $rh/2 - 1 ?>"
                                      text-anchor="middle" font-size="9" font-weight="bold" fill="<?= $meta['text'] ?>" pointer-events="none"
                                      ><?= htmlspecialchars($p['plot_number'] ?? '') ?></text>
                                <?php if ($st === 'hold' && !empty($p['hold_remaining'])): ?>
                                <text x="<?= $x + $cw/2 ?>" y="<?= $y + $rh/2 + 12 ?>"
                                      text-anchor="middle" font-size="7.5" fill="<?= $meta['text'] ?>" pointer-events="none"
                                      >⏳ <?= htmlspecialchars($p['hold_remaining']) ?></text>
                                <?php endif; ?>
                            </g>
                            <?php endforeach; ?>
                        </svg>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-3">
            <div class="aps-cp-card mb-3 position-sticky" style="top:12px">
                <div class="aps-cp-card-header"><span><i class="fas fa-crosshairs me-2"></i>Plot Details</span></div>
                <div class="aps-cp-card-body" id="plotPanel">
                    <div class="text-center text-muted py-4" id="plotPanelEmpty">
                        <i class="fas fa-mouse-pointer fa-2x mb-2"></i>
                        <p class="mb-0 small">Click any plot on the map to see details and hold it.</p>
                    </div>
                    <div id="plotPanelBody" class="d-none">
                        <h5 id="ppNumber" class="mb-1"></h5>
                        <div class="mb-2"><span id="ppStatus" class="badge"></span></div>
                        <table class="table table-sm small mb-2">
                            <tr><th class="w-50">Block</th><td id="ppBlock"></td></tr>
                            <tr><th>Area</th><td id="ppArea"></td></tr>
                            <tr><th>Size</th><td id="ppSize"></td></tr>
                            <tr><th>Facing</th><td id="ppFacing"></td></tr>
                            <tr><th>Corner</th><td id="ppCorner"></td></tr>
                            <tr><th>Total Price</th><td id="ppPrice" class="fw-bold text-success"></td></tr>
                            <tr><th>Rate / sqft</th><td id="ppPps"></td></tr>
                            <tr id="ppHoldRow" class="d-none"><th>Held By</th><td id="ppHolder"></td></tr>
                            <tr id="ppHoldLeftRow" class="d-none"><th>Hold Left</th><td id="ppHoldLeft" class="text-warning fw-bold"></td></tr>
                        </table>
                        <div class="d-grid gap-2">
                            <form id="ppHoldForm" method="POST" action="">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <button type="submit" class="btn btn-warning btn-sm w-100" id="ppHoldBtn">
                                    <i class="fas fa-pause-circle me-1"></i>Hold for 24 Hours
                                </button>
                            </form>
                            <form id="ppReleaseForm" method="POST" action="" class="d-none">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <button type="submit" class="btn btn-outline-success btn-sm w-100">
                                    <i class="fas fa-play-circle me-1"></i>Release Hold
                                </button>
                            </form>
                            <a id="ppViewBtn" href="#" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye me-1"></i>Full Plot Page
                            </a>
                            <a id="ppBookBtn" href="#" class="btn btn-success btn-sm d-none">
                                <i class="fas fa-file-signature me-1"></i>Book This Plot
                            </a>
                        </div>
                        <small class="text-muted d-block mt-2">Hold locks the plot as unavailable for every agent for 24 hours — no double-showing on site visits.</small>
                    </div>
                </div>
            </div>

            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <h6 class="mb-2 small fw-bold">Legend</h6>
                    <?php foreach ($statusMeta as $k => $m): ?>
                    <div class="d-flex align-items-center gap-2 mb-1 small">
                        <span class="badge" style="background:<?= $m['color'] ?>;width:22px">&nbsp;</span>
                        <?= $m['label'] ?>
                        <strong class="ms-auto" id="legend-<?= $k ?>"><?= $totals[$k] ?? 0 ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.plot-cell { cursor: pointer; transition: opacity .15s, stroke .15s; stroke: transparent; stroke-width: 2.5; }
.plot-cell:hover { opacity: .82; stroke: #1e293b; }
.plot-g.selected .plot-cell { stroke: #0f172a; stroke-width: 3; }
.plot-g.dim { opacity: .18; }
</style>

<script>
(function () {
    var base = <?= json_encode($base) ?>;
    var current = { colony: '0', status: 'all', q: '' };

    function applyFilters() {
        document.querySelectorAll('.colony-section').forEach(function (sec) {
            var showColony = (current.colony === '0' || sec.dataset.colony === current.colony);
            sec.style.display = showColony ? '' : 'none';
            if (!showColony) return;
            sec.querySelectorAll('.plot-g').forEach(function (g) {
                var okStatus = (current.status === 'all' || g.dataset.status === current.status);
                var okQ = (!current.q || (g.dataset.number || '').indexOf(current.q) !== -1);
                g.classList.toggle('dim', !(okStatus && okQ));
            });
        });
    }
    window.filterByColony = function (v) { current.colony = String(v); applyFilters(); };
    window.filterByStatus = function (v) { current.status = String(v); applyFilters(); };
    document.getElementById('plotSearch').addEventListener('input', function () {
        current.q = this.value.trim().toLowerCase(); applyFilters();
    });

    var badgeClass = {
        available: 'bg-success', hold: 'bg-warning text-dark', booked: 'bg-danger',
        sold: 'bg-danger', registry_done: 'bg-primary', reserved: 'bg-warning text-dark',
        under_construction: 'bg-secondary'
    };

    document.querySelectorAll('.plot-cell').forEach(function (rect) {
        rect.addEventListener('click', function () {
            document.querySelectorAll('.plot-g.selected').forEach(function (g) { g.classList.remove('selected'); });
            this.closest('.plot-g').classList.add('selected');
            var d = this.dataset;
            document.getElementById('plotPanelEmpty').classList.add('d-none');
            document.getElementById('plotPanelBody').classList.remove('d-none');
            document.getElementById('ppNumber').textContent = 'Plot ' + (d.number || ('#' + d.id));
            var st = document.getElementById('ppStatus');
            st.textContent = d.statuslabel || d.status;
            st.className = 'badge ' + (badgeClass[d.status] || 'bg-secondary');
            document.getElementById('ppBlock').textContent = d.block || '—';
            document.getElementById('ppArea').textContent = (d.area || '0') + ' sqft';
            document.getElementById('ppSize').textContent = (d.size || '—') + ' ft';
            document.getElementById('ppFacing').textContent = d.facing || '—';
            document.getElementById('ppCorner').textContent = d.corner || 'No';
            document.getElementById('ppPrice').textContent = '₹' + (d.price || '0');
            document.getElementById('ppPps').textContent = '₹' + (d.pps || '0');
            var isHold = (d.status === 'hold');
            var isAvail = (d.status === 'available');
            document.getElementById('ppHoldRow').classList.toggle('d-none', !isHold);
            document.getElementById('ppHoldLeftRow').classList.toggle('d-none', !isHold);
            document.getElementById('ppHolder').textContent = d.holder || '—';
            document.getElementById('ppHoldLeft').textContent = d.holdleft || '—';
            document.getElementById('ppHoldForm').action = base + '/admin/plots/' + d.id + '/hold';
            document.getElementById('ppHoldForm').classList.toggle('d-none', !isAvail);
            document.getElementById('ppReleaseForm').action = base + '/admin/plots/' + d.id + '/release-hold';
            document.getElementById('ppReleaseForm').classList.toggle('d-none', !isHold);
            document.getElementById('ppViewBtn').href = base + '/admin/plots/' + d.id;
            var bookBtn = document.getElementById('ppBookBtn');
            bookBtn.href = base + '/admin/plots/' + d.id + '/book';
            bookBtn.classList.toggle('d-none', !(isAvail || isHold));
            document.getElementById('plotPanel').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });
})();
</script>
