<?php
$page_title = $page_title ?? 'Campaigns';
$account = $account ?? [];
$campaigns = $campaigns ?? [];
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-bullhorn me-2"></i>Campaigns — <?= htmlspecialchars($account['account_name'] ?? '') ?></h2>
    <a href="<?= BASE_URL ?>/admin/social-media" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Accounts</a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Active & Past Campaigns</h5></div>
            <div class="card-body p-0">
                <?php if (empty($campaigns)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No campaigns</h5>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Name</th>
                                    <th>Platform</th>
                                    <th>Objective</th>
                                    <th>Status</th>
                                    <th>Daily Budget</th>
                                    <th>Spend</th>
                                    <th>Leads</th>
                                    <th>CPL</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($campaigns as $c): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($c['name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($c['platform_campaign_id']) ?></small></td>
                                    <td><span class="badge bg-secondary"><?= ucfirst($c['platform']) ?></span></td>
                                    <td><?= ucfirst(str_replace('_', ' ', $c['objective'] ?? '')) ?></td>
                                    <td><span class="badge bg-<?= match($c['status']) { 'active' => 'success', 'paused' => 'warning', 'archived' => 'secondary', 'deleted' => 'danger', default => 'secondary' } ?>"><?= ucfirst($c['status']) ?></span></td>
                                    <td><?= !empty($c['daily_budget']) ? '₹' . number_format($c['daily_budget'], 2) : '—' ?></td>
                                    <td>₹<?= number_format($c['spend'] ?? 0, 2) ?></td>
                                    <td><?= $c['leads_count'] ?? 0 ?></td>
                                    <td>₹<?= number_format($c['cost_per_lead'] ?? 0, 2) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <?php if (!empty($account['id'])): ?>
        <div class="card border-primary">
            <div class="card-header bg-primary text-white"><i class="fas fa-plus me-1"></i> Add Campaign</div>
            <div class="card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/social-media/campaigns/<?= $account['id'] ?>/create">
                    <input type="hidden" name="csrf_token" value="<?= $csrf ?>">
                    <div class="mb-2">
                        <label class="form-label small">Campaign ID</label>
                        <input type="text" name="platform_campaign_id" class="form-control" placeholder="Platform campaign ID">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Platform</label>
                        <select name="platform" class="form-select">
                            <option value="facebook">Facebook</option>
                            <option value="instagram">Instagram</option>
                            <option value="linkedin">LinkedIn</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Objective</label>
                        <select name="objective" class="form-select">
                            <option value="lead_generation">Lead Generation</option>
                            <option value="traffic">Traffic</option>
                            <option value="brand_awareness">Brand Awareness</option>
                            <option value="messages">Messages</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select">
                            <option value="active">Active</option>
                            <option value="paused">Paused</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label small">Daily Budget</label>
                            <input type="number" step="0.01" name="daily_budget" class="form-control">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label small">Lifetime Budget</label>
                            <input type="number" step="0.01" name="lifetime_budget" class="form-control">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i> Save Campaign</button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="card border-info">
            <div class="card-header bg-info text-white"><i class="fas fa-info-circle me-1"></i> All Campaigns</div>
            <div class="card-body">
                <p class="text-muted small">Showing campaigns across all connected accounts. Select an account from the main Social Media page to add a new campaign.</p>
                <?php if (!empty($accounts)): ?>
                <div class="list-group">
                    <?php foreach ($accounts as $acc): ?>
                        <a href="<?= BASE_URL ?>/admin/social-media/campaigns/<?= $acc['id'] ?>" class="list-group-item list-group-item-action"><?= htmlspecialchars($acc['account_name']) ?></a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
