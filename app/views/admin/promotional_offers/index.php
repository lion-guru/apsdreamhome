<?php
$page_title = $page_title ?? 'Promotional Offers';
$offers = $offers ?? [];
$today = date('Y-m-d');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-percent me-2"></i><?= htmlspecialchars($page_title) ?></h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#promoModal"><i class="fas fa-plus me-1"></i>New Offer</button>
</div>
<p class="text-muted small">Customer-facing property discounts — active, unexpired rows feed the newsletter automation.</p>
<div class="card border-0 shadow-sm">
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Title</th><th class="text-end">Discount</th><th class="text-center">Valid Until</th><th>Status</th><th style="width:200px">Actions</th></tr></thead>
        <tbody>
            <?php if (empty($offers)): ?><tr><td colspan="5" class="text-center text-muted py-3">No promotional offers yet</td></tr><?php endif; ?>
            <?php foreach ($offers as $o): ?>
            <?php $live = ($o['status'] ?? '') === 'active' && ($o['valid_until'] ?? '') >= $today; ?>
            <tr>
                <td class="fw-medium"><?= htmlspecialchars($o['title'] ?? '') ?><br><small class="text-muted"><?= htmlspecialchars(mb_substr($o['description'] ?? '', 0, 80)) ?></small></td>
                <td class="text-end"><strong><?= htmlspecialchars($o['discount_percentage'] ?? 0) ?>%</strong></td>
                <td class="text-center small"><?= htmlspecialchars($o['valid_until'] ?? '') ?></td>
                <td><span class="badge bg-<?= $live ? 'success' : 'secondary' ?>"><?= $live ? 'live' : htmlspecialchars($o['status'] ?? '') ?></span></td>
                <td>
                    <form method="POST" action="<?= BASE_URL ?>/admin/promotional-offers/status" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                        <input type="hidden" name="status" value="<?= ($o['status'] ?? '') === 'active' ? 'inactive' : 'active' ?>">
                        <button class="btn btn-sm btn-outline-secondary"><?= ($o['status'] ?? '') === 'active' ? 'Deactivate' : 'Activate' ?></button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>/admin/promotional-offers/delete" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this offer?')">Delete</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>
<div class="modal fade" id="promoModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="POST" action="<?= BASE_URL ?>/admin/promotional-offers/store">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="modal-header"><h5 class="modal-title">New Promotional Offer</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Title <span class="text-danger">*</span></label>
                    <input name="title" class="form-control" maxlength="255" required placeholder="e.g. Monsoon Special — 5% off"></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2"></textarea></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Discount %</label>
                        <input name="discount_percentage" type="number" step="0.01" min="0" max="100" class="form-control" value="5"></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Valid until</label>
                        <input name="valid_until" type="date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div></div>
</div>
