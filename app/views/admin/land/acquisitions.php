<?php
$page_title = $page_title ?? 'Land Acquisitions';
$acquisitions = $acquisitions ?? [];
$total_acquisitions = $total_acquisitions ?? 0;
$total_area = $total_area ?? 0;
$total_cost = $total_cost ?? 0;
$filters = $filters ?? ['status' => '', 'land_type' => ''];
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Land Acquisitions</h1>
            <p class="text-muted">Track land acquisition transactions</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-tree fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Acquisitions</h6>
                            <h3 class="mb-0"><?php echo e($total_acquisitions); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-vector-square fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Area</h6>
                            <h3 class="mb-0"><?php echo number_format($total_area, 2); ?> <small>sqft</small></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-rupee-sign fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Cost</h6>
                            <h3 class="mb-0">₹<?php echo number_format($total_cost, 2); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" action="<?php echo BASE_URL; ?>/admin/land/acquisitions" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-auto">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="in_progress" <?php echo ($filters['status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="registered" <?php echo ($filters['status'] ?? '') === 'registered' ? 'selected' : ''; ?>>Registered</option>
                        <option value="mutated" <?php echo ($filters['status'] ?? '') === 'mutated' ? 'selected' : ''; ?>>Mutated</option>
                        <option value="closed" <?php echo ($filters['status'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        <option value="cancelled" <?php echo ($filters['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">Mutation Status</label>
                    <select name="mutation_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="not_started" <?php echo ($filters['mutation_status'] ?? '') === 'not_started' ? 'selected' : ''; ?>>Not Started</option>
                        <option value="applied" <?php echo ($filters['mutation_status'] ?? '') === 'applied' ? 'selected' : ''; ?>>Applied</option>
                        <option value="in_progress" <?php echo ($filters['mutation_status'] ?? '') === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                        <option value="completed" <?php echo ($filters['mutation_status'] ?? '') === 'completed' ? 'selected' : ''; ?>>Completed</option>
                        <option value="rejected" <?php echo ($filters['mutation_status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?php echo BASE_URL; ?>/admin/land/acquisitions" class="btn btn-sm btn-outline-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Acquisition Records</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Acq #</th>
                            <th>Location</th>
                            <th>Total Area</th>
                            <th>Acquired Area</th>
                            <th>Cost</th>
                            <th>Total Consideration</th>
                            <th>Advance Paid</th>
                            <th>Balance</th>
                            <th>Mutation Status</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($acquisitions)): ?>
                        <tr>
                            <td colspan="13" class="text-center py-5 text-muted">
                                <i class="fas fa-tree fa-3x d-block mb-3"></i>
                                No land acquisition records found
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($acquisitions as $a): ?>
                        <tr>
                            <td class="ps-4"><strong><?php echo $a['acquisition_number'] ?? '#' . $a['id']; ?></strong></td>
                            <td>
                                <?php echo $a['location'] ?? ''; ?>
                                <?php if (!empty($a['village'])): ?>
                                <br><small class="text-muted"><?php echo e($a['village']); ?>, <?php echo $a['tehsil'] ?? ''; ?></small>
                                <?php endif; ?>
                            </td>
                            <td><?php echo number_format($a['total_area_sqft'] ?? 0, 2); ?> <small>sqft</small></td>
                            <td><?php echo number_format($a['acquired_area_sqft'] ?? 0, 2); ?> <small>sqft</small></td>
                            <td class="fw-bold">₹<?php echo number_format($a['acquisition_cost'] ?? 0, 2); ?></td>
                            <td>₹<?php echo number_format($a['total_consideration'] ?? 0, 2); ?></td>
                            <td>₹<?php echo number_format($a['advance_paid'] ?? 0, 2); ?></td>
                            <td>₹<?php echo number_format($a['balance_amount'] ?? 0, 2); ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($a['mutation_status'] ?? '') === 'completed' ? 'success' : (($a['mutation_status'] ?? '') === 'in_progress' ? 'primary' : (($a['mutation_status'] ?? '') === 'applied' ? 'info' : (($a['mutation_status'] ?? '') === 'rejected' ? 'danger' : 'secondary'))); ?>-subtle text-<?php echo ($a['mutation_status'] ?? '') === 'completed' ? 'success' : (($a['mutation_status'] ?? '') === 'in_progress' ? 'primary' : (($a['mutation_status'] ?? '') === 'applied' ? 'info' : (($a['mutation_status'] ?? '') === 'rejected' ? 'danger' : 'secondary'))); ?> rounded-pill px-3">
                                    <?php echo ucfirst(str_replace('_', ' ', $a['mutation_status'] ?? 'not_started')); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo ($a['status'] ?? '') === 'in_progress' ? 'primary' : (($a['status'] ?? '') === 'registered' ? 'info' : (($a['status'] ?? '') === 'mutated' ? 'success' : (($a['status'] ?? '') === 'closed' ? 'secondary' : 'danger'))); ?>-subtle text-<?php echo ($a['status'] ?? '') === 'in_progress' ? 'primary' : (($a['status'] ?? '') === 'registered' ? 'info' : (($a['status'] ?? '') === 'mutated' ? 'success' : (($a['status'] ?? '') === 'closed' ? 'secondary' : 'danger'))); ?> rounded-pill px-3">
                                    <?php echo ucfirst(str_replace('_', ' ', $a['status'] ?? 'in_progress')); ?>
                                </span>
                            </td>
                            <td><?php echo $a['created_at'] ?? '-'; ?></td>
                            <td class="text-end pe-4">
                                <a href="<?php echo BASE_URL; ?>/admin/land/acquisitions/<?php echo e($a['id']); ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
