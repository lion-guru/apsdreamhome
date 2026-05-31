<?php $pageTitle = 'Edit Visit'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-edit me-2"></i>Edit Visit</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/visits">Visits</a></li>
                    <li class="breadcrumb-item"><a href="/admin/visits/show/<?= $visit['id'] ?? 0 ?>">#<?= $visit['id'] ?? 0 ?></a></li>
                    <li class="breadcrumb-item active">Edit</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="/admin/visits/update/<?= $visit['id'] ?? 0 ?>">
                <div class="row g-3">
                    <div class="col-md-6"><label class="form-label">Customer</label><select name="customer_id" class="form-select"><option value="">Select</option><?php foreach ($users as $c): ?><option value="<?= $c['id'] ?>" <?= ($visit['customer_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= $c['name'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select"><option value="">Unassigned</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>" <?= ($visit['assigned_to'] ?? '') == $u['id'] ? 'selected' : '' ?>><?= $u['name'] ?? $u['username'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-6"><label class="form-label">Scheduled Date & Time</label><input type="datetime-local" name="scheduled_at" class="form-control" value="<?= isset($visit['scheduled_at']) ? date('Y-m-d\TH:i', strtotime($visit['scheduled_at'])) : '' ?>"></div>
                    <div class="col-md-3"><label class="form-label">Status</label><select name="status" class="form-select"><option value="scheduled" <?= ($visit['status'] ?? '') === 'scheduled' ? 'selected' : '' ?>>Scheduled</option><option value="completed" <?= ($visit['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option><option value="cancelled" <?= ($visit['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Cancelled</option></select></div>
                    <div class="col-md-3"><label class="form-label">Purpose</label><select name="purpose" class="form-select"><option value="site_visit" <?= ($visit['purpose'] ?? '') === 'site_visit' ? 'selected' : '' ?>>Site Visit</option><option value="meeting" <?= ($visit['purpose'] ?? '') === 'meeting' ? 'selected' : '' ?>>Meeting</option><option value="paperwork" <?= ($visit['purpose'] ?? '') === 'paperwork' ? 'selected' : '' ?>>Paperwork</option></select></div>
                    <div class="col-12"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="3"><?= $visit['notes'] ?? '' ?></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Update Visit</button> <a href="/admin/visits" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
