<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-hard-hat me-2"></i>Colony Construction Progress</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/projects/progress" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Project Progress</a>
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#milestoneModal" onclick="resetMilestoneForm()"><i class="fas fa-plus me-1"></i>Add Milestone</button>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?= BASE_URL ?>/admin/construction/colony-progress" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label mb-1">Select Colony</label>
                    <select name="colony_id" id="colonySelect" class="form-select">
                        <option value="">-- Choose a colony --</option>
                        <?php foreach (($colonies ?? []) as $c): ?>
                            <option value="<?= $c['id'] ?? '' ?>" <?= ((int)($c['id'] ?? 0) === (int)($colony_id ?? 0)) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['name'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i>View Progress</button>
                </div>
                <?php if ((int)($colony_id ?? 0) > 0): ?>
                <div class="col-md-2">
                    <a href="<?= BASE_URL ?>/admin/construction/colony-progress" class="btn btn-outline-secondary w-100">Clear</a>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <?php if (($selected_colony ?? null) !== null): ?>
        <?php $avgPct = (float)($selected_colony['avg_progress'] ?? 0); ?>
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-start border-primary border-4 h-100">
                    <div class="card-body">
                        <h6 class="text-muted mb-1">Colony</h6>
                        <h5 class="mb-0"><strong><?= htmlspecialchars($selected_colony['name'] ?? '') ?></strong></h5>
                        <small class="text-muted">
                            <?= htmlspecialchars(($selected_colony['district_name'] ?? '—') . ' / Phase ' . ($selected_colony['phase'] ?? '—')) ?>
                        </small>
                        <div class="mt-2">
                            <span class="badge bg-<?= strtolower($selected_colony['colony_status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                <?= htmlspecialchars(ucfirst($selected_colony['colony_status'] ?? 'Unknown')) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm bg-primary text-white h-100">
                    <div class="card-body text-center">
                        <h2 class="mb-0"><?= (int)($selected_colony['total_plots'] ?? 0) ?></h2>
                        <small>Total Plots</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm bg-info text-white h-100">
                    <div class="card-body text-center">
                        <h2 class="mb-0"><?= (int)($selected_colony['available_plots'] ?? 0) ?></h2>
                        <small>Available</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm bg-success text-white h-100">
                    <div class="card-body text-center">
                        <h2 class="mb-0"><?= (int)($selected_colony['completed_milestones'] ?? 0) ?> / <?= (int)($selected_colony['total_milestones'] ?? 0) ?></h2>
                        <small>Milestones Done</small>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="card shadow-sm bg-warning text-white h-100">
                    <div class="card-body text-center">
                        <h2 class="mb-0"><?= number_format($avgPct, 1) ?>%</h2>
                        <small>Avg Progress</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header aps-cp-card-header">
                <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Milestones — <?= htmlspecialchars($selected_colony['name'] ?? '') ?></h5>
            </div>
            <div class="card-body p-0 aps-cp-card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Milestone</th>
                                <th>Category</th>
                                <th>Status</th>
                                <th>Progress</th>
                                <th>Start</th>
                                <th>Expected</th>
                                <th>Contractor</th>
                                <th>Est. Cost</th>
                                <th>Actual Cost</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($milestones ?? [])): ?>
                                <tr><td colspan="11" class="text-center text-muted py-5">
                                    <i class="fas fa-hard-hat fa-3x text-muted mb-3"></i>
                                    <h5>No Milestones</h5>
                                    <p class="mb-3">No construction milestones recorded for this colony yet.</p>
                                    <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#milestoneModal" onclick="resetMilestoneForm()">
                                        <i class="fas fa-plus me-1"></i>Add First Milestone
                                    </button>
                                </td></tr>
                            <?php else: ?>
                                <?php foreach ($milestones as $i => $m): ?>
                                    <?php $pct = (float)($m['progress_pct'] ?? 0); ?>
                                    <tr>
                                        <td><?= $i + 1 ?></td>
                                        <td><strong><?= htmlspecialchars($m['milestone_name'] ?? '') ?></strong></td>
                                        <td><span class="badge bg-secondary"><?= htmlspecialchars(ucfirst($m['category'] ?? 'other')) ?></span></td>
                                        <td>
                                            <?php $st = $m['status'] ?? 'not_started'; ?>
                                            <span class="badge bg-<?= $st === 'completed' ? 'success' : ($st === 'in_progress' ? 'primary' : ($st === 'on_hold' ? 'warning' : ($st === 'cancelled' ? 'danger' : 'secondary'))) ?>">
                                                <?= htmlspecialchars(str_replace('_', ' ', ucfirst($st))) ?>
                                            </span>
                                        </td>
                                        <td style="min-width:140px;">
                                            <div class="progress">
                                                <div class="progress-bar bg-<?= $pct >= 100 ? 'success' : ($pct >= 50 ? 'info' : 'warning') ?>" role="progressbar" style="width: <?= min(100, $pct) ?>%" aria-valuenow="<?= (int)$pct ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <?= (int)$pct ?>%
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= !empty($m['start_date']) ? date('d M Y', strtotime($m['start_date'])) : '—' ?></td>
                                        <td><?= !empty($m['expected_completion']) ? date('d M Y', strtotime($m['expected_completion'])) : '—' ?></td>
                                        <td>
                                            <?php if (!empty($m['contractor_name'])): ?>
                                                <?= htmlspecialchars($m['contractor_name']) ?>
                                                <?php if (!empty($m['contractor_contact'])): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($m['contractor_contact']) ?></small>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                        <td>₹<?= number_format((float)($m['estimated_cost'] ?? 0), 2) ?></td>
                                        <td class="text-<?= (float)($m['actual_cost'] ?? 0) > (float)($m['estimated_cost'] ?? 0) ? 'danger' : 'success' ?>">
                                            ₹<?= number_format((float)($m['actual_cost'] ?? 0), 2) ?>
                                        </td>
                                        <td class="text-end">
                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#milestoneModal"
                                                onclick='editMilestone(<?= json_encode([
                                                    'milestone_id' => (int)($m['id'] ?? 0),
                                                    'colony_id' => (int)($m['colony_id'] ?? 0),
                                                    'milestone_name' => $m['milestone_name'] ?? '',
                                                    'category' => $m['category'] ?? 'other',
                                                    'status' => $m['status'] ?? 'not_started',
                                                    'progress_pct' => $pct,
                                                    'start_date' => $m['start_date'] ?? '',
                                                    'expected_completion' => $m['expected_completion'] ?? '',
                                                    'actual_completion' => $m['actual_completion'] ?? '',
                                                    'contractor_name' => $m['contractor_name'] ?? '',
                                                    'contractor_contact' => $m['contractor_contact'] ?? '',
                                                    'estimated_cost' => $m['estimated_cost'] ?? '',
                                                    'actual_cost' => $m['actual_cost'] ?? '',
                                                    'notes' => $m['notes'] ?? ''
                                                ], JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="milestoneModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <form method="post" action="<?= BASE_URL ?>/admin/construction/colony-progress/update">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="milestone_id" id="milestone_id" value="0">
                        <input type="hidden" name="colony_id" id="m_colony_id" value="<?= (int)($selected_colony['id'] ?? 0) ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="milestoneModalTitle"><i class="fas fa-hard-hat me-2"></i>Add Milestone</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-8">
                                    <label class="form-label">Milestone Name <span class="text-danger">*</span></label>
                                    <input type="text" name="milestone_name" id="milestone_name" class="form-control" required maxlength="255">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Category</label>
                                    <select name="category" id="m_category" class="form-select">
                                        <option value="roads">Roads</option>
                                        <option value="drainage">Drainage</option>
                                        <option value="electricity">Electricity</option>
                                        <option value="boundary">Boundary</option>
                                        <option value="water">Water</option>
                                        <option value="landscaping">Landscaping</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Status</label>
                                    <select name="status" id="m_status" class="form-select">
                                        <option value="not_started">Not Started</option>
                                        <option value="in_progress">In Progress</option>
                                        <option value="on_hold">On Hold</option>
                                        <option value="completed">Completed</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Progress % (0–100)</label>
                                    <input type="number" name="progress_pct" id="progress_pct" class="form-control" min="0" max="100" step="0.01" value="0">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Start Date</label>
                                    <input type="date" name="start_date" id="start_date" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Expected Completion</label>
                                    <input type="date" name="expected_completion" id="expected_completion" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Actual Completion</label>
                                    <input type="date" name="actual_completion" id="actual_completion" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Contractor Name</label>
                                    <input type="text" name="contractor_name" id="contractor_name" class="form-control" maxlength="255">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Contractor Contact</label>
                                    <input type="text" name="contractor_contact" id="contractor_contact" class="form-control" maxlength="50">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Estimated Cost (₹)</label>
                                    <input type="number" name="estimated_cost" id="estimated_cost" class="form-control" min="0" step="0.01" value="0">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Actual Cost (₹)</label>
                                    <input type="number" name="actual_cost" id="actual_cost" class="form-control" min="0" step="0.01" value="0">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea name="notes" id="m_notes" class="form-control" rows="3" maxlength="2000"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success"><i class="fas fa-save me-1"></i>Save Milestone</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-hard-hat fa-3x text-muted mb-3"></i>
                <h5>Select a Colony</h5>
                <p class="text-muted mb-3">Choose a colony above to view and manage its construction milestones.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function resetMilestoneForm() {
    document.getElementById('milestoneModalTitle').innerHTML = '<i class="fas fa-hard-hat me-2"></i>Add Milestone';
    document.getElementById('milestone_id').value = '0';
    document.getElementById('milestone_name').value = '';
    document.getElementById('m_category').selectedIndex = 0;
    document.getElementById('m_status').selectedIndex = 0;
    document.getElementById('progress_pct').value = '0';
    document.getElementById('start_date').value = '';
    document.getElementById('expected_completion').value = '';
    document.getElementById('actual_completion').value = '';
    document.getElementById('contractor_name').value = '';
    document.getElementById('contractor_contact').value = '';
    document.getElementById('estimated_cost').value = '0';
    document.getElementById('actual_cost').value = '0';
    document.getElementById('m_notes').value = '';
}

function editMilestone(m) {
    document.getElementById('milestoneModalTitle').innerHTML = '<i class="fas fa-hard-hat me-2"></i>Edit Milestone';
    document.getElementById('milestone_id').value = m.milestone_id || '0';
    document.getElementById('m_colony_id').value = m.colony_id || document.getElementById('m_colony_id').value;
    document.getElementById('milestone_name').value = m.milestone_name || '';
    document.getElementById('m_category').value = m.category || 'other';
    document.getElementById('m_status').value = m.status || 'not_started';
    document.getElementById('progress_pct').value = m.progress_pct || '0';
    document.getElementById('start_date').value = m.start_date || '';
    document.getElementById('expected_completion').value = m.expected_completion || '';
    document.getElementById('actual_completion').value = m.actual_completion || '';
    document.getElementById('contractor_name').value = m.contractor_name || '';
    document.getElementById('contractor_contact').value = m.contractor_contact || '';
    document.getElementById('estimated_cost').value = m.estimated_cost || '0';
    document.getElementById('actual_cost').value = m.actual_cost || '0';
    document.getElementById('m_notes').value = m.notes || '';
}
</script>