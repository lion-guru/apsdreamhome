<?php
$page_title = $page_title ?? 'Calling Campaigns - APS Dream Home';
$campaigns = $campaigns ?? [];
$totalCampaigns = $totalCampaigns ?? 0;
$activeCampaigns = $activeCampaigns ?? 0;
$totalScheduled = $totalScheduled ?? 0;
$totalCompleted = $totalCompleted ?? 0;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="mb-0"><i class="fas fa-bullhorn me-2 text-primary"></i>Calling Campaigns</h2>
        <a href="<?= BASE_URL ?>/admin/ai-calling/dashboard" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm style-27108">
                <div class="card-body py-3"><div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-bullhorn"></i></span></div>
                    <div><div class="style-49205"><?= $totalCampaigns ?></div><div class="small text-muted text-uppercase">Total Campaigns</div></div>
                </div></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm style-62228">
                <div class="card-body py-3"><div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-success rounded-pill p-2"><i class="fas fa-play-circle"></i></span></div>
                    <div><div class="style-49205 text-success"><?= $activeCampaigns ?></div><div class="small text-muted text-uppercase">Active</div></div>
                </div></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm style-61873">
                <div class="card-body py-3"><div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-calendar-check"></i></span></div>
                    <div><div class="style-49205 text-info"><?= number_format($totalScheduled) ?></div><div class="small text-muted text-uppercase">Total Scheduled</div></div>
                </div></div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm style-413">
                <div class="card-body py-3"><div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-purple rounded-pill p-2 style-58541"><i class="fas fa-check-double"></i></span></div>
                    <div><div class="style-49205"><?= number_format($totalCompleted) ?></div><div class="small text-muted text-uppercase">Completed</div></div>
                </div></div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Campaigns</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Campaign Name</th>
                            <th>Status</th>
                            <th>Scheduled</th>
                            <th>Completed</th>
                            <th>Calls Made</th>
                            <th>Interested</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($campaigns)): ?>
                        <tr><td colspan="9" class="text-center text-muted py-5">
                            <i class="fas fa-bullhorn fa-3x mb-3 style-39608"></i>
                            <h5 class="text-muted">No campaigns yet</h5>
                            <p class="text-muted mb-0">Create your first calling campaign to start automated outreach.</p>
                        </td></tr>
                        <?php else: ?>
                        <?php foreach ($campaigns as $c): ?>
                        <tr>
                            <td><?= $c['id'] ?></td>
                            <td><strong><?= htmlspecialchars($c['name'] ?? $c['campaign_name'] ?? 'Campaign #' . $c['id']) ?></strong></td>
                            <td>
                                <?php
                                $statusColors = ['active'=>'success','paused'=>'warning','completed'=>'info','draft'=>'secondary','cancelled'=>'danger'];
                                $sColor = $statusColors[$c['status']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?= $sColor ?>"><?= ucfirst($c['status'] ?? 'draft') ?></span>
                            </td>
                            <td><?= number_format($c['total_scheduled'] ?? 0) ?></td>
                            <td><?= number_format($c['completed'] ?? 0) ?></td>
                            <td><?= number_format($c['calls_made'] ?? 0) ?></td>
                            <td>
                                <?php
                                $callsMade = $c['calls_made'] ?? 0;
                                $interested = $c['interested'] ?? 0;
                                $convRate = $callsMade > 0 ? round($interested / $callsMade * 100, 1) : 0;
                                ?>
                                <span class="text-success fw-bold"><?= $interested ?></span>
                                <small class="text-muted">(<?= $convRate ?>%)</small>
                            </td>
                            <td><small class="text-muted"><?= date('d M Y', strtotime($c['created_at'] ?? 'now')) ?></small></td>
                            <td>
                                <a href="<?= BASE_URL ?>/admin/ai-calling/campaign/<?= $c['id'] ?>" class="btn btn-sm btn-outline-primary" title="View Details"><i class="fas fa-eye"></i></a>
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
