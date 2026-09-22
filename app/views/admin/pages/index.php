<?php
$page_title = $page_title ?? 'CMS Pages';
$pageList   = $pages ?? [];
$total      = count($pageList);
$published  = 0; $draft = 0;
foreach ($pageList as $p) {
    if (($p['status'] ?? '') === 'published') { $published++; } else { $draft++; }
}
$legalSlugs = ['terms-conditions','privacy-policy','refund-policy','disclaimer',
                'cancellation-policy','associate-rules','services','legal-services','legal-documents'];
?>

<!-- Page Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1 fw-bold"><i class="fas fa-file-alt text-primary me-2"></i>CMS Pages</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/erp">Dashboard</a></li>
                <li class="breadcrumb-item active">Pages</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/legal-pages"   class="btn btn-outline-warning btn-sm"><i class="fas fa-gavel me-1"></i>Legal Pages</a>
        <a href="<?= BASE_URL ?>/admin/blogs"          class="btn btn-outline-secondary btn-sm"><i class="fas fa-blog me-1"></i>Blog</a>
        <a href="<?= BASE_URL ?>/admin/site-content"   class="btn btn-outline-info btn-sm"><i class="fas fa-layer-group me-1"></i>Site Content</a>
        <a href="<?= BASE_URL ?>/admin/pages/create"   class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>New Page</a>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-radius:12px; border-top:4px solid #0d6efd;">
            <div class="card-body text-center py-4">
                <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center mb-2" style="width:48px;height:48px;">
                    <i class="fas fa-file-alt text-primary"></i>
                </div>
                <div class="fw-bold fs-3 text-dark"><?= $total ?></div>
                <div class="text-muted small">Total Pages</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4">
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
    <div class="col-6 col-md-4">
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
</div>

<!-- Pages Table -->
<div class="card border-0 shadow-sm" style="border-radius:12px;">
    <div class="card-header bg-white border-bottom py-3 px-4 d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold">All Pages</h6>
        <input type="text" id="pageSearch" class="form-control form-control-sm" placeholder="Search pages..." style="width:200px;">
    </div>
    <div class="card-body p-0">
        <?php if (empty($pageList)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3 d-block opacity-50"></i>
                <h5 class="text-muted">No pages found</h5>
                <p class="text-muted small mb-3">Create CMS pages for About Us, Terms of Service, Privacy Policy, etc.</p>
                <a href="<?= BASE_URL ?>/admin/pages/create" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i>Create Page
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="pagesTable">
                    <thead style="background:#f8fafc;">
                        <tr>
                            <th class="px-4">Title</th>
                            <th>Slug / URL</th>
                            <th class="text-center">Status</th>
                            <th>Last Updated</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pageList as $p): ?>
                            <?php
                            $slug = $p['slug'] ?? '';
                            if (in_array($slug, $legalSlugs)) {
                                $previewUrl = BASE_URL . '/legal/' . $slug;
                            } elseif ($slug === 'about-us') {
                                $previewUrl = BASE_URL . '/about';
                            } elseif ($slug === 'contact-us') {
                                $previewUrl = BASE_URL . '/contact';
                            } elseif ($slug === 'careers') {
                                $previewUrl = BASE_URL . '/careers';
                            } else {
                                $previewUrl = BASE_URL . '/' . $slug;
                            }
                            ?>
                            <tr class="page-row">
                                <td class="px-4 fw-semibold text-dark"><?= htmlspecialchars($p['title'] ?? '') ?></td>
                                <td>
                                    <code class="small text-muted">/<?= htmlspecialchars($slug) ?></code>
                                </td>
                                <td class="text-center">
                                    <?php if (($p['status'] ?? '') === 'published'): ?>
                                        <span class="badge bg-success">Published</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td class="small text-muted">
                                    <?= !empty($p['updated_at']) ? date('d M Y', strtotime($p['updated_at'])) : '—' ?>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/admin/pages/edit/<?= $p['id'] ?>" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="<?= $previewUrl ?>" class="btn btn-outline-info" title="Preview" target="_blank">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function () {
    var si = document.getElementById('pageSearch');
    if (!si) return;
    si.addEventListener('input', function () {
        var q = this.value.toLowerCase();
        document.querySelectorAll('#pagesTable .page-row').forEach(function (r) {
            r.style.display = r.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
})();
</script>
