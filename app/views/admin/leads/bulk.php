<?php $page_title = 'Bulk Actions'; ?>
<div class="container-fluid py-4">
    <h2 class="mb-4"><i class="fas fa-tasks me-2"></i>Bulk Actions</h2>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body aps-cp-card-body">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <label class="form-label">Action</label>
                    <select id="bulkAction" class="form-select">
                        <option value="">Select Action</option>
                        <option value="assign">Assign To</option>
                        <option value="status">Change Status</option>
                        <option value="delete">Delete Selected</option>
                    </select>
                </div>
                <div class="col-md-3" id="assignTo" style="display:none">
                    <label class="form-label">Assign To</label>
                    <select name="assign_to" class="form-select">
                        <option value="">Select User</option>
                    </select>
                </div>
                <div class="col-md-3" id="statusTo" style="display:none">
                    <label class="form-label">New Status</label>
                    <select name="new_status" class="form-select">
                        <option value="new">New</option>
                        <option value="contacted">Contacted</option>
                        <option value="qualified">Qualified</option>
                        <option value="converted">Converted</option>
                        <option value="closed">Closed</option>
                        <option value="dead">Dead</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button id="bulkApply" class="btn btn-primary mt-4" disabled><i class="fas fa-check me-1"></i>Apply</button>
                </div>
            </div>
        </div>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($leads)): ?>
                <p class="text-muted text-center py-4">No leads</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll" class="form-check-input"></th>
                                <th>#</th><th>Name</th><th>Phone</th><th>Source</th><th>Status</th><th>Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($leads as $l): ?>
                            <tr>
                                <td><input type="checkbox" class="form-check-input bulk-check" value="<?= $l['id'] ?>"></td>
                                <td><?= $l['id'] ?></td>
                                <td><?= htmlspecialchars($l['name']) ?></td>
                                <td><?= htmlspecialchars($l['phone'] ?? '') ?></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($l['source'] ?? 'N/A') ?></span></td>
                                <td><span class="badge bg-<?= $l['status']==='new'?'primary':'secondary' ?>"><?= ucfirst($l['status']) ?></span></td>
                                <td><?= htmlspecialchars($l['assignee_name'] ?? 'Unassigned') ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.bulk-check').forEach(c => c.checked = this.checked);
    document.getElementById('bulkApply').disabled = !this.checked;
});
document.querySelectorAll('.bulk-check').forEach(c => c.addEventListener('change', function() {
    const any = document.querySelectorAll('.bulk-check:checked').length > 0;
    document.getElementById('bulkApply').disabled = !any;
}));
document.getElementById('bulkAction')?.addEventListener('change', function() {
    document.getElementById('assignTo').style.display = this.value === 'assign' ? 'block' : 'none';
    document.getElementById('statusTo').style.display = this.value === 'status' ? 'block' : 'none';
});
</script>
