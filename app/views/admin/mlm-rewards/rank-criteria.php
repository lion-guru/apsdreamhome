<?php

$page_title = 'MLM Rank Criteria';
$criteria = $criteria ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-trophy me-2"></i>Rank Criteria</h1>
            <p class="text-muted">Define MLM rank requirements for users</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCriteriaModal">
            <i class="fas fa-plus me-1"></i>Add Criteria
        </button>
    </div>


    <div class="card aps-cp-card">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Rank Criteria</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($criteria)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-trophy fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No rank criteria defined yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Rank</th>
                                <th>Min Monthly Sales (₹)</th>
                                <th>Min Team Size</th>
                                <th>Min Active Downlines</th>
                                <th>Min Monthly Commission (₹)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($criteria as $c): ?>
                            <tr>
                                <td><span class="badge bg-warning text-dark"><?= ucfirst(htmlspecialchars($c['rank'] ?? '')) ?></span></td>
                                <td>₹<?= number_format(floatval($c['min_monthly_sales'] ?? 0), 2) ?></td>
                                <td><?= intval($c['min_team_size'] ?? 0) ?></td>
                                <td><?= intval($c['min_active_downlines'] ?? 0) ?></td>
                                <td>₹<?= number_format(floatval($c['min_monthly_commission'] ?? 0), 2) ?></td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                            onclick="editCriteria(<?= htmlspecialchars(json_encode($c)) ?>)">
                                        <i class="fas fa-edit"></i>
                                    </button>
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

<!-- Add/Edit Criteria Modal -->
<div class="modal fade" id="criteriaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/admin/mlm/rank-criteria/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id" id="criteriaId" value="0">
                <div class="modal-header">
                    <h5 class="modal-title" id="criteriaModalTitle"><i class="fas fa-plus me-2"></i>Add Rank Criteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Rank <span class="text-danger">*</span></label>
                        <select name="rank" id="criteriaRank" class="form-select" required>
                            <option value="">Select Rank</option>
                            <option value="bronze">Bronze</option>
                            <option value="silver">Silver</option>
                            <option value="gold">Gold</option>
                            <option value="platinum">Platinum</option>
                            <option value="diamond">Diamond</option>
                            <option value="crown">Crown</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min Monthly Sales (₹)</label>
                        <input type="number" step="0.01" name="min_monthly_sales" id="criteriaSales" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min Team Size</label>
                        <input type="number" name="min_team_size" id="criteriaTeam" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min Active Downlines</label>
                        <input type="number" name="min_active_downlines" id="criteriaDownlines" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Min Monthly Commission (₹)</label>
                        <input type="number" step="0.01" name="min_monthly_commission" id="criteriaCommission" class="form-control" value="0">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCriteria(data) {
    document.getElementById('criteriaId').value = data.id || 0;
    document.getElementById('criteriaRank').value = data.rank || '';
    document.getElementById('criteriaSales').value = data.min_monthly_sales || 0;
    document.getElementById('criteriaTeam').value = data.min_team_size || 0;
    document.getElementById('criteriaDownlines').value = data.min_active_downlines || 0;
    document.getElementById('criteriaCommission').value = data.min_monthly_commission || 0;
    document.getElementById('criteriaModalTitle').innerHTML = '<i class="fas fa-edit me-2"></i>Edit Rank Criteria';
    new bootstrap.Modal(document.getElementById('criteriaModal')).show();
}

document.querySelector('[data-bs-target="#addCriteriaModal"]')?.addEventListener('click', function() {
    document.getElementById('criteriaId').value = 0;
    document.getElementById('criteriaRank').value = '';
    document.getElementById('criteriaSales').value = 0;
    document.getElementById('criteriaTeam').value = 0;
    document.getElementById('criteriaDownlines').value = 0;
    document.getElementById('criteriaCommission').value = 0;
    document.getElementById('criteriaModalTitle').innerHTML = '<i class="fas fa-plus me-2"></i>Add Rank Criteria';
});
</script>
