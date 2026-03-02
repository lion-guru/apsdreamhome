<?php require_once 'app/views/layouts/associate_header.php'; ?>

<div class="container-fluid mt-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Lead Management</h1>
        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addLeadModal">
            <i class="fas fa-plus mr-2"></i>Add New Lead
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Leads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['total'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">New Leads</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['new_leads'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-star fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Contacted</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['contacted'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-phone fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Converted</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $stats['converted'] ?? 0 ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Leads Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Recent Leads</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="leadsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Date Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($leads)): ?>
                            <?php foreach ($leads as $lead): ?>
                                <tr>
                                    <td><?= htmlspecialchars($lead['first_name'] . ' ' . $lead['last_name']) ?></td>
                                    <td><?= htmlspecialchars($lead['phone']) ?></td>
                                    <td><?= htmlspecialchars($lead['email']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= getStatusBadgeColor($lead['status']) ?>">
                                            <?= ucfirst($lead['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d M Y', strtotime($lead['created_at'])) ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-primary" title="Add Note">
                                            <i class="fas fa-sticky-note"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No leads found. Add your first lead!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Lead Modal -->
<div class="modal fade" id="addLeadModal" tabindex="-1" role="dialog" aria-labelledby="addLeadModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLeadModalLabel">Add New Lead</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="/associate/leads/add" method="POST">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="first_name">First Name *</label>
                            <input type="text" class="form-control" id="first_name" name="first_name" required>
                        </div>
                        <div class="form-group col-md-6">
                            <label for="last_name">Last Name</label>
                            <input type="text" class="form-control" id="last_name" name="last_name">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number *</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Lead</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
function getStatusBadgeColor($status) {
    switch ($status) {
        case 'new': return 'warning';
        case 'contacted': return 'info';
        case 'qualified': return 'primary';
        case 'converted': return 'success';
        case 'lost': return 'danger';
        default: return 'secondary';
    }
}
?>

<?php require_once 'app/views/layouts/footer.php'; ?>