<?php $pd = $privacy_data ?? []; $gdpr = $pd['gdpr_compliance'] ?? []; $dp = $pd['data_protection'] ?? []; $pbd = $pd['privacy_by_design'] ?? []; $consent = $pd['user_consent_management'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-user-shield me-2"></i>Data Privacy & GDPR Compliance</h4>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>GDPR Compliance</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php $gitems = ['compliance_status' => 'Status', 'last_audit' => 'Last Audit', 'data_protection_officer' => 'DPO', 'privacy_impact_assessments' => 'PIAs', 'data_breach_notifications' => 'Breach Notifications', 'user_consent_rate' => 'Consent Rate']; ?>
                    <?php foreach ($gitems as $k => $l): ?>
                        <div class="mb-2 d-flex justify-content-between"><small class="text-muted"><?= htmlspecialchars($l, ENT_QUOTES, 'UTF-8') ?></small><strong><?= htmlspecialchars($gdpr[$k] ?? '-') ?></strong></div>
                        <?php if ($k === 'data_breach_notifications'): ?><hr><?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-shield me-2"></i>Data Protection Measures</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <h6 class="fw-bold text-secondary">Encryption Standards</h6>
                            <?php $es = $dp['encryption_standards'] ?? []; foreach ($es as $k => $v): ?><div class="mb-2"><span class="badge bg-info me-1"><?= ucwords(str_replace('_', ' ', $k)) ?></span><small class="d-block text-muted"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-bold text-secondary">Access Controls</h6>
                            <?php $ac = $dp['access_controls'] ?? []; foreach ($ac as $k => $v): ?><div class="mb-2"><span class="badge bg-primary me-1"><?= ucwords(str_replace('_', ' ', $k)) ?></span><small class="d-block text-muted"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                        </div>
                        <div class="col-md-4">
                            <h6 class="fw-bold text-secondary">Data Minimization</h6>
                            <?php $dm = $dp['data_minimization'] ?? []; foreach ($dm as $k => $v): ?><div class="mb-2"><span class="badge bg-success me-1"><?= ucwords(str_replace('_', ' ', $k)) ?></span><small class="d-block text-muted"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-lightbulb me-2"></i>Privacy by Design</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">Design Principles</h6>
                    <?php $dpr = $pbd['design_principles'] ?? []; foreach ($dpr as $k => $v): ?><div class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Implementation Framework</h6>
                    <?php $ifr = $pbd['implementation_framework'] ?? []; foreach ($ifr as $k => $v): ?><div class="mb-2"><i class="fas fa-tools text-warning me-2"></i><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-hand-peace me-2"></i>User Consent Management</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">Consent Collection</h6>
                    <?php $cc = $consent['consent_collection'] ?? []; foreach ($cc as $k => $v): ?><div class="mb-2"><i class="fas fa-file-signature text-primary me-2"></i><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Consent Analytics</h6>
                    <?php $ca = $consent['consent_analytics'] ?? []; foreach ($ca as $k => $v): ?><div class="mb-2"><i class="fas fa-chart-pie text-info me-2"></i><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
