<?php $crypto_data = $crypto_data ?? []; $algorithms = $crypto_data['current_algorithms'] ?? []; $solutions = $crypto_data['quantum_resistant_solutions'] ?? []; $timeline = $crypto_data['migration_timeline'] ?? []; $impl_status = $crypto_data['implementation_status'] ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-microchip me-2"></i>Quantum-Resistant Cryptography</h4>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-exclamation-triangle text-warning me-2"></i>Current Vulnerable Algorithms</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Algorithm</th><th>Key Size</th><th>Quantum Vulnerable</th><th>Estimated Break Time</th></tr></thead>
                            <tbody>
                                <?php if (!empty($algorithms)): ?>
                                    <?php foreach ($algorithms as $a): ?>
                                        <tr><td><?= htmlspecialchars($a['algorithm'] ?? '-') ?></td><td><?= htmlspecialchars($a['key_size'] ?? '-') ?></td><td><span class="badge bg-danger">Yes</span></td><td><small><?= htmlspecialchars($a['estimated_break_time'] ?? '-') ?></small></td></tr>
                                    <?php endforeach; ?>
                                <?php else: ?><tr><td colspan="4" class="text-center text-muted py-3">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-shield text-success me-2"></i>Quantum-Resistant Solutions</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                            <thead class="table-light"><tr><th>Algorithm</th><th>Type</th><th>Security Level</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php if (!empty($solutions)): ?>
                                    <?php foreach ($solutions as $s): ?>
                                        <tr><td><?= htmlspecialchars($s['algorithm'] ?? '-') ?></td><td><small><?= htmlspecialchars($s['type'] ?? '-') ?></small></td><td><span class="badge bg-info"><?= htmlspecialchars($s['security_level'] ?? '-') ?></span></td><td><span class="badge bg-<?= (($s['implementation_status'] ?? '') === 'Ready for deployment' ? 'success' : (($s['implementation_status'] ?? '') === 'Experimental' ? 'warning' : 'secondary')) ?>"><?= htmlspecialchars($s['implementation_status'] ?? '-') ?></span></td></tr>
                                    <?php endforeach; ?>
                                <?php else: ?><tr><td colspan="4" class="text-center text-muted py-3">No data available</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-road me-2"></i>Migration Timeline</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($timeline)): ?>
                        <?php foreach ($timeline as $period => $items): ?>
                            <div class="mb-3">
                                <h6 class="text-primary text-uppercase small fw-bold"><?= htmlspecialchars($period) ?></h6>
                                <ul class="list-unstyled ms-3">
                                    <?php foreach ($items as $status => $desc): ?>
                                        <li class="mb-1"><i class="fas fa-<?= $status === 'completed' ? 'check-circle text-success' : ($status === 'in_progress' ? 'spinner text-warning' : 'clock text-muted') ?> me-2"></i><?= htmlspecialchars($desc) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?><p class="text-muted text-center mb-0">No timeline data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Implementation Status</h5></div>
                <div class="card-body aps-cp-card-body">
                    <?php $progress_items = ['algorithms_implemented' => 'Algorithms Implemented', 'systems_migrated' => 'Systems Migrated', 'testing_completed' => 'Testing Completed', 'performance_validated' => 'Performance Validated']; ?>
                    <?php foreach ($progress_items as $key => $label): ?>
                        <?php $val = $impl_status[$key] ?? '0%'; $pct = (int) $val; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between"><small><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></small><small class="text-muted"><?= htmlspecialchars($val) ?></small></div>
                            <div class="progress" class="style-32124"><div class="progress-bar bg-<?= $pct >= 90 ? 'success' : ($pct >= 50 ? 'warning' : 'danger') ?>" class="style-35193"></div></div>
                        </div>
                    <?php endforeach; ?>
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted"><i class="fas fa-calendar me-1"></i>Go-Live Date: <strong><?= htmlspecialchars($impl_status['go_live_date'] ?? 'TBD') ?></strong></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
