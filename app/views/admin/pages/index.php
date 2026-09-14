<?php $page_title = $page_title ?? "CMS Pages"; $pages = $pages ?? []; ?>
<div class="container-fluid py-4">
    <div class="row"><div class="col-12">
        <div class="card aps-cp-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i><?= $page_title ?></h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <?php if (empty($pages)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No pages found</h5>
                        <p class="text-muted mb-3">Create CMS pages for your website content like About Us, Terms of Service, and Privacy Policy.</p>
                        <a href="<?= BASE_URL ?>/admin/pages/create" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i> Create Page
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Slug</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pages as $p): ?>
                                <tr>
                                    <td><?= $p['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($p['title'] ?? '') ?></strong></td>
                                    <td><code>/<?= htmlspecialchars($p['slug'] ?? '') ?></code></td>
                                    <td>
                                        <?php if (($p['status'] ?? '') == 'published'): ?>
                                            <span class="badge bg-success">Published</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">Draft</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($p['updated_at'] ?? '') ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/pages/edit/<?= $p['id'] ?>" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <?php
                                        // Preview links for CMS pages: legal pages go to /legal/{slug}, others go to /{slug}
                                        $legalSlugs = ['terms-conditions','privacy-policy','refund-policy','disclaimer','cancellation-policy','associate-rules','services','legal-services','legal-documents'];
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
                                        <a href="<?= $previewUrl ?>" class="btn btn-sm btn-outline-secondary" target="_blank" title="Preview"><i class="fas fa-external-link-alt"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div></div>
</div>
