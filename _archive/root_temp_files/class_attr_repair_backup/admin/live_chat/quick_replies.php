<?php
$page_title = $page_title ?? 'Quick Replies';
$page_heading = $page_heading ?? 'Quick Reply Templates';
$content = $content ?? '';
$replies = $replies ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Quick Reply Templates</h2>
        <a href="<?= BASE_URL ?>/admin/live-chat" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Shortcut</th>
                            <th>Message</th>
                            <th>Category</th>
                            <th>Sort</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($replies)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <i class="fas fa-comment-dots fa-3x text-muted mb-3" class="style-82835"></i>
                                <h5 class="text-muted">No quick replies found</h5>
                                <p class="text-muted mb-3">Create quick reply templates to speed up live chat responses.</p>
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($replies as $r): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($r['title'] ?? '') ?></strong></td>
                                <td><code><?= htmlspecialchars($r['shortcut'] ?? '') ?></code></td>
                                <td><small><?= htmlspecialchars(substr($r['message'], 0, 100)) ?>...</small></td>
                                <td><span class="badge bg-light text-dark"><?= htmlspecialchars($r['category'] ?? '') ?></span></td>
                                <td><?= $r['sort_order'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
