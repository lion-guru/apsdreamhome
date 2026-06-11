<?php $mfa = $mfa_data ?? []; $methods = $mfa['current_mfa_methods'] ?? []; $advanced = $mfa['advanced_authentication'] ?? []; $biometric = $mfa['biometric_integration'] ?? []; $adaptive = $mfa['adaptive_authentication'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-lock me-2"></i>Enhanced Multi-Factor Authentication</h4>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Current MFA Methods</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Method</th><th>Security Level</th><th>Adoption</th><th>Vulnerabilities</th></tr></thead>
                            <tbody>
                                <?php if (!empty($methods)): foreach ($methods as $m): ?>
                                    <tr><td><?= htmlspecialchars($m['method'] ?? '-') ?></td><td><span class="badge bg-<?= ($m['security_level'] ?? '') === 'Very High' ? 'success' : (($m['security_level'] ?? '') === 'High' ? 'info' : (($m['security_level'] ?? '') === 'Medium' ? 'warning' : 'danger')) ?>"><?= htmlspecialchars($m['security_level'] ?? '-') ?></span></td><td><?= htmlspecialchars($m['adoption_rate'] ?? '0%') ?></td><td><small><?= implode(', ', array_map('htmlspecialchars', $m['vulnerabilities'] ?? [])) ?></small></td></tr>
                                <?php endforeach; else: ?><tr><td colspan="4" class="text-center text-muted py-3">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-fingerprint me-2"></i>Advanced Authentication</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">Biometric Authentication</h6>
                    <?php $ba = $advanced['biometric_authentication'] ?? []; foreach ($ba as $k => $v): ?><div class="mb-1 d-flex justify-content-between"><span><i class="fas fa-<?= $k === 'fingerprint' ? 'fingerprint' : ($k === 'facial_recognition' ? 'face-smile' : ($k === 'voice_recognition' ? 'microphone' : 'eye')) ?> me-2"></i><?= ucwords(str_replace('_', ' ', $k)) ?></span><span class="badge bg-<?= $v === 'Implemented' ? 'success' : ($v === 'In development' ? 'warning' : 'secondary') ?>"><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></span></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Behavioral Biometrics</h6>
                    <?php $bb = $advanced['behavioral_biometrics'] ?? []; foreach ($bb as $k => $v): ?><div class="mb-1 d-flex justify-content-between"><span><i class="fas fa-chart-line me-2"></i><?= ucwords(str_replace('_', ' ', $k)) ?></span><span class="badge bg-<?= $v === 'Active' ? 'success' : 'secondary' ?>"><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></span></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Contextual Authentication</h6>
                    <?php $ca = $advanced['contextual_authentication'] ?? []; foreach ($ca as $k => $v): ?><div class="mb-1 d-flex justify-content-between"><span><i class="fas fa-location-dot me-2"></i><?= ucwords(str_replace('_', ' ', $k)) ?></span><span class="badge bg-success"><?= htmlspecialchars($v, ENT_QUOTES, 'UTF-8') ?></span></div><?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-people-group me-2"></i>Biometric Integration</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">Standards</h6>
                    <?php $bstd = $biometric['biometric_standards'] ?? []; foreach ($bstd as $k => $v): ?><div class="mb-2"><span class="badge bg-primary me-2"><?= strtoupper(str_replace('_', ' ', $k)) ?></span><small class="text-muted"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Security</h6>
                    <?php $bsec = $biometric['biometric_security'] ?? []; foreach ($bsec as $k => $v): ?><div class="mb-2"><i class="fas fa-shield text-success me-2"></i><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-sliders me-2"></i>Adaptive Authentication</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">Risk-Based Adaptation</h6>
                    <?php $rba = $adaptive['risk_based_adaptation'] ?? []; foreach ($rba as $k => $v): ?><div class="mb-2"><span class="badge bg-<?= $k === 'critical_risk' ? 'danger' : ($k === 'high_risk' ? 'warning' : ($k === 'medium_risk' ? 'info' : 'success')) ?> me-2"><?= ucwords(str_replace('_', ' ', $k)) ?></span><small class="text-muted"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Contextual Factors</h6>
                    <?php $cf = $adaptive['contextual_factors'] ?? []; foreach ($cf as $k => $v): ?><div class="mb-1"><i class="fas fa-circle-info text-info me-2"></i><small><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong> <?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Adaptive Policies</h6>
                    <?php $ap = $adaptive['adaptive_policies'] ?? []; foreach ($ap as $k => $v): ?><div class="mb-1"><i class="fas fa-arrows-spin text-primary me-2"></i><small><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong> <?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
