<?php
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$templates = $templates ?? [];
$error = $error ?? null;
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fab fa-whatsapp me-2 text-success"></i>WhatsApp Templates</h4>
        <form method="post" action="<?php echo $base; ?>/admin/whatsapp/templates/sync" class="style-71727" onsubmit="return confirm('Sync templates from Meta Cloud API?')">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
            <button type="submit" class="btn btn-outline-primary">
                <i class="fas fa-sync me-1"></i> Sync from Meta
            </button>
        </form>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error ?? ''); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Language</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Usage</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($templates)): ?>
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x d-block mb-2"></i>
                                    No templates found.<br>
                                    <a href="<?php echo $base; ?>/admin/whatsapp/settings" class="btn btn-sm btn-outline-primary mt-2">Configure WhatsApp API</a>
                                    to sync templates from Meta.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($templates as $i => $t): ?>
                                <tr>
                                    <td><?php echo $i + 1; ?></td>
                                    <td><strong><?php echo htmlspecialchars($t['name'] ?? ''); ?></strong></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($t['category'] ?? 'general'); ?></span></td>
                                    <td><?php echo htmlspecialchars(strtoupper($t['language'] ?? 'en')); ?></td>
                                    <td><?php echo htmlspecialchars($t['template_type'] ?? 'text'); ?></td>
                                    <td>
                                        <?php if (($t['is_active'] ?? 0) == 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo (int)($t['usage_count'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars(date('d M Y', strtotime($t['created_at'] ?? 'now'))); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
