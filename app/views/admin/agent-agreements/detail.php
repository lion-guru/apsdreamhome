<?php
$agreement = $agreement ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/apsdreamhome';

$statusBadgeClass = [
    'draft' => 'aag-badge-draft',
    'pending' => 'aag-badge-pending',
    'signed' => 'aag-badge-signed',
    'expired' => 'aag-badge-expired',
    'cancelled' => 'aag-badge-cancelled',
];
?>
<style>
.aag-card { background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; margin-bottom: 16px; }
.aag-card h5 { color: #f8fafc; margin-bottom: 16px; font-size: 15px; }
.aag-badge { padding: 4px 14px; border-radius: 10px; font-size: 12px; font-weight: 600; display: inline-block; }
.aag-badge-draft { background: #64748b20; color: #94a3b8; border: 1px solid #64748b40; }
.aag-badge-pending { background: #f59e0b20; color: #f59e0b; border: 1px solid #f59e0b40; }
.aag-badge-signed { background: #10b98120; color: #10b981; border: 1px solid #10b98140; }
.aag-badge-expired { background: #ef444420; color: #ef4444; border: 1px solid #ef444440; }
.aag-badge-cancelled { background: #ef444420; color: #ef4444; border: 1px solid #ef444440; }
.aag-content { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 24px; color: #f8fafc; line-height: 1.7; }
.aag-content h2 { color: #f8fafc; border-bottom: 1px solid #334155; padding-bottom: 8px; }
.aag-content h3 { color: #e2e8f0; margin-top: 20px; }
.aag-content ol, .aag-content ul { padding-left: 20px; }
.aag-content li { margin-bottom: 6px; }
.aag-info { display: flex; gap: 20px; flex-wrap: wrap; }
.aag-info-item { background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 14px 18px; flex: 1; min-width: 180px; }
.aag-info-item .label { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
.aag-info-item .value { font-size: 14px; color: #f8fafc; font-weight: 600; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="style-76816"><i class="fas fa-file-signature me-2"></i><?= htmlspecialchars($agreement['title'] ?? 'Agreement') ?></h4>
            <span class="aag-badge <?= $statusBadgeClass[$agreement['status']] ?? '' ?>" class="style-91038">
                <?= ucfirst($agreement['status']) ?>
            </span>
        </div>
        <div>
            <a href="<?= $base ?>/admin/agent-agreements" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <?php if (!empty($_SESSION['flash_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" class="style-99395">
            <?= $_SESSION['flash_success'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" class="style-22908"></button>
        </div>
        <?php unset($_SESSION['flash_success']); ?>
    <?php endif; ?>

    <!-- Info Cards -->
    <div class="aag-info mb-4">
        <div class="aag-info-item">
            <div class="label">Agent</div>
            <div class="value"><?= htmlspecialchars($agreement['agent_name'] ?? 'N/A') ?></div>
            <div class="style-70095"><?= htmlspecialchars($agreement['agent_email'] ?? '') ?></div>
        </div>
        <div class="aag-info-item">
            <div class="label">Property</div>
            <div class="value"><?= htmlspecialchars($agreement['property_name'] ?? 'General') ?></div>
            <div class="style-70095"><?= htmlspecialchars($agreement['property_location'] ?? '') ?></div>
        </div>
        <div class="aag-info-item">
            <div class="label">Commission</div>
            <div class="value" class="style-54781"><?= (float)$agreement['commission_pct'] ?>%</div>
        </div>
        <div class="aag-info-item">
            <div class="label">Duration</div>
            <div class="value" class="style-24590">
                <?= $agreement['start_date'] ? date('d M Y', strtotime($agreement['start_date'])) : '—' ?>
                â†’
                <?= $agreement['end_date'] ? date('d M Y', strtotime($agreement['end_date'])) : '—' ?>
            </div>
        </div>
        <div class="aag-info-item">
            <div class="label">Created</div>
            <div class="value" class="style-24590"><?= date('d M Y, h:i A', strtotime($agreement['created_at'])) ?></div>
        </div>
    </div>

    <!-- Agreement Content -->
    <div class="aag-card">
        <h5><i class="fas fa-file-alt me-2" class="style-75937"></i>Agreement Content</h5>
        <div class="aag-content">
            <?= $agreement['content'] ?>
        </div>
    </div>

    <?php if (!empty($agreement['signed_at'])): ?>
    <div class="aag-card">
        <h5><i class="fas fa-check-circle me-2" class="style-54781"></i>Signature Details</h5>
        <div class="aag-info">
            <div class="aag-info-item">
                <div class="label">Signed At</div>
                <div class="value" class="style-24590"><?= date('d M Y, h:i A', strtotime($agreement['signed_at'])) ?></div>
            </div>
            <div class="aag-info-item">
                <div class="label">Signed By</div>
                <div class="value" class="style-24590"><?= htmlspecialchars($agreement['signed_by_name'] ?? 'N/A') ?></div>
            </div>
            <div class="aag-info-item">
                <div class="label">IP Address</div>
                <div class="value" class="style-24590"><?= htmlspecialchars($agreement['signed_ip'] ?? 'N/A') ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (!empty($agreement['notes'])): ?>
    <div class="aag-card">
        <h5><i class="fas fa-sticky-note me-2" class="style-62159"></i>Notes</h5>
        <p class="style-77298"><?= nl2br(htmlspecialchars($agreement['notes'] ?? '')) ?></p>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <?php if (in_array($agreement['status'], ['draft', 'pending'])): ?>
    <div class="aag-card">
        <h5><i class="fas fa-cogs me-2" class="style-22437"></i>Actions</h5>
        <div class="style-83366">
            <?php if ($agreement['status'] === 'draft'): ?>
                <form method="POST" action="<?= $base ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">/admin/agent-agreements/send/<?= (int)$agreement['id'] ?>">
    <?php echo CSRFProtection::csrfField(); ?>
                    <button type="submit" class="btn btn-warning btn-sm">
                        <i class="fas fa-paper-plane me-1"></i>Send for Signature
                    </button>
                </form>
            <?php endif; ?>
            <?php if ($agreement['status'] === 'pending'): ?>
                <form method="POST" action="<?= $base ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">/admin/agent-agreements/sign/<?= (int)$agreement['id'] ?>">
    <?php echo CSRFProtection::csrfField(); ?>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check me-1"></i>Sign Agreement
                    </button>
                </form>
            <?php endif; ?>
            <form method="POST" action="<?= $base ?>
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">/admin/agent-agreements/cancel/<?= (int)$agreement['id'] ?>">
    <?php echo CSRFProtection::csrfField(); ?>
                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to cancel this agreement?')">
                    <i class="fas fa-times me-1"></i>Cancel Agreement
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>
