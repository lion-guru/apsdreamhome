<?php
$blogList    = $blogs ?? [];
$total       = count($blogList);
$published   = 0; $draft = 0; $archived = 0; $totalViews = 0;
foreach ($blogList as $b) {
    $st = $b['status'] ?? 'draft';
    if ($st === 'published') { $published++; }
    elseif ($st === 'draft') { $draft++; }
    else { $archived++; }
    $totalViews += (int)($b['views'] ?? 0);
}
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-blog text-primary me-2"></i>Blog Manager</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item active">Blog</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/pages"        class="btn btn-outline-secondary btn-sm"><i class="fas fa-file me-1"></i>Pages</a>
        <a href="<?= BASE_URL ?>/admin/news"          class="btn btn-outline-secondary btn-sm"><i class="fas fa-newspaper me-1"></i>News</a>
        <a href="<?= BASE_URL ?>/admin/gallery"       class="btn btn-outline-info btn-sm"><i class="fas fa-images me-1"></i>Gallery</a>
        <a href="<?= BASE_URL ?>/admin/blogs/create"  class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Post</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0d6efd;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-blog text-primary"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $total ?></div>
                <div class="text-muted small">Total Posts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #198754;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-success bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-check-circle text-success"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $published ?></div>
                <div class="text-muted small">Published</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #ffc107;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-warning bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-pencil-alt text-warning"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $draft ?></div>
                <div class="text-muted small">Drafts</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0dcaf0;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-info bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-eye text-info"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= number_format($totalViews) ?></div>
                <div class="text-muted small">Total Views</div>
            </div>
        </div>
    </div>
</div>

<!-- Blogs Table -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="mb-0 fw-bold">All Blog Posts</h6>
        <div class="d-flex gap-2">
            <input type="text" id="blogSearch" class="form-control form-control-sm" placeholder="Search posts..." style="width:200px;">
            <select id="blogStatusFilter" class="form-select form-select-sm" style="width:140px;">
                <option value="">All Status</option>
                <option value="published">Published</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
            </select>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="blogsTable">
                <thead style="background:#f8fafc;">
                    <tr>
                        <th class="px-4">Title</th>
                        <th>Category</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Views</th>
                        <th>Published Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($blogList as $blog): ?>
                    <tr class="blog-row" data-status="<?= htmlspecialchars($blog['status'] ?? 'draft') ?>">
                        <td class="px-4">
                            <div class="fw-semibold text-dark"><?= htmlspecialchars($blog['title'] ?? '') ?></div>
                            <?php if (!empty($blog['meta_description'])): ?>
                                <small class="text-muted"><?= htmlspecialchars(substr($blog['meta_description'] ?? '', 0, 80)) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($blog['category'])): ?>
                                <span class="badge bg-secondary bg-opacity-15 text-secondary"><?= htmlspecialchars(ucfirst($blog['category'])) ?></span>
                            <?php else: ?>
                                <span class="text-muted small">—</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($blog['status'] === 'published'): ?>
                                <span class="badge bg-success">Published</span>
                            <?php elseif ($blog['status'] === 'draft'): ?>
                                <span class="badge bg-warning">Draft</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Archived</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center fw-semibold"><?= number_format($blog['views'] ?? 0) ?></td>
                        <td class="small text-muted"><?= !empty($blog['created_at']) ? date('d M Y', strtotime($blog['created_at'])) : '—' ?></td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>/admin/blogs/<?= e($blog['id']) ?>" class="btn btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/blogs/<?= e($blog['id']) ?>/edit" class="btn btn-outline-warning" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/blog/<?= e($blog['slug'] ?? $blog['id']) ?>" class="btn btn-outline-info" title="View Public" target="_blank">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <a href="<?= BASE_URL ?>/admin/blogs/<?= e($blog['id']) ?>/delete" class="btn btn-outline-danger" data-aps-confirm="Delete this post?" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($blogList)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3 d-block opacity-50"></i>
                            <h5 class="text-muted">No blog posts yet</h5>
                            <p class="text-muted small mb-3">Publish your first blog post to engage customers and boost SEO.</p>
                            <a href="<?= BASE_URL ?>/admin/blogs/create" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Create First Post
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
    var si = document.getElementById('blogSearch');
    var sf = document.getElementById('blogStatusFilter');
    function applyFilters() {
        var q  = si ? si.value.toLowerCase() : '';
        var st = sf ? sf.value : '';
        document.querySelectorAll('#blogsTable .blog-row').forEach(function (r) {
            var matchText   = !q  || r.textContent.toLowerCase().includes(q);
            var matchStatus = !st || r.getAttribute('data-status') === st;
            r.style.display = (matchText && matchStatus) ? '' : 'none';
        });
    }
    if (si) si.addEventListener('input', applyFilters);
    if (sf) sf.addEventListener('change', applyFilters);
})();
</script>
