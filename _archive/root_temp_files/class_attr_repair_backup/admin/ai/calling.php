ï»¿<?php
$page_title = $page_title ?? 'AI Calling System - APS Dream Home';
$page_heading = $page_heading ?? 'AI Calling System';

$connected = $connected ?? false;
$channels = $channels ?? [];
$stats = $stats ?? [];
$recent_calls = $recent_calls ?? [];
$schedule_stats = $schedule_stats ?? [];
$pending_today = $pending_today ?? 0;
$completed_today = $completed_today ?? 0;
$emi_reminders = $emi_reminders ?? 0;
$ai_agents = $ai_agents ?? [];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-phone-volume text-teal"></i> AI Calling System</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/ai">AI</a></li>
                        <li class="breadcrumb-item active">Calling System</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- System Status Row -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card card-outline <?= $connected ? 'card-success' : 'card-danger' ?>">
                        <div class="card-body text-center">
                            <i class="fas <?= $connected ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' ?> fa-3x mb-2"></i>
                            <h4><?= $connected ? 'Online' : 'Offline' ?></h4>
                            <p class="text-muted mb-1">Asterisk AMI</p>
                            <small class="text-muted"><?= count($channels) ?> active channel<?= count($channels) !== 1 ? 's' : '' ?></small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-outline card-info">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-check fa-3x mb-2 text-info"></i>
                            <h4><?= $pending_today ?></h4>
                            <p class="text-muted mb-1">Pending Today</p>
                            <small class="text-muted"><?= $schedule_stats['total_pending'] ?? 0 ?> total queued</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-outline card-success">
                        <div class="card-body text-center">
                            <i class="fas fa-check-double fa-3x mb-2 text-success"></i>
                            <h4><?= $completed_today ?></h4>
                            <p class="text-muted mb-1">Completed Today</p>
                            <small class="text-muted"><?= $schedule_stats['total_completed'] ?? 0 ?> all time</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card card-outline card-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-file-invoice-dollar fa-3x mb-2 text-warning"></i>
                            <h4><?= $emi_reminders ?></h4>
                            <p class="text-muted mb-1">EMI Reminders</p>
                            <small class="text-muted">overdue/upcoming</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Row -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card card-outline card-teal">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-phone-alt"></i> Quick Call</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="input-group mb-2">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        </div>
                                        <input type="tel" id="quickCallPhone" class="form-control" placeholder="919277121112" maxlength="15">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <select id="quickCallScript" class="form-control">
                                        <option value="default">Default IVR</option>
                                        <option value="property_inquiry">Property Inquiry</option>
                                        <option value="lead_followup">Lead Follow-up</option>
                                        <option value="emi_reminder">EMI Reminder</option>
                                        <option value="site_visit">Site Visit</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select id="quickCallAgent" class="form-control">
                                        <option value="">Auto Agent</option>
                                        <?php foreach ($ai_agents as $agent): ?>
                                        <option value="<?= $agent['agent_id'] ?>"><?= $agent['agent_name'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-teal btn-block" onclick="quickCall()" id="quickCallBtn">
                                        <i class="fas fa-phone-alt"></i> Call
                                    </button>
                                </div>
                            </div>
                            <div id="quickCallStatus" class="mt-2" class="style-2248"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-robot"></i> AI Voice Pipeline</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <i class="fas fa-microphone fa-2x text-info mb-1"></i>
                                    <br><small class="text-muted">STT</small>
                                    <br><span class="badge badge-success">Whisper</span>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-brain fa-2x text-primary mb-1"></i>
                                    <br><small class="text-muted">LLM</small>
                                    <br><span class="badge badge-success">Ollama</span>
                                </div>
                                <div class="col-4">
                                    <i class="fas fa-volume-up fa-2x text-success mb-1"></i>
                                    <br><small class="text-muted">TTS</small>
                                    <br><span class="badge badge-info">Google</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Call Statistics + Active Channels -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Call Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-3">
                                    <h3 class="text-teal"><?= $stats['total_calls'] ?? 0 ?></h3>
                                    <small class="text-muted">Total</small>
                                </div>
                                <div class="col-3">
                                    <h3 class="text-success"><?= $stats['completed'] ?? 0 ?></h3>
                                    <small class="text-muted">Completed</small>
                                </div>
                                <div class="col-3">
                                    <h3 class="text-info"><?= $stats['answered'] ?? 0 ?></h3>
                                    <small class="text-muted">Answered</small>
                                </div>
                                <div class="col-3">
                                    <?php
                                    $total = $stats['total_calls'] ?? 0;
                                    $answered = $stats['answered'] ?? 0;
                                    $rate = $total > 0 ? round(($answered / $total) * 100) : 0;
                                    ?>
                                    <h3 class="text-primary"><?= $rate ?>%</h3>
                                    <small class="text-muted">Pickup Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-broadcast-tower"></i> Active Channels</h3>
                            <button class="btn btn-sm btn-outline-info float-right" onclick="refreshChannels()" aria-label="Refresh"><i class="fas fa-sync"></i></button>
                        </div>
                        <div class="card-body p-0">
                            <?php if (empty($channels)): ?>
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-phone-slash fa-2x mb-2"></i>
                                <p>No active calls</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead><tr><th>Channel</th><th>State</th><th>Caller</th><th>Action</th></tr></thead>
                                    <tbody>
                                    <?php foreach ($channels as $ch): ?>
                                    <tr>
                                        <td><code><?= $ch['Channel'] ?? '' ?></code></td>
                                        <td><span class="badge badge-info"><?= $ch['ChannelState'] ?? '' ?></span></td>
                                        <td><?= $ch['CallerIDNum'] ?? '' ?></td>
                                        <td>
                                            <button class="btn btn-xs btn-danger" onclick="hangupCall('<?= $ch['Channel'] ?? '' ?>')">
                                                <i class="fas fa-phone-slash"></i>
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
                </div>
            </div>

            <!-- Recent Calls + AI Agents -->
            <div class="row mb-4">
                <div class="col-md-8">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history"></i> Recent Calls</h3>
                            <a href="<?= BASE_URL ?>/admin/sim-calling" class="btn btn-sm btn-outline-teal float-right">Full Dashboard</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover text-nowrap mb-0">
                                    <thead><tr><th>Time</th><th>Phone</th><th>Customer</th><th>Status</th><th>Duration</th></tr></thead>
                                    <tbody>
                                    <?php if (empty($recent_calls)): ?>
                                    <tr><td colspan="5" class="text-center text-muted py-3">No calls yet</td></tr>
                                    <?php else: ?>
                                    <?php foreach (array_slice($recent_calls, 0, 10) as $call): ?>
                                    <tr>
                                        <td><?= date('d M, H:i', strtotime($call['created_at'] ?? 'now')) ?></td>
                                        <td><i class="fas fa-phone text-muted"></i> <?= $call['customer_phone'] ?? $call['phone'] ?? '' ?></td>
                                        <td><?= $call['customer_name'] ?? $call['lead_name'] ?? 'Unknown' ?></td>
                                        <td>
                                            <?php
                                            $colors = ['initiated'=>'info','ringing'=>'warning','answered'=>'success','completed'=>'success','no-answer'=>'danger','busy'=>'warning','failed'=>'danger'];
                                            $color = $colors[$call['status']] ?? 'secondary';
                                            ?>
                                            <span class="badge badge-<?= $color ?>"><?= ucfirst($call['status'] ?? 'unknown') ?></span>
                                        </td>
                                        <td><?= isset($call['duration']) ? $call['duration'].'s' : '-' ?></td>
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
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-robot"></i> AI Agents</h3>
                        </div>
                        <div class="card-body p-0">
                            <ul class="list-group list-group-flush">
                                <?php if (empty($ai_agents)): ?>
                                <li class="list-group-item text-center text-muted py-3">No agents configured</li>
                                <?php else: ?>
                                <?php foreach ($ai_agents as $agent): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= $agent['agent_name'] ?></strong>
                                        <br><small class="text-muted"><?= $agent['current_calls'] ?? 0 ?>/<?= $agent['max_concurrent_calls'] ?? 3 ?> concurrent</small>
                                    </div>
                                    <span class="badge badge-<?= ($agent['status'] ?? '') === 'active' ? 'success' : 'secondary' ?>">
                                        <?= ucfirst($agent['status'] ?? 'unknown') ?>
                                    </span>
                                </li>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="row">
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>/admin/sim-calling" class="btn btn-outline-teal btn-block mb-3">
                        <i class="fas fa-phone-volume"></i> SIM Calling Dashboard
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>/admin/voice-users/dashboard" class="btn btn-outline-primary btn-block mb-3">
                        <i class="fas fa-headset"></i> Voice Agent Dashboard
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>/admin/voice-users/schedule" class="btn btn-outline-success btn-block mb-3">
                        <i class="fas fa-calendar"></i> Call Schedule
                    </a>
                </div>
                <div class="col-md-3">
                    <a href="<?= BASE_URL ?>/admin/voice-users/scripts" class="btn btn-outline-info btn-block mb-3">
                        <i class="fas fa-scroll"></i> Call Scripts
                    </a>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.text-teal { color: #0d9488 !important; }
.btn-teal { background-color: #0d9488; color: #fff; border-color: #0d9488; }
.btn-teal:hover { background-color: #0f766e; color: #fff; }
</style>

<script>
async function quickCall() {
    const phone = document.getElementById('quickCallPhone').value.trim();
    if (!phone) { showToast('Enter phone number', 'warning'); return; }

    const btn = document.getElementById('quickCallBtn');
    const statusDiv = document.getElementById('quickCallStatus');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calling...';
    statusDiv.style.display = 'block';
    statusDiv.innerHTML = '<div class="alert alert-info mb-0">Initiating call...</div>';

    try {
        const res = await fetch('<?= BASE_URL ?>/admin/sim-calling/api/make-call', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                phone: phone,
                agent_script: document.getElementById('quickCallScript').value,
                agent_id: document.getElementById('quickCallAgent').value
            })
        });
        const data = await res.json();
        if (data.success) {
            statusDiv.innerHTML = '<div class="alert alert-success mb-0"><i class="fas fa-check"></i> ' + data.message + '</div>';
        } else {
            statusDiv.innerHTML = '<div class="alert alert-danger mb-0"><i class="fas fa-exclamation-triangle"></i> ' + (data.message || data.error || 'Failed') + '</div>';
        }
    } catch (e) {
        statusDiv.innerHTML = '<div class="alert alert-danger mb-0">Connection error. Is Asterisk running?</div>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-phone-alt"></i> Call';
    }
}

async function hangupCall(channel) {
    apsConfirm('Hangup this call?').then(function(ok) {
        if (!ok) return;
    try {
        await fetch('<?= BASE_URL ?>/admin/sim-calling/api/hangup', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ channel })
        });
        location.reload();
    });
    } catch (e) { showToast('Hangup failed', 'danger'); }
}

function refreshChannels() { location.reload(); }
</script>
