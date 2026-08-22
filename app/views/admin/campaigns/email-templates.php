<?php
$page_title = $page_title ?? 'Email Templates';
$templates = $templates ?? [];
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-1"><i class="fas fa-envelope-open-text me-2 text-primary"></i>Email Templates</h2>
        <a href="<?php echo e($base); ?>/admin/campaigns" class="btn btn-outline-secondary">Back</a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (!empty($templates)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Template Name</th><th>Subject</th><th>Category</th><th>Status</th><th>Updated</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($templates as $t): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($t['template_name'] ?? $t['name'] ?? '-'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($t['subject'] ?? '-'); ?></td>
                                    <td><span class="badge bg-info"><?php echo htmlspecialchars($t['category'] ?? 'general'); ?></span></td>
                                    <td><span class="badge bg-<?php echo ($t['status'] ?? 'active') === 'active' ? 'success' : 'secondary'; ?>"><?php echo ucfirst($t['status'] ?? 'active'); ?></span></td>
                                    <td><?php echo isset($t['updated_at']) ? date('M d, Y', strtotime($t['updated_at'])) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No email templates found.</p>
                    <a href="<?php echo e($base); ?>/admin/emails/template-editor" class="btn btn-primary mt-2"><i class="fas fa-plus me-2"></i>Open Template Editor</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
