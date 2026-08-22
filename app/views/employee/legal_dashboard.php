<?php $pageTitle = 'Legal Advisor Dashboard'; ?>
<div class="container-fluid py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>"><i class="fas fa-home me-1"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/employee/dashboard">Employee</a></li>
            <li class="breadcrumb-item active" aria-current="page">Legal Dashboard</li>
        </ol>
    </nav>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-gavel me-2"></i>Legal Advisor Dashboard</h4>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-warning mb-2"><i class="fas fa-file-signature"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($pendingReview ?? 0) ?></h3>
                    <p class="text-muted mb-0">Pending Review</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-success mb-2"><i class="fas fa-check-double"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($completedReview ?? 0) ?></h3>
                    <p class="text-muted mb-0">Completed Reviews</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-danger mb-2"><i class="fas fa-balance-scale"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($activeCases ?? 0) ?></h3>
                    <p class="text-muted mb-0">Active Cases</p>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="display-6 text-info mb-2"><i class="fas fa-file-contract"></i></div>
                    <h3 class="fw-bold mb-1"><?= e($documentsDrafted ?? 0) ?></h3>
                    <p class="text-muted mb-0">Documents Drafted</p>
                </div>
            </div>
        </div>
    </div>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-file-alt me-2"></i>Document Review Queue</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($documents)): ?>
                        <div class="table-responsive"><div class="table-responsive"><table class="table table-sm table-hover mb-0 table-responsive">
                            <thead><tr><th>Document</th><th>Submitted By</th><th>Date</th><th>Priority</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php foreach ($documents as $doc): ?>
                                <tr>
                                    <td class="small"><?= htmlspecialchars($doc['title'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($doc['submitted_by'] ?? '') ?></td>
                                    <td class="small"><?= htmlspecialchars($doc['created_at'] ?? '') ?></td>
                                    <td><span class="badge bg-<?= ($doc['priority'] ?? '') === 'high' ? 'danger' : (($doc['priority'] ?? '') === 'medium' ? 'warning' : 'info') ?>"><?= ucfirst($doc['priority'] ?? '') ?></span></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary">Review</a></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table></div></div>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-check-circle fa-2x text-success mb-2"></i><p class="text-muted mb-0">No documents pending review</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom-0"><h6 class="mb-0"><i class="fas fa-scale-balanced me-2"></i>Case Stats</h6></div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($caseStats)): ?>
                        <?php foreach ($caseStats as $stat): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= htmlspecialchars($stat['label'] ?? '') ?></span>
                            <strong><?= e($stat['count'] ?? 0) ?></strong>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-4"><i class="fas fa-gavel fa-2x text-muted mb-2"></i><p class="text-muted mb-0">No case data</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
