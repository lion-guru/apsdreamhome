<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-phone-volume text-teal"></i> SIM Calling Dashboard</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                        <li class="breadcrumb-item active">SIM Calling</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Connection Status -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card card-outline <?= $connected ? 'card-success' : 'card-danger' ?>">
                        <div class="card-body text-center">
                            <i class="fas <?= $connected ? 'fa-check-circle text-success' : 'fa-times-circle text-danger' ?> fa-3x mb-2"></i>
                            <h4><?= $connected ? 'Connected' : 'Disconnected' ?></h4>
                            <p class="text-muted">Asterisk AMI — <?= $connected ? 'Online' : 'Offline' ?></p>
                            <a href="/admin/sim-calling/settings" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-outline card-info">
                        <div class="card-body text-center">
                            <i class="fas fa-phone fa-3x mb-2 text-info"></i>
                            <h4><?= (int)($stats['active_channels'] ?? 0) ?></h4>
                            <p class="text-muted">Active Channels</p>
                            <button onclick="refreshStatus()" class="btn btn-sm btn-outline-info">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card card-outline card-warning">
                        <div class="card-body text-center">
                            <i class="fas fa-calendar-day fa-3x mb-2 text-warning"></i>
                            <h4><?= (int)($stats['today_calls'] ?? 0) ?></h4>
                            <p class="text-muted">Calls Today</p>
                            <span class="badge badge-success"><?= (int)($stats['answered'] ?? 0) ?> answered</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Call -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card card-outline card-teal">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-phone-alt"></i> Quick Call</h3>
                        </div>
                        <div class="card-body">
                            <div class="input-group mb-3">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                </div>
                                <input type="tel" id="callPhone" class="form-control" placeholder="919277121112" maxlength="15">
                                <div class="input-group-append">
                                    <button class="btn btn-teal" onclick="makeCall()" id="callBtn">
                                        <i class="fas fa-phone-alt"></i> Call Now
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Agent Script</label>
                                    <select id="agentScript" class="form-control form-control-sm">
                                        <option value="default">Default IVR</option>
                                        <option value="property_inquiry">Property Inquiry</option>
                                        <option value="lead_followup">Lead Follow-up</option>
                                        <option value="site_visit">Site Visit Booking</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label>Caller ID (SIM)</label>
                                    <input type="text" id="callerId" class="form-control form-control-sm" placeholder="SIM number" value="<?= htmlspecialchars($config['caller_id'] ?? '') ?>">
                                </div>
                            </div>
                            <div id="callStatus" class="mt-3" style="display:none;"></div>
                        </div>
                    </div>
                </div>

                <!-- Stats -->
                <div class="col-md-6">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Call Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-4">
                                    <h3 class="text-teal"><?= (int)($stats['total_calls'] ?? 0) ?></h3>
                                    <small class="text-muted">Total Calls</small>
                                </div>
                                <div class="col-4">
                                    <h3 class="text-success"><?= (int)($stats['completed'] ?? 0) ?></h3>
                                    <small class="text-muted">Completed</small>
                                </div>
                                <div class="col-4">
                                    <h3 class="text-danger"><?= (int)($stats['no_answer'] ?? 0) ?></h3>
                                    <small class="text-muted">No Answer</small>
                                </div>
                            </div>
                            <hr>
                            <div class="row text-center">
                                <div class="col-4">
                                    <h4 class="text-info"><?= (int)($stats['answered'] ?? 0) ?></h4>
                                    <small>Answered</small>
                                </div>
                                <div class="col-4">
                                    <h4 class="text-warning"><?= (int)($stats['busy'] ?? 0) ?></h4>
                                    <small>Busy</small>
                                </div>
                                <div class="col-4">
                                    <?php
                                    $total = (int)($stats['total_calls'] ?? 0);
                                    $answered = (int)($stats['answered'] ?? 0);
                                    $rate = $total > 0 ? round(($answered / $total) * 100) : 0;
                                    ?>
                                    <h4 class="text-primary"><?= $rate ?>%</h4>
                                    <small>Answer Rate</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Calls -->
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history"></i> Recent Calls</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Phone</th>
                                <th>Customer</th>
                                <th>Status</th>
                                <th>Duration</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recentCalls)): ?>
                            <tr><td colspan="6" class="text-center text-muted py-4">No calls yet. Make your first call above!</td></tr>
                            <?php else: ?>
                            <?php foreach ($recentCalls as $call): ?>
                            <tr>
                                <td><?= date('d M, H:i', strtotime($call['created_at'])) ?></td>
                                <td><i class="fas fa-phone text-muted"></i> <?= htmlspecialchars($call['customer_phone'] ?? '') ?></td>
                                <td><?= htmlspecialchars($call['customer_name'] ?? 'Unknown') ?></td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'initiated' => 'info',
                                        'ringing' => 'warning',
                                        'answered' => 'success',
                                        'completed' => 'success',
                                        'no-answer' => 'danger',
                                        'busy' => 'warning',
                                        'failed' => 'danger',
                                    ];
                                    $color = $statusColors[$call['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge badge-<?= $color ?>"><?= ucfirst($call['status']) ?></span>
                                </td>
                                <td><?= isset($call['duration']) ? $call['duration'] . 's' : '-' ?></td>
                                <td>
                                    <button class="btn btn-xs btn-outline-danger" onclick="redial('<?= htmlspecialchars($call['customer_phone'] ?? '') ?>')" title="Redial">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Active Channels -->
            <?php if (!empty($channels)): ?>
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-broadcast-tower"></i> Active Channels</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-sm">
                        <thead><tr><th>Channel</th><th>State</th><th>Caller</th><th>Called</th><th></th></tr></thead>
                        <tbody>
                            <?php foreach ($channels as $ch): ?>
                            <tr>
                                <td><code><?= htmlspecialchars($ch['Channel'] ?? '') ?></code></td>
                                <td><span class="badge badge-info"><?= htmlspecialchars($ch['ChannelState'] ?? '') ?></span></td>
                                <td><?= htmlspecialchars($ch['CallerIDNum'] ?? '') ?></td>
                                <td><?= htmlspecialchars($ch['Exten'] ?? '') ?></td>
                                <td>
                                    <button class="btn btn-xs btn-danger" onclick="hangup('<?= htmlspecialchars($ch['Channel'] ?? '') ?>')">
                                        <i class="fas fa-phone-slash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

            <!-- Help Section -->
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-question-circle"></i> Setup Guide</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h5><i class="fas fa-server text-teal"></i> 1. Asterisk Server</h5>
                            <p class="small text-muted">Install Asterisk on Ubuntu/Debian server. Configure PJSIP with your GSM Gateway.</p>
                            <code class="small">sudo apt install asterisk</code>
                        </div>
                        <div class="col-md-4">
                            <h5><i class="fas fa-mobile-alt text-teal"></i> 2. GSM Gateway</h5>
                            <p class="small text-muted">Connect GoIP-1/4 or any SIP-GSM gateway. Insert SIM card. Register with Asterisk.</p>
                            <a href="/admin/sim-calling/generate-dialplan" class="btn btn-sm btn-outline-teal">
                                <i class="fas fa-download"></i> Download Dialplan
                            </a>
                        </div>
                        <div class="col-md-4">
                            <h5><i class="fas fa-cog text-teal"></i> 3. Configure</h5>
                            <p class="small text-muted">Enter AMI credentials in Settings. Test connection. Start calling!</p>
                            <a href="/admin/sim-calling/settings" class="btn btn-sm btn-outline-teal">
                                <i class="fas fa-cog"></i> Open Settings
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<style>
.text-teal { color: #0d9488 !important; }
.btn-teal { background-color: #0d9488; color: #fff; border-color: #0d9488; }
.btn-teal:hover { background-color: #0f766e; color: #fff; }
.border-teal { border-color: #0d9488 !important; }
.card-teal { border-top-color: #0d9488; }
</style>

<script>
async function makeCall() {
    const phone = document.getElementById('callPhone').value.trim();
    if (!phone) { alert('Enter phone number'); return; }

    const btn = document.getElementById('callBtn');
    const statusDiv = document.getElementById('callStatus');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Calling...';
    statusDiv.style.display = 'block';
    statusDiv.innerHTML = '<div class="alert alert-info">Initiating call...</div>';

    try {
        const res = await fetch('/admin/sim-calling/api/make-call', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                phone: phone,
                agent_script: document.getElementById('agentScript').value,
                caller_id: document.getElementById('callerId').value
            })
        });
        const data = await res.json();
        if (data.success) {
            statusDiv.innerHTML = '<div class="alert alert-success"><i class="fas fa-check"></i> ' + data.message + ' (ID: ' + data.call_id + ')</div>';
        } else {
            statusDiv.innerHTML = '<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' + data.message + '</div>';
        }
    } catch (e) {
        statusDiv.innerHTML = '<div class="alert alert-danger">Connection error. Is Asterisk running?</div>';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-phone-alt"></i> Call Now';
    }
}

function redial(phone) {
    document.getElementById('callPhone').value = phone;
    makeCall();
}

async function refreshStatus() {
    try {
        const res = await fetch('/admin/sim-calling/api/status');
        const data = await res.json();
        location.reload();
    } catch (e) {
        alert('Cannot reach Asterisk');
    }
}

async function hangup(channel) {
    if (!confirm('Hangup this call?')) return;
    try {
        await fetch('/admin/sim-calling/api/hangup', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ channel })
        });
        location.reload();
    } catch (e) {
        alert('Hangup failed');
    }
}

// Auto-refresh every 30 seconds
setInterval(refreshStatus, 30000);
</script>
