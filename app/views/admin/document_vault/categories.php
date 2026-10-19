<?php
/** Admin Document Vault - Categories */

$page_title = $page_title ?? 'Document Categories';
$categories = $categories ?? [];
?>

<style nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>"></style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-tags me-2 text-primary"></i>Document Categories</h4>
            <p class="text-muted mb-0 small">Manage document categories and settings</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/document-vault/categories" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Category
        </a>
    </div>

    <!-- Categories Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <?php if (empty($categories)): ?>
            <div class="text-center py-5">
                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Categories</h5>
                <p class="text-muted small mb-3">Create your first document category</p>
                <a href="<?= BASE_URL ?>/admin/document-vault/categories" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Add Category</a>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px">ID</th>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Description</th>
                            <th style="width: 100px">Mandatory</th>
                            <th style="width: 100px">Retention</th>
                            <th style="width: 150px">Types / Size</th>
                            <th style="width: 100px">Expiry</th>
                            <th style="width: 100px">Status</th>
                            <th style="width: 150px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $cat): 
                            $allowedTypes = $cat['allowed_types'] ? json_decode($cat['allowed_types'], true) : [];
                            $typesStr = $allowedTypes ? implode(', ', array_map('strtoupper', $allowedTypes)) : 'Any';
                        ?>
                        <tr>
                            <td><?= $cat['id'] ?></td>
                            <td>
                                <div class="fw-semibold"><?= htmlspecialchars($cat['name']) ?></div>
                                <div class="small text-muted">Code: <code><?= htmlspecialchars($cat['code']) ?></code></div>
                            </td>
                            <td><?= htmlspecialchars($cat['description'] ?? '—') ?></td>
                            <td>
                                <span class="badge bg-<?= $cat['is_mandatory'] ? 'success' : 'secondary' ?>">
                                    <?= $cat['is_mandatory'] ? 'Yes' : 'No' ?>
                                </span>
                            </td>
                            <td><?= $cat['retention_years'] ?? 7 ?> years</td>
                            <td>
                                <div class="small"><?= htmlspecialchars(implode(', ', array_map('strtoupper', json_decode($cat['allowed_types'] ?? '[]', true) ?: [])) ?: 'Any') ?></div>
                                <div class="small text-muted">Max: <?= $cat['max_size_mb'] ?? 10 ?> MB</div>
                            </td>
                            <td>
                                <?php if ($cat['requires_expiry']): ?>
                                    <span class="badge bg-warning text-dark">Required</span>
                                    <div class="small text-muted">Alert: <?= $cat['expiry_alert_days'] ?? 30 ?> days</div>
                                <?php else: ?>
                                    <span class="badge bg-secondary">No Expiry</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?= $cat['is_active'] ? 'success' : 'secondary' ?>">
                                    <?= $cat['is_active'] ? 'Active' : 'Inactive' ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/admin/document-vault/categories/edit/<?= $cat['id'] ?>" class="btn btn-outline-secondary" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="<?= BASE_URL ?>/admin/document-vault/categories/delete/<?= $cat['id'] ?>" class="btn btn-outline-danger" onclick="return confirm('Delete this category?')"><i class="fas fa-trash"></i></a>
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
</div>