<?php
// Smart Registration Session Detail
$s = $session ?? [];
$behavior = $behavior ?? [];
?>
<style>
    .sd-info-label { font-size: 0.8rem; color: #6c757d; text-transform: uppercase; letter-spacing: 0.5px; }
    .sd-info-value { font-size: 1rem; font-weight: 600; }
    .sd-timeline-item { border-left: 2px solid #dee2e6; padding-left: 1rem; padding-bottom: 1rem; position: relative; }
    .sd-timeline-item::before { content: ''; width: 10px; height: 10px; border-radius: 50%; background: #007bff; position: absolute; left: -6px; top: 4px; }
    .sd-timeline-item.active::before { background: #28a745; box-shadow: 0 0 0 3px rgba(40,167,69,0.2); }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?= BASE_URL ?>/admin/smart-registration" class="text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
            <h4 class="mt-2 mb-0">Session #<?= $s['id'] ?? '' ?> — <?= htmlspecialchars($s['phone'] ?? '') ?></h4>
        </div>
        <div>
            <span class="sr-status-badge sr-status-<?= $s['registration_status'] ?? '' ?> fs-6">
                <?= str_replace('_', ' ', $s['registration_status'] ?? '') ?>
            </span>
        </div>
    </div>

    <div class="row g-4">
        <!-- Session Info -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-info-circle me-2"></i>Session Details</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="sd-info-label">Phone</div>
                            <div class="sd-info-value"><?= htmlspecialchars($s['phone'] ?? '—') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="sd-info-label">Email</div>
                            <div class="sd-info-value"><?= htmlspecialchars($s['email'] ?? '—') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="sd-info-label">OTP Channel</div>
                            <div class="sd-info-value">
                                <span class="sr-channel-badge sr-channel-<?= $s['otp_channel'] ?? 'email' ?>">
                                    <?= ucfirst($s['otp_channel'] ?? '—') ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="sd-info-label">OTP Verified</div>
                            <div class="sd-info-value">
                                <?php if ($s['otp_verified']): ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Yes</span>
                                <?php else: ?>
                                    <span class="text-danger"><i class="fas fa-times-circle"></i> No</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="sd-info-label">User Created</div>
                            <div class="sd-info-value">
                                <?php if ($s['user_created']): ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Yes (ID: <?= $s['user_id'] ?>)</span>
                                <?php else: ?>
                                    <span class="text-muted">No</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="sd-info-label">Profile Completion</div>
                            <div class="sd-info-value">
                                <div class="progress style-31164">
                                    <div class="progress-bar bg-<?= ($s['profile_completion_pct'] ?? 0) >= 80 ? 'success' : 'warning' ?> style-79680"></div>
                                </div>
                                <small><?= $s['profile_completion_pct'] ?? 0 ?>%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detection & Behavior -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-robot me-2"></i>Role Detection</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="sd-info-label">Detected Role</div>
                            <div class="sd-info-value">
                                <?php if ($s['detected_role']): ?>
                                    <span class="badge bg-<?= $s['detected_role'] === 'associate' ? 'warning' : ($s['detected_role'] === 'agent' ? 'success' : 'primary') ?> fs-6">
                                        <?= ucfirst($s['detected_role']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">Not detected</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="sd-info-label">Confidence</div>
                            <div class="sd-info-value">
                                <?php if ($s['role_confidence']): ?>
                                    <?= round($s['role_confidence'] * 100) ?>%
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="sd-info-label">IP Address</div>
                            <div class="sd-info-value"><code><?= htmlspecialchars($s['ip_address'] ?? '—') ?></code></div>
                        </div>
                        <div class="col-12">
                            <div class="sd-info-label">User Agent</div>
                            <div class="sd-info-value"><small class="text-muted"><?= htmlspecialchars(substr($s['user_agent'] ?? '', 0, 100)) ?></small></div>
                        </div>
                        <div class="col-12">
                            <div class="sd-info-label">Landing Page</div>
                            <div class="sd-info-value"><small><?= htmlspecialchars($s['landing_page'] ?? '—') ?></small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Follow-up Status -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-paper-plane me-2"></i>Follow-up Status</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-4 text-center">
                            <div class="sd-info-label">WhatsApp</div>
                            <?php if ($s['followup_whatsapp_sent']): ?>
                                <div class="text-success"><i class="fas fa-check-circle fa-lg"></i></div>
                            <?php else: ?>
                                <div class="text-muted"><i class="fas fa-circle fa-lg"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-4 text-center">
                            <div class="sd-info-label">Email</div>
                            <?php if ($s['followup_email_sent']): ?>
                                <div class="text-success"><i class="fas fa-check-circle fa-lg"></i></div>
                            <?php else: ?>
                                <div class="text-muted"><i class="fas fa-circle fa-lg"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-4 text-center">
                            <div class="sd-info-label">SMS</div>
                            <?php if ($s['followup_sms_sent']): ?>
                                <div class="text-success"><i class="fas fa-check-circle fa-lg"></i></div>
                            <?php else: ?>
                                <div class="text-muted"><i class="fas fa-circle fa-lg"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-6">
                            <div class="sd-info-label">Total Follow-ups</div>
                            <div class="sd-info-value"><?= $s['followup_count'] ?? 0 ?></div>
                        </div>
                        <div class="col-6">
                            <div class="sd-info-label">Last Follow-up</div>
                            <div class="sd-info-value"><small><?= $s['last_followup_at'] ? date('d M H:i', strtotime($s['last_followup_at'])) : '—' ?></small></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timestamps -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-clock me-2"></i>Timeline</div>
                <div class="card-body">
                    <div class="sd-timeline-item <?= $s['registration_status'] === 'pending_otp' ? 'active' : '' ?>">
                        <div class="d-flex justify-content-between">
                            <small class="fw-bold">Session Created</small>
                            <small class="text-muted"><?= $s['created_at'] ? date('d M Y H:i:s', strtotime($s['created_at'])) : '—' ?></small>
                        </div>
                    </div>
                    <div class="sd-timeline-item <?= $s['registration_status'] === 'otp_sent' ? 'active' : '' ?>">
                        <div class="d-flex justify-content-between">
                            <small class="fw-bold">OTP Sent</small>
                            <small class="text-muted"><?= $s['otp_sent_at'] ? date('d M Y H:i:s', strtotime($s['otp_sent_at'])) : '—' ?></small>
                        </div>
                    </div>
                    <div class="sd-timeline-item <?= $s['registration_status'] === 'otp_verified' ? 'active' : '' ?>">
                        <div class="d-flex justify-content-between">
                            <small class="fw-bold">OTP Verified</small>
                            <small class="text-muted"><?= $s['otp_verified_at'] ? date('d M Y H:i:s', strtotime($s['otp_verified_at'])) : '—' ?></small>
                        </div>
                    </div>
                    <div class="sd-timeline-item <?= $s['registration_status'] === 'profile_complete' ? 'active' : '' ?>">
                        <div class="d-flex justify-content-between">
                            <small class="fw-bold">Completed</small>
                            <small class="text-muted"><?= $s['completed_at'] ? date('d M Y H:i:s', strtotime($s['completed_at'])) : '—' ?></small>
                        </div>
                    </div>
                    <?php if ($s['abandoned_at']): ?>
                        <div class="sd-timeline-item active">
                            <div class="d-flex justify-content-between">
                                <small class="fw-bold text-danger">Abandoned</small>
                                <small class="text-muted"><?= date('d M Y H:i:s', strtotime($s['abandoned_at'])) ?></small>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Behavior Events -->
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold"><i class="fas fa-mouse-pointer me-2"></i>Behavior Events</div>
                <div class="card-body">
                    <?php if (empty($behavior)): ?>
                        <p class="text-muted text-center mb-0">No behavior events tracked</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>Time</th>
                                        <th>Event Type</th>
                                        <th>Page</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($behavior as $b): ?>
                                        <tr>
                                            <td><small><?= date('d M H:i:s', strtotime($b['created_at'])) ?></small></td>
                                            <td><span class="badge bg-secondary"><?= $b['event_type'] ?></span></td>
                                            <td><small><?= htmlspecialchars($b['page_url'] ?? '') ?></small></td>
                                            <td><small class="text-muted"><?= htmlspecialchars($b['event_data'] ?? '') ?></small></td>
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
</div>
