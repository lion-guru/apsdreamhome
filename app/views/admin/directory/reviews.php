<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-star me-2"></i>Review Moderation</h1>
        <a href="<?= BASE_URL ?>/admin/directory" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Listing</th><th>Reviewer</th><th>Rating</th><th>Review</th><th>Status</th><th>Date</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No reviews yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $r): ?>
                                <tr>
                                    <td><?= $r['id'] ?></td>
                                    <td><?= htmlspecialchars($r['business_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($r['reviewer_name'] ?? '') ?></td>
                                    <td><span class="text-warning"><?= str_repeat('★', (int)$r['rating']) ?></span><?= str_repeat('☆', 5 - (int)$r['rating']) ?></td>
                                    <td><?= htmlspecialchars(mb_substr($r['review'] ?? '', 0, 100)) ?></td>
                                    <td><span class="badge bg-<?= $r['status'] === 'approved' ? 'success' : ($r['status'] === 'pending' ? 'warning' : 'danger') ?>"><?= $r['status'] ?></span></td>
                                    <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                                    <td>
                                        <?php if ($r['status'] !== 'approved'): ?>
                                            <a href="<?= BASE_URL ?>/admin/directory/approve-review/<?= $r['id'] ?>" class="btn btn-sm btn-outline-success"><i class="fas fa-check"></i></a>
                                        <?php endif; ?>
                                        <?php if ($r['status'] !== 'rejected'): ?>
                                            <a href="<?= BASE_URL ?>/admin/directory/reject-review/<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
