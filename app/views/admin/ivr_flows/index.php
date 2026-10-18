<?php
/**
 * IVR Flows Management
 * S347
 */
$page_title = $page_title ?? 'IVR Flows';
$flows = $flows ?? [];
?>

<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="fas fa-sitemap me-2 text-primary"></i>IVR Flows</h4>
            <a href="/admin/ivr-flows/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Create Flow
            </a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body p-0">
                <?php if (empty($flows)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-sitemap fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No IVR Flows Yet</h5>
                        <p class="text-muted">Create your first IVR flow to start routing calls</p>
                        <a href="/admin/ivr-flows/create" class="btn btn-primary mt-2">Create Flow</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Description</th>
                                    <th>Steps</th>
                                    <th>Calls</th>
                                    <th>Status</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($flows as $f): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($f['name']) ?></strong>
                                    </td>
                                    <td class="text-muted" style="max-width: 200px;">
                                        <?= htmlspecialchars(substr($f['description'] ?? '', 0, 100)) ?><?= strlen($f['description'] ?? '') > 100 ? '...' : '' ?>
                                    </td>
                                    <td><?= $f['step_count'] ?? 0 ?></td>
                                    <td><?= $f['call_count'] ?? 0 ?></td>
                                    <td>
                                        <span class="badge bg-<?= ($f['is_active'] ?? 0) ? 'success' : 'secondary' ?>">
                                            <?= ($f['is_active'] ?? 0) ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                    <td><?= date('M d, Y', strtotime($f['created_at'])) ?></td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="/admin/ivr-flows/<?= $f['id'] ?>/steps" class="btn btn-outline-primary" title="Steps">
                                                <i class="fas fa-list"></i>
                                            </a>
                                            <a href="/admin/ivr-flows/<?= $f['id'] ?>/edit" class="btn btn-outline-secondary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="/admin/ivr-flows/<?= $f['id'] ?>/delete" class="d-inline" onsubmit="return confirm('Delete this flow?')">
                                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                                                <button type="submit" class="btn btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
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
</div>