<?php
/** Employee Self-Service Certifications */

$page_title = $page_title ?? 'My Certifications';
$certifications = $certifications ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
.cert-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
    margin-bottom: 1rem;
    background: #fafafa;
    transition: all 0.2s;
}
.cert-card:hover {
    border-color: #7c2d12;
    box-shadow: 0 4px 15px rgba(124,45,18,0.1);
}
.cert-card.active { border-left: 4px solid #22c55e; }
.cert-card.expired { border-left: 4px solid #ef4444; }
.cert-card.pending_verification { border-left: 4px solid #f59e0b; }
.cert-card.revoked { border-left: 4px solid #64748b; }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-certificate me-2 text-primary"></i>My Certifications</h4>
            <p class="text-muted mb-0 small">View and manage your professional certifications</p>
        </div>
    </div>

    <?php if (empty($certifications)): ?>
    <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
            <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
            <h5 class="text-muted">No Certifications Yet</h5>
            <p class="text-muted small mb-3">Complete training courses to earn certifications</p>
            <a href="<?= BASE_URL ?>/employee/self-service/training" class="btn btn-primary">
                <i class="fas fa-graduation-cap me-1"></i> Browse Courses
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="row">
        <?php foreach ($certifications as $cert): 
            $statusClass = $cert['status'];
            $daysLeft = $cert['expiry_date'] ? ceil((strtotime($cert['expiry_date']) - time()) / 86400) : null;
            $isExpiring = $daysLeft !== null && $daysLeft <= 60 && $daysLeft > 0;
            $isExpired = $daysLeft !== null && $daysLeft < 0;
        ?>
        <div class="col-md-6 col-lg-4 mb-3">
            <div class="cert-card <?= $statusClass ?>">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-circle bg-primary text-white" style="width: 48px; height: 48px; line-height: 48px; font-size: 1.25rem;">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-semibold"><?= htmlspecialchars($cert['certification_name']) ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($cert['course_title'] ?? 'Standalone Certification') ?></small>
                        </div>
                    </div>
                    <span class="badge bg-<?= 
                        $cert['status'] === 'active' ? 'success' : 
                        ($cert['status'] === 'expired' ? 'danger' : 
                        ($cert['status'] === 'pending_verification' ? 'warning' : 'secondary')) 
                    ?>">
                        <?= ucfirst(str_replace('_', ' ', $cert['status'])) ?>
                    </span>
                </div>
                
                <div class="mb-3">
                    <div class="small text-muted">Issuing Body</div>
                    <div class="fw-semibold"><?= htmlspecialchars($cert['issuing_body'] ?? '—') ?></div>
                </div>
                
                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="small text-muted">Issue Date</div>
                        <div class="fw-semibold"><?= $cert['issue_date'] ? date('d M Y', strtotime($cert['issue_date'])) : '—' ?></div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted">Expiry Date</div>
                        <div class="fw-semibold <?= $isExpired ? 'text-danger' : ($isExpiring ? 'text-warning' : '') ?>">
                            <?= $cert['expiry_date'] ? date('d M Y', strtotime($cert['expiry_date'])) : 'No Expiry' ?>
                            <?php if ($daysLeft !== null): ?>
                                <div class="small <?= $isExpired ? 'text-danger' : ($isExpiring ? 'text-warning' : 'text-muted') ?>">
                                    <?= $daysLeft > 0 ? $daysLeft . ' days left' : abs($daysLeft) . ' days ago' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($cert['credential_id'])): ?>
                    <div class="mb-2">
                        <div class="small text-muted">Credential ID</div>
                        <div class="fw-semibold font-monospace small"><?= htmlspecialchars($cert['credential_id']) ?></div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="d-flex gap-2 mt-3">
                        <?php if (!empty($cert['credential_url'])): ?>
                            <a href="<?= htmlspecialchars($cert['credential_url']) ?>" target="_blank" class="btn btn-sm btn-outline-primary flex-grow-1">
                                <i class="fas fa-external-link-alt me-1"></i> Verify Online
                            </a>
                        <?php endif; ?>
                        <?php if (!empty($cert['attachment_path'])): ?>
                            <a href="<?= BASE_URL . '/' . htmlspecialchars($cert['attachment_path']) ?>" target="_blank" class="btn btn-sm btn-outline-secondary flex-grow-1">
                                <i class="fas fa-file me-1"></i> View Document
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>