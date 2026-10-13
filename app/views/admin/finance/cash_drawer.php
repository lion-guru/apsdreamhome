<?php
/** Day-end cash drawer & safe closing. */
/** @var array $pos */
/** @var array|null $today */
/** @var array $history */
$pos = $pos ?? ['opening' => 0, 'receipts' => 0, 'expenses' => 0, 'deposits' => 0, 'system' => 0];
$today = $today ?? null;
$history = $history ?? [];
$date = $date ?? date('Y-m-d');
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$inr = function ($v) { return '₹' . number_format((float)$v, 2); };
$denoms = [500, 200, 100, 50, 20, 10];
?>
<style>@media print { .no-print { display: none !important; } body { background: #fff !important; } }</style>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-cash-register me-2"></i>Cash Drawer — <?= $e($date) ?></h4>
        <p class="text-muted mb-0">System: <strong><?= $inr($pos['system'] ?? 0) ?></strong> (open <?= $inr($pos['opening'] ?? 0) ?> + receipts <?= $inr($pos['receipts'] ?? 0) ?> − expenses <?= $inr($pos['expenses'] ?? 0) ?> − deposits <?= $inr($pos['deposits'] ?? 0) ?>)</p>
    </div>
    <div class="d-flex gap-2 no-print">
        <form method="GET" class="d-flex"><input type="date" name="date" class="form-control form-control-sm" value="<?= $e($date) ?>" onchange="this.form.submit()"></form>
        <button class="btn btn-outline-secondary btn-sm js-print"><i class="fas fa-print me-1"></i>Print Sheet</button>
<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">document.querySelectorAll(".js-print").forEach(function(b){b.addEventListener("click",function(){window.print();});});</script>
    </div>
</div>
<?php if ($today): $d = json_decode($today['denominations_json'] ?? '{}', true) ?: []; ?>
    <div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
        <h6 class="fw-bold">Closed Register — <?= $e($today['closing_date']) ?> <span class="badge bg-<?= ($today['status'] ?? '') === 'audited' ? 'info text-dark' : 'success' ?>"><?= $e($today['status'] ?? '') ?></span></h6>
        <div class="row g-2 small">
            <div class="col-md-2">System<br><strong><?= $inr($today['system_closing'] ?? 0) ?></strong></div>
            <div class="col-md-2">Physical<br><strong><?= $inr($today['physical_closing'] ?? 0) ?></strong></div>
            <div class="col-md-2">Discrepancy<br><strong class="<?= ((float)($today['discrepancy'] ?? 0) == 0) ? 'text-success' : 'text-danger' ?>"><?= $inr($today['discrepancy'] ?? 0) ?></strong></div>
            <div class="col-md-2">Bank deposit<br><strong><?= $inr($today['bank_deposit'] ?? 0) ?></strong></div>
            <div class="col-md-4">Notes<br><?= $e($today['notes'] ?? '—') ?><?= !empty($today['bank_slip_path']) ? ' · <a target="_blank" href="' . $base . '/' . $e($today['bank_slip_path']) . '">challan</a>' : '' ?></div>
        </div>
    </div></div>
<?php else: ?>
    <div class="aps-cp-card mb-3"><div class="aps-cp-card-body">
        <h6 class="fw-bold mb-3">Close the Day (denomination count)</h6>
        <form method="POST" action="<?= $base ?>/admin/finance/cash-drawer/submit-close" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
            <input type="hidden" name="closing_date" value="<?= $e($date) ?>">
            <div class="row g-2">
                <?php foreach ($denoms as $dval): ?>
                    <div class="col-4 col-md-2"><label class="form-label small">₹<?= $dval ?> ×</label><input type="number" min="0" name="denom_<?= $dval ?>" class="form-control denom" data-val="<?= $dval ?>" value="0"></div>
                <?php endforeach; ?>
                <div class="col-4 col-md-2"><label class="form-label small">Coins ₹</label><input type="number" min="0" name="denom_coins" class="form-control denom" data-val="1" value="0"></div>
                <div class="col-md-3"><label class="form-label small">Bank deposit ₹</label><input type="number" step="0.01" min="0" name="bank_deposit" id="bankDeposit" class="form-control" value="0"></div>
                <div class="col-md-3"><label class="form-label small">Challan photo (≤5MB)</label><input type="file" name="bank_slip" class="form-control" accept=".jpg,.jpeg,.png,.webp"></div>
                <div class="col-12"><label class="form-label small">Notes</label><input type="text" name="notes" class="form-control" maxlength="1000"></div>
            </div>
            <div class="alert alert-info mt-3 mb-0">Counted: <strong id="countTotal">₹0</strong> · System (pre-deposit) <strong><?= $inr($pos['system'] ?? 0) ?></strong> · Diff vs (system − deposit): <strong id="countDiff">—</strong></div>
            <button type="submit" class="btn btn-success mt-3"><i class="fas fa-lock me-1"></i>Freeze Day Closing</button>
        </form>
    </div></div>
    <script>
    (function () {
        var sys = <?= json_encode((float)($pos['system'] ?? 0) - 0) ?>;
        function recalc() {
            var total = 0;
            document.querySelectorAll('.denom').forEach(function (el) {
                total += (parseInt(el.value || '0', 10) || 0) * parseFloat(el.dataset.val || '1');
            });
            var dep = parseFloat((document.getElementById('bankDeposit') || {}).value || '0') || 0;
            var diff = total - (sys - dep);
            document.getElementById('countTotal').textContent = '₹' + total.toLocaleString('en-IN');
            document.getElementById('countDiff').textContent = '₹' + diff.toLocaleString('en-IN') + (diff === 0 ? ' (tally ✓)' : '');
        }
        document.querySelectorAll('.denom, #bankDeposit').forEach(function (el) {
            el.addEventListener('input', recalc);
        });
        recalc();
    })();
    </script>
<?php endif; ?>
<?php if (!empty($history)): ?>
<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Date</th><th class="text-end">System</th><th class="text-end">Physical</th><th class="text-end">Diff</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($history as $h): ?>
            <tr>
                <td><a href="<?= $base ?>/admin/finance/cash-drawer?date=<?= $e($h['closing_date']) ?>"><?= $e($h['closing_date']) ?></a></td>
                <td class="text-end"><?= $inr($h['system_closing'] ?? 0) ?></td>
                <td class="text-end"><?= $inr($h['physical_closing'] ?? 0) ?></td>
                <td class="text-end <?= ((float)($h['discrepancy'] ?? 0) == 0) ? 'text-success' : 'text-danger' ?>"><?= $inr($h['discrepancy'] ?? 0) ?></td>
                <td><span class="badge bg-secondary"><?= $e($h['status'] ?? '') ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
</div></div>
<?php endif; ?>
