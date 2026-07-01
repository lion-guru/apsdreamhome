<?php $page_title = $page_title ?? __('finance_tds_certificates'); $page_heading = $page_heading ?? __('finance_tds_certificates'); $fy = $fy ?? '2025-26'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-certificate me-2 text-primary"></i><?php echo __('finance_tds_certificates'); ?></h2>
    </div>
    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="url" value="/admin/finance/tds-certificates">
                <div class="col-md-3"><label class="form-label small"><?php echo __('finance_fy'); ?></label>
                    <select name="fy" class="form-select form-select-sm">
                        <?php foreach (['2024-25','2025-26','2026-27'] as $y): ?>
                            <option value="<?= $y ?>" <?= $fy === $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i><?php echo __('finance_filter'); ?></button></div>
            </form>
        </div>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light"><tr><th><?php echo __('finance_certificate_hash'); ?></th><th><?php echo __('finance_deductee'); ?></th><th><?php echo __('finance_pan'); ?></th><th><?php echo __('finance_fy'); ?></th><th><?php echo __('finance_quarter'); ?></th><th class="text-end"><?php echo __('finance_tds_amt'); ?></th><th><?php echo __('finance_issued'); ?></th></tr></thead>
                <tbody>
                <?php if (empty($certificates)): ?>
                    <tr><td colspan="7" class="text-center text-muted py-4"><?php echo __('finance_no_certificates_issued_for'); ?> <?= htmlspecialchars($fy) ?></td></tr>
                <?php else: foreach ($certificates as $c): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($c['certificate_number'] ?? '-') ?></code></td>
                        <td><?= htmlspecialchars($c['deductee_name'] ?? '-') ?></td>
                        <td><code><?= htmlspecialchars($c['deductee_pan'] ?? '-') ?></code></td>
                        <td><?= htmlspecialchars($c['financial_year'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($c['quarter'] ?? '-') ?></td>
                        <td class="text-end">₹<?= number_format((float)($c['tds_amount'] ?? 0), 2) ?></td>
                        <td><?= htmlspecialchars($c['issued_date'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
