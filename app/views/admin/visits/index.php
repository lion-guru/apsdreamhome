<?php

/**
 * Site Visit Management - List View
 */

$page_title = 'Site Visit Management - APS Dream Home';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-car me-2"></i>Site Visit Management</h1>
            <p class="text-muted">Schedule and track property site visits</p>
        </div>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/visits/create" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Schedule Visit
            </a>
            <a href="<?= BASE_URL ?>/admin/visits/calendar" class="btn btn-outline-secondary">
                <i class="fas fa-calendar-alt me-2"></i>Calendar View
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
                            <h6 class="card-title mb-0">Total Visits</h6>
                            <h2 class="mb-0"><?= $stats['total_visits'] ?? 0 ?></h2>
                        </div>
                        <i class="fas fa-car fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Scheduled</h6>
                            <h2 class="mb-0"><?= $stats['scheduled_count'] ?? 0 ?></h2>
                        </div>
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Completed</h6>
                            <h2 class="mb-0"><?= $stats['completed_count'] ?? 0 ?></h2>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title mb-0">Interested</h6>
                            <h2 class="mb-0"><?= $stats['interested_count'] ?? 0 ?></h2>
                        </div>
                        <i class="fas fa-thumbs-up fa-2x opacity-50"></i>
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
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All</option>
                        <option value="scheduled" <?= $filters['status'] == 'scheduled' ? 'selected' : '' ?>>Scheduled</option>
                        <option value="completed" <?= $filters['status'] == 'completed' ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled" <?= $filters['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        <option value="no_show" <?= $filters['status'] == 'no_show' ? 'selected' : '' ?>>No Show</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control" name="date_from" value="<?= $filters['date_from'] ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control" name="date_to" value="<?= $filters['date_to'] ?>">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-2"></i>Apply
                    </button>
                    <a href="<?= BASE_URL ?>/admin/visits" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Visits Table -->
    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Visits (<?= count($visits) ?>)</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Lead</th>
                            <th>Property</th>
                            <th>Agent</th>
                            <th>Status</th>
                            <th>Outcome</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($visits as $visit):
                            $statusBadge = [
                                'scheduled' => 'primary',
                                'completed' => 'success',
                                'cancelled' => 'danger',
                                'no_show' => 'warning'
                            ][$visit['status']] ?? 'secondary';
                        ?>
                            <tr>
                                <td>
                                    <strong><?= date('M d, Y', strtotime($visit['visit_date'])) ?></strong>
                                    <br><small class="text-muted"><?= date('h:i A', strtotime($visit['visit_time'])) ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(visit['lead_name'] ?? '') ?></strong>
                                    <br><small class="text-muted"><i class="fas fa-phone me-1"></i><?= htmlspecialchars(visit['lead_phone'] ?? '') ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars(visit['property_title'] ?? '') ?></strong>
                                    <br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars(visit['property_location'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($visit['agent_name'] ?? 'Unassigned') ?></td>
                                <td>
                                    <span class="badge bg-<?= $statusBadge ?>">
                                        <?= ucfirst($visit['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($visit['outcome']): ?>
                                        <span class="badge bg-<?= $visit['outcome'] == 'interested' ? 'success' : ($visit['outcome'] == 'not_interested' ? 'danger' : 'info') ?>">
                                            <?= ucfirst(str_replace('_', ' ', $visit['outcome'])) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-success" onclick="updateStatus(<?= $visit['id'] ?>, 'completed')">
                                        <i class="fas fa-check" title="Mark Completed"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="updateStatus(<?= $visit['id'] ?>, 'cancelled')">
                                        <i class="fas fa-times" title="Cancel"></i>
                                    </button>
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
    function updateStatus(visitId, status) {
        if (!confirm('Are you sure you want to mark this visit as ' + status + '?')) {
            return;
        }

        fetch('/admin/visits/' + visitId + '/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'status=' + status
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed to update status');
                }
            })
            .catch(error => {
                alert('Error updating status');
            });
    }
</script>