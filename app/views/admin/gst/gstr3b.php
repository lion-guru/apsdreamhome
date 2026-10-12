<?php
$base = rtrim(BASE_URL, '/');
$csrf = $this->getCsrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - APS Dream Home</title>
    <link href="<?= $base ?>/assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>/assets/fonts/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="<?= $base ?>/assets/css/uiux-fixes.css?v=1">
    <style>
        .gstr3b-table { font-size: .9rem; }
        .gstr3b-table th, .gstr3b-table td { padding: .5rem; }
        .section-header { background: #f0f4f8; font-weight: 600; }
        .input-cell input { border: 1px solid #ced4da; border-radius: .25rem; padding: .25rem .5rem; width: 100%; text-align: right; }
        .total-row { font-weight: 700; background: #e7f3ff; }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../../layouts/admin.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-file-import me-2 text-warning"></i><?= htmlspecialchars($page_title) ?></h1>
        <div class="d-flex gap-2">
            <a href="<?= $base ?>/admin/gst/gstr1" class="btn btn-outline-info"><i class="fas fa-file-export me-1"></i> GSTR-1</a>
            <a href="<?= $base ?>/admin/gst/invoices" class="btn btn-outline-secondary"><i class="fas fa-file-invoice me-1"></i> Invoices</a>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <form method="GET" class="row g-3 align-items-end">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <div class="col-md-4">
                    <label class="form-label">Return Period</label>
                    <input type="month" name="period" class="form-control" value="<?= htmlspecialchars($period) ?>" required>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-sync me-1"></i> Recompute</button>
                    <button type="submit" name="save" value="1" class="btn btn-success" onclick="return confirm('Save GSTR-3B summary data?')"><i class="fas fa-save me-1"></i> Save</button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <form method="POST" id="gstr3bForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="period" value="<?= htmlspecialchars($period) ?>">
                <input type="hidden" name="save" value="1">
                
                <div class="table-responsive">
                    <table class="table table-bordered gstr3b-table">
                        <thead>
                            <tr class="section-header">
                                <th colspan="6" class="text-center">GSTR-3B Summary - <?= htmlspecialchars($period) ?></th>
                            </tr>
                            <tr class="table-light">
                                <th>Table</th>
                                <th>Description</th>
                                <th class="text-end">Taxable Value</th>
                                <th class="text-end">IGST</th>
                                <th class="text-end">CGST</th>
                                <th class="text-end">SGST</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sections = [
                                '3.1' => 'Outward Supplies (Taxable)',
                                '3.2' => 'Outward Supplies (Nil/Exempt/Non-GST)',
                                '4' => 'ITC Available',
                                '5' => 'Tax Liability & Payment',
                            ];
                            foreach ($sections as $section => $desc): 
                                $d = $data[$section] ?? [];
                                $s = $saved[$section] ?? [];
                            ?>
                                <tr class="section-header">
                                    <td colspan="6"><?= $section ?> - <?= $desc ?></td>
                                </tr>
                                <?php if ($section === '3.1' || $section === '3.2'): ?>
                                    <tr>
                                        <td><?= $section ?></td>
                                        <td><?= $desc ?></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][taxable]" class="form-control input-cell" value="<?= number_format($s['taxable_value'] ?? $d['taxable'] ?? 0, 2, '.', '') ?>"></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][igst]" class="form-control input-cell" value="<?= number_format($s['igst_amount'] ?? $d['igst'] ?? 0, 2, '.', '') ?>"></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][cgst]" class="form-control input-cell" value="<?= number_format($s['cgst_amount'] ?? $d['cgst'] ?? 0, 2, '.', '') ?>"></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][sgst]" class="form-control input-cell" value="<?= number_format($s['sgst_amount'] ?? $d['sgst'] ?? 0, 2, '.', '') ?>"></td>
                                    </tr>
                                <?php elseif ($section === '4'): ?>
                                    <tr>
                                        <td><?= $section ?></td>
                                        <td><?= $desc ?></td>
                                        <td></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][igst]" class="form-control input-cell" value="<?= number_format($s['igst_amount'] ?? $d['igst'] ?? 0, 2, '.', '') ?>"></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][cgst]" class="form-control input-cell" value="<?= number_format($s['cgst_amount'] ?? $d['cgst'] ?? 0, 2, '.', '') ?>"></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][sgst]" class="form-control input-cell" value="<?= number_format($s['sgst_amount'] ?? $d['sgst'] ?? 0, 2, '.', '') ?>"></td>
                                    </tr>
                                <?php elseif ($section === '5'): ?>
                                    <tr>
                                        <td><?= $section ?></td>
                                        <td><?= $desc ?></td>
                                        <td></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][tax_paid_itc]" class="form-control input-cell" value="<?= number_format($s['tax_paid_itc'] ?? $d['tax_paid_itc'] ?? 0, 2, '.', '') ?>"></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][tax_paid_cash]" class="form-control input-cell" value="<?= number_format($s['tax_paid_cash'] ?? $d['tax_paid_cash'] ?? 0, 2, '.', '') ?>"></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][interest]" class="form-control input-cell" value="<?= number_format($s['interest'] ?? $d['interest'] ?? 0, 2, '.', '') ?>"></td>
                                    </tr>
                                    <tr>
                                        <td colspan="2">Late Fee</td>
                                        <td></td>
                                        <td class="text-end"><input type="number" step="0.01" name="data[<?= $section ?>][late_fee]" class="form-control input-cell" value="<?= number_format($s['late_fee'] ?? $d['late_fee'] ?? 0, 2, '.', '') ?>"></td>
                                        <td class="text-end"></td>
                                        <td class="text-end"></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <tr class="total-row">
                                <td colspan="2">Grand Total</td>
                                <td class="text-end" id="totTaxable">0.00</td>
                                <td class="text-end" id="totIgst">0.00</td>
                                <td class="text-end" id="totCgst">0.00</td>
                                <td class="text-end" id="totSgst">0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="d-flex gap-2 justify-content-end mt-3 no-print">
                    <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-save me-1"></i> Save GSTR-3B Data</button>
                    <button type="button" class="btn btn-primary btn-lg" onclick="recompute()"><i class="fas fa-calculator me-1"></i> Recompute</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="<?= $base ?>/assets/js/bootstrap.bundle.min.js"></script>
<script>
function recompute() {
    const period = '<?= htmlspecialchars($period) ?>';
    window.location.href = '<?= $base ?>/admin/gst/gstr3b?period=' + period;
}

function updateTotals() {
    let taxable = 0, igst = 0, cgst = 0, sgst = 0;
    document.querySelectorAll('input[name^="data["]').forEach(input => {
        const val = parseFloat(input.value) || 0;
        const name = input.name;
        if (name.includes('[taxable]')) taxable += val;
        else if (name.includes('[igst]')) igst += val;
        else if (name.includes('[cgst]')) cgst += val;
        else if (name.includes('[sgst]')) sgst += val;
        else if (name.includes('[tax_paid_itc]')) igst += val; // ITC includes all
        else if (name.includes('[tax_paid_cash]')) cgst += val; // Cash payment
    });
    document.getElementById('totTaxable').textContent = taxable.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('totIgst').textContent = igst.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('totCgst').textContent = cgst.toLocaleString('en-IN', {minimumFractionDigits: 2});
    document.getElementById('totSgst').textContent = sgst.toLocaleString('en-IN', {minimumFractionDigits: 2});
}

document.querySelectorAll('input[name^="data["]').forEach(input => {
    input.addEventListener('input', updateTotals);
});
updateTotals();
</script>
</body>
</html>