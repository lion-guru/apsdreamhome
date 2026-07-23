<?php $pageTitle = 'Create Workflow'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-plus-circle me-2"></i>Create Workflow</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/workflows">Workflows</a></li>
                    <li class="breadcrumb-item active">Create</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form method="POST" action="<?= BASE_URL ?>/admin/workflows/create">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="row g-3">
                    <div class="col-md-8"><label class="form-label">Workflow Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
                    <div class="col-md-4"><label class="form-label">Type</label><select name="type" class="form-select"><option value="approval">Approval</option><option value="task">Task</option><option value="onboarding">Onboarding</option><option value="custom">Custom</option></select></div>
                    <div class="col-md-4"><label class="form-label">Assigned To</label><select name="assigned_to" class="form-select"><option value="">Unassigned</option><?php foreach ($users as $u): ?><option value="<?= $u['id'] ?>"><?= $u['name'] ?? $u['username'] ?></option><?php endforeach; ?></select></div>
                    <div class="col-md-4"><label class="form-label">Priority</label><select name="priority" class="form-select"><option value="medium">Medium</option><option value="high">High</option><option value="low">Low</option></select></div>
                    <div class="col-md-4"><label class="form-label">Due Date</label><input type="date" name="due_date" class="form-control"></div>
                    <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3"></textarea></div>
                    <div class="col-12"><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Create Workflow</button> <a href="<?= BASE_URL ?>/admin/workflows" class="btn btn-secondary">Cancel</a></div>
                </div>
            </form>
        </div>
    </div>
</div>
