<?php
/** @var array $me */
/** @var array $payouts */
$me = $me ?? []; $payouts = $payouts ?? [];
$fy = $fy ?? ''; $fy_options = $fy_options ?? [];
$gross = $gross ?? 0; $tds = $tds ?? 0; $net = $net ?? 0; $rate = $rate ?? 5;
$base = defined('BASE_URL') ? BASE_URL : '';
$stColors = ['paid' => 'success', 'processing' => 'warning', 'pending' => 'secondary', 'failed' => 'danger', 'on_hold' => 'warning', 'cancelled' => 'dark'];
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-receipt me-2"></i>My TDS Slips</h4>
        <p class="text-muted mb-0 small"><?= htmlspecialchars($me['name'] ?? '') ?> · PAN: <strong><?= htmlspecialchars(!empty($me['pan_number']) ? strtoupper($me['pan_number']) : 'not on record — 20% TDS may apply u/s 206AA') ?></strong></p>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <form method="GET" class="d-flex gap-2">
            <select name="fy" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                <?php foreach ($fy_options as $o): ?>
                    <option value="<?= $o ?>" <?= $o === $fy ? 'selected' : '' ?>>FY <?= $o ?></option>
                <?php endforeach; ?>
            </select>
        </form>
        <a href="<?= $base ?>/associate/tds-slips/certificate?fy=<?= urlencode($fy) ?>" target="_blank" class="btn btn-primary btn-sm">
            <i class="fas fa-certificate me-1"></i>Annual Certificate (PDF)
        </a>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-3"><div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
        <div class="fw-bold fs-4">₹<?= number_format($gross, 0) ?></div><div class="text-muted small">Gross Commission (FY <?= htmlspecialchars($fy) ?>)</div>
    </div></div></div>
    <div class="col-md-3"><div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
        <div class="fw-bold fs-4 text-danger">₹<?= number_format($tds, 0) ?></div><div class="text-muted small">TDS Deducted @ <?= $rate ?>% (194H)</div>
    </div></div></div>
    <div class="col-md-3"><div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
        <div class="fw-bold fs-4 text-success">₹<?= number_format($net, 0) ?></div><div class="text-muted small">Net Paid to You</div>
    </div></div></div>
    <div class="col-md-3"><div class="aps-cp-card text-center"><div class="aps-cp-card-body py-3">
        <div class="fw-bold fs-4"><?= count($payouts) ?></div><div class="text-muted small">Payout Slips</div>
    </div></div></div>
</div>

<div class="aps-cp-card">
    <div class="aps-cp-card-header"><span><i class="fas fa-list me-2"></i>Payout-wise TDS Breakup</span></div>
    <div class="aps-cp-card-body p-0">
        <?php if (empty($payouts)): ?>
            <div class="text-center text-muted py-4">No payouts in FY <?= htmlspecialchars($fy) ?> yet.</div>
        <?php else: ?>
        <div class="table-responsive"><table class="table table-hover mb-0">
            <thead class="table-light"><tr><th>Date</th><th class="text-end">Gross</th><th class="text-end">TDS</th><th class="text-end">Other Ded.</th><th class="text-end">Net</th><th>Mode</th><th>Ref / UTR</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach ($payouts as $p):
                $ref = $p['transaction_ref'] ?: $p['cheque_number'] ?: '—'; ?>
                <tr>
                    <td><?= htmlspecialchars($p['paid_date'] ?: substr($p['created_at'] ?? '', 0, 10)) ?></td>
                    <td class="text-end">₹<?= number_format((float)$p['gross_amount'], 0) ?></td>
                    <td class="text-end text-danger">₹<?= number_format((float)$p['tds_amount'], 0) ?></td>
                    <td class="text-end">₹<?= number_format((float)$p['other_deductions'], 0) ?></td>
                    <td class="text-end fw-bold text-success">₹<?= number_format((float)$p['net_amount'], 0) ?></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $p['payment_mode'] ?? ''))) ?></span></td>
                    <td class="small"><?= htmlspecialchars($ref) ?></td>
                    <td><span class="badge bg-<?= $stColors[$p['status']] ?? 'secondary' ?>"><?= ucfirst(str_replace('_', ' ', $p['status'] ?? '')) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div>
        <?php endif; ?>
    </div>
</div>
<p class="text-muted small mt-2"><i class="fas fa-info-circle me-1"></i>TDS u/s 194H @ 5% (20% if PAN invalid/missing u/s 206AA). Use the annual certificate for your ITR filing.</p>
