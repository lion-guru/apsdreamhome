<?php
$page_title = 'Lead Assignment';
$unassigned = $unassigned ?? [];
$assignees = $assignees ?? [];
$recent_assignments = $recent_assignments ?? [];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-user-plus text-primary me-2"></i>Lead Assignment</h2>
        <a href="<?= BASE_URL ?>/admin/leads" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Leads</a>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white"><div class="card-body"><h5>Unassigned</h5><h3><?= count($unassigned) ?></h3></div></div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white"><div class="card-body"><h5>Available Agents</h5><h3><?= count($assignees) ?></h3></div></div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Assign Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-hand-pointer me-2"></i>Select Leads to Assign</h6></div>
                <div class="card-body">
                    <?php if (empty($unassigned)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">All leads are assigned! Great job.</p>
                        </div>
                    <?php else: ?>
                        <form action="<?= BASE_URL ?>/admin/leads/assign/process" method="POST" id="assignForm">
                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">

                            <!-- Assignee Select -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Assign to:</label>
                                <select class="form-select" name="assigned_to" required id="assigneeSelect">
                                    <option value="">— Select Agent —</option>
                                    <?php foreach ($assignees as $a): ?>
                                        <option value="<?= $a['id'] ?>"><?= htmlspecialchars($a['name']) ?> (<?= ucfirst($a['role']) ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Reason -->
                            <div class="mb-3">
                                <label class="form-label fw-bold">Reason (optional):</label>
                                <input type="text" class="form-control" name="reason" placeholder="e.g. Round-robin, by specialization, etc.">
                            </div>

                            <!-- Lead Checkboxes -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="form-label fw-bold mb-0">Select Leads:</label>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="toggleAll()">Select All</button>
                                </div>
                                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                    <table class="table table-hover table-sm">
                                        <thead class="sticky-top bg-light"><tr><th width="40"><input type="checkbox" id="checkAll" onchange="toggleAll()"></th><th>Lead</th><th>Contact</th><th>Score</th><th>Created</th></tr></thead>
                                        <tbody>
                                        <?php foreach ($unassigned as $l): ?>
                                            <tr>
                                                <td><input type="checkbox" name="lead_ids[]" value="<?= $l['id'] ?>" class="lead-check"></td>
                                                <td>
                                                    <strong><?= htmlspecialchars($l['name']) ?></strong>
                                                    <br><small class="text-muted"><?= htmlspecialchars($l['source'] ?? '') ?></small>
                                                </td>
                                                <td>
                                                    <small><?= htmlspecialchars($l['phone'] ?? '') ?></small>
                                                    <?php if ($l['email']): ?><br><small class="text-muted"><?= htmlspecialchars($l['email']) ?></small><?php endif; ?>
                                                </td>
                                                <td><span class="badge bg-<?= ($l['lead_score'] ?? 0) >= 70 ? 'success' : (($l['lead_score'] ?? 0) >= 40 ? 'warning' : 'secondary') ?>"><?= $l['lead_score'] ?? 0 ?></span></td>
                                                <td><small><?= date('M d', strtotime($l['created_at'])) ?></small></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Auto-assign -->
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary" id="assignBtn" disabled>
                                    <i class="fas fa-user-plus me-1"></i> Assign Selected
                                </button>
                                <a href="<?= BASE_URL ?>/admin/leads/scoring/auto-assign" class="btn btn-outline-warning" onclick="return confirm('Auto-assign all unassigned leads using round-robin?')">
                                    <i class="fas fa-magic me-1"></i> Auto-Assign (Round Robin)
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Assignments -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Assignments</h6></div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($recent_assignments)): ?>
                        <p class="text-muted text-center py-3">No assignments yet</p>
                    <?php else: ?>
                        <?php foreach ($recent_assignments as $ra): ?>
                        <div class="mb-3 pb-3 border-bottom">
                            <div class="fw-bold" style="font-size: 0.85rem;"><?= htmlspecialchars($ra['lead_name'] ?? 'Unknown') ?></div>
                            <small class="text-muted">
                                <?php if ($ra['from_name']): ?>
                                    <?= htmlspecialchars($ra['from_name']) ?> →
                                <?php endif; ?>
                                <strong><?= htmlspecialchars($ra['to_name'] ?? 'Unknown') ?></strong>
                                by <?= htmlspecialchars($ra['by_name'] ?? 'System') ?>
                            </small>
                            <br><small class="text-muted"><?= date('M d, g:i A', strtotime($ra['created_at'])) ?></small>
                            <?php if (!empty($ra['reason'])): ?>
                                <br><small class="text-muted"><i class="fas fa-info-circle me-1"></i><?= htmlspecialchars($ra['reason']) ?></small>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleAll() {
    var checkAll = document.getElementById('checkAll');
    var boxes = document.querySelectorAll('.lead-check');
    boxes.forEach(function(b) { b.checked = checkAll.checked; });
    updateBtn();
}
document.querySelectorAll('.lead-check').forEach(function(b) {
    b.addEventListener('change', updateBtn);
});
function updateBtn() {
    var checked = document.querySelectorAll('.lead-check:checked').length;
    var btn = document.getElementById('assignBtn');
    var sel = document.getElementById('assigneeSelect');
    btn.disabled = checked === 0 || !sel.value;
}
document.getElementById('assigneeSelect').addEventListener('change', updateBtn);
</script>
