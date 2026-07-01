<?php $page_title = $page_title ?? __('finance_cash_flow_forecast'); $page_heading = $page_heading ?? __('finance_cash_flow_forecast'); $days = $days ?? 30; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i><?php echo __('finance_cash_flow_forecast'); ?></h2>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/finance/forecast?days=7" class="btn btn-sm btn-outline-primary <?= $days == 7 ? 'active' : '' ?>">7d</a>
            <a href="<?= BASE_URL ?>/admin/finance/forecast?days=30" class="btn btn-sm btn-outline-primary <?= $days == 30 ? 'active' : '' ?>">30d</a>
            <a href="<?= BASE_URL ?>/admin/finance/forecast?days=60" class="btn btn-sm btn-outline-primary <?= $days == 60 ? 'active' : '' ?>">60d</a>
            <a href="<?= BASE_URL ?>/admin/finance/forecast?days=90" class="btn btn-sm btn-outline-primary <?= $days == 90 ? 'active' : '' ?>">90d</a>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-header"><h5 class="mb-0"><?php echo __('finance_projected_cash_flows'); ?> (<?= (int)$days ?> <?php echo __('finance_days'); ?>)</h5></div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th><?php echo __('finance_date'); ?></th><th><?php echo __('finance_source'); ?></th><th><?php echo __('finance_category'); ?></th><th><?php echo __('finance_direction'); ?></th><th class="text-end"><?php echo __('finance_amount'); ?></th><th><?php echo __('finance_probability'); ?></th></tr>
                </thead>
                <tbody>
                <?php if (empty($forecast)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4"><?php echo __('finance_no_forecast_entries'); ?></td></tr>
                <?php else: foreach ($forecast as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($f['forecast_date'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($f['source'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($f['category'] ?? '-') ?></td>
                        <td><span class="badge bg-<?= ($f['direction'] ?? '') === 'inflow' ? 'success' : 'danger' ?>"><?= htmlspecialchars($f['direction'] ?? '-') ?></span></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($f['expected_amount'] ?? 0), 0) ?></td>
                        <td><?= htmlspecialchars($f['probability'] ?? '-') ?>%</td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-balance-scale me-2"></i><?php echo __('finance_actual_vs_forecast'); ?></h5></div>
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th><?php echo __('finance_date'); ?></th><th class="text-end"><?php echo __('finance_forecast_inflow'); ?></th><th class="text-end"><?php echo __('finance_forecast_outflow'); ?></th></tr></thead>
                <tbody>
                <?php if (empty($actuals)): ?>
                    <tr><td colspan="3" class="text-center text-muted py-4"><?php echo __('finance_no_data'); ?></td></tr>
                <?php else: foreach ($actuals as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['forecast_date'] ?? '-') ?></td>
                        <td class="text-end text-success">₹<?= number_format((float)($a['forecast_inflow'] ?? 0), 0) ?></td>
                        <td class="text-end text-danger">₹<?= number_format((float)($a['forecast_outflow'] ?? 0), 0) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
