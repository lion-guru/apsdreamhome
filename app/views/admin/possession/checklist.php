<?php
$page_title = $page_title ?? 'Handover Checklist';
$active_page = 'possession';
?>
<style>
    .checklist-item { display: flex; align-items: center; gap: 12px; padding: 10px 14px; border-bottom: 1px solid #eee; transition: background .2s; }
    .checklist-item:last-child { border-bottom: none; }
    .checklist-item:hover { background: #f8f9fa; }
    .checklist-item.completed { background: #f0fff4; }
    .checklist-item.completed:hover { background: #e8f8ef; }
    .checklist-item .item-name { flex: 1; font-size: 14px; }
    .checklist-item .item-name.completed-text { text-decoration: line-through; color: #6c757d; }
    .checklist-item .item-actions { display: flex; gap: 6px; }
    .checklist-edit-form { border-top: 2px dashed #dee2e6; padding-top: 16px; margin-top: 8px; }
</style>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-clipboard-check"></i> Handover Checklist - Booking #<?= htmlspecialchars($booking['booking_number'] ?? $booking['id']) ?></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?= BASE_URL ?>/admin/possession/show/<?= $booking['id'] ?>" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back to Possession</a>
    </div>
</div>

<?php if (isset($_SESSION['flash_message'])): ?>
    <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?> alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-list"></i> Checklist Items <small class="text-muted">(<?= count($checklist_items) ?> items)</small></h5>
                <span class="badge bg-info fs-6">
                    <?php $completed = 0; foreach ($checklist_items as $ci): if ($ci['is_completed']) $completed++; endforeach; ?>
                    <?= $completed ?>/<?= count($checklist_items) ?> Completed
                </span>
            </div>
            <div class="card-body p-0">
                <?php if (empty($checklist_items)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="fas fa-clipboard-list fa-3x mb-3"></i>
                        <p>No checklist items yet. Add items below.</p>
                    </div>
                <?php else: ?>
                    <?php $progress = count($checklist_items) > 0 ? round(($completed / count($checklist_items)) * 100) : 0; ?>
                    <div class="px-3 pt-3">
                        <div class="progress style-87912">
                            <div class="progress-bar bg-success style-8643"><?= $progress ?>%</div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <?php foreach ($checklist_items as $item): ?>
                            <div class="checklist-item <?= $item['is_completed'] ? 'completed' : '' ?>">
                                <form method="POST" action="<?= BASE_URL ?>/admin/possession/checklist/<?= $booking['id'] ?>/complete" class="d-flex align-items-center gap-3 w-100">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="item_id" value="<?= $item['id'] ?>">
                                    <input type="hidden" name="is_completed" value="<?= $item['is_completed'] ? 0 : 1 ?>">
                                    <button type="submit" class="btn btn-sm <?= $item['is_completed'] ? 'btn-success' : 'btn-outline-secondary' ?> style-46378">
                                        <i class="fas <?= $item['is_completed'] ? 'fa-check' : 'fa-times' ?>"></i>
                                    </button>
                                    <span class="item-name <?= $item['is_completed'] ? 'completed-text' : '' ?>"><?= htmlspecialchars($item['item_name'] ?? '') ?></span>
                                    <?php if ($item['is_completed'] && !empty($item['completed_at'])): ?>
                                        <small class="text-muted"><?= date('d M Y h:i A', strtotime($item['completed_at'])) ?></small>
                                    <?php endif; ?>
                                    <?php if ($item['is_completed']): ?>
                                        <span class="badge bg-success">Done</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Pending</span>
                                    <?php endif; ?>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-plus-circle"></i> Add Item</h5></div>
            <div class="card-body aps-cp-card-body">
                <form method="POST" action="<?= BASE_URL ?>/admin/possession/checklist/<?= $booking['id'] ?>/add">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="mb-2">
                        <label class="form-label">Item Name</label>
                        <input type="text" class="form-control" name="item_name" placeholder="e.g. Water connection checked" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus"></i> Add Item</button>
                </form>
            </div>
        </div>

        <div class="card aps-cp-card">
            <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-lightbulb"></i> Suggested Items</h5></div>
            <div class="card-body aps-cp-card-body">
                <ul class="list-unstyled mb-0">
                    <?php $suggested = [
                        'Water connection checked & functional',
                        'Electricity meter installed & functional',
                        'Boundary wall completed',
                        'Internal roads completed',
                        'Drainage system functional',
                        'Property number / Plot marking done',
                        'Gates & fencing installed',
                        'Landscaping completed',
                        'Street lighting functional',
                        'Common area amenities ready',
                        'Fire safety equipment in place',
                        'Property cleaned & ready',
                    ]; ?>
                    <?php $existingNames = array_map(function($ci) { return strtolower(trim($ci['item_name'])); }, $checklist_items); ?>
                    <?php foreach ($suggested as $s): ?>
                        <?php if (!in_array(strtolower($s), $existingNames)): ?>
                            <li class="mb-1">
                                <form method="POST" action="<?= BASE_URL ?>/admin/possession/checklist/<?= $booking['id'] ?>/add" class="style-35851">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="item_name" value="<?= htmlspecialchars($s ?? '') ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-success" aria-label="Add"><i class="fas fa-plus"></i></button>
                                    <small><?= htmlspecialchars($s ?? '') ?></small>
                                </form>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>
