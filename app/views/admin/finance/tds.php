<?php $page_title = $page_title ?? __('tds_register'); $page_heading = $page_heading ?? __('tds_register_compliance'); $fy = $fy ?? '2025-26'; $quarter = $quarter ?? null; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i><?= __('tds_register') ?></h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/tds-certificates" class="btn btn-outline-primary"><i class="fas fa-certificate me-1"></i><?= __('tds_certificates') ?></a>
            <a href="<?= BASE_URL ?>/admin/finance/tds-record" class="btn btn-primary"><i class="fas fa-plus me-1"></i><?= __('tds_record_tds') ?></a>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="url" value="/admin/finance/tds">
                <div class="col-md-3"><label class="form-label small"><?= __('tds_financial_year') ?></label>
                    <select name="fy" class="form-select form-select-sm">
                        <?php foreach (['2024-25','2025-26','2026-27','2027-28'] as $y): ?>
                            <option value="<?= $y ?>" <?= $fy === $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3"><label class="form-label small"><?= __('tds_quarter') ?></label>
                    <select name="quarter" class="form-select form-select-sm">
                        <option value=""><?= __('tds_all') ?></option>
                        <?php foreach (['Q1','Q2','Q3','Q4'] as $q): ?>
                            <option value="<?= $q ?>" <?= $quarter === $q ? 'selected' : '' ?>><?= $q ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i><?= __('tds_filter') ?></button></div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label"><?= __('tds_total_gross') ?></div><div class="aps-cp-stat-value">₹<?= number_format((float)($summary['total_gross'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-4"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label"><?= __('tds_total_tds_deducted') ?></div><div class="aps-cp-stat-value text-danger">₹<?= number_format((float)($summary['total_tds'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-4"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label"><?= __('tds_fy') ?></div><div class="aps-cp-stat-value text-primary"><?= htmlspecialchars($fy) ?></div></div></div></div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <div class="table-responsive"><table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th><?= __('tds_date') ?></th><th><?= __('tds_section') ?></th><th><?= __('tds_deductee') ?></th><th><?= __('tds_pan') ?></th><th><?= __('tds_quarter') ?></th><th class="text-end"><?= __('tds_gross') ?></th><th class="text-end"><?= __('tds_amount') ?></th><th><?= __('tds_status') ?></th></tr>
                </thead>
                <tbody>
                <?php if (empty($entries)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4"><?= __('tds_no_entries') ?> <?= htmlspecialchars($fy) ?></td></tr>
                <?php else: foreach ($entries as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['tds_date'] ?? '-') ?></td>
                        <td><code><?= htmlspecialchars($t['section_code'] ?? '-') ?></code></td>
                        <td><?= htmlspecialchars($t['deductee_name'] ?? '-') ?></td>
                        <td><code><?= htmlspecialchars($t['deductee_pan'] ?? '-') ?></code></td>
                        <td><span class="badge bg-info"><?= htmlspecialchars($t['quarter'] ?? '-') ?></span></td>
                        <td class="text-end">₹<?= number_format((float)($t['gross_amount'] ?? 0), 2) ?></td>
                        <td class="text-end fw-bold text-danger">₹<?= number_format((float)($t['tds_amount'] ?? 0), 2) ?></td>
                        <td><span class="badge bg-<?= ($t['deposit_status'] ?? '') === 'deposited' ? 'success' : 'warning' ?>"><?= htmlspecialchars(__($t['deposit_status'] ?? 'pending')) ?></span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table></div>
        </div>
    </div>
</div>
