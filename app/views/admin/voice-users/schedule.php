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
                                            <button class="btn btn-sm btn-outline-danger" onclick="alert('Cancel call')"><i class="fas fa-times"></i></button>
                                            <button class="btn btn-sm btn-outline-success" onclick="alert('Reschedule call')"><i class="fas fa-calendar-alt"></i></button>
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
