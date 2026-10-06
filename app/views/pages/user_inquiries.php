<?php
$extraHead = '<style>
    .inquiry-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
</style>';
?>

<div class="container py-5">
    <?php $unreadCount = 0; foreach (($inquiries ?? []) as $qi) { if (empty($qi['is_read'])) $unreadCount++; } ?>
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3 class="mb-0"><i class="fas fa-envelope me-2 text-success"></i><?= __('user_inquiries_heading') ?>
            <?php if ($unreadCount > 0): ?><span class="badge bg-primary ms-2"><?= $unreadCount ?> new</span><?php endif; ?>
        </h3>
        <div class="d-flex gap-2 flex-wrap">
            <?php if ($unreadCount > 0): ?>
            <button type="button" class="btn btn-outline-primary btn-sm" onclick="markAllRead()">
                <i class="fas fa-check-double me-1"></i>Mark All Read (<?= $unreadCount ?>)
            </button>
            <?php endif; ?>
            <a href="<?php echo BASE_URL; ?>/user/inquiries/threads" class="btn btn-outline-success btn-sm">
                <i class="fas fa-comments me-1"></i>Property Message Threads
            </a>
        </div>
    </div>

    <?php if (empty($inquiries)): ?>
        <div class="card aps-cp-card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h5 class="text-muted"><?= __('user_inquiries_empty_title') ?></h5>
                <p class="text-muted"><?= __('user_inquiries_empty_desc') ?></p>
                <a href="<?php echo BASE_URL; ?>/properties" class="btn btn-primary">
                    <i class="fas fa-search me-2"></i><?= __('user_inquiries_browse_button') ?>
                </a>
            </div>
        </div>
    <?php else: ?>
        <div class="card aps-cp-card">
            <div class="card-body aps-cp-card-body">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th><?= __('user_inquiries_col_type') ?></th>
                                <th><?= __('user_inquiries_col_message') ?></th>
                                <th><?= __('user_inquiries_col_status') ?></th>
                                <th><?= __('user_inquiries_col_priority') ?></th>
                                <th><?= __('user_inquiries_col_date') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inquiries as $inq): ?>
                                <tr class="<?= empty($inq['is_read']) ? 'table-primary' : '' ?>">
                                    <td>
                                        <span class="badge bg-<?php echo ($inq['type'] ?? '') === 'property_listing' ? 'success' : 'info'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', __((!empty($inq['type']) ? 'inq_type_' . $inq['type'] : 'inq_type_general'), null, ucfirst(str_replace('_', ' ', $inq['type'] ?? 'General'))))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="mb-0">
                                            <?php echo htmlspecialchars(substr($inq['message'] ?? '', 0, 100)); ?>
                                            <?php if (strlen($inq['message'] ?? '') > 100): ?>...<?php endif; ?>
                                        </p>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($inq['status'] ?? 'new') {
                                            'new' => 'primary',
                                            'contacted' => 'info',
                                            'pending' => 'warning',
                                            'in_progress' => 'warning',
                                            'completed' => 'success',
                                            'cancelled' => 'danger',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo e($statusClass); ?>"><?php echo ucfirst(__('inq_status_' . ($inq['status'] ?? 'new'))); ?></span>
                                    </td>
                                    <td>
                                        <?php
                                        $priorityClass = match($inq['priority'] ?? 'medium') {
                                            'high' => 'danger',
                                            'medium' => 'warning',
                                            'low' => 'info',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo e($priorityClass); ?>"><?php echo e(ucfirst(__('priority_' . ($inq['priority'] ?? 'medium')))); ?></span>
                                    </td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($inq['created_at'])); ?>
                                        <br><small class="text-muted"><?php echo date('h:i A', strtotime($inq['created_at'])); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function markAllRead() {
    if (!confirm('Mark all inquiries as read?')) return;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '<?= $_SESSION["csrf_token"] ?? "" ?>';
    fetch('<?= BASE_URL ?>/user/inquiries/mark-all-read', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-Token': csrfToken
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Failed: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(e => alert('Error: ' + e.message));
}
</script>
