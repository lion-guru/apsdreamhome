<?php
// Section 194H TDS quarterly report (Form 26Q filing aid).
// Vars: $rows, $summary, $range, $threshold, $threshold_label, $fy, $quarter, $fy_options.
$rows = $rows ?? [];
$summary = $summary ?? ['deductees' => 0, 'gross' => 0, 'tds' => 0, 'net_paid' => 0];
$range = $range ?? ['start' => '', 'end' => '', 'qlabel' => ''];
$threshold_label = $threshold_label ?? '';
$fy = $fy ?? '';
$quarter = $quarter ?? '';
$fy_options = $fy_options ?? [$fy];
$q = ['fy' => $fy, 'quarter' => $quarter];
$exportUrl = BASE_URL . '/admin/reports/tds-194h/export?fy=' . urlencode($fy) . ($quarter !== '' ? '&quarter=' . urlencode($quarter) : '');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Section 194H TDS Report <small class="text-muted">(Form 26Q)</small></h4>
    <div class="d-flex gap-2">
        <a href="<?php echo BASE_URL; ?>/admin/efiling/tds" class="btn btn-outline-secondary btn-sm"><i class="fas fa-landmark me-1"></i>E-Filing</a>
        <a href="<?php echo $exportUrl; ?>" class="btn btn-success btn-sm"><i class="fas fa-file-csv me-1"></i>Export CSV</a>
    </div>
</div>

<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo BASE_URL; ?>/admin/reports/tds-194h" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Financial Year</label>
                <select name="fy" class="form-select">
                    <?php foreach ($fy_options as $o): ?>
                    <option value="<?php echo $o; ?>" <?php echo $o === $fy ? 'selected' : ''; ?>><?php echo $o; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Quarter</label>
                <select name="quarter" class="form-select">
                    <option value="" <?php echo $quarter === '' ? 'selected' : ''; ?>>All quarters</option>
                    <?php foreach (['Q1' => 'Q1 (Apr–Jun)', 'Q2' => 'Q2 (Jul–Sep)', 'Q3' => 'Q3 (Oct–Dec)', 'Q4' => 'Q4 (Jan–Mar)'] as $k => $l): ?>
                    <option value="<?php echo $k; ?>" <?php echo $quarter === $k ? 'selected' : ''; ?>><?php echo $l; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter me-1"></i>Apply</button>
            </div>
            <div class="col-md-3 text-muted small">
                Period: <?php echo e($range['start'] ?? ''); ?> to <?php echo e($range['end'] ?? ''); ?><br>
                194H rate 5% (valid PAN) / 20% u/s 206AA (no PAN) · Threshold <?php echo e($threshold_label); ?>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Deductees', $summary['deductees'] ?? 0, 'primary', 'fa-users', false],
        ['Gross Brokerage', $summary['gross'] ?? 0, 'primary', 'fa-briefcase', true],
        ['TDS Deducted', $summary['tds'] ?? 0, 'danger', 'fa-cut', true],
        ['Net Paid (payouts)', $summary['net_paid'] ?? 0, 'success', 'fa-hand-holding-usd', true],
    ];
    foreach ($cards as $c): ?>
    <div class="col-md-3 col-6">
        <div class="card shadow-sm border-start border-4 border-<?php echo $c[2]; ?>">
            <div class="card-body py-3">
                <div class="fs-5 fw-bold"><?php echo $c[4] ? '₹' . number_format($c[1], 2) : (int)$c[1]; ?></div>
                <div class="text-muted small text-uppercase"><i class="fas <?php echo $c[3]; ?> me-1"></i><?php echo $c[0]; ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover mb-0">
                <thead class="table-dark">
                    <tr><th>Associate</th><th>PAN</th><th class="text-end">Gross</th><th>Threshold</th><th class="text-end">Rate</th><th class="text-end">TDS</th><th class="text-end">Net Paid</th><th>UTR / Ref</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($rows)): ?>
                        <tr><td colspan="8" class="text-center text-muted py-4">No commission payouts in this period.</td></tr>
                    <?php else: ?>
                    <?php foreach ($rows as $r): ?>
                        <tr>
                            <td><strong><?php echo e($r['name'] ?? ''); ?></strong><br><small class="text-muted"><?php echo e($r['code'] ?? ''); ?> · <?php echo (int)($r['entries'] ?? 0); ?> entries</small></td>
                            <td><code><?php echo e($r['pan'] ?? ''); ?></code><br>
                                <span class="badge bg-<?php echo !empty($r['pan_valid']) ? 'success' : 'danger'; ?>"><?php echo !empty($r['pan_valid']) ? 'PAN OK · 5%' : 'NO PAN · 20%'; ?></span></td>
                            <td class="text-end">₹<?php echo number_format($r['gross'] ?? 0, 2); ?></td>
                            <td><?php echo !empty($r['below_threshold']) ? '<span class="badge bg-secondary">Below ' . e($threshold_label) . '</span>' : '<span class="badge bg-info">Taxable</span>'; ?></td>
                            <td class="text-end"><?php echo $r['rate'] ?? 0; ?>%</td>
                            <td class="text-end text-danger">₹<?php echo number_format($r['tds'] ?? 0, 2); ?></td>
                            <td class="text-end text-success">₹<?php echo number_format($r['net_paid'] ?? 0, 2); ?></td>
                            <td><small><?php echo e($r['utr'] ?? ''); ?></small></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr class="table-secondary fw-bold">
                        <td>TOTAL (<?php echo (int)($summary['deductees'] ?? 0); ?>)</td><td></td>
                        <td class="text-end">₹<?php echo number_format($summary['gross'] ?? 0, 2); ?></td><td></td><td></td>
                        <td class="text-end">₹<?php echo number_format($summary['tds'] ?? 0, 2); ?></td>
                        <td class="text-end">₹<?php echo number_format($summary['net_paid'] ?? 0, 2); ?></td><td></td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<p class="text-muted small mt-2">CA note: TDS computed per FY gross per deductee via TdsConfigService (194H). Below-threshold rows attract nil TDS. Use Export CSV for Form 26Q filing annexure.</p>
