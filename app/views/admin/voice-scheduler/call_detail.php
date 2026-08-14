<?php $call = $call ?? []; $session = $session ?? null; $extractedLead = $extractedLead ?? null; $callHistory = $callHistory ?? []; ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0"><i class="fas fa-phone-volume me-2"></i>Call Detail #<?= htmlspecialchars($call['id'] ?? '', ENT_QUOTES, 'UTF-8') ?></h4>
    <a href="<?= BASE_URL ?>admin/voice-scheduler/calls" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to Calls</a>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header aps-cp-card-header"><i class="fas fa-user me-2"></i>Lead Information</div>
            <div class="card-body aps-cp-card-body">
                <table class="table table-sm mb-0">
                    <tr><th class="style-72730">Name</th><td><?= htmlspecialchars($call['lead_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th>Phone</th><td><?= htmlspecialchars($call['phone'] ?: ($call['lead_phone'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th>Email</th><td><?= htmlspecialchars($call['lead_email'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th>Interest</th><td><?= htmlspecialchars($call['property_interest'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th>Budget</th><td><?= htmlspecialchars($call['budget_range'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th>Lead Status</th><td><span class="badge bg-info"><?= $call['lead_status'] ?? 'unknown' ?></span></td></tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm h-100">
            <div class="card-header aps-cp-card-header"><i class="fas fa-calendar me-2"></i>Schedule Details</div>
            <div class="card-body aps-cp-card-body">
                <table class="table table-sm mb-0">
                    <tr><th class="style-72730">Agent</th><td><i class="fas fa-robot me-1 text-primary"></i><?= htmlspecialchars($call['agent_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td></tr>
                    <tr><th>Date/Time</th><td><?= date('d M Y', strtotime($call['scheduled_date'] ?? '')) ?> at <?= date('h:i A', strtotime($call['scheduled_time'] ?? '')) ?></td></tr>
                    <tr><th>Priority</th><td><span class="badge bg-<?= $call['priority'] === 'urgent' ? 'danger' : ($call['priority'] === 'high' ? 'warning' : 'primary') ?>"><?= $call['priority'] ?? 'medium' ?></span></td></tr>
                    <tr><th>Status</th><td><span class="badge bg-<?= $call['status'] === 'completed' ? 'success' : ($call['status'] === 'failed' ? 'danger' : ($call['status'] === 'cancelled' ? 'secondary' : 'warning')) ?>"><?= $call['status'] ?? 'pending' ?></span></td></tr>
                    <tr><th>Attempts</th><td><?= (int)($call['attempt_count'] ?? 0) ?> / <?= (int)($call['max_attempts'] ?? 3) ?></td></tr>
                    <tr><th>Script</th><td><?= htmlspecialchars($call['script_template'] ?? 'default', ENT_QUOTES, 'UTF-8') ?></td></tr>
                </table>
                <?php if ($call['status'] === 'pending'): ?>
                <div class="mt-3">
                    <form method="post" action="<?= BASE_URL ?>admin/voice-scheduler/process" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <input type="hidden" name="limit" value="1">
                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-play me-1"></i>Process Now</button>
                    </form>
                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#rescheduleModal"><i class="fas fa-calendar-alt me-1"></i>Reschedule</button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#cancelModal"><i class="fas fa-times me-1"></i>Cancel</button>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($session): ?>
<div class="card shadow-sm mt-3">
    <div class="card-header aps-cp-card-header"><i class="fas fa-phone me-2"></i>Call Session #<?= $session['id'] ?? '' ?></div>
    <div class="card-body aps-cp-card-body">
        <div class="row mb-3">
            <div class="col-md-3"><strong>Status:</strong> <span class="badge bg-<?= ($session['status'] ?? '') === 'completed' ? 'success' : 'warning' ?>"><?= $session['status'] ?? 'N/A' ?></span></div>
            <div class="col-md-3"><strong>Duration:</strong> <?= gmdate('i:s', (int)($session['duration_seconds'] ?? 0)) ?> min</div>
            <div class="col-md-3"><strong>Sentiment:</strong> <span class="badge bg-<?= ($session['sentiment_score'] ?? 0) >= 0.5 ? 'success' : 'secondary' ?>"><?= $session['sentiment_score'] ?? '0.00' ?></span></div>
            <div class="col-md-3"><strong>Response:</strong> <?= $session['customer_response'] ?? 'N/A' ?></div>
        </div>
        <?php if (!empty($session['call_transcript'])): ?>
        <div class="mt-2">
            <strong>Transcript:</strong>
            <pre class="bg-light p-3 rounded mt-1" class="style-53016"><?= htmlspecialchars($session['call_transcript'], ENT_QUOTES, 'UTF-8') ?></pre>
        </div>
        <?php endif; ?>
        <?php if (!empty($session['ai_summary'])): ?>
        <div class="mt-2">
            <strong>Summary:</strong>
            <p class="mb-0 p-2 bg-light rounded"><?= htmlspecialchars($session['ai_summary'], ENT_QUOTES, 'UTF-8') ?></p>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($extractedLead): ?>
<div class="card shadow-sm mt-3">
    <div class="card-header aps-cp-card-header"><i class="fas fa-database me-2"></i>Extracted Lead Data</div>
    <div class="card-body aps-cp-card-body">
        <table class="table table-sm mb-0">
            <tr><th class="style-33863">Name</th><td><?= htmlspecialchars($extractedLead['extracted_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><th>Phone</th><td><?= htmlspecialchars($extractedLead['extracted_phone'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><th>Email</th><td><?= htmlspecialchars($extractedLead['extracted_email'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><th>Budget</th><td><?= htmlspecialchars($extractedLead['extracted_budget'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td></tr>
            <tr><th>Interest Level</th><td><span class="badge bg-<?= ($extractedLead['interest_level'] ?? 'cold') === 'hot' ? 'danger' : (($extractedLead['interest_level'] ?? 'cold') === 'warm' ? 'warning' : 'secondary') ?>"><?= $extractedLead['interest_level'] ?? 'cold' ?></span></td></tr>
            <tr><th>Quality Score</th><td><?= (int)($extractedLead['quality_score'] ?? 0) ?>/100</td></tr>
            <tr><th>Requirements</th><td><?= htmlspecialchars($extractedLead['extracted_requirements'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td></tr>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="card shadow-sm mt-3">
    <div class="card-header aps-cp-card-header"><i class="fas fa-history me-2"></i>Call History for this Lead</div>
    <div class="card-body p-0">
        <?php if (empty($callHistory)): ?>
        <div class="text-center text-muted py-3">No previous call history</div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light"><tr>
                    <th>Session</th><th>Date</th><th>Agent</th><th>Status</th><th>Duration</th><th>Response</th>
                </tr></thead>
                <tbody>
                <?php foreach ($callHistory as $h): ?>
                    <tr>
                        <td><a href="<?= BASE_URL ?>admin/voice-scheduler/calls/<?= $h['id'] ?>">#<?= $h['id'] ?></a></td>
                        <td><?= date('d M Y h:i A', strtotime($h['created_at'] ?? '')) ?></td>
                        <td><?= htmlspecialchars($h['agent_name'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge bg-<?= ($h['status'] ?? '') === 'completed' ? 'success' : 'warning' ?>"><?= $h['status'] ?? 'N/A' ?></span></td>
                        <td><?= gmdate('i:s', (int)($h['duration_seconds'] ?? 0)) ?></td>
                        <td><?= htmlspecialchars($h['customer_response'] ?? 'N/A', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="card shadow-sm mt-3">
    <div class="card-header aps-cp-card-header"><i class="fas fa-clipboard-list me-2"></i>Result Notes</div>
    <div class="card-body aps-cp-card-body">
        <p class="mb-0"><?= nl2br(htmlspecialchars($call['result_notes'] ?? 'No notes recorded', ENT_QUOTES, 'UTF-8')) ?></p>
    </div>
</div>

<?php if ($call['status'] === 'pending'): ?>
<div class="modal fade" id="rescheduleModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="post" action="<?= BASE_URL ?>admin/voice-scheduler/reschedule">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="schedule_id" value="<?= $call['id'] ?>">
            <div class="modal-header"><h5 class="modal-title">Reschedule Call</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label">New Date</label><input type="date" name="new_date" class="form-control" value="<?= $call['scheduled_date'] ?? date('Y-m-d') ?>" required></div>
                <div class="mb-3"><label class="form-label">New Time</label><input type="time" name="new_time" class="form-control" value="<?= date('H:i', strtotime($call['scheduled_time'] ?? '10:00')) ?>"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-warning">Reschedule</button>
            </div>
        </form>
    </div></div>
</div>

<div class="modal fade" id="cancelModal" tabindex="-1">
    <div class="modal-dialog"><div class="modal-content">
        <form method="post" action="<?= BASE_URL ?>admin/voice-scheduler/cancel">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="schedule_id" value="<?= $call['id'] ?>">
            <div class="modal-header"><h5 class="modal-title">Cancel Call</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p>Are you sure you want to cancel this scheduled call?</p></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-danger">Cancel Call</button>
            </div>
        </form>
    </div></div>
</div>
<?php endif; ?>
