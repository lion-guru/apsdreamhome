<?php $page_title = $page_title ?? 'Assign Leads'; ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><?php echo htmlspecialchars($page_title ?? ''); ?></h4>
        <button class="btn btn-primary" onclick="bulkAssign()"><i class="fas fa-users-cog me-1"></i> Bulk Assign</button>
    </div>
    <div class="card aps-cp-card">
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr><th><input type="checkbox" id="selectAll"></th><th>Name</th><th>Phone</th><th>Source</th><th>Status</th><th>Date</th><th>Assign To</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($leads)): foreach ($leads as $lead): ?>
                        <tr>
                            <td><input type="checkbox" class="lead-check" value="<?= (int)$lead['id'] ?>"></td>
                            <td><?= htmlspecialchars($lead['name'] ?? '') ?></td>
                            <td><?= htmlspecialchars($lead['phone'] ?? '') ?></td>
                            <td><?= htmlspecialchars($lead['source'] ?? '') ?></td>
                            <td><span class="badge bg-<?= $lead['status'] == 'new' ? 'info' : 'warning' ?>"><?= htmlspecialchars($lead['status'] ?? '') ?></span></td>
                            <td><?= htmlspecialchars($lead['created_at'] ?? '') ?></td>
                            <td>
                                <select class="form-select form-select-sm assign-select">
                                    <option value="">Select...</option>
                                    <?php foreach ($telecallers as $tc): ?>
                                    <option value="<?= $tc['id'] ?>"><?= htmlspecialchars($tc['name'] ?? '') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><button class="btn btn-sm btn-outline-primary assign-btn" data-lead-id="<?= (int)$lead['id'] ?>">Assign</button></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No leads to assign</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
document.getElementById('selectAll')?.addEventListener('click', function() {
    document.querySelectorAll('.lead-check').forEach(c => c.checked = this.checked);
});
document.querySelectorAll('.assign-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        alert('Assign functionality - Integration pending');
    });
});
function bulkAssign() { alert('Bulk assign - Integration pending'); }
</script>
