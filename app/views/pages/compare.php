<?php
/**
 * Property Comparison View - APS Dream Home
 * Side-by-side plot comparison (up to 4 plots)
 */
$plots = $plots ?? [];
$count = count($plots);
?>

<style>
.compare-page { padding: 40px 0 80px; }
.compare-empty { text-align: center; padding: 80px 20px; }
.compare-empty i { font-size: 4rem; color: #cbd5e1; margin-bottom: 20px; }
.compare-empty h2 { font-weight: 700; color: #1e293b; margin-bottom: 10px; }
.compare-empty p { color: #64748b; margin-bottom: 24px; }

.compare-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.compare-header h1 { font-size: 1.6rem; font-weight: 700; color: #1e293b; margin: 0; }
.compare-actions { display: flex; gap: 8px; flex-wrap: wrap; }

.compare-grid { display: grid; grid-template-columns: 180px repeat(<?= $count ?>, 1fr); gap: 0; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff; }
.compare-label-col { background: #f8fafc; }
.compare-label { padding: 14px 16px; font-weight: 600; font-size: 0.88rem; color: #475569; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; min-height: 52px; }
.compare-cell { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; min-height: 52px; font-size: 0.92rem; color: #1e293b; }
.compare-cell:last-child { border-bottom: none; }
.compare-plot-header { background: linear-gradient(135deg, #1e3a5f, #2563eb); color: #fff; padding: 20px 16px; text-align: center; }
.compare-plot-header h3 { margin: 0 0 4px; font-size: 1.1rem; font-weight: 700; }
.compare-plot-header .plot-colony { font-size: 0.82rem; opacity: 0.85; }
.compare-row-even .compare-label,
.compare-row-even .compare-cell { background: #fafbfe; }
.compare-best { background: #ecfdf5 !important; font-weight: 600; color: #15803d; }

.compare-actions-cell { text-align: center; padding: 16px !important; }
.btn-remove-compare { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; border-radius: 8px; padding: 6px 14px; font-size: 0.82rem; cursor: pointer; transition: all 0.2s; }
.btn-remove-compare:hover { background: #dc2626; color: #fff; }
.btn-book-compare { background: #2563eb; color: #fff; border: none; border-radius: 8px; padding: 8px 16px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; text-decoration: none; display: inline-block; }
.btn-book-compare:hover { background: #1d4ed8; color: #fff; }

.status-available { color: #15803d; font-weight: 600; }
.status-booked { color: #ca8a04; font-weight: 600; }
.status-sold { color: #dc2626; font-weight: 600; }
.status-hold { color: #64748b; font-weight: 600; }

@media (max-width: 768px) {
    .compare-grid { overflow-x: auto; display: block; }
    .compare-grid table { min-width: <?= max(600, $count * 160) ?>px; }
    .compare-label-col { min-width: 140px; }
}

.price-highlight { font-size: 1.1rem; font-weight: 700; color: #16a34a; }
.price-per-sqft { font-size: 0.85rem; color: #64748b; }
.area-highlight { font-weight: 600; color: #2563eb; }
.dimension-tag { background: #f1f5f9; border-radius: 6px; padding: 4px 10px; font-size: 0.85rem; color: #334155; display: inline-block; }
</style>

<div class="container compare-page">
    <?php if ($count === 0): ?>
        <div class="compare-empty">
            <i class="fas fa-balance-scale"></i>
            <h2><?= __('compare_no_plots_title') ?></h2>
            <p><?= __('compare_no_plots_hint') ?></p>
            <a href="<?= BASE_URL ?>/plots" class="btn btn-primary btn-lg px-5 rounded-3">
                <i class="fas fa-map me-2"></i> <?= __('compare_browse_plots') ?>
            </a>
        </div>
    <?php else: ?>
        <div class="compare-header">
            <h1><i class="fas fa-balance-scale me-2 text-primary"></i> <?= sprintf(__('compare_title'), $count) ?></h1>
            <div class="compare-actions">
                <?php if ($count < 4): ?>
                    <a href="<?= BASE_URL ?>/plots" class="btn btn-outline-primary btn-sm rounded-3">
                        <i class="fas fa-plus me-1"></i> <?= __('compare_add_more') ?>
                    </a>
                <?php endif; ?>
                <button onclick="clearCompare()" class="btn btn-outline-danger btn-sm rounded-3">
                    <i class="fas fa-trash me-1"></i> <?= __('compare_clear_all') ?>
                </button>
            </div>
        </div>

        <div class="compare-grid">
            <!-- Row: Plot Header -->
            <div class="compare-label-col">
                <div class="compare-label" class="style-86246"><?= __('compare_plot_header') ?></div>
            </div>
            <?php foreach ($plots as $i => $plot): ?>
                <div class="compare-plot-header">
                    <h3>Plot <?= htmlspecialchars($plot['plot_number'] ?? '') ?></h3>
                    <div class="plot-colony"><?= htmlspecialchars($plot['colony_name'] ?? '') ?></div>
                </div>
            <?php endforeach; ?>

            <!-- Row: Colony Name -->
            <div class="compare-label-col"><div class="compare-label"><?= __('compare_colony') ?></div></div>
            <?php foreach ($plots as $plot): ?>
                <div class="compare-cell"><?= htmlspecialchars($plot['colony_name'] ?? '-') ?></div>
            <?php endforeach; ?>

            <!-- Row: Block + Plot -->
            <div class="compare-label-col compare-row-even"><div class="compare-label"><?= __('compare_block_plot_no') ?></div></div>
            <?php foreach ($plots as $plot): ?>
                <div class="compare-cell compare-row-even">
                    <?= htmlspecialchars(($plot['block'] ?? '-') . ' / ' . ($plot['plot_number'] ?? '-')) ?>
                </div>
            <?php endforeach; ?>

            <!-- Row: Area -->
            <?php
                $areas = array_map(fn($p) => floatval($p['area_sqft'] ?? 0), $plots);
                $maxArea = max($areas);
            ?>
            <div class="compare-label-col"><div class="compare-label"><?= __('compare_area') ?></div></div>
            <?php foreach ($plots as $i => $plot): ?>
                <div class="compare-cell <?= ($areas[$i] === $maxArea && $maxArea > 0) ? 'compare-best' : '' ?>">
                    <span class="area-highlight"><?= number_format(floatval($plot['area_sqft'] ?? 0)) ?></span> sqft
                </div>
            <?php endforeach; ?>

            <!-- Row: Dimensions -->
            <div class="compare-label-col compare-row-even"><div class="compare-label"><?= __('compare_dimensions') ?></div></div>
            <?php foreach ($plots as $plot): ?>
                <div class="compare-cell compare-row-even">
                    <?php if (!empty($plot['dimension_label'])): ?>
                        <span class="dimension-tag"><?= htmlspecialchars($plot['dimension_label'] ?? '') ?></span>
                    <?php elseif (!empty($plot['width_ft']) && !empty($plot['length_ft'])): ?>
                        <span class="dimension-tag"><?= htmlspecialchars($plot['width_ft'] ?? '') ?> x <?= htmlspecialchars($plot['length_ft'] ?? '') ?> ft</span>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Row: Price -->
            <?php
                $prices = array_map(fn($p) => floatval($p['total_price'] ?? 0), $plots);
                $minPrice = min(array_filter($prices));
            ?>
            <div class="compare-label-col"><div class="compare-label"><?= __('compare_price') ?></div></div>
            <?php foreach ($plots as $i => $plot): ?>
                <div class="compare-cell <?= ($prices[$i] > 0 && $prices[$i] === $minPrice) ? 'compare-best' : '' ?>">
                    <span class="price-highlight">₹<?= number_format(intval($plot['total_price'] ?? 0)) ?></span>
                </div>
            <?php endforeach; ?>

            <!-- Row: Price per sqft -->
            <?php
                $pps = array_map(fn($p) => floatval($p['price_per_sqft'] ?? 0), $plots);
                $minPps = min(array_filter($pps));
            ?>
            <div class="compare-label-col compare-row-even"><div class="compare-label"><?= __('compare_price_per_sqft') ?></div></div>
            <?php foreach ($plots as $i => $plot): ?>
                <div class="compare-cell compare-row-even <?= ($pps[$i] > 0 && $pps[$i] === $minPps) ? 'compare-best' : '' ?>">
                    <span class="price-per-sqft">₹<?= number_format(floatval($plot['price_per_sqft'] ?? 0)) ?></span>
                </div>
            <?php endforeach; ?>

            <!-- Row: Status -->
            <div class="compare-label-col"><div class="compare-label"><?= __('compare_status') ?></div></div>
            <?php foreach ($plots as $plot): ?>
                <div class="compare-cell">
                    <?php
                        $st = $plot['status'] ?? 'available';
                        $cls = match($st) { 'available' => 'status-available', 'booked' => 'status-booked', 'sold' => 'status-sold', default => 'status-hold' };
                        $lbl = match($st) { 'available' => __('compare_status_available'), 'booked' => __('compare_status_booked'), 'sold' => __('compare_status_sold'), default => __('compare_status_hold') };
                    ?>
                    <span class="<?= $cls ?>"><?= $lbl ?></span>
                </div>
            <?php endforeach; ?>

            <!-- Row: Facing -->
            <div class="compare-label-col compare-row-even"><div class="compare-label"><?= __('compare_facing') ?></div></div>
            <?php foreach ($plots as $plot): ?>
                <div class="compare-cell compare-row-even">
                    <?= htmlspecialchars(ucfirst($plot['facing'] ?? '-')) ?>
                </div>
            <?php endforeach; ?>

            <!-- Row: Corner Plot -->
            <div class="compare-label-col"><div class="compare-label"><?= __('compare_corner_plot') ?></div></div>
            <?php foreach ($plots as $plot): ?>
                <div class="compare-cell">
                    <?php if (!empty($plot['corner_plot'])): ?>
                        <span class="text-success"><i class="fas fa-check-circle"></i> <?= __('compare_yes') ?></span>
                    <?php else: ?>
                        <span class="text-muted"><?= __('compare_no') ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Row: Park Facing -->
            <div class="compare-label-col compare-row-even"><div class="compare-label"><?= __('compare_park_facing') ?></div></div>
            <?php foreach ($plots as $plot): ?>
                <div class="compare-cell compare-row-even">
                    <?php if (!empty($plot['park_facing'])): ?>
                        <span class="text-success"><i class="fas fa-check-circle"></i> <?= __('compare_yes') ?></span>
                    <?php else: ?>
                        <span class="text-muted"><?= __('compare_no') ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Row: Road Width -->
            <div class="compare-label-col"><div class="compare-label"><?= __('compare_road_width') ?></div></div>
            <?php foreach ($plots as $plot): ?>
                <div class="compare-cell">
                    <?= !empty($plot['road_width_ft']) ? htmlspecialchars($plot['road_width_ft'] ?? '') . ' ft' : '-' ?>
                </div>
            <?php endforeach; ?>

            <!-- Row: Available for Booking -->
            <div class="compare-label-col compare-row-even"><div class="compare-label"><?= __('compare_available_booking') ?></div></div>
            <?php foreach ($plots as $plot): ?>
                <div class="compare-cell compare-row-even">
                    <?php if (($plot['status'] ?? '') === 'available'): ?>
                        <span class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i> <?= __('compare_yes') ?></span>
                    <?php else: ?>
                        <span class="text-danger fw-semibold"><i class="fas fa-times-circle me-1"></i> <?= __('compare_no') ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <!-- Row: Actions -->
            <div class="compare-label-col"><div class="compare-label" class="style-48741"><?= __('compare_actions') ?></div></div>
            <?php foreach ($plots as $plot): ?>
                <div class="compare-cell compare-actions-cell">
                    <button onclick="removeFromCompare(<?= $plot['id'] ?>)" class="btn-remove-compare mb-2">
                        <i class="fas fa-times me-1"></i> <?= __('compare_remove') ?>
                    </button>
                    <br>
                    <?php if (($plot['status'] ?? '') === 'available'): ?>
                        <a href="<?= BASE_URL ?>/plot/<?= $plot['id'] ?>" class="btn-book-compare">
                            <i class="fas fa-shopping-cart me-1"></i> <?= __('compare_book_now') ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function removeFromCompare(plotId) {
    fetch('<?= BASE_URL ?>/compare/remove', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= $csrf_token ?? '' ?>'
        },
        body: 'plot_id=' + plotId
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) location.reload();
    });
}

function clearCompare() {
    if (!confirm('<?= __('compare_clear_confirm') ?>')) return;
    fetch('<?= BASE_URL ?>/compare/clear', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-CSRF-Token': '<?= $csrf_token ?? '' ?>'
        }
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) window.location.href = '<?= BASE_URL ?>/plots';
    });
}
</script>
