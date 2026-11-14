<?php $upcomingCalls = $upcomingCalls ?? []; $scripts_list = $scripts_list ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-calendar-check text-primary me-2" aria-hidden="true"></i>AI Call Schedule</h4>
    <a href="<?= BASE_URL ?>/admin/voice-users/history" class="btn btn-sm btn-outline-secondary"><i class="fas fa-clock-rotate-left me-1" aria-hidden="true"></i>Call History</a>
</div>
<div class="row g-2 mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-primary text-white"><div class="card-body text-center py-2"><h5 class="mb-0"><?= (int)($today_count ?? 0) ?></h5><small>Today</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-warning text-dark"><div class="card-body text-center py-2"><h5 class="mb-0"><?= (int)($pending ?? 0) ?></h5><small>Pending</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-success text-white"><div class="card-body text-center py-2"><h5 class="mb-0"><?= (int)($completed ?? 0) ?></h5><small>Completed</small></div></div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-danger text-white"><div class="card-body text-center py-2"><h5 class="mb-0"><?= (int)($failed ?? 0) ?></h5><small>Failed</small></div></div></div>
</div>
<div class="row">
    <div class="col-md-8">
        <div class="card aps-cp-card border-0 shadow-sm">
            <div class="card-header aps-cp-card-header">Upcoming Calls</div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Lead</th>
                                <th scope="col">Phone</th>
                                <th scope="col">Script</th>
                                <th scope="col">Scheduled For</th>
                                <th scope="col">Status</th>
                                <th scope="col">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($upcomingCalls)): ?>
                                <tr><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No upcoming calls.</td></tr>
                            <?php else: ?>
                                <?php foreach ($upcomingCalls as $c): ?>
                                    <?php
                                    $statusClass = match ($c['status'] ?? '') {
                                        'pending' => 'primary',
                                        'processing' => 'warning',
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        'cancelled' => 'secondary',
                                        default => 'secondary',
                                    };
                                    $when = trim(($c['scheduled_date'] ?? '') . ' ' . substr((string)($c['scheduled_time'] ?? ''), 0, 5));
                                    ?>
                                    <tr>
                                        <td><div class="fw-semibold"><?= htmlspecialchars($c['lead_name'] ?? '—') ?></div></td>
                                        <td><small><?= htmlspecialchars($c['lead_phone'] ?? $c['phone'] ?? '—') ?></small></td>
                                        <td><small><?= htmlspecialchars($c['script_name'] ?? $c['script_template'] ?? 'AI Call') ?></small></td>
                                        <td><small><?= htmlspecialchars($when !== '' ? $when : '—') ?></small></td>
                                        <td><span class="badge bg-<?= $statusClass ?>"><?= ucfirst(str_replace('_', ' ', $c['status'] ?? 'pending')) ?></span></td>
                                        <td>
                                            <form method="post" action="<?= BASE_URL ?>/admin/voice-users/cancel-schedule/<?= (int)($c['id'] ?? 0) ?>" class="d-inline" data-aps-confirm="Cancel this call?">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Cancel call"><i class="fas fa-times"></i></button>
                                            </form>
                                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#rescheduleModal" data-schedule-id="<?= (int)($c['id'] ?? 0) ?>" data-customer="<?= htmlspecialchars(($c['lead_name'] ?? $c['phone'] ?? ''), ENT_QUOTES) ?>" aria-label="Reschedule"><i class="fas fa-calendar-alt"></i></button>
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
        <div class="card mb-3 border-0 shadow-sm">
            <div class="card-header aps-cp-card-header">Auto-Assign</div>
            <div class="card-body aps-cp-card-body">
                <p class="text-muted small">Distribute up to 50 new, contacted or nurture leads from the CRM — anyone not already scheduled for an AI call — round-robin across <strong><?= count($scripts_list) ?></strong> active call script(s).</p>
                <form method="post" action="<?= BASE_URL ?>/admin/voice-users/auto-assign">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <button type="submit" class="btn btn-primary w-100" data-aps-confirm="Auto-assign pending leads now?"><i class="fas fa-robot me-2" aria-hidden="true"></i>Assign Pending Leads</button>
                </form>
                <hr>
                <p class="small text-muted mb-1">Active scripts:</p>
                <?php if (empty($scripts_list)): ?>
                    <span class="badge bg-secondary">none</span>
                <?php else: ?>
                    <?php foreach ($scripts_list as $sr): ?>
                        <span class="badge bg-info text-dark me-1 mb-1"><?= htmlspecialchars($sr['script_name'] ?? $sr['script_code']) ?></span>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="rescheduleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Reschedule Call</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
    <form method="post" action="<?= BASE_URL ?>/admin/voice-users/reschedule/0" id="rescheduleForm">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <input type="hidden" name="schedule_id" id="rescheduleId">
        <div class="modal-body">
            <p id="rescheduleCustomer"></p>
            <div class="mb-3"><label class="form-label" for="rescheduleDate">New Date</label><input id="rescheduleDate" aria-label="New date" type="date" name="new_date" class="form-control" required></div>
            <div class="mb-3"><label class="form-label" for="rescheduleTime">New Time</label><input id="rescheduleTime" aria-label="New time" type="time" name="new_time" class="form-control"></div>
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
    document.getElementById('rescheduleForm').action = '<?= BASE_URL ?>/admin/voice-users/reschedule/' + id;
});
</script>
