<?php
$stats = $stats ?? ['total' => 0, 'initiated' => 0, 'signed' => 0, 'expired' => 0, 'today' => 0];
$transactions = $transactions ?? [];
$config = $config ?? [];
$status_filter = $status_filter ?? '';
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-file-signature me-2 text-primary"></i>eSign Management</h4>
            <p class="text-muted mb-0">NSDL/CDSL eSign transactions, OTP verification, and signed documents</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Total Transactions</div>
                            <div class="fs-3 fw-bold"><?= number_format($stats['total']) ?></div>
                        </div>
                        <i class="fas fa-file-signature fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Pending OTP</div>
                            <div class="fs-3 fw-bold"><?= $stats['initiated'] ?></div>
                        </div>
                        <i class="fas fa-hourglass-half fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Signed</div>
                            <div class="fs-3 fw-bold"><?= number_format($stats['signed']) ?></div>
                        </div>
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="text-white-50 small">Expired</div>
                            <div class="fs-3 fw-bold"><?= $stats['expired'] ?></div>
                        </div>
                        <i class="fas fa-times-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- eSign Provider Config -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-cog me-2"></i>eSign Provider Configuration</h5>
            <span class="badge bg-<?= !empty($config) ? 'success' : 'warning' ?>"><?= !empty($config) ? 'Configured' : 'Mock Mode' ?></span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label class="form-label">Provider</label>
                    <select class="form-select" disabled>
                        <option selected>Mock (Testing)</option>
                        <option>NSDL eSign</option>
                        <option>CDSL eSign</option>
                        <option>eMudhra</option>
                        <option>Protean (NSDL eGov)</option>
                    </select>
                    <div class="form-text">Switch to NSDL/CDSL for production</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">API Endpoint</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($config['api_endpoint'] ?? 'mock://localhost/esign') ?>" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">OTP Expiry (seconds)</label>
                    <input type="number" class="form-control" value="<?= htmlspecialchars($config['otp_expiry'] ?? '300') ?>" disabled>
                </div>
            </div>
            <div class="alert alert-info mt-3 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                <strong>Mock Mode Active:</strong> Any 6-digit OTP is accepted. For production, configure NSDL/CDSL API credentials.
            </div>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Recent Transactions</h5>
            <div class="btn-group btn-group-sm">
                <a href="<?= BASE_URL ?>/admin/tools/esign" class="btn btn-<?= $status_filter === '' ? 'primary' : 'outline-primary' ?>">All</a>
                <a href="<?= BASE_URL ?>/admin/tools/esign?status=initiated" class="btn btn-<?= $status_filter === 'initiated' ? 'warning' : 'outline-warning' ?>">Pending</a>
                <a href="<?= BASE_URL ?>/admin/tools/esign?status=signed" class="btn btn-<?= $status_filter === 'signed' ? 'success' : 'outline-success' ?>">Signed</a>
                <a href="<?= BASE_URL ?>/admin/tools/esign?status=expired" class="btn btn-<?= $status_filter === 'expired' ? 'danger' : 'outline-danger' ?>">Expired</a>
            </div>
        </div>
        <div class="card-body p-0">
            <?php if (empty($transactions)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-file-signature fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No eSign transactions found</p>
                    <p class="text-muted small">Transactions will appear here when customers sign documents via Aadhaar eSign</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Transaction ID</th>
                                <th>Document Type</th>
                                <th>Signer</th>
                                <th>Aadhaar</th>
                                <th>Status</th>
                                <th>Provider</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td><code class="small"><?= htmlspecialchars($t['transaction_id'] ?? '') ?></code></td>
                                <td><span class="badge bg-info"><?= htmlspecialchars(str_replace('_', ' ', $t['document_type'] ?? '')) ?></span></td>
                                <td><?= htmlspecialchars($t['signer_name'] ?? '') ?></td>
                                <td><code class="small"><?= htmlspecialchars($t['signer_aadhaar'] ?? '') ?></code></td>
                                <td>
                                    <?php
                                    $statusColors = ['initiated' => 'warning', 'pending_otp' => 'info', 'signed' => 'success', 'expired' => 'danger', 'failed' => 'danger'];
                                    $color = $statusColors[$t['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>"><?= ucfirst(str_replace('_', ' ', $t['status'])) ?></span>
                                </td>
                                <td><span class="badge bg-secondary"><?= htmlspecialchars($t['esign_provider'] ?? 'mock') ?></span></td>
                                <td><small class="text-muted"><?= date('d M Y H:i', strtotime($t['created_at'] ?? '')) ?></small></td>
                                <td>
                                    <?php if (($t['status'] ?? '') === 'signed'): ?>
                                        <a href="<?= BASE_URL ?>/api/esign/document/<?= htmlspecialchars($t['transaction_id'] ?? '') ?>" class="btn btn-sm btn-outline-primary" title="Download Signed Doc">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
