<?php
$page_title = $page_title ?? 'Campaign Management';
$campaigns = $campaigns ?? [];
$success = $success ?? $_SESSION['success'] ?? null;
$error = $error ?? $_SESSION['error'] ?? null;

function getCampaignTypeColor($type) {
    $colors = ['general' => 'primary', 'offer' => 'success', 'promotion' => 'warning', 'announcement' => 'info'];
    return $colors[$type] ?? 'secondary';
}

function getCampaignStatusColor($status) {
    $colors = ['planned' => 'secondary', 'active' => 'success', 'completed' => 'primary', 'cancelled' => 'danger'];
    return $colors[$status] ?? 'secondary';
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0">Campaign Management</h1>
        <p class="text-muted mb-0">Manage marketing campaigns and offers</p>
    </div>
    <a href="<?= BASE_URL ?>/admin/campaigns/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Create Campaign
    </a>
</div>

<?php if (!empty($success)): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (!empty($campaigns)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Target Audience</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Budget</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($campaigns as $campaign): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($campaign['name'] ?? '') ?></strong>
                                    <br><small class="text-muted"><?= substr(htmlspecialchars($campaign['description'] ?? ''), 0, 100) ?>...</small>
                                </td>
                                <td><span class="badge bg-<?= getCampaignTypeColor($campaign['type'] ?? '') ?>"><?= ucfirst($campaign['type'] ?? '') ?></span></td>
                                <td><span class="badge bg-<?= getCampaignStatusColor($campaign['status'] ?? '') ?>"><?= ucfirst($campaign['status'] ?? '') ?></span></td>
                                <td><?= ucfirst($campaign['target_audience'] ?? '') ?></td>
                                <td><?= date('M j, Y', strtotime($campaign['start_date'] ?? 'now')) ?></td>
                                <td><?= !empty($campaign['end_date']) ? date('M j, Y', strtotime($campaign['end_date'])) : 'Ongoing' ?></td>
                                <td>₹<?= number_format(floatval($campaign['budget'] ?? 0), 2) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/campaigns/<?= $campaign['id'] ?>/edit" class="btn btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/campaigns/<?= $campaign['id'] ?>/analytics" class="btn btn-outline-info"><i class="fas fa-chart-line"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-bullhorn fa-3x text-muted mb-3"></i>
                <h5>No campaigns found</h5>
                <p class="text-muted">Create your first marketing campaign</p>
                <a href="<?= BASE_URL ?>/admin/campaigns/create" class="btn btn-primary">Create Campaign</a>
            </div>
        <?php endif; ?>
    </div>
</div>