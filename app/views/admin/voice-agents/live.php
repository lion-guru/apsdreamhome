<?php
/**
 * Live Voice Calls View
 * Real-time monitoring of in-progress and recent Twilio voice calls.
 * Polls /api/voice-agent/call-history every 5s for new status updates.
 * Data passed from VoiceAgentAdminController::live()
 */
$page_title = $page_title ?? 'Live Voice Calls';
$page_heading = $page_heading ?? 'Live Voice Calls Monitor';
$activePage = $activePage ?? 'voice-agents-live';

$sessions = $sessions ?? [];
$inProgress = $inProgress ?? [];
$recent = $recent ?? [];
$stat = $stat ?? ['in_progress' => 0, 'completed' => 0, 'failed' => 0, 'with_recording' => 0];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-phone-volume text-success"></i> Live Voice Calls</h1>
            <p class="text-muted small mb-0">Real-time monitoring of Twilio voice calls</p>
        </div>
        <div>
            <span class="badge bg-success" id="live-indicator">
                <i class="fas fa-circle"></i> LIVE
            </span>
            <small class="text-muted ms-2">Auto-refresh: 5s</small>
        </div>
    </div>

    <!-- Stats cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-warning shadow-sm">
                <div class="card-body py-3">
                    <div class="text-muted small">In Progress</div>
                    <div class="h3 mb-0 text-warning"><?= $stat['in_progress'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success shadow-sm">
                <div class="card-body py-3">
                    <div class="text-muted small">Completed</div>
                    <div class="h3 mb-0 text-success"><?= $stat['completed'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger shadow-sm">
                <div class="card-body py-3">
                    <div class="text-muted small">Failed</div>
                    <div class="h3 mb-0 text-danger"><?= $stat['failed'] ?></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-info shadow-sm">
                <div class="card-body py-3">
                    <div class="text-muted small">With Recording</div>
                    <div class="h3 mb-0 text-info"><?= $stat['with_recording'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- In-progress calls -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <i class="fas fa-broadcast-tower"></i> In-Progress Calls
            <span class="badge bg-dark ms-2"><?= count($inProgress) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($inProgress)): ?>
                <div class="text-center text-muted py-4">
                    <i class="fas fa-phone-slash fa-2x mb-2"></i>
                    <p class="mb-0">No active calls</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Call SID</th>
                                <th>Lead</th>
                                <th>Phone</th>
                                <th>Agent</th>
                                <th>Status</th>
                                <th>Started</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inProgress as $s): ?>
                                <tr>
                                    <td><code class="small"><?= htmlspecialchars(mb_substr($s['call_sid'] ?? '', 0, 14)) ?>...</code></td>
                                    <td><?= htmlspecialchars($s['lead_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($s['phone'] ?? $s['lead_phone'] ?? '') ?></td>
                                    <td><span class="badge bg-info"><?= htmlspecialchars($s['ai_agent_id'] ?? '') ?></span></td>
                                    <td><span class="badge bg-warning text-dark"><?= htmlspecialchars($s['status'] ?? '') ?></span></td>
                                    <td><small><?= htmlspecialchars($s['started_at'] ?? '') ?></small></td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-primary"
                                                onclick="transferCall('<?= htmlspecialchars($s['call_sid'] ?? '') ?>')">
                                            <i class="fas fa-share"></i> Transfer
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="hangupCall('<?= htmlspecialchars($s['call_sid'] ?? '') ?>')">
                                            <i class="fas fa-phone-slash"></i> Hangup
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

    <!-- Recent completed calls -->
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <i class="fas fa-history"></i> Recent Completed Calls
            <span class="badge bg-secondary ms-2"><?= count($recent) ?></span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($recent)): ?>
                <div class="text-center text-muted py-4">
                    <p class="mb-0">No recent calls</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Call SID</th>
                                <th>Lead</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Duration</th>
                                <th>DTMF</th>
                                <th>Recording</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent as $s): ?>
                                <tr>
                                    <td><code class="small"><?= htmlspecialchars(mb_substr($s['call_sid'] ?? '', 0, 14)) ?>...</code></td>
                                    <td><?= htmlspecialchars($s['lead_name'] ?? 'N/A') ?></td>
                                    <td><?= htmlspecialchars($s['phone'] ?? '') ?></td>
                                    <td>
                                        <?php $st = $s['status'] ?? ''; ?>
                                        <span class="badge bg-<?= $st === 'completed' ? 'success' : 'danger' ?>">
                                            <?= htmlspecialchars($st ?? '') ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars(($s['duration_seconds'] ?? '-') . 's') ?></td>
                                    <td><?= htmlspecialchars($s['digits_pressed'] ?? '-') ?></td>
                                    <td>
                                        <?php if (!empty($s['recording_url'])): ?>
                                            <a href="<?= htmlspecialchars($s['recording_url'] ?? '') ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-play"></i> Listen
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
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

<!-- Transfer modal -->
<div class="modal fade" id="transferModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Transfer Call</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Transfer to (E.164 phone)</label>
                    <input type="tel" class="form-control" id="transferToNumber" placeholder="+919876543210">
                </div>
                <div id="transferResult" class="alert d-none"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitTransfer()">Transfer</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentCallSid = null;

function transferCall(callSid) {
    currentCallSid = callSid;
    document.getElementById('transferToNumber').value = '';
    document.getElementById('transferResult').className = 'alert d-none';
    const modal = new bootstrap.Modal(document.getElementById('transferModal'));
    modal.show();
}

function submitTransfer() {
    const number = document.getElementById('transferToNumber').value.trim();
    if (!currentCallSid || !number) return;
    const result = document.getElementById('transferResult');
    result.className = 'alert alert-info';
    result.textContent = 'Transferring call...';
    result.classList.remove('d-none');

    showLoader();
    fetch('<?= BASE_URL ?>/admin/voice-agents/transfer-call', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': window.CSRF_TOKEN || ''},
        body: 'call_sid=' + encodeURIComponent(currentCallSid) + '&to=' + encodeURIComponent(number)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            result.className = 'alert alert-success';
            result.textContent = 'Call transferred successfully.';
            setTimeout(() => location.reload(), 1500);
        } else {
            result.className = 'alert alert-danger';
            result.textContent = 'Transfer failed: ' + (data.error || 'unknown error');
        }
    })
    .catch(e => {
        result.className = 'alert alert-danger';
        result.textContent = 'Error: ' + e.message;
    ).finally(() => hideLoader());
}

function hangupCall(callSid) {
    if (!confirm('Hangup this call? This action cannot be undone.')) return;
    showLoader();
    fetch('<?= BASE_URL ?>/admin/voice-agents/hangup-call', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-CSRF-TOKEN': window.CSRF_TOKEN || ''},
        body: 'call_sid=' + encodeURIComponent(callSid)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showToast('Call ended.', 'info');
            location.reload();
        } else {
            showToast('Hangup failed: ' + (data.error || 'unknown'), 'danger');
        }
    })
    .catch(e => showToast('Error: ' + e.message, 'danger')).finally(() => hideLoader());
}

// Auto-refresh every 5s
setInterval(() => location.reload(), 5000);
</script>
