<?php
$page_title = 'Follow-ups - APS Dream Home';
$extraHead = '<style>
    .followup-card { border: none; border-radius: 12px; box-shadow: 0 3px 15px rgba(0,0,0,0.05); margin-bottom: 1rem; }
    .followup-card.overdue { border-left: 4px solid #dc3545; }
    .followup-card.today { border-left: 4px solid #0d6efd; }
    .priority-high { color: #dc3545; }
    .priority-medium { color: #fd7e14; }
    .priority-low { color: #6c757d; }
</style>';
?>

<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-phone-alt me-2 text-primary"></i>Follow-up Schedule</h3>
        <a href="<?= BASE_URL ?>" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Back</a>
    </div>

    <?php if (!empty($overdue_followups)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong><?= count($overdue_followups) ?> overdue follow-ups</strong> need your attention!
        </div>
    <?php endif; ?>

    <ul class="nav nav-tabs mb-3" id="followupTabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#today">
                <i class="fas fa-calendar-day me-1"></i>Today (<?= count($today_followups) ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#overdue">
                <i class="fas fa-exclamation-circle me-1"></i>Overdue (<?= count($overdue_followups) ?>)
            </a>
        </li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="today">
            <?php if (empty($today_followups)): ?>
                <div class="card"><div class="card-body text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>All caught up!</h5>
                    <p class="text-muted">No follow-ups scheduled for today.</p>
                </div></div>
            <?php else: ?>
                <?php foreach ($today_followups as $fu): ?>
                    <div class="card followup-card today">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($fu['lead_name'] ?? 'Lead') ?>
                                        <span class="badge bg-<?= $fu['priority'] === 'high' ? 'danger' : ($fu['priority'] === 'medium' ? 'warning' : 'secondary') ?> ms-2">
                                            <?= ucfirst($fu['priority']) ?>
                                        </span>
                                    </h6>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-phone me-1"></i><?= htmlspecialchars($fu['lead_phone'] ?? '') ?>
                                        <?php if ($fu['lead_email']): ?>
                                            | <i class="fas fa-envelope me-1"></i><?= htmlspecialchars($fu['lead_email']) ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="mb-1"><strong>Property:</strong> <?= htmlspecialchars($fu['property_interest'] ?? 'N/A') ?></p>
                                    <?php if ($fu['budget_range']): ?>
                                        <p class="mb-1"><strong>Budget:</strong> <?= htmlspecialchars($fu['budget_range']) ?></p>
                                    <?php endif; ?>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-clock me-1"></i>Scheduled: <?= date('h:i A', strtotime($fu['scheduled_for'])) ?>
                                        | <i class="fas fa-<?= $fu['followup_type'] === 'call' ? 'phone' : ($fu['followup_type'] === 'whatsapp' ? 'whatsapp' : 'envelope') ?> me-1"></i><?= ucfirst($fu['followup_type']) ?>
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="tel:<?= htmlspecialchars($fu['lead_phone'] ?? '') ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-phone"></i> Call
                                    </a>
                                    <button class="btn btn-sm btn-primary" onclick="completeFollowup(<?= (int)$fu['id'] ?>)">
                                        <i class="fas fa-check"></i> Complete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="tab-pane fade" id="overdue">
            <?php if (empty($overdue_followups)): ?>
                <div class="card"><div class="card-body text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>No overdue follow-ups!</h5>
                </div></div>
            <?php else: ?>
                <?php foreach ($overdue_followups as $fu): ?>
                    <div class="card followup-card overdue">
                        <div class="card-body aps-cp-card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        <i class="fas fa-user me-1"></i><?= htmlspecialchars($fu['lead_name'] ?? 'Lead') ?>
                                        <span class="badge bg-danger ms-2">OVERDUE</span>
                                    </h6>
                                    <p class="text-muted small mb-1">
                                        <i class="fas fa-phone me-1"></i><?= htmlspecialchars($fu['lead_phone'] ?? '') ?>
                                    </p>
                                    <p class="mb-1"><strong>Property:</strong> <?= htmlspecialchars($fu['property_interest'] ?? 'N/A') ?></p>
                                    <p class="text-muted small mb-0">
                                        <i class="fas fa-clock me-1"></i>Was due: <?= date('d M Y h:i A', strtotime($fu['scheduled_for'])) ?>
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="tel:<?= htmlspecialchars($fu['lead_phone'] ?? '') ?>" class="btn btn-sm btn-success">
                                        <i class="fas fa-phone"></i> Call
                                    </a>
                                    <button class="btn btn-sm btn-primary" onclick="completeFollowup(<?= (int)$fu['id'] ?>)">
                                        <i class="fas fa-check"></i> Complete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function completeFollowup(followupId) {
    const outcome = prompt('Outcome (interested, not_interested, callback_later, site_visit_booked, deal_closed, wrong_number, no_response):', 'interested');
    if (!outcome) return;
    
    const notes = prompt('Notes:', '') || '';
    
    fetch('<?= BASE_URL ?>/marketplace/complete-followup', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'followup_id=' + followupId + '&outcome=' + encodeURIComponent(outcome) + '&notes=' + encodeURIComponent(notes)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert('Failed: ' + (data.message || 'Unknown error'));
    })
    .catch(() => alert('Network error'));
}
</script>