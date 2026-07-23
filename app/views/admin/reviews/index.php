<?php
$page_title = $page_title ?? 'Reviews & Testimonials';
$page_heading = $page_heading ?? 'Reviews & Testimonials';
$content = $content ?? '';
$stats = $stats ?? [];
$reviews = $reviews ?? [];
$testimonials = $testimonials ?? [];
ob_start();
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Reviews & Testimonials</h2>
            <p class="text-muted mb-0">Moderate customer reviews and manage testimonials</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Total Reviews</p>
                    <h3><?= number_format($stats['total_reviews'] ?? 0) ?></h3>
                    <small class="text-muted"><?= $stats['pending_reviews'] ?? 0 ?> pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Average Rating</p>
                    <h3>
                        <i class="fas fa-star text-warning"></i>
                        <?= number_format($stats['avg_rating'] ?? 0, 1) ?>
                    </h3>
                    <small class="text-muted"><?= $stats['5_star'] ?? 0 ?> five-star reviews</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Approved Reviews</p>
                    <h3 class="text-success"><?= number_format($stats['approved_reviews'] ?? 0) ?></h3>
                    <small class="text-muted"><?= $stats['rejected_reviews'] ?? 0 ?> rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <p class="text-muted small mb-1">Testimonials</p>
                    <h3 class="text-info"><?= number_format($stats['total_testimonials'] ?? 0) ?></h3>
                    <small class="text-muted"><?= $stats['featured_testimonials'] ?? 0 ?> featured</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-star me-2"></i>Customer Reviews</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Property</th>
                            <th>Rating</th>
                            <th>Review</th>
                            <th>Helpful</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($reviews)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-star fa-3x text-muted mb-3" style="opacity:0.2"></i>
                                    <h5 class="text-muted">No reviews yet</h5>
                                    <p class="text-muted mb-3">Customer reviews will appear here once buyers start sharing feedback about their property experience.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($reviews as $r): ?>
                                <tr>
                                    <td>#<?= $r['id'] ?></td>
                                    <td>
                                        <?php if ($r['anonymous']): ?>
                                            <em class="text-muted">Anonymous</em>
                                        <?php else: ?>
                                            <strong><?= htmlspecialchars($r['customer_name'] ?? 'Guest') ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars($r['property_title'] ?? 'Property #' . $r['property_id']) ?></small></td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= $r['rating'] ? 'text-warning' : 'text-muted' ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars(substr($r['review_text'] ?? '', 0, 80)) ?><?= strlen($r['review_text'] ?? '') > 80 ? '...' : '' ?></small></td>
                                    <td><span class="badge bg-secondary"><?= $r['helpful_count'] ?? 0 ?></span></td>
                                    <td>
                                        <span class="badge bg-<?= ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$r['status']] ?? 'secondary' ?>">
                                            <?= ucfirst($r['status']) ?>
                                        </span>
                                    </td>
                                    <td><small><?= date('M j', strtotime($r['created_at'])) ?></small></td>
                                    <td>
                                        <?php if ($r['status'] === 'pending'): ?>
                                            <a href="<?= BASE_URL ?>/admin/reviews/approve?id=<?= $r['id'] ?>" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </a>
                                            <a href="<?= BASE_URL ?>/admin/reviews/reject?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/admin/reviews/delete?id=<?= $r['id'] ?>" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete this review?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php if (!empty($r['admin_response'])): ?>
                                    <tr><td colspan="9" class="bg-light">
                                        <div class="ps-4 small">
                                            <strong class="text-primary">Your response:</strong>
                                            <em>"<?= htmlspecialchars($r['admin_response']) ?>"</em>
                                        </div>
                                    </td></tr>
                                <?php else: ?>
                                    <tr><td colspan="9" class="bg-light">
                                        <form method="POST" action="<?= BASE_URL ?>/admin/reviews/respond" class="d-flex gap-2 ps-4">
                                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                            <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                            <input type="text" class="form-control form-control-sm" name="response" placeholder="Add a public response to this review...">
                                            <button type="submit" class="btn btn-sm btn-outline-primary">Reply</button>
                                        </form>
                                    </td></tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-quote-left me-2"></i>Testimonials</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Project / Location</th>
                            <th>Rating</th>
                            <th>Content</th>
                            <th>Status</th>
                            <th>Featured</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($testimonials)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <i class="fas fa-quote-left fa-3x text-muted mb-3" style="opacity:0.2"></i>
                                    <h5 class="text-muted">No testimonials yet</h5>
                                    <p class="text-muted mb-3">Add customer testimonials to build trust and showcase your best property experiences.</p>
                                    <a href="<?= BASE_URL ?>/admin/testimonials/create" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i> Add Testimonial
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($testimonials as $t): ?>
                                <tr>
                                    <td>#<?= $t['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($t['customer_name']) ?></strong>
                                        <?php if (!empty($t['client_photo'])): ?>
                                            <br><img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="rounded-circle mt-1" style="width:30px;height:30px;" />
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($t['project_name'] ?? 'N/A') ?></strong>
                                        <?php if ($t['location']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($t['location']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star <?= $i <= ($t['rating'] ?? 5) ? 'text-warning' : 'text-muted' ?>"></i>
                                        <?php endfor; ?>
                                    </td>
                                    <td><small><?= htmlspecialchars(substr($t['testimonial'] ?? '', 0, 100)) ?>...</small></td>
                                    <td>
                                        <span class="badge bg-<?= ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'][$t['status']] ?? 'secondary' ?>">
                                            <?= ucfirst($t['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($t['is_featured'])): ?>
                                            <i class="fas fa-star text-warning" title="Featured"></i>
                                        <?php else: ?>
                                            <i class="far fa-star text-muted"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><small><?= date('M j, Y', strtotime($t['created_at'])) ?></small></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/reviews/feature-testimonial?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-warning" title="Toggle featured">
                                            <i class="fas fa-star"></i>
                                        </a>
                                        <?php if ($t['status'] === 'pending'): ?>
                                            <a href="<?= BASE_URL ?>/admin/reviews/approve-testimonial?id=<?= $t['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-check"></i></a>
                                            <a href="<?= BASE_URL ?>/admin/reviews/reject-testimonial?id=<?= $t['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-times"></i></a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/admin/reviews/delete-testimonial?id=<?= $t['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete?')"><i class="fas fa-trash"></i></a>
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

<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/admin.php';
