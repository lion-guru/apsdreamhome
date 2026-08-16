<?php $page_title = $page_title ?? __('cf_forecast_title'); $page_heading = $page_heading ?? __('cf_forecast_title'); $days = $days ?? 30; $summary = $summary ?? []; $rows = $rows ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i><?= htmlspecialchars($page_heading ?? '') ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/dashboard" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i><?= __('cf_back_to_finance') ?></a>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-md-4">
                    <label class="form-label small"><?= __('cf_forecast_period') ?></label>
                    <select name="days" class="form-select form-select-sm">
                        <option value="7" <?= $days == 7 ? 'selected' : '' ?>><?= __('cf_next_7_days') ?></option>
                        <option value="14" <?= $days == 14 ? 'selected' : '' ?>><?= __('cf_next_14_days') ?></option>
                        <option value="30" <?= $days == 30 ? 'selected' : '' ?>><?= __('cf_next_30_days') ?></option>
                        <option value="60" <?= $days == 60 ? 'selected' : '' ?>><?= __('cf_next_60_days') ?></option>
                        <option value="90" <?= $days == 90 ? 'selected' : '' ?>><?= __('cf_next_90_days') ?></option>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i><?= __('cf_update') ?></button></div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?= __('cf_expected_inflow') ?></div>
                    <div class="aps-cp-stat-value text-success">₹<?= number_format((float)($summary['inflow_total'] ?? 0), 0) ?></div>
                    <small class="text-muted"><?= __('cf_weighted') ?>: ₹<?= number_format((float)($summary['weighted_inflow'] ?? 0), 0) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?= __('cf_expected_outflow') ?></div>
                    <div class="aps-cp-stat-value text-danger">₹<?= number_format((float)($summary['outflow_total'] ?? 0), 0) ?></div>
                    <small class="text-muted"><?= __('cf_weighted') ?>: ₹<?= number_format((float)($summary['weighted_outflow'] ?? 0), 0) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?= __('cf_net_cash_flow') ?></div>
                    <div class="aps-cp-stat-value <?= ((float)($summary['net'] ?? 0)) >= 0 ? 'text-success' : 'text-danger' ?>">₹<?= number_format((float)($summary['net'] ?? 0), 0) ?></div>
                    <small class="text-muted"><?= __('cf_weighted_net') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label"><?= __('cf_forecast_entries') ?></div>
                    <div class="aps-cp-stat-value"><?= count($rows) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($from ?? '') ?> to <?= htmlspecialchars($to ?? '') ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= __('cf_date') ?></th>
                        <th><?= __('cf_direction') ?></th>
                        <th><?= __('cf_category') ?></th>
                        <th><?= __('cf_description') ?></th>
                        <th class="text-end"><?= __('cf_amount') ?></th>
                        <th class="text-end"><?= __('cf_probability') ?></th>
                        <th class="text-end"><?= __('cf_weighted') ?></th>
                        <th><?= __('cf_days_ahead') ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><?= __('cf_no_entries') ?></td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['expected_date'] ?? '') ?></td>
                        <td><span class="badge bg-<?= ($r['type'] ?? '') === 'inflow' ? 'success' : 'danger' ?>"><?= htmlspecialchars(__($r['type'] ?? '')) ?></span></td>
                        <td><small class="text-muted"><?= htmlspecialchars(__($r['category'] ?? '-')) ?></small></td>
                        <td><?= htmlspecialchars($r['description'] ?? '-') ?></td>
                        <td class="text-end fw-bold <?= ($r['type'] ?? '') === 'inflow' ? 'text-success' : 'text-danger' ?>">₹<?= number_format((float)($r['expected_amount'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= (int)($r['probability_pct'] ?? 100) ?>%</td>
                        <td class="text-end">₹<?= number_format((float)($r['weighted_amount'] ?? 0), 2) ?></td>
                        <td><span class="badge bg-secondary"><?= (int)($r['days_ahead'] ?? 0) ?>d</span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
