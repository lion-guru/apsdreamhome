<?php

/**
 * Marketplace Apps - APS Dream Home Admin
 */
$page_title = 'Marketplace Apps';
$page_description = 'Manage marketplace applications and integrations';

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><i class="fas fa-store me-2"></i>Marketplace Apps</h1>
            <p class="text-muted mb-0">Manage marketplace applications and integrations</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppModal">
            <i class="fas fa-plus me-1"></i>Add App
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>App Name</th>
                            <th>Provider</th>
                            <th>URL</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($apps ?? [])): ?>
                            <tr><td colspan="6" class="text-center text-muted py-5">
                                <i class="fas fa-store fa-3x text-muted mb-3"></i>
                                <h5>No Marketplace Apps</h5>
                                <p class="mb-3">Add your first marketplace app.</p>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppModal">
                                    <i class="fas fa-plus me-1"></i>Add App
                                </button>
                            </td></tr>
                        <?php else: ?>
                            <?php foreach ($apps as $a): ?>
                                <tr>
                                    <td><?= $a['id'] ?? '' ?></td>
                                    <td><strong><?= htmlspecialchars($a['app_name'] ?? '') ?></strong></td>
                                    <td><?= htmlspecialchars($a['provider'] ?? '—') ?></td>
                                    <td>
                                        <?php if (!empty($a['app_url'])): ?>
                                            <a href="<?= htmlspecialchars($a['app_url'] ?? '') ?>" target="_blank" rel="noopener">
                                                <?= htmlspecialchars(mb_strimwidth($a['app_url'], 0, 40, '...')) ?>
                                                <i class="fas fa-external-link-alt ms-1 small"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y', strtotime($a['created_at'] ?? 'now')) ?></td>
                                    <td>
                                        <a href="<?= htmlspecialchars($a['app_url'] ?? '#') ?>" target="_blank" class="btn btn-sm btn-outline-primary" <?= empty($a['app_url']) ? 'disabled' : '' ?>>
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
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

<!-- Add App Modal -->
<div class="modal fade" id="addAppModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?= BASE_URL ?>/admin/marketing/marketplace/store">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Add Marketplace App</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">App Name <span class="text-danger">*</span></label>
                        <input type="text" name="app_name" class="form-control" required placeholder="e.g. Google Analytics">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Provider</label>
                        <input type="text" name="provider" class="form-control" placeholder="e.g. Google">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">App URL</label>
                        <input type="url" name="app_url" class="form-control" placeholder="https://analytics.google.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Add App</button>
                </div>
            </form>
        </div>
    </div>
</div>
