<?php $pageTitle = $pageTitle ?? 'Fraud Detection'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-search-minus me-2"></i>Fraud Detection Dashboard</h4>
        <button class="btn btn-outline-danger btn-sm"><i class="fas fa-flag me-1"></i>Review Alerts</button>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent"><h5 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Fraud Alerts</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                    <thead class="table-light">
                        <tr><th>ID</th><th>Alert Type</th><th>Entity</th><th>Risk Score</th><th>Status</th><th>Detected At</th></tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($fraud_alerts)): ?>
                            <?php foreach ($fraud_alerts as $a): ?>
                                <tr>
                                    <td><?= $a['id'] ?? '-' ?></td>
                                    <td><?= htmlspecialchars($a['type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($a['entity'] ?? '-') ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 me-2 style-12222">
                                                <div class="progress-bar bg-<?= ($a['risk_score'] ?? 0) > 80 ? 'danger' : (($a['risk_score'] ?? 0) > 50 ? 'warning' : 'success') ?>" class="style-30026"></div>
                                            </div>
                                            <small><?= $a['risk_score'] ?? 0 ?></small>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-<?= ($a['status'] ?? 'open') === 'investigating' ? 'warning' : (($a['status'] ?? 'open') === 'resolved' ? 'success' : 'danger') ?>"><?= str_replace('_', ' ', ucfirst($a['status'] ?? 'open')) ?></span></td>
                                    <td><?= htmlspecialchars($a['detected_at'] ?? '-') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center text-muted py-3">No fraud alerts</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
