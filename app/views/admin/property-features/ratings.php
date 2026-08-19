<?php $page_title = 'Ratings & Reviews'; ?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2"><i class="fas fa-star text-warning me-2"></i>Ratings & Reviews</h1>
            <p class="text-muted">Manage property ratings and customer reviews</p>
        </div>
    </div>

    <?php if ($msg = $_SESSION['flash_success'] ?? null): ?><div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($msg ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['flash_success']); endif; ?>
    <?php if ($msg = $_SESSION['flash_error'] ?? null): ?><div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($msg ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php unset($_SESSION['flash_error']); endif; ?>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><div class="bg-warning bg-opacity-10 text-warning rounded p-3"><i class="fas fa-star fa-2x"></i></div></div>
                        <div class="flex-grow-1"><h6 class="text-muted mb-1">Total Ratings</h6><h3 class="mb-0"><?= number_format($total_ratings ?? 0) ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><div class="bg-info bg-opacity-10 text-info rounded p-3"><i class="fas fa-chart-line fa-2x"></i></div></div>
                        <div class="flex-grow-1"><h6 class="text-muted mb-1">Average Rating</h6><h3 class="mb-0"><?= number_format($avg_rating ?? 0, 2) ?> / 5</h3></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3"><div class="bg-danger bg-opacity-10 text-danger rounded p-3"><i class="fas fa-clock fa-2x"></i></div></div>
                        <div class="flex-grow-1"><h6 class="text-muted mb-1">Pending Reviews</h6><h3 class="mb-0"><?= number_format($pending_reviews ?? 0) ?></h3></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ratings Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-star-half-alt me-2 text-warning"></i>Property Ratings</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">Property</th><th>User</th><th>Rating</th><th>Review</th><th>Verified</th><th>Helpful</th><th class="text-end pe-4">Date</th></tr></thead>
                    <tbody>
                        <?php if (empty($ratings)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-star fa-3x d-block mb-3"></i>No ratings yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($ratings as $r): ?>
                            <tr>
                                <td class="ps-4"><strong><?= htmlspecialchars($r['property_title'] ?? 'Property #' . $r['property_id']) ?></strong></td>
                                <td><?= htmlspecialchars($r['user_name'] ?? $r['user_email'] ?? '-') ?></td>
                                <td>
                                    <?php $rating = floatval($r['rating'] ?? 0); for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= $rating ? ' text-warning' : ' text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </td>
                                <td><?= htmlspecialchars(mb_substr($r['review_text'] ?? '-', 0, 100)) ?></td>
                                <td><?= ($r['is_verified_viewing'] ?? 0) ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                                <td><?= number_format($r['helpful_votes'] ?? 0) ?></td>
                                <td class="text-end pe-4 small"><?= date('d M Y', strtotime($r['created_at'] ?? 'now')) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-comment me-2 text-primary"></i>Customer Reviews</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">Property</th><th>Customer</th><th>Rating</th><th>Review</th><th>Status</th><th>Anonymous</th><th class="text-end pe-4">Actions</th></tr></thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-comment fa-3x d-block mb-3"></i>No reviews yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $r): ?>
                            <tr>
                                <td class="ps-4"><strong><?= htmlspecialchars($r['property_title'] ?? 'Property #' . $r['property_id']) ?></strong></td>
                                <td><?= htmlspecialchars($r['customer_name'] ?? $r['customer_email'] ?? '-') ?></td>
                                <td>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star<?= $i <= intval($r['rating'] ?? 0) ? ' text-warning' : ' text-muted' ?>"></i>
                                    <?php endfor; ?>
                                </td>
                                <td><?= htmlspecialchars(mb_substr($r['review_text'] ?? '-', 0, 100)) ?></td>
                                <td>
                                    <?php $s = $r['status'] ?? 'pending'; $colors = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger']; ?>
                                    <span class="badge bg-<?= $colors[$s] ?? 'secondary' ?>"><?= ucfirst($s) ?></span>
                                </td>
                                <td><?= ($r['anonymous'] ?? 0) ? '<span class="badge bg-info">Yes</span>' : '<span class="badge bg-secondary">No</span>' ?></td>
                                <td class="text-end pe-4">
                                    <?php if (($r['status'] ?? '') === 'pending'): ?>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/property-features/reviews/update-status/<?= $r['id'] ?>" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="btn btn-sm btn-success" title="Approve" aria-label="Approve"><i class="fas fa-check"></i></button>
                                        </form>
                                        <form method="POST" action="<?= BASE_URL ?>/admin/property-features/reviews/update-status/<?= $r['id'] ?>" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="btn btn-sm btn-danger" title="Reject" aria-label="Reject"><i class="fas fa-times"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small"><?= date('d M Y', strtotime($r['created_at'] ?? 'now')) ?></span>
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
