<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-user-check me-2 text-success"></i>Assignment Approvals</h4>
        <span class="badge bg-warning fs-6"><?= count($pending) ?> Pending</span>
    </div>

    <?php if (!empty($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> Action completed successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white shadow">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?= $stats['pending'] ?? 0 ?></div>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white shadow">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?= $stats['approved'] ?? 0 ?></div>
                    <small>Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white shadow">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?= $stats['rejected'] ?? 0 ?></div>
                    <small>Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white shadow">
                <div class="card-body text-center">
                    <div class="h2 mb-0"><?= $stats['total'] ?? 0 ?></div>
                    <small>Total Requests</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h6 class="mb-0"><i class="fas fa-clock me-1"></i>Pending Approval Requests</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($pending)): ?>
                <form method="POST" action="<?= BASE_URL ?>/admin/crm/assignments/bulk">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>Lead</th>
                                    <th>Current Assignee</th>
                                    <th>Requested Assignee</th>
                                    <th>Requested By</th>
                                    <th>Reason</th>
                                    <th>Requested</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pending as $req): ?>
                                    <tr>
                                        <td><input type="checkbox" name="approval_ids[]" value="<?= $req['id'] ?>"></td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/leads/<?= $req['lead_id'] ?>" class="text-decoration-none">
                                                <strong><?= htmlspecialchars($req['lead_name'] ?? 'Lead #' . $req['lead_id']) ?></strong>
                                            </a>
                                            <br><small class="text-muted"><?= htmlspecialchars($req['lead_phone'] ?? '') ?></small>
                                        </td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars($req['from_name'] ?? 'Unassigned') ?></span></td>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($req['to_name'] ?? 'Unknown') ?></span></td>
                                        <td><?= htmlspecialchars($req['requested_by_name'] ?? 'Unknown') ?></td>
                                        <td><small class="text-muted"><?= htmlspecialchars($req['reason'] ?? 'No reason') ?></small></td>
                                        <td><small><?= date('M j, g:i A', strtotime($req['created_at'])) ?></small></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <form method="POST" action="<?= BASE_URL ?>/admin/crm/assignments/<?= $req['id'] ?>/approve" class="style-71727">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <button class="btn btn-success btn-sm" title="Approve" aria-label="Confirm"><i class="fas fa-check"></i></button>
                                                </form>
                                                <form method="POST" action="<?= BASE_URL ?>/admin/crm/assignments/<?= $req['id'] ?>/reject" class="style-71727">
                                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                    <input type="hidden" name="reason" value="Rejected by admin">
                                                    <button class="btn btn-danger btn-sm" title="Reject" onclick="return confirm('Reject this request?')" aria-label="Reject"><i class="fas fa-times"></i></button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">
                        <button type="submit" name="bulk_action" value="approve" class="btn btn-success btn-sm"><i class="fas fa-check-double me-1"></i>Bulk Approve</button>
                        <button type="submit" name="bulk_action" value="reject" class="btn btn-danger btn-sm" onclick="return confirm('Reject all selected?')"><i class="fas fa-ban me-1"></i>Bulk Reject</button>
                    </div>
                </form>
            <?php else: ?>
                <div class="text-center py-4">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <p class="text-muted">No pending approval requests. All caught up!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- History -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h6 class="mb-0"><i class="fas fa-history me-1"></i>Approval History</h6>
        </div>
        <div class="card-body">
            <?php if (!empty($history)): ?>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Lead</th><th>From</th><th>To</th><th>Requested By</th><th>Status</th><th>Approved By</th><th>Date</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($history as $h): ?>
                                <tr>
                                    <td><?= htmlspecialchars($h['lead_name'] ?? '#' . $h['lead_id']) ?></td>
                                    <td><small><?= htmlspecialchars($h['from_name'] ?? '') ?></small></td>
                                    <td><small><?= htmlspecialchars($h['to_name'] ?? '') ?></small></td>
                                    <td><small><?= htmlspecialchars($h['requested_by_name'] ?? '') ?></small></td>
                                    <td>
                                        <?php
                                        $statusColors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
                                        $color = $statusColors[$h['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?= $color ?>"><?= ucfirst($h['status']) ?></span>
                                    </td>
                                    <td><small><?= htmlspecialchars($h['approved_by_name'] ?? '—') ?></small></td>
                                    <td><small><?= date('M j, g:i A', strtotime($h['created_at'])) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center mb-0">No history yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('input[name="approval_ids[]"]').forEach(cb => cb.checked = this.checked);
});
</script>
