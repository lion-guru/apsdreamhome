<?php
$page_title = $page_title ?? 'Social Media Integration';
$accounts = $accounts ?? [];
$platforms = $platforms ?? [];
$filters = $filters ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-share-alt me-2"></i>Social Media Integration</h2>
    <a href="<?= BASE_URL ?>/admin/social-media/add" class="btn btn-primary">
        <i class="fas fa-plus me-1"></i> Connect Account
    </a>
</div>

<div class="row mb-3">
    <div class="col-12">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-auto">
                <label class="form-label small">Platform</label>
                <select name="platform" class="form-select">
                    <option value="">All</option>
                    <?php foreach ($platforms as $p): ?>
                        <option value="<?= $p ?>" <?= ($filters['platform'] ?? '') === $p ? 'selected' : '' ?>><?= ucfirst($p) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <label class="form-label small">Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    <option value="connected" <?= ($filters['status'] ?? '') === 'connected' ? 'selected' : '' ?>>Connected</option>
                    <option value="expired" <?= ($filters['status'] ?? '') === 'expired' ? 'selected' : '' ?>>Expired</option>
                    <option value="revoked" <?= ($filters['status'] ?? '') === 'revoked' ? 'selected' : '' ?>>Revoked</option>
                    <option value="error" <?= ($filters['status'] ?? '') === 'error' ? 'selected' : '' ?>>Error</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-filter me-1"></i>Filter</button>
            </div>
        </form>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Connected Accounts</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($accounts)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-plug fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No accounts connected</h5>
                        <p class="text-muted">Connect your Facebook, Instagram, LinkedIn, or WhatsApp Business accounts to sync leads automatically.</p>
                        <a href="<?= BASE_URL ?>/admin/social-media/add" class="btn btn-primary mt-2">Connect First Account</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Platform</th>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Leads</th>
                                    <th>Campaigns</th>
                                    <th>Last Sync</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($accounts as $acc): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-<?= match($acc['platform']) { 'facebook' => 'primary', 'instagram' => 'danger', 'linkedin' => 'info', 'whatsapp_business' => 'success', 'twitter' => 'dark', 'youtube' => 'danger', default => 'secondary' } ?> fs-6">
                                            <i class="fab fa-<?= $acc['platform'] === 'whatsapp_business' ? 'whatsapp' : $acc['platform'] ?> me-1"></i>
                                            <?= ucfirst(str_replace('_', ' ', $acc['platform'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($acc['account_name'] ?? '') ?></strong>
                                        <br><small class="text-muted">ID: <?= htmlspecialchars($acc['account_id'] ?? '') ?></small>
                                    </td>
                                    <td><?= ucfirst(str_replace('_', ' ', $acc['account_type'] ?? 'personal')) ?></td>
                                    <td>
                                        <span class="badge bg-<?= match($acc['status']) { 'connected' => 'success', 'expired' => 'warning', 'revoked' => 'danger', 'error' => 'danger', default => 'secondary' } ?>">
                                            <?= ucfirst($acc['status']) ?>
                                        </span>
                                    </td>
                                    <td><span class="badge bg-primary"><?= $acc['leads_count'] ?? 0 ?></span></td>
                                    <td><span class="badge bg-info"><?= $acc['active_campaigns'] ?? 0 ?></span></td>
                                    <td>
                                        <?php if (!empty($acc['last_sync_at'])): ?>
                                            <small><?= date('M d, H:i', strtotime($acc['last_sync_at'])) ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?= BASE_URL ?>/admin/social-media/leads/<?= $acc['id'] ?>" class="btn btn-outline-primary" title="View Leads">
                                                <i class="fas fa-users"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/social-media/campaigns/<?= $acc['id'] ?>" class="btn btn-outline-info" title="Campaigns">
                                                <i class="fas fa-bullhorn"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/social-media/insights/<?= $acc['id'] ?>" class="btn btn-outline-success" title="Insights">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-warning" onclick="syncLeads(<?= $acc['id'] ?>)" title="Sync Leads">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                            <a href="<?= BASE_URL ?>/admin/social-media/edit/<?= $acc['id'] ?>" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $acc['id'] ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-cog me-2"></i>API Configuration</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Configure your API credentials to enable lead sync from social platforms.</p>
                <a href="<?= BASE_URL ?>/admin/social-media/settings" class="btn btn-outline-primary">
                    <i class="fas fa-key me-1"></i> Manage API Keys
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function syncLeads(accountId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;

    showLoader();
    fetch(`/admin/social-media/sync/${accountId}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= $csrf ?>' } })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                showToast(`Sync completed! Fetched: ${data.stats.fetched}, New: ${data.stats.new}, Updated: ${data.stats.updated}`, 'success');
                location.reload();
            } else {
                showToast('Sync failed: ' + (data.error || 'Unknown error'), 'danger');
            }
        })
        .catch(() => showToast('Sync request failed', 'danger'))
        .finally(() => { btn.innerHTML = originalHtml; btn.disabled = false; });
}

function confirmDelete(id) {
    if (confirm('Delete this social media account? This will also delete all synced leads.')) {
        showLoader();
        fetch(`/admin/social-media/delete/${id}`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': '<?= $csrf ?>' } })
            .then(r => r.json())
            .then(data => { if (data.success) location.reload(); else showToast('Delete failed', 'danger'); })
            .catch(() => showToast('Delete request failed', 'danger')).finally(() => hideLoader());
    }
}
</script>
