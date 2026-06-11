<?php
$extraHead = '<style>
    .inquiry-card { border: none; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }
</style>';
?>

<div class="container py-5">
    <h3 class="mb-4"><i class="fas fa-envelope me-2 text-success"></i><?= __('user_inquiries_heading') ?></h3>

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
                                <tr>
                                    <td>
                                        <span class="badge bg-<?php echo ($inq['type'] ?? '') === 'property_listing' ? 'success' : 'info'; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', __((!empty($inq['type']) ? 'inq_type_' . $inq['type'] : 'inq_type_general'), null, ucfirst(str_replace('_', ' ', $inq['type'] ?? 'General'))))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <p class="mb-0" style="max-width: min(300px, 60vw);">
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
                                        <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst(__('inq_status_' . ($inq['status'] ?? 'new'))); ?></span>
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
                                        <span class="badge bg-<?php echo $priorityClass; ?>"><?php echo ucfirst(__('priority_' . ($inq['priority'] ?? 'medium'))); ?></span>
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
