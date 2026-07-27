<?php
$lead = $lead ?? null;
$activities = $activities ?? [];
$notes = $notes ?? [];
$statuses = $statuses ?? [];
$base = BASE_URL ?? '';

$statusColors = [
    'new' => 'bg-primary',
    'contacted' => 'bg-info',
    'qualified' => 'bg-warning text-dark',
    'site_visit' => 'bg-purple text-white',
    'proposal' => 'bg-info text-white',
    'negotiation' => 'bg-warning text-dark',
    'booking' => 'bg-success',
    'won' => 'bg-success',
    'lost' => 'bg-danger',
    'nurture' => 'bg-orange text-white',
];

$activityIcons = [
    'status_change' => 'fas fa-exchange-alt text-primary',
    'note_added' => 'fas fa-sticky-note text-warning',
    'call' => 'fas fa-phone text-success',
    'email' => 'fas fa-envelope text-info',
    'meeting' => 'fas fa-calendar text-purple',
    'whatsapp' => 'fas fa-comment text-success',
    'sms' => 'fas fa-sms text-secondary',
    'system' => 'fas fa-cog text-muted',
];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.lead-info-card { border: none; border-radius: 12px; }
.lead-detail-header { background: linear-gradient(135deg, #1e40af, #3b82f6); color: #fff; border-radius: 12px; padding: 24px; margin-bottom: 20px; }
.lead-detail-header h4 { margin: 0; font-weight: 700; }
.lead-detail-header .lead-meta { opacity: 0.9; margin-top: 8px; font-size: 0.9rem; }
.timeline-item { position: relative; padding-left: 30px; padding-bottom: 20px; }
.timeline-item::before { content: ''; position: absolute; left: 10px; top: 25px; bottom: 0; width: 2px; background: #e2e8f0; }
.timeline-item:last-child::before { display: none; }
.timeline-dot { position: absolute; left: 3px; top: 5px; width: 16px; height: 16px; border-radius: 50%; background: #3b82f6; border: 3px solid #fff; box-shadow: 0 0 0 2px #3b82f6; }
.timeline-dot.status { background: #10b981; box-shadow: 0 0 0 2px #10b981; }
.timeline-dot.note { background: #f59e0b; box-shadow: 0 0 0 2px #f59e0b; }
.timeline-dot.call { background: #22c55e; box-shadow: 0 0 0 2px #22c55e; }
.timeline-dot.email { background: #3b82f6; box-shadow: 0 0 0 2px #3b82f6; }
.note-card { border-left: 4px solid #f59e0b; background: #fffbeb; }
.score-badge { font-size: 1.1rem; font-weight: 700; padding: 8px 16px; border-radius: 20px; }
</style>

<?php if (!$lead): ?>
<div class="text-center py-5">
    <i class="fas fa-exclamation-triangle fa-3x text-muted mb-3"></i>
    <h5 class="text-muted">Lead not found</h5>
    <a href="<?= $base ?>/employee/leads" class="btn btn-primary">Back to Leads</a>
</div>
<?php return; endif; ?>

<!-- Header -->
<div class="lead-detail-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <a href="<?= $base ?>/employee/leads" class="text-white text-decoration-none" style="opacity:0.8;font-size:0.85rem;">
                <i class="fas fa-arrow-left me-1"></i>Back to Leads
            </a>
            <h4 class="mt-2">
                <i class="fas fa-user-tie me-2"></i><?= htmlspecialchars($lead['name'] ?? 'Unknown') ?>
                <?php
                $leadStatus = $lead['status'] ?? 'new';
                $statusCls = $statusColors[$leadStatus] ?? 'bg-secondary';
                ?>
                <span class="badge <?= $statusCls ?> ms-2" style="font-size:0.85rem;"><?= ucfirst(str_replace('_', ' ', $leadStatus)) ?></span>
            </h4>
            <div class="lead-meta">
                <?php if (!empty($lead['phone'])): ?>
                    <span class="me-3"><i class="fas fa-phone me-1"></i><?= htmlspecialchars($lead['phone']) ?></span>
                <?php endif; ?>
                <?php if (!empty($lead['email'])): ?>
                    <span class="me-3"><i class="fas fa-envelope me-1"></i><?= htmlspecialchars($lead['email']) ?></span>
                <?php endif; ?>
                <?php if (!empty($lead['city'])): ?>
                    <span class="me-3"><i class="fas fa-map-marker-alt me-1"></i><?= htmlspecialchars($lead['city']) ?></span>
                <?php endif; ?>
                <span><i class="fas fa-clock me-1"></i>Created <?= date('d M Y', strtotime($lead['created_at'] ?? 'now')) ?></span>
            </div>
        </div>
        <div class="text-end">
            <?php $scoreVal = (int)($lead['lead_score'] ?? 0); ?>
            <div class="score-badge <?= $scoreVal >= 70 ? 'bg-danger text-white' : ($scoreVal >= 40 ? 'bg-warning text-dark' : ($scoreVal >= 20 ? 'bg-info text-white' : 'bg-secondary text-white')) ?>">
                Score: <?= $scoreVal ?>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Left: Lead Info + Status Update + Notes -->
    <div class="col-lg-7">
        <!-- Lead Info Card -->
        <div class="card lead-info-card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0" style="color:#1e40af;"><i class="fas fa-info-circle me-2"></i>Lead Information</h6>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="text-muted small">Full Name</label>
                        <div class="fw-semibold"><?= htmlspecialchars($lead['name'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Phone</label>
                        <div class="fw-semibold"><?= htmlspecialchars($lead['phone'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Email</label>
                        <div class="fw-semibold"><?= htmlspecialchars($lead['email'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Source</label>
                        <div class="fw-semibold"><?= ucfirst(htmlspecialchars($lead['source'] ?? '-')) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Property Interest</label>
                        <div class="fw-semibold"><?= htmlspecialchars($lead['property_interest'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Budget</label>
                        <div class="fw-semibold"><?= !empty($lead['budget']) ? '₹' . number_format((float)$lead['budget']) : '-' ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Location Preference</label>
                        <div class="fw-semibold"><?= htmlspecialchars($lead['location_preference'] ?? '-') ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="text-muted small">Priority</label>
                        <div>
                            <?php
                            $priority = $lead['priority'] ?? 'medium';
                            $pCls = ['low' => 'bg-secondary', 'medium' => 'bg-info', 'high' => 'bg-warning text-dark', 'urgent' => 'bg-danger'];
                            ?>
                            <span class="badge <?= $pCls[$priority] ?? 'bg-secondary' ?>"><?= ucfirst($priority) ?></span>
                        </div>
                    </div>
                    <?php if (!empty($lead['notes'])): ?>
                    <div class="col-12">
                        <label class="text-muted small">Notes</label>
                        <div class="fw-semibold"><?= nl2br(htmlspecialchars($lead['notes'])) ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Status Update -->
        <div class="card lead-info-card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0" style="color:#1e40af;"><i class="fas fa-sync-alt me-2"></i>Update Status</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $base ?>/employee/leads/<?= (int)$lead['id'] ?>/status" class="d-flex gap-2 align-items-end">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="flex-grow-1">
                        <select name="status" class="form-select">
                            <?php foreach ($statuses as $s): ?>
                                <option value="<?= $s ?>" <?= ($lead['status'] ?? 'new') === $s ? 'selected' : '' ?>>
                                    <?= ucfirst(str_replace('_', ' ', $s)) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-check me-1"></i>Update
                    </button>
                </form>
            </div>
        </div>

        <!-- Add Note -->
        <div class="card lead-info-card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0" style="color:#1e40af;"><i class="fas fa-sticky-note me-2"></i>Add Note</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= $base ?>/employee/leads/<?= (int)$lead['id'] ?>/note">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <textarea name="note" class="form-control mb-2" rows="3" placeholder="Type your note here..." required></textarea>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-plus me-1"></i>Add Note
                    </button>
                </form>
            </div>
        </div>

        <!-- Notes List -->
        <?php if (!empty($notes)): ?>
        <div class="card lead-info-card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0" style="color:#1e40af;"><i class="fas fa-sticky-note me-2"></i>Notes (<?= count($notes) ?>)</h6>
            </div>
            <div class="card-body">
                <?php foreach ($notes as $n): ?>
                <div class="note-card p-3 mb-2 rounded">
                    <div class="d-flex justify-content-between">
                        <div><?= nl2br(htmlspecialchars($n['note'] ?? $n['content'] ?? '')) ?></div>
                        <small class="text-muted text-nowrap ms-2"><?= date('d M Y H:i', strtotime($n['created_at'] ?? 'now')) ?></small>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Right: Activity Timeline + Quick Actions -->
    <div class="col-lg-5">
        <!-- Quick Actions -->
        <div class="card lead-info-card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0" style="color:#1e40af;"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <?php if (!empty($lead['phone'])): ?>
                    <a href="tel:<?= htmlspecialchars($lead['phone']) ?>" class="btn btn-success d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-phone"></i>Call <?= htmlspecialchars($lead['phone']) ?>
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($lead['phone'])): ?>
                    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $lead['phone']) ?>" target="_blank" class="btn btn-outline-success d-flex align-items-center justify-content-center gap-2">
                        <i class="fab fa-whatsapp"></i>WhatsApp
                    </a>
                    <?php endif; ?>
                    <?php if (!empty($lead['email'])): ?>
                    <a href="mailto:<?= htmlspecialchars($lead['email']) ?>" class="btn btn-outline-info d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-envelope"></i>Email
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Activity Timeline -->
        <div class="card lead-info-card shadow-sm">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0" style="color:#1e40af;"><i class="fas fa-stream me-2"></i>Activity Timeline</h6>
            </div>
            <div class="card-body">
                <?php if (empty($activities)): ?>
                    <p class="text-muted text-center mb-0">No activities recorded yet</p>
                <?php else: ?>
                    <?php foreach ($activities as $act): ?>
                    <?php
                        $actType = $act['activity_type'] ?? 'system';
                        $iconClass = $activityIcons[$actType] ?? 'fas fa-circle text-muted';
                        $dotClass = in_array($actType, ['status_change']) ? 'status' : (in_array($actType, ['note_added']) ? 'note' : (in_array($actType, ['call']) ? 'call' : (in_array($actType, ['email']) ? 'email' : '')));
                    ?>
                    <div class="timeline-item">
                        <div class="timeline-dot <?= $dotClass ?>"></div>
                        <div class="d-flex justify-content-between">
                            <div>
                                <i class="<?= $iconClass ?> me-1"></i>
                                <span class="fw-semibold" style="font-size:0.85rem;"><?= ucfirst(str_replace('_', ' ', $actType)) ?></span>
                                <div class="text-muted mt-1" style="font-size:0.85rem;"><?= htmlspecialchars($act['description'] ?? '') ?></div>
                            </div>
                            <small class="text-muted text-nowrap ms-2" style="font-size:0.75rem;">
                                <?= date('d M', strtotime($act['created_at'] ?? 'now')) ?>
                            </small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
