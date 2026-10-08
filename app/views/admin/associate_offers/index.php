<?php
$page_title = $page_title ?? 'Offer Campaigns';
$offers = $offers ?? [];
$colonies = $colonies ?? [];
$rewardLabels = ['bonus_amount' => 'Bonus ₹', 'commission_boost_pct' => 'Comm. boost %', 'gift' => 'Gift'];
$criteriaLabels = ['sale_volume' => 'Sale volume ₹', 'booking_count' => 'Bookings'];
$today = date('Y-m-d');
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-tags me-2"></i><?= htmlspecialchars($page_title) ?></h4>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#offerModal"><i class="fas fa-plus me-1"></i>New Offer</button>
</div>
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white fw-bold">Campaigns (activate → visible to associates &amp; agents)</div>
    <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0">
        <thead class="table-light"><tr><th>Title</th><th>Reward</th><th>Target</th><th class="text-center">Window</th><th>Status</th><th style="width:190px">Actions</th></tr></thead>
        <tbody>
            <?php if (empty($offers)): ?><tr><td colspan="6" class="text-center text-muted py-3">No offer campaigns yet — create the first one</td></tr><?php endif; ?>
            <?php foreach ($offers as $o): ?>
            <?php $live = ($o['status'] ?? '') === 'active' && $today >= ($o['starts_at'] ?? '') && $today <= ($o['ends_at'] ?? ''); ?>
            <tr>
                <td class="fw-medium"><?= htmlspecialchars($o['title'] ?? '') ?><br><small class="text-muted"><?= htmlspecialchars(mb_substr($o['description'] ?? '', 0, 80)) ?><?= !empty($o['colony_name']) ? ' · ' . htmlspecialchars($o['colony_name']) : '' ?></small></td>
                <td><?= htmlspecialchars($rewardLabels[$o['reward_type']] ?? '') ?>: <strong><?= ($o['reward_type'] ?? '') === 'gift' ? htmlspecialchars($o['reward_value']) : number_format($o['reward_value'] ?? 0, ($o['reward_type'] ?? '') === 'commission_boost_pct' ? 1 : 0) ?></strong></td>
                <td><?= htmlspecialchars($criteriaLabels[$o['criteria_type']] ?? '') ?> ≥ <strong><?= number_format($o['criteria_value'] ?? 0, 0) ?></strong></td>
                <td class="text-center small"><?= htmlspecialchars($o['starts_at'] ?? '') ?> → <?= htmlspecialchars($o['ends_at'] ?? '') ?></td>
                <td><span class="badge bg-<?= $live ? 'success' : (($o['status'] ?? '') === 'active' ? 'warning' : 'secondary') ?>"><?= $live ? 'live' : htmlspecialchars($o['status'] ?? '') ?></span></td>
                <td>
                    <?php if (($o['status'] ?? '') === 'draft'): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/activate" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                        <button class="btn btn-sm btn-success" onclick="return confirm('Activate? Associates/agents will see this offer.')">Activate</button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/delete" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this draft?')">Delete</button>
                    </form>
                    <?php elseif (($o['status'] ?? '') === 'active'): ?>
                    <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/close" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="id" value="<?= (int)$o['id'] ?>">
                        <button class="btn btn-sm btn-outline-secondary" onclick="return confirm('Close this offer? It will disappear from portals.')">Close</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table></div></div>
</div>
<div class="modal fade" id="offerModal" tabindex="-1">
    <div class="modal-dialog modal-lg"><div class="modal-content">
        <form method="POST" action="<?= BASE_URL ?>/admin/associate-offers/store">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="modal-header"><h5 class="modal-title">New Offer Campaign</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">Title <span class="text-danger">*</span></label>
                    <input name="title" class="form-control" maxlength="150" required placeholder="e.g. Diwali Dhamaka — extra 1% on every booking"></div>
                <div class="mb-3"><label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Terms, payout timing, exclusions"></textarea></div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Reward type</label>
                        <select name="reward_type" class="form-select"><option value="bonus_amount">Flat bonus (₹)</option><option value="commission_boost_pct">Commission boost (%)</option><option value="gift">Gift</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Reward value</label>
                        <input name="reward_value" type="number" step="0.01" min="0" class="form-control" value="5000"></div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3"><label class="form-label">Target type</label>
                        <select name="criteria_type" class="form-select"><option value="sale_volume">Sale volume (₹)</option><option value="booking_count">Booking count</option></select></div>
                    <div class="col-md-6 mb-3"><label class="form-label">Target value</label>
                        <input name="criteria_value" type="number" step="0.01" min="0" class="form-control" value="1000000"></div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label">Colony (optional)</label>
                        <select name="colony_id" class="form-select"><option value="0">All colonies</option>
                        <?php foreach ($colonies as $c): ?><option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Starts</label>
                        <input name="starts_at" type="date" class="form-control" value="<?= $today ?>"></div>
                    <div class="col-md-4 mb-3"><label class="form-label">Ends</label>
                        <input name="ends_at" type="date" class="form-control" value="<?= date('Y-m-d', strtotime('+30 days')) ?>"></div>
                </div>
                <p class="text-muted small mb-0">Saved as draft — press Activate to publish to associate/agent portals.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Draft</button>
            </div>
        </form>
    </div></div>
</div>
