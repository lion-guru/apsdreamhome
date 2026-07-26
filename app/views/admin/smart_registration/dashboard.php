<?php
// Smart Registration Admin Dashboard
$total = $total ?? 0;
$pendingOtp = $pendingOtp ?? 0;
$otpSent = $otpSent ?? 0;
$abandoned = $abandoned ?? 0;
$completed = $completed ?? 0;
$accountCreated = $accountCreated ?? 0;
$conversionRate = $conversionRate ?? 0;
$sessions = $sessions ?? [];
$roles = $roles ?? [];
$channels = $channels ?? [];
?>
<style>
    .sr-stat-card {
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 4px solid;
        transition: transform 0.2s;
    }
    .sr-stat-card:hover { transform: translateY(-2px); }
    .sr-stat-card .stat-value { font-size: 1.8rem; font-weight: 700; }
    .sr-stat-card .stat-label { font-size: 0.85rem; color: #6c757d; }
    .sr-status-badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .sr-status-pending_otp { background: #fff3cd; color: #856404; }
    .sr-status-otp_sent { background: #cce5ff; color: #004085; }
    .sr-status-otp_verified { background: #d4edda; color: #155724; }
    .sr-status-account_created { background: #d1ecf1; color: #0c5460; }
    .sr-status-profile_incomplete { background: #ffeaa7; color: #856404; }
    .sr-status-profile_complete { background: #c3e6cb; color: #155724; }
    .sr-status-abandoned { background: #f5c6cb; color: #721c24; }
    .sr-session-row { cursor: pointer; transition: background 0.15s; }
    .sr-session-row:hover { background: #f8f9fa; }
    .sr-channel-badge { padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: 600; }
    .sr-channel-whatsapp { background: #25d366; color: white; }
    .sr-channel-sms { background: #007bff; color: white; }
    .sr-channel-email { background: #6f42c1; color: white; }
    .sr-progress-bar { height: 6px; border-radius: 3px; }
    .sr-chart-bar { height: 24px; border-radius: 4px; margin-bottom: 4px; display: flex; align-items: center; padding: 0 8px; color: white; font-size: 0.75rem; font-weight: 600; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-user-plus me-2 text-primary"></i>Smart Registration Analytics</h4>
            <small class="text-muted">Phone-first registration flow — abandoned recovery dashboard</small>
        </div>
        <div>
            <button class="btn btn-sm btn-outline-primary" onclick="location.reload()"><i class="fas fa-sync-alt me-1"></i>Refresh</button>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="sr-stat-card" style="border-color: #007bff;">
                <div class="stat-value text-primary"><?= number_format($total) ?></div>
                <div class="stat-label">Total Sessions</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="sr-stat-card" style="border-color: #ffc107;">
                <div class="stat-value text-warning"><?= number_format($pendingOtp) ?></div>
                <div class="stat-label">Pending OTP</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="sr-stat-card" style="border-color: #17a2b8;">
                <div class="stat-value text-info"><?= number_format($otpSent) ?></div>
                <div class="stat-label">OTP Sent</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="sr-stat-card" style="border-color: #dc3545;">
                <div class="stat-value text-danger"><?= number_format($abandoned) ?></div>
                <div class="stat-label">Abandoned</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="sr-stat-card" style="border-color: #28a745;">
                <div class="stat-value text-success"><?= number_format($completed) ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="sr-stat-card" style="border-color: #6f42c1;">
                <div class="stat-value text-purple" style="color:#6f42c1;"><?= $conversionRate ?>%</div>
                <div class="stat-label">Conversion Rate</div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Role Distribution -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-users me-2"></i>Detected Roles</div>
                <div class="card-body">
                    <?php if (empty($roles)): ?>
                        <p class="text-muted text-center mb-0">No data yet</p>
                    <?php else: ?>
                        <?php
                        $maxRole = max(array_column($roles, 'c'));
                        $roleColors = ['customer' => '#007bff', 'agent' => '#28a745', 'associate' => '#fd7e14', 'employee' => '#6f42c1'];
                        ?>
                        <?php foreach ($roles as $r): ?>
                            <div class="mb-2">
                                <div class="d-flex justify-content-between mb-1">
                                    <small class="fw-semibold"><?= ucfirst($r['detected_role'] ?? 'Unknown') ?></small>
                                    <small class="text-muted"><?= $r['c'] ?></small>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar" style="width: <?= $maxRole > 0 ? ($r['c'] / $maxRole * 100) : 0 ?>%; background: <?= $roleColors[$r['detected_role']] ?? '#6c757d' ?>;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Channel Distribution -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-paper-plane me-2"></i>OTP Channels</div>
                <div class="card-body">
                    <?php if (empty($channels)): ?>
                        <p class="text-muted text-center mb-0">No data yet</p>
                    <?php else: ?>
                        <?php
                        $maxCh = max(array_column($channels, 'c'));
                        foreach ($channels as $ch):
                        ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span><span class="sr-channel-badge sr-channel-<?= $ch['otp_channel'] ?>"><?= ucfirst($ch['otp_channel']) ?></span></span>
                                    <small class="text-muted"><?= $ch['c'] ?> sessions</small>
                                </div>
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-<?= $ch['otp_channel'] === 'whatsapp' ? 'success' : ($ch['otp_channel'] === 'sms' ? 'primary' : 'info') ?>"
                                         style="width: <?= $maxCh > 0 ? ($ch['c'] / $maxCh * 100) : 0 ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Conversion Funnel -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-filter me-2"></i>Conversion Funnel</div>
                <div class="card-body">
                    <?php
                    $funnel = [
                        ['Phone Entered', $total, '#007bff'],
                        ['OTP Sent', $otpSent, '#17a2b8'],
                        ['OTP Verified', $completed + $accountCreated - $completed, '#28a745'],
                        ['Account Created', $accountCreated, '#fd7e14'],
                        ['Profile Complete', $completed, '#28a745'],
                    ];
                    $maxFunnel = max($total, 1);
                    ?>
                    <?php foreach ($funnel as $i => $f): ?>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between mb-1">
                                <small><?= $f[0] ?></small>
                                <small class="text-muted"><?= number_format($f[1]) ?></small>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <div class="progress-bar" style="width: <?= round($f[1] / $maxFunnel * 100) ?>%; background: <?= $f[2] ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="card border-0 shadow-sm mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Recent Sessions (Last 50)</h6>
            <div class="d-flex gap-2">
                <select class="form-select form-select-sm" id="filterStatus" style="width:auto;" onchange="filterSessions()">
                    <option value="">All Status</option>
                    <option value="pending_otp">Pending OTP</option>
                    <option value="otp_sent">OTP Sent</option>
                    <option value="otp_verified">OTP Verified</option>
                    <option value="account_created">Account Created</option>
                    <option value="profile_incomplete">Profile Incomplete</option>
                    <option value="profile_complete">Profile Complete</option>
                    <option value="abandoned">Abandoned</option>
                </select>
                <select class="form-select form-select-sm" id="filterChannel" style="width:auto;" onchange="filterSessions()">
                    <option value="">All Channels</option>
                    <option value="whatsapp">WhatsApp</option>
                    <option value="sms">SMS</option>
                    <option value="email">Email</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Role Detected</th>
                            <th>Profile %</th>
                            <th>Follow-ups</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($sessions)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4">No sessions yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($sessions as $s): ?>
                                <tr class="sr-session-row" onclick="location.href='<?= BASE_URL ?>/admin/smart-registration/detail?id=<?= $s['id'] ?>'">
                                    <td>#<?= $s['id'] ?></td>
                                    <td><i class="fas fa-phone me-1 text-muted"></i><?= htmlspecialchars($s['phone'] ?? '—') ?></td>
                                    <td><small><?= htmlspecialchars($s['email'] ?? '—') ?></small></td>
                                    <td><span class="sr-channel-badge sr-channel-<?= $s['otp_channel'] ?? 'email' ?>"><?= ucfirst($s['otp_channel'] ?? '—') ?></span></td>
                                    <td><span class="sr-status-badge sr-status-<?= $s['registration_status'] ?>"><?= str_replace('_', ' ', $s['registration_status']) ?></span></td>
                                    <td>
                                        <?php if ($s['detected_role']): ?>
                                            <span class="badge bg-<?= $s['detected_role'] === 'associate' ? 'warning' : ($s['detected_role'] === 'agent' ? 'success' : 'primary') ?>">
                                                <?= ucfirst($s['detected_role']) ?>
                                                <?php if ($s['role_confidence']): ?>
                                                    <small>(<?= round($s['role_confidence'] * 100) ?>%)</small>
                                                <?php endif; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2" style="height: 4px; width: 60px;">
                                                <div class="progress-bar bg-<?= ($s['profile_completion_pct'] ?? 0) >= 80 ? 'success' : (($s['profile_completion_pct'] ?? 0) >= 40 ? 'warning' : 'danger') ?>"
                                                     style="width: <?= $s['profile_completion_pct'] ?? 0 ?>%;"></div>
                                            </div>
                                            <small><?= $s['profile_completion_pct'] ?? 0 ?>%</small>
                                        </div>
                                    </td>
                                    <td>
                                        <small>
                                            <?= ($s['followup_count'] ?? 0) > 0 ? $s['followup_count'] . ' sent' : '—' ?>
                                        </small>
                                    </td>
                                    <td><small class="text-muted"><?= date('d M H:i', strtotime($s['created_at'])) ?></small></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function filterSessions() {
    const status = document.getElementById('filterStatus').value.toLowerCase();
    const channel = document.getElementById('filterChannel').value.toLowerCase();
    document.querySelectorAll('.sr-session-row').forEach(row => {
        const text = row.textContent.toLowerCase();
        const matchStatus = !status || text.includes(status.replace(/_/g, ' '));
        const matchChannel = !channel || text.includes(channel);
        row.style.display = (matchStatus && matchChannel) ? '' : 'none';
    });
}
</script>
