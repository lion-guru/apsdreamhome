<?php $page_title = $page_title ?? 'Templates'; $page_heading = $page_heading ?? 'Demand Letter Templates'; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-file-alt me-2 text-primary"></i>Demand Letter Templates</h2>
        <a href="<?= BASE_URL ?>/admin/finance/template-form" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Template</a>
    </div>
    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr><th>Name</th><th>Type</th><th>Subject</th><th>Active</th><th>Created</th><th></th></tr>
                </thead>
                <tbody>
                <?php if (empty($templates)): ?>
                    <tr><td colspan="6" class="text-center text-muted py-4">No templates yet</td></tr>
                <?php else: foreach ($templates as $t): ?>
                    <tr>
                        <td class="fw-semibold"><?= htmlspecialchars($t['template_name'] ?? '-') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($t['template_type'] ?? '-') ?></span></td>
                        <td><?= htmlspecialchars($t['subject'] ?? '-') ?></td>
                        <td><?= !empty($t['active']) ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>' ?></td>
                        <td><?= htmlspecialchars($t['created_at'] ?? '-') ?></td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/finance/template-form?id=<?= (int)$t['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <form method="post" action="<?= BASE_URL ?>/admin/finance/template-delete" class="d-inline" onsubmit="return confirm('Delete this template?')">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
