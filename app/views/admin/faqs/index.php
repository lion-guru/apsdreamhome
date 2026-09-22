<?php
$faqList    = $faqs ?? [];
$total      = count($faqList);
$active     = 0; $inactive = 0;
$categories = [];
foreach ($faqList as $f) {
    if (($f['status'] ?? 'active') === 'active') { $active++; } else { $inactive++; }
    $cat = $f['category'] ?? 'general';
    if (!isset($categories[$cat])) { $categories[$cat] = 0; }
    $categories[$cat]++;
}
arsort($categories);
$topCat = array_key_first($categories) ?? '';
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-question-circle text-primary me-2"></i>FAQ Manager</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item active">FAQs</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/testimonials" class="btn btn-outline-secondary btn-sm"><i class="fas fa-star me-1"></i>Testimonials</a>
        <a href="<?= BASE_URL ?>/admin/blogs"         class="btn btn-outline-secondary btn-sm"><i class="fas fa-blog me-1"></i>Blog</a>
        <a href="<?= BASE_URL ?>/admin/faqs/create"   class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New FAQ</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0d6efd;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-question-circle text-primary"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $total ?></div>
                <div class="text-muted small">Total FAQs</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #198754;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-check text-success"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $active ?></div>
                <div class="text-muted small">Active</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0dcaf0;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-layer-group text-info"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= count($categories) ?></div>
                <div class="text-muted small">Categories</div>
            </div>
        </div>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 fw-bold">All FAQs</h6>
        <div class="d-flex gap-2">
            <input type="text" id="faqSearch" class="form-control form-control-sm" placeholder="Search FAQs..." style="width:180px;">
            <?php if (!empty($categories)): ?>
            <select id="faqCatFilter" class="form-select form-select-sm" style="width:140px;">
                <option value="">All Categories</option>
                <?php foreach ($categories as $cat => $count): ?>
                    <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars(ucfirst($cat)) ?> (<?= $count ?>)</option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="faqsTable">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="px-4" style="width:40%;">Question</th>
                        <th>Category</th>
                        <th class="text-center">Order</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faqList as $faq): ?>
                    <tr class="faq-row" data-cat="<?= htmlspecialchars($faq['category'] ?? '') ?>">
                        <td class="px-4">
                            <div class="fw-semibold text-dark small"><?= htmlspecialchars($faq['question'] ?? '') ?></div>
                            <?php if (!empty($faq['answer'])): ?>
                                <small class="text-muted"><?= htmlspecialchars(substr(strip_tags($faq['answer'] ?? ''), 0, 80)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge bg-info bg-opacity-15 text-info"><?= htmlspecialchars(ucfirst($faq['category'] ?? 'general')) ?></span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-light text-dark border"><?= (int)($faq['display_order'] ?? 0) ?></span>
                        </td>
                        <td class="text-center">
                            <?php if (($faq['status'] ?? 'active') === 'active'): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>/admin/faqs/<?= e($faq['id']) ?>" class="btn btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
                                <a href="<?= BASE_URL ?>/admin/faqs/<?= e($faq['id']) ?>/edit" class="btn btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="<?= BASE_URL ?>/admin/faqs/<?= e($faq['id']) ?>/delete" class="btn btn-outline-danger" data-aps-confirm="Delete this FAQ?" title="Delete"><i class="fas fa-trash"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($faqList)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-question-circle fa-3x text-muted mb-3 d-block opacity-50"></i>
                            <h5 class="text-muted">No FAQs found</h5>
                            <p class="text-muted small mb-3">Add frequently asked questions to help customers make faster decisions.</p>
                            <a href="<?= BASE_URL ?>/admin/faqs/create" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Create First FAQ</a>
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
    var si = document.getElementById('faqSearch');
    var sc = document.getElementById('faqCatFilter');
    function apply() {
        var q  = si ? si.value.toLowerCase() : '';
        var ct = sc ? sc.value : '';
        document.querySelectorAll('#faqsTable .faq-row').forEach(function (r) {
            var mT = !q  || r.textContent.toLowerCase().includes(q);
            var mC = !ct || r.getAttribute('data-cat') === ct;
            r.style.display = (mT && mC) ? '' : 'none';
        });
    }
    if (si) si.addEventListener('input', apply);
    if (sc) sc.addEventListener('change', apply);
})();
</script>
