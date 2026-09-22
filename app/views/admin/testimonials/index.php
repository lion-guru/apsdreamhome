<?php
$list      = $testimonials ?? [];
$total     = count($list);
$approved  = 0; $pending = 0; $rejected = 0; $avgRating = 0;
foreach ($list as $t) {
    $st = $t['status'] ?? 'pending';
    if ($st === 'approved')  { $approved++; }
    elseif ($st === 'pending') { $pending++; }
    else { $rejected++; }
    $avgRating += (int)($t['rating'] ?? 0);
}
$avgRating = $total > 0 ? round($avgRating / $total, 1) : 0;
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-star text-warning me-2"></i>Testimonials</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item active">Testimonials</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/blogs"               class="btn btn-outline-secondary btn-sm"><i class="fas fa-blog me-1"></i>Blog</a>
        <a href="<?= BASE_URL ?>/admin/gallery"              class="btn btn-outline-info btn-sm"><i class="fas fa-images me-1"></i>Gallery</a>
        <a href="<?= BASE_URL ?>/admin/testimonials/create"  class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Testimonial</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0d6efd;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-quote-left text-primary"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $total ?></div>
                <div class="text-muted small">Total</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #198754;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $approved ?></div>
                <div class="text-muted small">Approved</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #ffc107;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-clock text-warning"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $pending ?></div>
                <div class="text-muted small">Pending Review</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #fd7e14;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-star text-warning"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $avgRating ?></div>
                <div class="text-muted small">Avg Rating</div>
            </div>
        </div>
    </div>
</div>

<?php if ($pending > 0): ?>
<div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius:10px; border-left:4px solid #ffc107 !important;">
    <i class="fas fa-clock me-2"></i>
    <strong><?= $pending ?> testimonial<?= $pending > 1 ? 's' : '' ?> awaiting review.</strong>
    Approve them to display on your website.
</div>
<?php endif; ?>

<!-- Table Card -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 fw-bold">All Testimonials</h6>
        <div class="d-flex gap-2">
            <input type="text" id="testimonialSearch" class="form-control form-control-sm" placeholder="Search..." style="width:180px;">
            <select id="testimonialStatusFilter" class="form-select form-select-sm" style="width:140px;">
                <option value="">All Status</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
                <option value="rejected">Rejected</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="testimonialsTable">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="px-4">Customer</th>
                        <th>Review</th>
                        <th class="text-center">Rating</th>
                        <th class="text-center">Status</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $t): ?>
                    <tr class="testimonial-row" data-status="<?= htmlspecialchars($t['status'] ?? 'pending') ?>">
                        <td class="px-4">
                            <div class="fw-semibold"><?= htmlspecialchars($t['customer_name'] ?? '') ?></div>
                            <?php if (!empty($t['designation'])): ?>
                                <small class="text-muted"><?= htmlspecialchars($t['designation']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <small class="text-muted"><?= htmlspecialchars(substr($t['review'] ?? $t['message'] ?? '', 0, 90)) ?>...</small>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= ($t['rating'] ?? 0) ? 'text-warning' : 'text-muted opacity-25' ?>" style="font-size:.8rem;"></i>
                                <?php endfor; ?>
                            </div>
                            <small class="text-muted"><?= (int)($t['rating'] ?? 0) ?>/5</small>
                        </td>
                        <td class="text-center">
                            <?php if (($t['status'] ?? '') === 'approved'): ?>
                                <span class="badge bg-success">Approved</span>
                            <?php elseif (($t['status'] ?? '') === 'pending'): ?>
                                <span class="badge bg-warning text-dark">Pending</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Rejected</span>
                            <?php endif; ?>
                        </td>
                        <td class="small text-muted"><?= !empty($t['created_at']) ? date('d M Y', strtotime($t['created_at'])) : '—' ?></td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>/admin/testimonials/<?= e($t['id']) ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                <a href="<?= BASE_URL ?>/admin/testimonials/<?= e($t['id']) ?>/edit" class="btn btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                <?php if (($t['status'] ?? '') === 'pending'): ?>
                                    <a href="<?= BASE_URL ?>/admin/testimonials/<?= e($t['id']) ?>/approve" class="btn btn-outline-success" title="Approve"><i class="fas fa-check"></i></a>
                                <?php endif; ?>
                                <a href="<?= BASE_URL ?>/admin/testimonials/<?= e($t['id']) ?>/delete" class="btn btn-outline-danger" data-aps-confirm="Delete this testimonial?" title="Delete"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($list)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-quote-left fa-3x text-muted mb-3 d-block opacity-50"></i>
                            <h5 class="text-muted">No testimonials found</h5>
                            <p class="text-muted small mb-3">Collect customer testimonials to build social proof and trust.</p>
                            <a href="<?= BASE_URL ?>/admin/testimonials/create" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Add First Testimonial
                            </a>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function () {
    var si = document.getElementById('testimonialSearch');
    var sf = document.getElementById('testimonialStatusFilter');
    function apply() {
        var q  = si ? si.value.toLowerCase() : '';
        var st = sf ? sf.value : '';
        document.querySelectorAll('#testimonialsTable .testimonial-row').forEach(function (r) {
            var mT = !q  || r.textContent.toLowerCase().includes(q);
            var mS = !st || r.getAttribute('data-status') === st;
            r.style.display = (mT && mS) ? '' : 'none';
        });
    }
    if (si) si.addEventListener('input', apply);
    if (sf) sf.addEventListener('change', apply);
})();
</script>