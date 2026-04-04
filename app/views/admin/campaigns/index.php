<?php
$page_title = $page_title ?? 'Campaign Management';
$campaigns = $campaigns ?? [];
$baseUrl = BASE_URL ?? '/apsdreamhome';
?>

<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Campaign Management</h1>
            <p class="text-muted mb-0">Manage marketing campaigns and offers</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/campaigns/create" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Campaign
        </a>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($this->data['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($this->data['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($this->data['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($this->data['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Campaigns Table -->
    <div class="card shadow">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Campaigns</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($this->data['campaigns'])): ?>
                <div class="table-responsive">
                    <table class="table table-bordered" id="campaignsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Target Audience</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                                <th>Budget</th>
                                <th>Performance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($this->data['campaigns'] as $campaign): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($campaign['name']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= substr(htmlspecialchars($campaign['description']), 0, 100) ?>...</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $this->getCampaignTypeColor($campaign['type']) ?>">
                                            <?= ucfirst($campaign['type']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $this->getCampaignStatusColor($campaign['status']) ?>">
                                            <?= ucfirst($campaign['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= ucfirst($campaign['target_audience']) ?></td>
                                    <td><?= date('M j, Y', strtotime($campaign['start_date'])) ?></td>
                                    <td><?= $campaign['end_date'] ? date('M j, Y', strtotime($campaign['end_date'])) : 'Ongoing' ?></td>
                                    <td>$<?= number_format($campaign['budget'], 2) ?></td>
                                    <td>
                                        <div class="small">
                                            <div>Sent: <?= $campaign['total_sent'] ?></div>
                                            <div>Opened: <?= $campaign['total_opened'] ?></div>
                                            <div>Clicked: <?= $campaign['total_clicked'] ?></div>
                                            <div>Converted: <?= $campaign['total_converted'] ?></div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="<?= BASE_URL ?>/admin/campaigns/<?= $campaign['campaign_id'] ?>/analytics" 
                                               class="btn btn-sm btn-info" title="View Analytics">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/campaigns/<?= $campaign['campaign_id'] ?>/edit" 
                                               class="btn btn-sm btn-warning" title="Edit Campaign">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($campaign['status'] === 'planned'): ?>
                                                <a href="<?= BASE_URL ?>/admin/campaigns/<?= $campaign['campaign_id'] ?>/launch" 
                                                   class="btn btn-sm btn-success" title="Launch Campaign"
                                                   onclick="return confirm('Are you sure you want to launch this campaign?')">
                                                    <i class="fas fa-rocket"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?= BASE_URL ?>/admin/campaigns/<?= $campaign['campaign_id'] ?>/delete" 
                                               class="btn btn-sm btn-danger" title="Delete Campaign"
                                               onclick="return confirm('Are you sure you want to delete this campaign?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-bullhorn fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-500">No campaigns found</h5>
                    <p class="text-gray-400">Create your first campaign to get started</p>
                    <a href="<?= BASE_URL ?>/admin/campaigns/create" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Campaign
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
$(document).ready(function() {
    $('#campaignsTable').DataTable({
        responsive: true,
        order: [[5, 'desc']], // Sort by start date
        pageLength: 25
    });
});

<?php
// Helper functions for badge colors
function getCampaignTypeColor($type) {
    $colors = [
        'general' => 'primary',
        'offer' => 'success',
        'promotion' => 'warning',
        'announcement' => 'info'
    ];
    return $colors[$type] ?? 'secondary';
}

function getCampaignStatusColor($status) {
    $colors = [
        'planned' => 'secondary',
        'active' => 'success',
        'completed' => 'primary',
        'cancelled' => 'danger'
    ];
    return $colors[$status] ?? 'secondary';
}
?>
</script>
