<?php
$page_title = $page_title ?? 'Bulk Outreach';
$base = defined('BASE_URL') ? BASE_URL : '';
$campaigns = $campaigns ?? [];
$lead_stats = $lead_stats ?? ['total' => 0, 'with_phone' => 0, 'new' => 0, 'contacted' => 0];
?>

<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-bullhorn me-2 text-warning"></i>Bulk Outreach</h4>
        <a href="<?= $base ?>/admin/crm" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to CRM</a>
    </div>


    <!-- Lead Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div style="font-size:1.8rem;font-weight:700;color:#6366f1;"><?= number_format($lead_stats['total']) ?></div>
                <div class="text-muted small">Total Leads</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div style="font-size:1.8rem;font-weight:700;color:#10b981;"><?= number_format($lead_stats['with_phone']) ?></div>
                <div class="text-muted small">With Phone</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div style="font-size:1.8rem;font-weight:700;color:#f59e0b;"><?= number_format($lead_stats['new']) ?></div>
                <div class="text-muted small">New Leads</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div style="font-size:1.8rem;font-weight:700;color:#ef4444;"><?= number_format($lead_stats['contacted']) ?></div>
                <div class="text-muted small">Contacted</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Create Campaign -->
        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="m-0"><i class="fas fa-plus-circle me-2"></i>Create Campaign</h5></div>
                <div class="card-body">
                    <form action="<?= $base ?>/admin/crm/outreach/create" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold">Campaign Name</label>
                            <input type="text" class="form-control" name="name" required placeholder="e.g., June Promo - Plots in Mathura">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Type</label>
                            <select class="form-select" name="campaign_type">
                                <option value="whatsapp_broadcast">WhatsApp Broadcast</option>
                                <option value="sms_blast">SMS Blast</option>
                                <option value="email_blast">Email Blast</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Target Status</label>
                            <select class="form-select" name="target_status">
                                <option value="all">All Leads</option>
                                <option value="new">New Only</option>
                                <option value="contacted">Contacted</option>
                                <option value="qualified">Qualified</option>
                                <option value="nurture">Nurture</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Target Source</label>
                            <select class="form-select" name="target_source">
                                <option value="all">All Sources</option>
                                <option value="website">Website</option>
                                <option value="referral">Referral</option>
                                <option value="google">Google</option>
                                <option value="facebook">Facebook</option>
                                <option value="walk-in">Walk-in</option>
                                <option value="csv_import">CSV Import</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Message Template</label>
                            <textarea class="form-control" name="message" rows="4" required placeholder="Hi {name}, we have exciting plots available..."></textarea>
                            <small class="text-muted">Use {name} and {phone} as placeholders</small>
                        </div>

                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-rocket me-2"></i>Create Campaign
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Campaign List -->
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="m-0"><i class="fas fa-list me-2"></i>Campaigns</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr><th>Name</th><th>Type</th><th>Leads</th><th>Sent</th><th>Status</th><th>Date</th><th>Action</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($campaigns)): ?>
                                    <tr><td colspan="7" class="text-center text-muted py-4">No campaigns yet</td></tr>
                                <?php else: ?>
                                    <?php foreach ($campaigns as $c): ?>
                                        <tr>
                                            <td class="fw-bold small"><?= htmlspecialchars($c['name']) ?></td>
                                            <td><span class="badge bg-info"><?= str_replace('_', ' ', ucfirst($c['campaign_type'])) ?></span></td>
                                            <td><?= (int)$c['total_leads'] ?></td>
                                            <td><?= (int)($c['total_sent'] ?? 0) ?></td>
                                            <td>
                                                <?php
                                                $statusClass = match($c['status'] ?? 'draft') {
                                                    'active' => 'success',
                                                    'completed' => 'primary',
                                                    'paused' => 'warning',
                                                    default => 'secondary'
                                                };
                                                ?>
                                                <span class="badge bg-<?= $statusClass ?>"><?= ucfirst($c['status'] ?? 'draft') ?></span>
                                            </td>
                                            <td class="small"><?= date('M d', strtotime($c['created_at'])) ?></td>
                                            <td>
                                                <?php if ($c['status'] === 'draft'): ?>
                                                    <form action="<?= $base ?>/admin/crm/outreach/<?= $c['id'] ?>/send" method="POST" style="display:inline;">
                                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Send to <?= (int)$c['total_leads'] ?> leads?')">
                                                            <i class="fas fa-paper-plane me-1"></i>Send
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <a href="<?= $base ?>/admin/crm/outreach/<?= $c['id'] ?>/stats" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-chart-bar me-1"></i>Stats
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
