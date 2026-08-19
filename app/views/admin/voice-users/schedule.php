<?php $upcomingCalls = $upcomingCalls ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Call Schedule</h4>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header aps-cp-card-header">Upcoming Calls</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Customer</th>
                                <th>Phone</th>
                                <th>Agent</th>
                                <th>Scheduled Time</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($upcomingCalls)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No upcoming calls.</td></tr>
                            <?php else: ?>
                                <?php foreach ($upcomingCalls as $c): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($c['customer_name'] ?? $c['name'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($c['phone'] ?? $c['customer_phone'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($c['agent_name'] ?? $c['agent'] ?? 'Auto') ?></td>
                                        <td><?= htmlspecialchars($c['scheduled_at'] ?? $c['schedule_time'] ?? '-') ?></td>
                                        <td><span class="badge bg-<?= ($c['status'] ?? 'scheduled') === 'scheduled' ? 'primary' : (($c['status'] ?? '') === 'in_progress' ? 'warning' : 'success') ?>"><?= ucfirst(str_replace('_', ' ', $c['status'] ?? 'scheduled')) ?></span></td>
                                        <td>
                                            <form method="post" action="<?= BASE_URL ?>admin/voice-users/cancel-schedule/<?= (int)($c['id'] ?? 0) ?>" class="d-inline" onsubmit="return confirm('Cancel this call?')">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Close"><i class="fas fa-times"></i></button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#rescheduleModal" data-schedule-id="<?= (int)($c['id'] ?? 0) ?>" data-customer="<?= htmlspecialchars(($c['customer_name'] ?? $c['name'] ?? ''), ENT_QUOTES) ?>"><i class="fas fa-calendar-alt"></i></button>
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
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header aps-cp-card-header">Bulk Schedule</div>
            <div class="card-body aps-cp-card-body">
                <form method="post" action="<?= BASE_URL ?>admin/voice-users/schedule">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-3">
                        <label class="form-label">Phone Numbers (one per line)</label>
                        <textarea name="phones" class="form-control" rows="4" placeholder="+919999999999&#10;+918888888888"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Agent</label>
                        <select name="agent" class="form-select">
                            <option value="auto">Auto Assign</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Schedule For</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Script</label>
                        <select name="script_id" class="form-select">
                            <option value="">Default Script</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-calendar-check"></i> Schedule Calls</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rescheduleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Reschedule Call</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="post" action="<?= BASE_URL ?>admin/voice-users/reschedule/0" id="rescheduleForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="schedule_id" id="rescheduleId">
        <div class="modal-body">
            <p id="rescheduleCustomer"></p>
            <div class="mb-3"><label class="form-label">New Date</label><input type="date" name="new_date" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">New Time</label><input type="time" name="new_time" class="form-control"></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-success">Reschedule</button></div>
    </form>
</div></div></div>

<script>
document.getElementById('rescheduleModal').addEventListener('show.bs.modal', function(e) {
    var btn = e.relatedTarget;
    var id = btn.getAttribute('data-schedule-id');
    document.getElementById('rescheduleId').value = id;
    document.getElementById('rescheduleCustomer').textContent = 'Rescheduling: ' + btn.getAttribute('data-customer');
    document.getElementById('rescheduleForm').action = '<?= BASE_URL ?>admin/voice-users/reschedule/' + id;
});
</script>
