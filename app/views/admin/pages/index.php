<?php $page_title = $page_title ?? "CMS Pages"; $pages = $pages ?? []; ?>
<div class="container-fluid py-4">
    <div class="row"><div class="col-12">
        <div class="card aps-cp-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i><?= $page_title ?></h5>
            </div>
            <div class="card-body aps-cp-card-body">
                <?php if (empty($pages)): ?>
                    <div class="alert alert-info"><i class="fas fa-info-circle me-1"></i>No pages found.</div>
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
                                        <a href="<?= BASE_URL ?>/admin/pages/edit/<?= $p['id'] ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/<?= htmlspecialchars($p['slug'] ?? '') ?>" class="btn btn-sm btn-outline-secondary" target="_blank"><i class="fas fa-external-link-alt"></i></a>
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
