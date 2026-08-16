<?php $page_title = $page_title ?? 'GST Summary'; $page_heading = $page_heading ?? 'GST Summary & ITC Reconciliation'; $fy = $fy ?? '2025-26'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>GST Summary — FY <?= htmlspecialchars($fy ?? '') ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/gst" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Output Tax (Sales)</div><div class="aps-cp-stat-value text-success">₹<?= number_format((float)($summary['output']['tax'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Input Tax Credit (ITC)</div><div class="aps-cp-stat-value text-info">₹<?= number_format((float)($summary['input']['tax'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Net GST Payable</div><div class="aps-cp-stat-value text-primary">₹<?= number_format((float)($summary['net_payable'] ?? 0), 0) ?></div></div></div></div>
        <div class="col-md-3"><div class="aps-cp-card"><div class="aps-cp-card-body"><div class="aps-cp-stat-label">Total Taxable</div><div class="aps-cp-stat-value">₹<?= number_format((float)(($summary['output']['taxable'] ?? 0) + ($summary['input']['taxable'] ?? 0)), 0) ?></div></div></div></div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><h5 class="mb-0">Output GST (Sales)</h5></div>
                <div class="aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <tr><td>CGST</td><td class="text-end">₹<?= number_format((float)($summary['output']['cgst'] ?? 0), 2) ?></td></tr>
                        <tr><td>SGST</td><td class="text-end">₹<?= number_format((float)($summary['output']['sgst'] ?? 0), 2) ?></td></tr>
                        <tr><td>IGST</td><td class="text-end">₹<?= number_format((float)($summary['output']['igst'] ?? 0), 2) ?></td></tr>
                        <tr class="table-light"><th>Total</th><th class="text-end">₹<?= number_format((float)($summary['output']['tax'] ?? 0), 2) ?></th></tr>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><h5 class="mb-0">Input GST (ITC)</h5></div>
                <div class="aps-cp-card-body">
                    <div class="table-responsive"><table class="table table-sm mb-0">
                        <tr><td>CGST</td><td class="text-end">₹<?= number_format((float)($summary['input']['cgst'] ?? 0), 2) ?></td></tr>
                        <tr><td>SGST</td><td class="text-end">₹<?= number_format((float)($summary['input']['sgst'] ?? 0), 2) ?></td></tr>
                        <tr><td>IGST</td><td class="text-end">₹<?= number_format((float)($summary['input']['igst'] ?? 0), 2) ?></td></tr>
                        <tr class="table-light"><th>Total ITC</th><th class="text-end">₹<?= number_format((float)($summary['input']['tax'] ?? 0), 2) ?></th></tr>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>
