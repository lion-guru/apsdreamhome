<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0"><i class="fas fa-calendar-alt me-2"></i> Call Schedule</h3>
        <div>
            <a href="<?= BASE_URL ?>/admin/voice-users/dashboard" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <h6 class="text-white-50 small">Scheduled Today</h6>
                    <h3 class="mb-0 fw-bold"><?= $today_count ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <h6 class="text-dark-50 small">Pending</h6>
                    <h3 class="mb-0 fw-bold"><?= $pending_count ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <h6 class="text-white-50 small">Completed</h6>
                    <h3 class="mb-0 fw-bold"><?= $completed_count ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6 mb-3">
            <div class="card bg-danger text-white">
                <div class="card-body py-3">
                    <h6 class="text-white-50 small">Failed</h6>
                    <h3 class="mb-0 fw-bold"><?= $failed_count ?? 0 ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Schedule Table -->
        <div class="col-md-8 mb-4">
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">Scheduled Calls</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="small">Date / Time</th>
                                    <th class="small">Lead</th>
                                    <th class="small">Phone</th>
                                    <th class="small">Agent</th>
                                    <th class="small text-center">Priority</th>
                                    <th class="small text-center">Attempts</th>
                                    <th class="small text-center">Status</th>
                                    <th class="small">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($schedule_items)): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No scheduled calls</td></tr>
                                <?php else: ?>
                                <?php foreach ($schedule_items as $item): ?>
                                <tr>
                                    <td class="small"><?= date('d M Y', strtotime($item['scheduled_date'] ?? 'now')) ?><br><span class="text-muted"><?= date('h:i A', strtotime($item['scheduled_time'] ?? '00:00:00')) ?></span></td>
                                    <td class="fw-medium"><?= htmlspecialchars($item['lead_name'] ?? "Lead #{$item['lead_id']}") ?></td>
                                    <td><?= htmlspecialchars($item['phone'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($item['agent_display_name'] ?? $item['ai_agent_id'] ?? 'Unassigned') ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $item['priority'] === 'urgent' ? 'danger' : ($item['priority'] === 'high' ? 'warning' : ($item['priority'] === 'low' ? 'secondary' : 'primary')) ?>">
                                            <?= ucfirst($item['priority'] ?? 'medium') ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?= (int)($item['attempt_count'] ?? 0) ?>/<?= (int)($item['max_attempts'] ?? 3) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $item['status'] === 'completed' ? 'success' : ($item['status'] === 'processing' ? 'info' : ($item['status'] === 'failed' ? 'danger' : ($item['status'] === 'cancelled' ? 'secondary' : 'warning'))) ?>">
                                            <?= ucfirst($item['status'] ?? 'pending') ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (($item['status'] ?? '') !== 'completed' && ($item['status'] ?? '') !== 'cancelled'): ?>
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#rescheduleModal"
                                            data-id="<?= $item['id'] ?>"
                                            data-date="<?= $item['scheduled_date'] ?>"
                                            data-time="<?= $item['scheduled_time'] ?>"
                                            onclick="setupReschedule(this)">
                                            <i class="fas fa-calendar"></i>
                                        </button>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/voice-users/schedule/cancel" class="style-71727">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="schedule_id" value="<?= $item['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this call?')">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Schedule Form -->
        <div class="col-md-4 mb-4">
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-plus-circle me-1"></i> Bulk Schedule</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/voice-users/schedule/bulk">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Select Leads</label>
                            <div class="style-68333">
                                <?php if (empty($leads_list)): ?>
                                <p class="text-muted small mb-0">No available leads to schedule</p>
                                <?php else: ?>
                                <?php foreach ($leads_list as $lead): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="lead_ids[]" value="<?= $lead['id'] ?>" id="lead<?= $lead['id'] ?>">
                                    <label class="form-check-label small" for="lead<?= $lead['id'] ?>">
                                        <?= htmlspecialchars($lead['name'] ?? 'Unknown') ?> - <?= htmlspecialchars($lead['phone'] ?? '-') ?>
                                        <?php if (!empty($lead['property_interest'])): ?>
                                        <span class="text-muted">(<?= htmlspecialchars($lead['property_interest']) ?>)</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Agent</label>
                            <select name="agent_id" class="form-select" required>
                                <option value="">Select Agent</option>
                                <?php foreach ($agents_list as $agent): ?>
                                <option value="<?= htmlspecialchars($agent['agent_id']) ?>">
                                    <?= htmlspecialchars($agent['agent_name']) ?>
                                    <?php if ($agent['status'] === 'busy'): ?>(Busy)<?php endif; ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label small fw-bold">Date</label>
                                <input type="date" name="schedule_date" class="form-control" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small fw-bold">Time</label>
                                <input type="time" name="schedule_time" class="form-control" value="10:00">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-calendar-plus me-1"></i> Schedule Calls</button>
                    </form>
                </div>
            </div>

            <!-- Auto-Assign -->
            <div class="card">
                <div class="card-body text-center">
                    <h6 class="fw-bold"><i class="fas fa-robot me-1"></i> Auto-Assign Leads</h6>
                    <p class="small text-muted">Automatically assign pending leads to available users</p>
                    <form method="POST" action="<?= BASE_URL ?>/admin/voice-users/schedule/auto-assign">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-info w-100" onclick="return confirm('Auto-assign pending leads to users?')">
                            <i class="fas fa-magic me-1"></i> Auto-Assign Leads to users
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="<?= BASE_URL ?>/admin/voice-users/schedule/reschedule">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reschedule Call</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="schedule_id" id="rescheduleId">
                    <div class="mb-3">
                        <label class="form-label">New Date</label>
                        <input type="date" name="new_date" id="rescheduleDate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Time</label>
                        <input type="time" name="new_time" id="rescheduleTime" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Reschedule</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function setupReschedule(btn) {
    document.getElementById('rescheduleId').value = btn.dataset.id;
    document.getElementById('rescheduleDate').value = btn.dataset.date || '';
    document.getElementById('rescheduleTime').value = btn.dataset.time || '';
}
</script>
