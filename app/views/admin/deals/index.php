<?php
/**
 * Deal Tracking - List View
 */

$page_title = 'Deal Tracking - APS Dream Home';
include __DIR__ . '/../../layouts/admin_header.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-handshake me-2"></i>Deal Tracking</h1>
            <p class="text-muted">Manage sales deals through the pipeline</p>
        </div>
        <div class="btn-group">
            <a href="/admin/deals/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Deal
            </a>
            <a href="/admin/deals/kanban" class="btn btn-outline-secondary">
                <i class="fas fa-columns me-2"></i>Kanban View
            </a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Pipeline Value</h6>
                            <h3 class="mb-0">₹<?= number_format($stats['pipeline_value'] ?? 0, 0) ?>L</h3>
                        </div>
                        <i class="fas fa-funnel-dollar fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Won Deals</h6>
                            <h3 class="mb-0"><?= $stats['won_count'] ?? 0 ?></h3>
                        </div>
                        <i class="fas fa-trophy fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Total Revenue</h6>
                            <h3 class="mb-0">₹<?= number_format($stats['total_revenue'] ?? 0, 0) ?>L</h3>
                        </div>
                        <i class="fas fa-rupee-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Win Rate</h6>
                            <h3 class="mb-0">
                                <?php
                                $total = ($stats['won_count'] ?? 0) + ($stats['lost_count'] ?? 0);
                                echo $total > 0 ? round(($stats['won_count'] / $total) * 100) : 0;
                                ?>%
                            </h3>
                        </div>
                        <i class="fas fa-percentage fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Stage</label>
                    <select class="form-select" name="stage">
                        <option value="">All Stages</option>
                        <?php foreach ($stages as $stage): ?>
                        <option value="<?= $stage['id'] ?>" <?= $filters['stage'] == $stage['id'] ? 'selected' : '' ?>>
                            <?= $stage['name'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($filters['search']) ?>" placeholder="Lead name, email, property...">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Apply
                    </button>
                    <a href="/admin/deals" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Deals Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Deals (<?= count($deals) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Lead</th>
                            <th>Property</th>
                            <th>Deal Value</th>
                            <th>Stage</th>
                            <th>Expected Close</th>
                            <th>Assigned To</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($deals as $deal):
                            $stageColor = array_column($stages, 'color', 'id')[$deal['stage']] ?? 'secondary';
                        ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($deal['lead_name']) ?></strong>
                                <br><small class="text-muted"><?= htmlspecialchars($deal['lead_email']) ?></small>
                            </td>
                            <td><?= htmlspecialchars($deal['property_title'] ?? 'Not specified') ?></td>
                            <td><strong>₹<?= number_format($deal['deal_value'], 0) ?>L</strong></td>
                            <td>
                                <span class="badge bg-<?= $stageColor ?>">
                                    <?= ucfirst($deal['stage']) ?>
                                </span>
                            </td>
                            <td>
                                <?= $deal['expected_close_date'] ? date('M d, Y', strtotime($deal['expected_close_date'])) : 'Not set' ?>
                            </td>
                            <td><?= htmlspecialchars($deal['assigned_to_name'] ?? 'Unassigned') ?></td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-success" onclick="updateStage(<?= $deal['id'] ?>, 'won')" title="Mark as Won">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button class="btn btn-outline-danger" onclick="updateStage(<?= $deal['id'] ?>, 'lost')" title="Mark as Lost">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function updateStage(dealId, stage) {
    if (!confirm('Are you sure you want to mark this deal as ' + stage.toUpperCase() + '?')) {
        return;
    }

    fetch('/admin/deals/' + dealId + '/stage', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'stage=' + stage
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed to update deal stage');
        }
    })
    .catch(error => {
        alert('Error updating deal stage');
    });
}
</script>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
