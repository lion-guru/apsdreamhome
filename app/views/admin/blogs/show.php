<?php
/**
 * S471: the admin blog post preview.
 *
 * The admin blogs desk had no single-post route at all - the list page's
 * "eye" button pointed at /admin/blogs/{id}, which was never registered, so
 * it 404'd.  This view is the preview the admin actually wanted: it shows a
 * DRAFT too, which the public /blog/{slug} page cannot (that route only
 * serves published posts).
 */
$blog = $blog ?? [];
$status = $blog['status'] ?? 'draft';
$badge = ['draft' => 'secondary', 'published' => 'success', 'archived' => 'dark'][$status] ?? 'secondary';
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <?= htmlspecialchars($blog['title'] ?? 'Blog Post') ?>
                        <span class="badge bg-<?= $badge ?> ms-2"><?= htmlspecialchars($status) ?></span>
                    </h5>
                    <div>
                        <a href="<?= BASE_URL ?>/admin/blog/<?= (int)($blog['id'] ?? 0) ?>/edit" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="<?= BASE_URL ?>/admin/blog" class="btn btn-sm btn-outline-secondary">Back to list</a>
                        <?php if ($status === 'published' && !empty($blog['slug'])): ?>
                            <a href="<?= BASE_URL ?>/blog/<?= rawurlencode((string)$blog['slug']) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-outline-success">
                                <i class="fas fa-external-link-alt"></i> View live
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body aps-cp-card-body">
                    <?php if (!empty($blog['featured_image'])): ?>
                        <img src="<?= BASE_URL ?>/assets/images/blogs/<?= htmlspecialchars((string)$blog['featured_image']) ?>"
                             class="img-fluid rounded mb-3" alt="<?= htmlspecialchars((string)($blog['title'] ?? 'Blog image')) ?>" loading="lazy">
                    <?php elseif (!empty($blog['image'])): ?>
                        <img src="<?= BASE_URL ?>/assets/images/blogs/<?= htmlspecialchars((string)$blog['image']) ?>"
                             class="img-fluid rounded mb-3" alt="<?= htmlspecialchars((string)($blog['title'] ?? 'Blog image')) ?>" loading="lazy">
                    <?php endif; ?>

                    <?php if (!empty($blog['excerpt'])): ?>
                        <p class="lead"><?= htmlspecialchars((string)$blog['excerpt']) ?></p>
                    <?php endif; ?>

                    <div class="mb-3">
                        <div class="text-muted small mb-1">Content</div>
                        <div class="border rounded p-3 bg-light" style="white-space: pre-wrap;"><?= htmlspecialchars((string)($blog['content'] ?? '')) ?></div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-muted small">Slug</div>
                            <div><?= htmlspecialchars((string)($blog['slug'] ?? '-')) ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Category</div>
                            <div><?= htmlspecialchars((string)($blog['category'] ?? '-')) ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Views</div>
                            <div><?= (int)($blog['views'] ?? 0) ?></div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-muted small">Created</div>
                            <div><?= htmlspecialchars((string)($blog['created_at'] ?? '-')) ?></div>
                        </div>
                    </div>

                    <?php if (!empty($blog['meta_title']) || !empty($blog['meta_description'])): ?>
                        <hr>
                        <div class="text-muted small mb-1">SEO</div>
                        <div><strong>Meta title:</strong> <?= htmlspecialchars((string)($blog['meta_title'] ?? '-')) ?></div>
                        <div><strong>Meta description:</strong> <?= htmlspecialchars((string)($blog['meta_description'] ?? '-')) ?></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
