<?php
/**
 * My Asset Assignments
 */

$page_title = $page_title ?? 'My Asset Assignments';
$assets = $assets ?? [];
$summary = $summary ?? ['total' => 0, 'active' => 0, 'returned' => 0, 'issues' => 0];
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1 fw-bold"><i class="fas fa-laptop me-2 text-primary"></i>My Asset Assignments</h4>
                    <p class="text-muted mb-0 small">Track company assets assigned to you</p>
                </div>
            </div>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <!-- Summary Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-primary"><?= $summary['total'] ?></div>
                            <div class="small text-muted">Total Assigned</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-success"><?= $summary['active'] ?></div>
                            <div class="small text-muted">Active</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-secondary"><?= $summary['returned'] ?></div>
                            <div class="small text-muted">Returned</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card stat-card border-0 shadow-sm">
                        <div class="card-body text-center">
                            <div class="fw-bold fs-4 text-danger"><?= $summary['issues'] ?></div>
                            <div class="small text-muted">Reported Issues</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Asset List -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list me-2"></i>Assigned Assets</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($assets)): ?>
                        <div class="text-center py-5">
                            <i class="fas fa-laptop fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Assets Assigned</h5>
                            <p class="text-muted">You don't have any assets currently assigned to you.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Asset</th>
                                        <th>Type</th>
                                        <th>Serial Number</th>
                                        <th>Assigned Date</th>
                                        <th>Expected Return</th>
                                        <th>Condition</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($assets as $a): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($a['asset_name'] ?? 'Unknown') ?></strong>
                                            <?php if (!empty($a['brand']) || !empty($a['model'])): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars(($a['brand'] ?? '') . ' ' . ($a['model'] ?? '')) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-info text-dark"><?= htmlspecialchars($a['asset_type'] ?? 'N/A') ?></span>
                                        </td>
                                        <td><code><?= htmlspecialchars($a['serial_number'] ?? 'N/A') ?></code></td>
                                        <td><?= date('d M Y', strtotime($a['assigned_date'] ?? 'now')) ?></td>
                                        <td>
                                            <?= $a['expected_return_date'] ? date('d M Y', strtotime($a['expected_return_date'])) : '<span class="text-muted">Not set</span>' ?>
                                        </td>
                                        <td>
                                            <span class="badge <?= $a['condition'] === 'excellent' ? 'bg-success' : ($a['condition'] === 'good' ? 'bg-primary' : ($a['condition'] === 'fair' ? 'bg-warning text-dark' : 'bg-danger')) ?>">
                                                <?= ucfirst($a['condition'] ?? 'good') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge <?= $a['status'] === 'active' ? 'bg-success' : ($a['status'] === 'returned' ? 'bg-secondary' : ($a['status'] === 'reported' ? 'bg-danger' : 'bg-info')) ?>">
                                                <?= ucfirst($a['status'] ?? 'active') ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (($a['status'] ?? '') === 'active'): ?>
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#reportIssueModal" data-asset-id="<?= $a['assignment_id'] ?? $a['id'] ?>" data-asset-name="<?= htmlspecialchars($a['asset_name'] ?? '') ?>">
                                                <i class="fas fa-exclamation-triangle me-1"></i> Report Issue
                                            </button>
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

            <!-- Asset Care Guidelines -->
            <div class="card mt-4 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Asset Care Guidelines</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success"><i class="fas fa-check-circle me-2"></i>Do's</h6>
                            <ul class="small">
                                <li>Keep assets clean and secure</li>
                                <li>Use protective cases/covers provided</li>
                                <li>Report issues immediately</li>
                                <li>Return on time when leaving</li>
                                <li>Back up data regularly (laptops/phones)</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-danger"><i class="fas fa-times-circle me-2"></i>Don'ts</h6>
                            <ul class="small">
                                <li>Don't install unauthorized software</li>
                                <li>Don't share with unauthorized persons</li>
                                <li>Don't modify hardware</li>
                                <li>Don't use for personal business</li>
                                <li>Don't remove asset tags</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Issue Modal -->
<div class="modal fade" id="reportIssueModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="<?= BASE_URL ?>/employee/self-service/assets/report-issue" id="reportIssueForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="assignment_id" id="reportAssetId">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Report Asset Issue</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Asset</label>
                        <input type="text" class="form-control" id="reportAssetName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="issue_type" class="form-label fw-bold">Issue Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="issue_type" name="issue_type" required>
                            <option value="">Select issue type</option>
                            <option value="lost">Lost</option>
                            <option value="stolen">Stolen</option>
                            <option value="damaged">Damaged</option>
                            <option value="malfunction">Malfunction</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" required placeholder="Describe what happened, when, and any relevant details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-flag me-1"></i> Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
document.addEventListener('DOMContentLoaded', function() {
    const reportModal = document.getElementById('reportIssueModal');
    reportModal.addEventListener('show.bs.modal', function(event) {
        const button = event.relatedTarget;
        const assetId = button.getAttribute('data-asset-id');
        const assetName = button.getAttribute('data-asset-name');
        
        document.getElementById('reportAssetId').value = assetId;
        document.getElementById('reportAssetName').value = assetName;
    });
});
</script>