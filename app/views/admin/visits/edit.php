<?php
$page_title = $page_title ?? 'Edit Visit';
$page_heading = $page_heading ?? 'Edit Visit';
$visit = $visit ?? [];
$users = $users ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><i class="fas fa-edit me-2"></i>Edit Visit</h2>
            <p class="text-muted mb-0">Visit #<?= $visit['id'] ?? 0 ?></p>
        </div>
        <a href="<?= BASE_URL ?>/admin/visits" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Back to Visits
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/visits/update">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token ?? $_SESSION['csrf_token'] ?? ''; ?>">
                <input type="hidden" name="visit_id" value="<?= $visit['id'] ?? 0 ?>">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($visit['customer_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Assigned To</label>
                        <select name="assigned_to" class="form-select">
                            <option value="">Unassigned</option>
                            <?php foreach ($users as $u): ?>
                                <option value="<?= $u['id'] ?>" <?= ($visit['assigned_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= htmlspecialchars($u['name'] ?? '') ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Visit Date</label>
                        <input type="date" name="visit_date" class="form-control" value="<?= isset($visit['visit_date']) ? date('Y-m-d', strtotime($visit['visit_date'])) : '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Visit Time</label>
                        <input type="time" name="visit_time" class="form-control" value="<?= $visit['visit_time'] ?? '' ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <?php foreach (['scheduled' => 'Scheduled', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'rescheduled' => 'Rescheduled', 'no_show' => 'No Show'] as $val => $lbl): ?>
                                <option value="<?= $val ?>" <?= ($visit['status'] ?? '') === $val ? 'selected' : '' ?>><?= $lbl ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($visit['notes'] ?? '') ?></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Visit</button>
                        <a href="<?= BASE_URL ?>/admin/visits" class="btn btn-secondary">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/admin.php';
