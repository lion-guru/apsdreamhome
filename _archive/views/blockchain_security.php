<?php $bs = $blockchain_security ?? []; $did = $bs['decentralized_identity'] ?? []; $tx = $bs['secure_transactions'] ?? []; $audit = $bs['audit_trails'] ?? []; $consensus = $bs['consensus_mechanisms'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-link me-2"></i>Blockchain Security Features</h4>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-id-card me-2"></i>Decentralized Identity</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">DID Implementation</h6>
                    <?php $di = $did['did_implementation'] ?? []; foreach ($di as $k => $v): ?><div class="mb-2"><i class="fas fa-check-circle text-success me-2"></i><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Identity Security</h6>
                    <?php $isec = $did['identity_security'] ?? []; foreach ($isec as $k => $v): ?><div class="mb-2"><i class="fas fa-lock text-primary me-2"></i><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong><small class="text-muted ms-1"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-shield me-2"></i>Secure Transactions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h6 class="fw-bold text-secondary">Transaction Security</h6>
                    <?php $ts = $tx['transaction_security'] ?? []; foreach ($ts as $k => $v): ?><div class="mb-2"><span class="badge bg-success me-2"><?= ucwords(str_replace('_', ' ', $k)) ?></span><small class="text-muted"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                    <hr>
                    <h6 class="fw-bold text-secondary">Transaction Privacy</h6>
                    <?php $tp = $tx['transaction_privacy'] ?? []; foreach ($tp as $k => $v): ?><div class="mb-2"><span class="badge bg-info me-2"><?= ucwords(str_replace('_', ' ', $k)) ?></span><small class="text-muted"><?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Audit Trails</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-secondary">Comprehensive Logging</h6>
                            <?php $cl = $audit['comprehensive_logging'] ?? []; foreach ($cl as $k => $v): ?><div class="mb-2"><i class="fas fa-arrow-right text-primary me-1"></i><small><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong> <?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-secondary">Audit Analysis</h6>
                            <?php $aa = $audit['audit_analysis'] ?? []; foreach ($aa as $k => $v): ?><div class="mb-2"><i class="fas fa-arrow-right text-success me-1"></i><small><strong><?= ucwords(str_replace('_', ' ', $k)) ?>:</strong> <?= htmlspecialchars($v) ?></small></div><?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-handshake me-2"></i>Consensus Mechanisms</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($consensus)): foreach ($consensus as $c): ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-2"><?= htmlspecialchars($c['mechanism'] ?? '-') ?></h6>
                            <small class="d-block text-muted"><i class="fas fa-bolt text-warning me-1"></i><?= htmlspecialchars($c['energy_efficiency'] ?? '-') ?></small>
                            <small class="d-block text-muted"><i class="fas fa-shield-alt text-success me-1"></i>Security: <?= htmlspecialchars($c['security_level'] ?? '-') ?></small>
                            <small class="d-block text-muted"><i class="fas fa-gauge-high text-info me-1"></i>Speed: <?= htmlspecialchars($c['transaction_speed'] ?? '-') ?></small>
                        </div>
                    <?php endforeach; else: ?><p class="text-muted mb-0">No data available</p><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
