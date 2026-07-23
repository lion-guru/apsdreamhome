<?php
$page_title = $page_title ?? 'Social Media Leads';
$leads = $leads ?? [];
$pagination = $pagination ?? ['page' => 1, 'pages' => 1];
$filters = $filters ?? [];
$accounts = $accounts ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Social Media Leads</h2>
    <a href="<?= BASE_URL ?>/admin/social-media" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Accounts</a>
</div>

<div class="row mb-3">
    <div class="col-12">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small">Account</label>
                <select name="account_id" class="form-select">
                    <option value="">All Accounts</option>
                    <?php foreach ($accounts as $a): ?>
                        <option value="<?= $a['id'] ?>" <?= ($filters['account_id'] ?? '') == $a['id'] ? 'selected' : '' ?>><?= htmlspecialchars($a['account_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <?php foreach (['new','contacted','qualified','converted','junk','duplicate'] as $s): ?>
                        <option value="<?= $s ?>" <?= ($filters['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small">Search</label>
                <input type="text" name="search" class="form-control" value="<?= htmlspecialchars($filters['search'] ?? '') ?>" placeholder="Name / email / phone">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-search me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <?php if (empty($leads)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No leads yet</h5>
                <p class="text-muted">Sync an account to pull leads from social platforms.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Contact</th>
                            <th>Location</th>
                            <th>Budget</th>
                            <th>Platform</th>
                            <th>Form</th>
                            <th>Status</th>
                            <th>Received</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($leads as $l): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($l['full_name'] ?? '—') ?></strong></td>
                            <td>
                                <small><?= htmlspecialchars($l['email'] ?? '—') ?></small><br>
                                <small class="text-muted"><?= htmlspecialchars($l['phone'] ?? '—') ?></small>
                            </td>
                            <td><?= htmlspecialchars(($l['city'] ?? '') . ($l['state'] ? ', ' . $l['state'] : '')) ?: '—' ?></td>
                            <td>
                                <?php if (!empty($l['budget_min'])): ?>
                                    ₹<?= number_format($l['budget_min']) ?><?= !empty($l['budget_max']) ? ' – ' . number_format($l['budget_max']) : '' ?>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= match($l['platform']) { 'facebook' => 'primary', 'instagram' => 'danger', 'linkedin' => 'info', 'whatsapp_business' => 'success', default => 'secondary' } ?>">
                                    <?= ucfirst(str_replace('_', ' ', $l['platform'])) ?>
                                </span>
                            </td>
                            <td><small><?= htmlspecialchars($l['form_name'] ?? '—') ?></small></td>
                            <td>
                                <select class="form-select form-select-sm lead-status" data-lead="<?= $l['id'] ?>">
                                    <?php foreach (['new','contacted','qualified','converted','junk','duplicate'] as $s): ?>
                                        <option value="<?= $s ?>" <?= ($l['status'] ?? 'new') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><small><?= date('M d, H:i', strtotime($l['created_at'])) ?></small></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($pagination['pages'] > 1): ?>
    <div class="card-footer">
        <nav>
            <ul class="pagination justify-content-center mb-0">
                <?php for ($i = 1; $i <= $pagination['pages']; $i++): ?>
                    <li class="page-item <?= $i === $pagination['page'] ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>&account_id=<?= $filters['account_id'] ?? '' ?>&status=<?= $filters['status'] ?? '' ?>&search=<?= urlencode($filters['search'] ?? '') ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.lead-status').forEach(sel => {
    sel.addEventListener('change', function() {
        const leadId = this.dataset.lead;
        const status = this.value;
        fetch('<?= BASE_URL ?>/admin/social-media/leads/update-status', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-Token': '<?= $csrf ?>' },
            body: `csrf_token=<?= $csrf ?>&lead_id=${leadId}&status=${status}`
        }).then(r => r.json()).then(d => {
            if (!d.success) alert('Update failed');
        });
    });
});
</script>
