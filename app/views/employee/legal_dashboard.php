<?php
$pageTitle = 'Legal Advisor Dashboard';
// Controller passes `pending_documents`; fall back so the review queue renders.
$documents = $documents ?? $pending_documents ?? [];
?>
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
                                    <td><button type="button" class="btn btn-sm btn-outline-primary review-doc-btn" data-doc-id="<?= (int)($doc['id'] ?? 0) ?>" data-doc-title="<?= htmlspecialchars($doc['title'] ?? '', ENT_QUOTES) ?>">Review</button></td>
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

<!-- Review Document Modal -->
<div class="modal fade" id="reviewDocModal" tabindex="-1" aria-labelledby="reviewDocModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewDocModalLabel"><i class="fas fa-file-signature me-2"></i>Review Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewDocForm">
                <div class="modal-body">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <input type="hidden" name="document_id" id="reviewDocId" value="0">
                    <p class="text-muted small mb-3">Reviewing: <strong id="reviewDocTitle"></strong></p>
                    <div class="mb-3">
                        <label class="form-label" for="reviewDocDecision">Decision *</label>
                        <select class="form-select" id="reviewDocDecision" name="status" required>
                            <option value="active">Approve</option>
                            <option value="draft">Request Changes</option>
                            <option value="rejected">Reject</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reviewDocNotes">Review Notes</label>
                        <textarea class="form-control" id="reviewDocNotes" name="review_notes" rows="3" maxlength="2000" placeholder="Notes for the submitter..."></textarea>
                    </div>
                    <div id="reviewDocMsg" class="small"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="reviewDocSubmitBtn"><i class="fas fa-check me-1"></i>Submit Review</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    var modalEl = document.getElementById('reviewDocModal');
    if (!modalEl) return;
    document.querySelectorAll('.review-doc-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            document.getElementById('reviewDocId').value = btn.getAttribute('data-doc-id') || 0;
            document.getElementById('reviewDocTitle').textContent = btn.getAttribute('data-doc-title') || '';
            document.getElementById('reviewDocMsg').innerHTML = '';
            new bootstrap.Modal(modalEl).show();
        });
    });
    document.getElementById('reviewDocForm').addEventListener('submit', function(e) {
        e.preventDefault();
        var btn = document.getElementById('reviewDocSubmitBtn');
        var statusEl = document.getElementById('reviewDocMsg');
        btn.disabled = true;
        statusEl.innerHTML = '<span class="text-muted">Submitting...</span>';
        fetch('<?= BASE_URL ?>/employee/legal/review-document', {
            method: 'POST',
            body: new FormData(e.target),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function() {
            statusEl.innerHTML = '<span class="text-success">Review submitted. Reloading...</span>';
            setTimeout(function() { window.location.reload(); }, 900);
        })
        .catch(function() {
            statusEl.innerHTML = '<span class="text-danger">Network error. Please try again.</span>';
            btn.disabled = false;
        });
    });
})();
</script>
