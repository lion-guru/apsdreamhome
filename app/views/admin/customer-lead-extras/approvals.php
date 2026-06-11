<?php
// Session started by controller
$page_title = 'Lead Assignment Approvals';
$page_description = 'Manage lead assignment and reassignment approval requests';
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Lead Assignment Approvals</h1>
            <p class="text-muted">Manage lead assignment and reassignment approval requests</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-user-check fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Requests</h6>
                            <h3 class="mb-0"><?php echo $stats['total_approvals'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Pending Requests</h6>
                            <h3 class="mb-0"><?php echo $stats['pending_approvals'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Approved Requests</h6>
                            <h3 class="mb-0"><?php echo $stats['approved_approvals'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-times-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Rejected Requests</h6>
                            <h3 class="mb-0"><?php echo $stats['rejected_approvals'] ?? 0; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0">Assignment Approval Requests</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Assignment #</th>
                            <th>Lead Name</th>
                            <th>Requested By â†’ To</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Priority</th>
                            <th>Request Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($approvals)): ?>
                            <?php foreach ($approvals as $approval): ?>
                                <?php
                                // Status badge colors
                                $statusClass = '';
                                switch ($approval['status']) {
                                    case 'pending':
                                        $statusClass = 'bg-warning';
                                        break;
                                    case 'approved':
                                        $statusClass = 'bg-success';
                                        break;
                                    case 'rejected':
                                        $statusClass = 'bg-danger';
                                        break;
                                    default:
                                        $statusClass = 'bg-secondary';
                                }
                                
                                // Priority badge colors
                                $priorityClass = '';
                                switch ($approval['priority']) {
                                    case 'low':
                                        $priorityClass = 'bg-success';
                                        break;
                                    case 'medium':
                                        $priorityClass = 'bg-warning';
                                        break;
                                    case 'high':
                                        $priorityClass = 'bg-danger';
                                        break;
                                    case 'urgent':
                                        $priorityClass = 'bg-dark';
                                        break;
                                    default:
                                        $priorityClass = 'bg-secondary';
                                }
                                ?>
                                <tr>
                                    <td>#<?php echo $approval['id']; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <img src="<?= BASE_URL ?>/assets/img/default-avatar.png" alt="Avatar" class="rounded-circle" width="32" height="32" />
                                            </div>
                                            <div class="flex-grow-1 ms-2">
                                                <h6 class="mb-0"><?php echo htmlspecialchars($approval['lead_name'] ?? 'Unknown'); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($approval['lead_email'] ?? ''); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo htmlspecialchars($approval['requested_by_name'] ?? 'Unknown'); ?> â†’ 
                                            <?php echo htmlspecialchars($approval['requested_to_name'] ?? 'Unknown'); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo ucfirst($approval['request_type'] ?? 'Unknown'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $statusClass; ?>">
                                            <?php echo ucfirst($approval['status'] ?? 'Unknown'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $priorityClass; ?>">
                                            <?php echo ucfirst($approval['priority'] ?? 'Unknown'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y H:i', strtotime($approval['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/admin/customer-lead/approvals/<?php echo $approval['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <p class="text-muted mb-0">No approval requests found</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>