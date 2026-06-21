<?php $page_title = $page_title ?? __('gst_transactions'); $page_heading = $page_heading ?? __('gst_transactions'); $fy = $fy ?? '2025-26'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-percent me-2 text-primary"></i><?= __('gst_transactions') ?></h2>
        <div>
            <a href="<?= BASE_URL ?>/admin/finance/gst-summary" class="btn btn-outline-primary"><i class="fas fa-chart-pie me-1"></i><?= __('gst_summary') ?></a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newGstModal"><i class="fas fa-plus me-1"></i><?= __('gst_record_gst') ?></button>
        </div>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <input type="hidden" name="url" value="/admin/finance/gst">
                <div class="col-md-3"><label class="form-label small"><?= __('gst_fy') ?></label>
                    <select name="fy" class="form-select form-select-sm">
                        <?php foreach (['2024-25','2025-26','2026-27'] as $y): ?>
                            <option value="<?= $y ?>" <?= $fy === $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2"><button class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i><?= __('gst_filter') ?></button></div>
            </form>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th><?= __('gst_date') ?></th><th><?= __('gst_type') ?></th><th><?= __('gst_party_invoice') ?></th><th><?= __('gst_gstin') ?></th><th class="text-end"><?= __('gst_taxable') ?></th><th class="text-end"><?= __('gst_cgst') ?></th><th class="text-end"><?= __('gst_sgst') ?></th><th class="text-end"><?= __('gst_igst') ?></th><th class="text-end"><?= __('gst_total_tax') ?></th></tr>
                </thead>
                <tbody>
                <?php if (empty($entries)): ?>
                    <tr><td colspan="9" class="text-center text-muted py-4"><?= __('gst_no_entries') ?> <?= htmlspecialchars($fy) ?></td></tr>
                <?php else: foreach ($entries as $g): ?>
                    <tr>
                        <td><?= htmlspecialchars($g['transaction_date'] ?? '-') ?></td>
                        <td><span class="badge bg-<?= ($g['transaction_type'] ?? '') === 'output' ? 'success' : 'info' ?>"><?= htmlspecialchars($g['transaction_type'] ?? '-') ?></span></td>
                        <td><?= htmlspecialchars($g['party_name'] ?? '-') ?><br><small class="text-muted"><?= htmlspecialchars($g['invoice_number'] ?? '') ?></small></td>
                        <td><code><?= htmlspecialchars($g['gstin'] ?? '-') ?></code></td>
                        <td class="text-end">₹<?= number_format((float)($g['taxable_amount'] ?? 0), 2) ?></td>
                        <td class="text-end">₹<?= number_format((float)($g['cgst'] ?? 0), 2) ?></td>
                        <td class="text-end">₹<?= number_format((float)($g['sgst'] ?? 0), 2) ?></td>
                        <td class="text-end">₹<?= number_format((float)($g['igst'] ?? 0), 2) ?></td>
                        <td class="text-end fw-bold">₹<?= number_format((float)($g['total_tax'] ?? 0), 2) ?></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="newGstModal" tabindex="-1"><div class="modal-dialog modal-lg">
<form method="post" action="<?= BASE_URL ?>/admin/finance/gst-store" class="modal-content">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
    <input type="hidden" name="financial_year" value="<?= htmlspecialchars($fy) ?>">
    <div class="modal-header"><h5 class="modal-title"><?= __('gst_record_transaction') ?></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-3"><label class="form-label"><?= __('gst_date') ?> <span class="text-danger">*</span></label><input type="date" name="transaction_date" required class="form-control" value="<?= date('Y-m-d') ?>"></div>
            <div class="col-md-3"><label class="form-label"><?= __('gst_type') ?> <span class="text-danger">*</span></label>
                <select name="transaction_type" required class="form-select">
                    <option value="output"><?= __('gst_type_output') ?></option>
                    <option value="input"><?= __('gst_type_input') ?></option>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label"><?= __('gst_supply_type') ?></label>
                <select name="supply_type" class="form-select">
                    <option value="intra"><?= __('gst_intra') ?></option>
                    <option value="inter"><?= __('gst_inter') ?></option>
                </select>
            </div>
            <div class="col-md-3"><label class="form-label"><?= __('gst_rate') ?></label>
                <select name="gst_rate" class="form-select">
                    <option value="0">0%</option><option value="5">5%</option><option value="12">12%</option>
                    <option value="18" selected>18%</option><option value="28">28%</option>
                </select>
            </div>
            <div class="col-md-6"><label class="form-label"><?= __('gst_party_name') ?></label><input type="text" name="party_name" class="form-control" required></div>
            <div class="col-md-3"><label class="form-label"><?= __('gst_gstin') ?></label><input type="text" name="gstin" class="form-control text-uppercase" maxlength="15"></div>
            <div class="col-md-3"><label class="form-label"><?= __('gst_invoice') ?></label><input type="text" name="invoice_number" class="form-control"></div>
            <div class="col-md-4"><label class="form-label"><?= __('gst_taxable_amount') ?> <span class="text-danger">*</span></label><input type="number" name="taxable_amount" step="0.01" min="0" required class="form-control" id="gstBase" oninput="calcGst()"></div>
            <div class="col-md-4"><label class="form-label"><?= __('gst_cgst_amount') ?></label><input type="number" name="cgst" step="0.01" class="form-control" id="cgst"></div>
            <div class="col-md-4"><label class="form-label"><?= __('gst_sgst_amount') ?></label><input type="number" name="sgst" step="0.01" class="form-control" id="sgst"></div>
            <div class="col-md-4"><label class="form-label"><?= __('gst_igst_amount') ?></label><input type="number" name="igst" step="0.01" class="form-control" id="igst"></div>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('gst_cancel') ?></button><button type="submit" class="btn btn-primary"><?= __('gst_record_btn') ?></button></div>
</form></div></div>
<script>
function calcGst(){
    const t = parseFloat(document.getElementById('gstBase').value)||0;
    const r = parseFloat(document.querySelector('[name="gst_rate"]').value)||0;
    const isInter = document.querySelector('[name="supply_type"]').value === 'inter';
    const tot = t * r / 100;
    if (isInter) {
        document.getElementById('igst').value = tot.toFixed(2);
        document.getElementById('cgst').value = '0.00';
        document.getElementById('sgst').value = '0.00';
    } else {
        const half = (tot / 2).toFixed(2);
        document.getElementById('cgst').value = half;
        document.getElementById('sgst').value = half;
        document.getElementById('igst').value = '0.00';
    }
}
</script>
