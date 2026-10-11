<?php
/** @var array $rows */
$rows = $rows ?? [];
$status_filter = $status_filter ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$csrf = htmlspecialchars($_SESSION['csrf_token'] ?? '');
$e = function ($v) { return htmlspecialchars((string)($v ?? '')); };
$badge = ['pending' => 'warning', 'verified' => 'success', 'rejected' => 'danger'];
?>
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-globe me-2"></i>NRI Investment &amp; FIRC Desk</h4>
        <p class="text-muted mb-0">Cross-border buyers (UAE, Saudi, US, UK…) — FIRC + FEMA tracking</p>
    </div>
    <form method="GET" class="d-flex gap-2">
        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">All statuses</option>
            <?php foreach (['pending', 'verified', 'rejected'] as $s): ?>
                <option value="<?= $s ?>" <?= $status_filter === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="aps-cp-card"><div class="aps-cp-card-body p-0">
<?php if (empty($rows)): ?>
    <div class="text-center py-5"><i class="fas fa-passport fa-3x text-muted mb-3"></i>
        <h6 class="text-muted">No NRI records<?= $status_filter !== '' ? ' with status ' . $e($status_filter) : '' ?></h6>
        <p class="text-muted small">NRI buyers submit FIRC + passport KYC from <code>/customer/nri-desk</code>.</p>
    </div>
<?php else: ?>
    <div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>#</th><th>Buyer</th><th>Booking</th><th>Country</th><th>Account</th><th>FIRC No.</th><th>Docs</th><th>Status</th><th>Action</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
            <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><?= $e($r['customer_name'] ?? '') ?><br><small class="text-muted"><?= $e($r['customer_phone'] ?? '') ?> · <?= $e($r['passport_number'] ?? '') ?></small></td>
                <td><?= $e($r['booking_number'] ?? '') ?><br><small class="text-muted"><?= $e($r['plot_number'] ?? '') ?>, <?= $e($r['colony_name'] ?? '') ?></small></td>
                <td><?= $e($r['country_of_residence'] ?? '') ?></td>
                <td><span class="badge bg-light text-dark border"><?= $e($r['bank_account_type'] ?? '') ?></span></td>
                <td><?= $e($r['firc_number'] ?? '—') ?></td>
                <td class="small">
                    <?php if (!empty($r['firc_doc_path'])): ?><a href="<?= $base ?>/<?= $e($r['firc_doc_path']) ?>" target="_blank">FIRC</a><?php endif; ?>
                    <?php if (!empty($r['passport_doc_path'])): ?><a href="<?= $base ?>/<?= $e($r['passport_doc_path']) ?>" target="_blank">Passport</a><?php endif; ?>
                    <?php if (empty($r['firc_doc_path']) && empty($r['passport_doc_path'])): ?><span class="text-muted">—</span><?php endif; ?>
                </td>
                <td><span class="badge bg-<?= $badge[$r['verification_status']] ?? 'secondary' ?>"><?= $e($r['verification_status'] ?? '') ?></span></td>
                <td>
                    <?php if (($r['verification_status'] ?? '') === 'pending'): ?>
                    <form method="POST" action="<?= $base ?>/admin/sales/nri-desk/verify/<?= (int)$r['id'] ?>" class="d-flex gap-1">
                        <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                        <input type="text" name="notes" class="form-control form-control-sm" placeholder="Note (optional)" style="max-width:130px">
                        <button type="submit" name="decision" value="verified" class="btn btn-sm btn-success">Verify</button>
                        <button type="submit" name="decision" value="rejected" class="btn btn-sm btn-outline-danger" data-aps-confirm="Reject this NRI record?">Reject</button>
                    </form>
                    <?php else: ?>
                        <small class="text-muted"><?= $e($r['verified_at'] ?? '') ?></small>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table></div>
<?php endif; ?>
</div></div>
