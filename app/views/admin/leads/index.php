<!-- Leads Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-1">Lead Management</h1>
        <p class="text-muted mb-0">Manage and track all leads</p>
    </div>
    <a href="<?php echo BASE_URL; ?>/admin/leads/create" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Add Lead
    </a>
</div>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo $success; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 text-primary mb-0"><?php echo count($leads ?? []); ?></div>
                <small class="text-muted">Total Leads</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 text-success mb-0">0</div>
                <small class="text-muted">Converted</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 text-warning mb-0">0</div>
                <small class="text-muted">In Progress</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
                <div class="h3 text-info mb-0">0</div>
                <small class="text-muted">New Today</small>
            </div>
        </div>
    </div>
</div>

<!-- Leads Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 ps-4">Lead</th>
                        <th class="border-0">Contact</th>
                        <th class="border-0">Property</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Date</th>
                        <th class="border-0 text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($leads)): ?>
                    <?php foreach ($leads as $lead): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-semibold"><?php echo htmlspecialchars($lead['name'] ?? 'Unknown'); ?></div>
                        </td>
                        <td>
                            <div><i class="fas fa-envelope text-muted me-1"></i> <?php echo htmlspecialchars($lead['email'] ?? ''); ?></div>
                            <small><i class="fas fa-phone text-muted me-1"></i> <?php echo htmlspecialchars($lead['phone'] ?? 'N/A'); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($lead['property_name'] ?? 'General Inquiry'); ?></td>
                        <td>
                            <span class="badge bg-<?php echo ($lead['status'] ?? 'new') === 'converted' ? 'success' : (($lead['status'] ?? 'new') === 'contacted' ? 'warning' : 'info'); ?>">
                                <?php echo ucfirst($lead['status'] ?? 'new'); ?>
                            </span>
                        </td>
                        <td><?php echo date('M d, Y', strtotime($lead['created_at'] ?? 'now')); ?></td>
                        <td class="text-end pe-4">
                            <a href="<?php echo BASE_URL; ?>/admin/leads/<?php echo $lead['id']; ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                            <a href="<?php echo BASE_URL; ?>/admin/leads/<?php echo $lead['id']; ?>/edit" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                            <a href="<?php echo BASE_URL; ?>/admin/leads/<?php echo $lead['id']; ?>/destroy" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this lead?');"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">No leads found</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
