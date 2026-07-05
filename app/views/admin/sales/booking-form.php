<?php
/** @var string $mode */
/** @var array $booking */
/** @var array $plots */
/** @var array $customers */
/** @var array $associates */
/** @var array $sales_managers */
$mode = $mode ?? 'create';
$booking = $booking ?? [];
$plots = $plots ?? [];
$customers = $customers ?? [];
$associates = $associates ?? [];
$sales_managers = $sales_managers ?? [];
$csrf_token = $csrf_token ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$action = $mode === 'edit'
    ? $base . '/admin/sales/bookings/' . (int)($booking['id'] ?? 0) . '/update'
    : $base . '/admin/sales/bookings/store';

// Group plots by colony for cascade
$coloniesMap = [];
$plotsByColony = [];
foreach ($plots as $p) {
    $cName = $p['colony_name'] ?? 'Unknown';
    $cId = $p['colony_id'] ?? 0;
    if ($cId && !isset($coloniesMap[$cId])) {
        $coloniesMap[$cId] = $cName;
    }
    if ($cId) {
        $plotsByColony[$cId][] = $p;
    }
}
$selectedColonyId = 0;
$selectedPlotId = (int)($booking['plot_id'] ?? 0);
if ($selectedPlotId) {
    foreach ($plots as $p) {
        if ((int)($p['id'] ?? 0) === $selectedPlotId) {
            $selectedColonyId = (int)($p['colony_id'] ?? 0);
            break;
        }
    }
}
?>
<div class="aps-cp-card">
    <div class="aps-cp-card-header">
        <h5 class="m-0"><i class="fas fa-<?= $mode === 'edit' ? 'edit' : 'plus' ?> me-2"></i><?= $mode === 'edit' ? __('sale_edit_booking') : __('sale_new_booking') ?></h5>
    </div>
    <div class="aps-cp-card-body">
        <form method="post" action="<?= htmlspecialchars($action) ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars((string)$csrf_token) ?>">
            <div class="row g-3">
                <?php if ($mode !== 'edit'): ?>
                <div class="col-md-6">
                    <label class="form-label"><?= __('sale_colony') ?></label>
                    <select id="booking_colony_id" class="form-select">
                        <option value=""><?= __('sale_select_colony') ?></option>
                        <?php foreach ($coloniesMap as $cId => $cName): ?>
                            <option value="<?= $cId ?>" <?= $cId === $selectedColonyId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cName) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-md-6">
                    <label class="form-label"><?= __('sale_plot_label') ?></label>
                    <select name="plot_id" id="booking_plot_id" class="form-select" required <?= $mode === 'edit' ? 'disabled' : '' ?>>
                        <option value=""><?= $mode === 'edit' ? __('sale_select_plot') : __('sale_select_colony_first') ?></option>
                        <?php if ($mode === 'edit'): ?>
                            <?php foreach ($plots as $p): ?>
                                <option value="<?= (int)($p['id'] ?? 0) ?>" <?= ((int)($booking['plot_id'] ?? 0) === (int)($p['id'] ?? 0)) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars((string)($p['plot_code'] ?? $p['plot_number'] ?? '')) ?> (&#8377;<?= number_format((float)($p['total_price'] ?? 0)) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label"><?= __('sale_customer_label') ?></label>
                    <select name="customer_id" class="form-select" required <?= $mode === 'edit' ? 'disabled' : '' ?>>
                        <option value=""><?= __('sale_select_customer') ?></option>
                        <?php foreach ($customers as $c): ?>
                            <option value="<?= (int)($c['id'] ?? 0) ?>" <?= ((int)($booking['customer_id'] ?? 0) === (int)($c['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($c['name'] ?? '')) ?> — <?= htmlspecialchars((string)($c['phone'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __('sale_plot_value_label') ?></label>
                    <input type="number" step="0.01" name="total_plot_value" value="<?= htmlspecialchars((string)($booking['total_plot_value'] ?? '')) ?>" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __('sale_token_amount_label') ?></label>
                    <input type="number" step="0.01" name="booking_amount" value="<?= htmlspecialchars((string)($booking['booking_amount'] ?? '')) ?>" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label"><?= __('sale_agreement_value_label') ?></label>
                    <input type="number" step="0.01" name="agreement_value" value="<?= htmlspecialchars((string)($booking['agreement_value'] ?? $booking['total_plot_value'] ?? '')) ?>" class="form-control" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= __('sale_channel_label') ?></label>
                    <select name="channel" class="form-select" required>
                        <?php foreach (['direct' => __('sale_direct'), 'associate' => __('sale_associate_label'), 'agent' => __('sale_agent'), 'self' => __('sale_self')] as $ch => $label): ?>
                            <option value="<?= $ch ?>" <?= (($booking['channel'] ?? 'direct') === $ch) ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= __('sale_associate_label') ?></label>
                    <select name="associate_id" id="booking_associate_id" class="form-select">
                        <option value=""><?= __('sale_none_select') ?></option>
                        <?php foreach ($associates as $a): ?>
                            <option value="<?= (int)($a['id'] ?? 0) ?>" <?= ((int)($booking['associate_id'] ?? 0) === (int)($a['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($a['name'] ?? '')) ?> — <?= htmlspecialchars((string)($a['phone'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= __('sale_sales_manager') ?></label>
                    <select name="sales_manager_id" class="form-select">
                        <option value=""><?= __('sale_none_select') ?></option>
                        <?php foreach ($sales_managers as $sm): ?>
                            <option value="<?= (int)($sm['id'] ?? 0) ?>" <?= ((int)($booking['sales_manager_id'] ?? 0) === (int)($sm['id'] ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars((string)($sm['name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><?= __('sale_override_commission') ?></label>
                    <input type="number" step="0.01" name="commission_pct" value="<?= htmlspecialchars((string)($booking['commission_pct'] ?? '')) ?>" class="form-control" placeholder="<?= __('sale_default') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label"><?= __('sale_notes') ?></label>
                    <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars((string)($booking['notes'] ?? '')) ?></textarea>
                </div>
            </div>
            <div class="mt-3 d-flex gap-2">
                <button class="btn btn-primary" type="submit"><i class="fas fa-save me-1"></i><?= $mode === 'edit' ? __('sale_update_booking') : __('sale_create_booking') ?></button>
                <a class="btn btn-link" href="<?= htmlspecialchars($base) ?>/admin/sales/bookings"><?= __('sale_cancel') ?></a>
            </div>
        </form>
    </div>
</div>

<?php if ($mode !== 'edit'): ?>
<script>
// Colony → Plot cascade
const plotsByColony = <?= json_encode($plotsByColony, JSON_UNESCAPED_UNICODE) ?>;
const colonySelect = document.getElementById('booking_colony_id');
const plotSelect = document.getElementById('booking_plot_id');

function updatePlots(colonyId) {
    plotSelect.innerHTML = '<option value=""><?= __('sale_select_plot') ?></option>';
    const plots = plotsByColony[colonyId] || [];
    if (plots.length === 0) {
        plotSelect.innerHTML = '<option value=""><?= __('sale_no_plots_available') ?></option>';
        return;
    }
    plots.forEach(function(p) {
        const opt = document.createElement('option');
        opt.value = p.id;
        opt.textContent = (p.plot_code || p.plot_number || '') + ' (₹' + Number(p.total_price || 0).toLocaleString('en-IN') + ') — ' + (p.block || '') + ' ' + (p.area_sqft || 0) + ' sqft';
        plotSelect.appendChild(opt);
    });
}

if (colonySelect) {
    colonySelect.addEventListener('change', function() {
        updatePlots(this.value);
    });
    // Auto-load if colony already selected (edit back)
    if (colonySelect.value) {
        updatePlots(colonySelect.value);
    }
}

// Associate search autocomplete
const associateSelect = document.getElementById('booking_associate_id');
if (associateSelect) {
    const searchInput = document.createElement('input');
    searchInput.type = 'text';
    searchInput.className = 'form-control mb-1';
    searchInput.placeholder = '<?= __('sale_search_associate') ?>';
    searchInput.style.fontSize = '0.875rem';
    associateSelect.parentNode.insertBefore(searchInput, associateSelect);

    const allOptions = Array.from(associateSelect.options);
    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase().trim();
        associateSelect.innerHTML = '';
        let matched = false;
        allOptions.forEach(function(opt) {
            if (!q || opt.text.toLowerCase().includes(q)) {
                associateSelect.appendChild(opt.cloneNode(true));
                matched = true;
            }
        });
        if (!matched) {
            const empty = document.createElement('option');
            empty.value = '';
            empty.textContent = '<?= __('sale_no_associates_found') ?>';
            associateSelect.appendChild(empty);
        }
    });

    // Show name+phone on select
    associateSelect.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected && selected.value) {
            searchInput.value = selected.text;
        }
    });
}

// Auto-fill plot value when plot is selected
plotSelect.addEventListener('change', function() {
    const selected = this.options[this.selectedIndex];
    if (selected && selected.value) {
        const plotId = selected.value;
        // Find in plotsByColony
        for (const cId in plotsByColony) {
            const plot = plotsByColony[cId].find(function(p) { return String(p.id) === plotId; });
            if (plot && plot.total_price) {
                const valueField = document.querySelector('input[name="total_plot_value"]');
                if (valueField && !valueField.value) {
                    valueField.value = plot.total_price;
                }
                const agreementField = document.querySelector('input[name="agreement_value"]');
                if (agreementField && !agreementField.value) {
                    agreementField.value = plot.total_price;
                }
                break;
            }
        }
    }
});
</script>
<?php endif; ?>
