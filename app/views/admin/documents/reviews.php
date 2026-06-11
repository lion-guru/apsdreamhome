<?php $page_title = $page_title ?? 'Document Reviews'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-star me-2"></i>Document Reviews</h1>
        <a href="<?= BASE_URL ?>/admin/documents" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <?php if (!empty($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= htmlspecialchars($_SESSION['flash_type'] ?? 'info') ?> alert-dismissible fade show">
            <?= htmlspecialchars($_SESSION['flash_message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php $_SESSION['flash_message'] = ''; $_SESSION['flash_type'] = ''; endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header aps-cp-card-header"><i class="fas fa-plus me-2"></i>Add Review</div>
                <div class="card-body aps-cp-card-body">
                    <form method="POST" action="<?= BASE_URL ?>/admin/documents/reviews/store">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                        <div class="mb-3">
                            <label class="form-label">Document <span class="text-danger">*</span></label>
                            <select name="document_id" class="form-select" required>
                                <option value="">-- Select --</option>
                                <?php if (!empty($documents)): ?>
                                    <?php foreach ($documents as $d): ?>
                                        <option value="<?= (int)$d['id'] ?>"><?= htmlspecialchars($d['title']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Review Status</label>
                            <select name="review_status" class="form-select">
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Comments</label>
                            <textarea name="comments" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save me-1"></i>Submit Review</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span><i class="fas fa-list me-2"></i>All Reviews</span>
                    <div>
                        <a href="?status=" class="btn btn-sm <?= empty($status_filter) ? 'btn-dark' : 'btn-outline-secondary' ?>">All</a>
                        <a href="?status=pending" class="btn btn-sm <?= $status_filter === 'pending' ? 'btn-warning' : 'btn-outline-warning' ?>">Pending</a>
                        <a href="?status=approved" class="btn btn-sm <?= $status_filter === 'approved' ? 'btn-success' : 'btn-outline-success' ?>">Approved</a>
                        <a href="?status=rejected" class="btn btn-sm <?= $status_filter === 'rejected' ? 'btn-danger' : 'btn-outline-danger' ?>">Rejected</a>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($reviews)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr><th>Document</th><th>Reviewer</th><th>Status</th><th>Comments</th><th>Reviewed At</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reviews as $r): ?>
                                        <tr>
                                            <td><a href="<?= BASE_URL ?>/admin/documents/show/<?= (int)$r['document_id'] ?>"><?= htmlspecialchars($r['document_title'] ?? '-') ?></a></td>
                                            <td><?= htmlspecialchars($r['reviewer_name'] ?? '-') ?></td>
                                            <td>
                                                <span class="badge bg-<?= $r['review_status'] === 'approved' ? 'success' : ($r['review_status'] === 'rejected' ? 'danger' : 'warning') ?>">
                                                    <?= htmlspecialchars(ucfirst($r['review_status'] ?? 'pending')) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($r['comments'] ?? '-') ?></td>
                                            <td><?= htmlspecialchars($r['reviewed_at'] ?? $r['created_at'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-3">No reviews yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
