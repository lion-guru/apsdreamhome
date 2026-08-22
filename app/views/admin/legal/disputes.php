<?php
$page_title = $page_title ?? 'Legal Disputes';
$disputes = $disputes ?? [];
$total = $total ?? 0;
$open = $open ?? 0;
$resolved = $resolved ?? 0;
?>
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-2">Legal Disputes</h1>
            <p class="text-muted">Track and manage legal disputes</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-primary bg-opacity-10 text-primary rounded p-3">
                                <i class="fas fa-balance-scale fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Disputes</h6>
                            <h3 class="mb-0"><?php echo e($total); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-warning bg-opacity-10 text-warning rounded p-3">
                                <i class="fas fa-folder-open fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Open</h6>
                            <h3 class="mb-0"><?php echo e($open); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-success bg-opacity-10 text-success rounded p-3">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Resolved</h6>
                            <h3 class="mb-0"><?php echo e($resolved); ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 me-3">
                            <div class="bg-info bg-opacity-10 text-info rounded p-3">
                                <i class="fas fa-spinner fa-2x"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">In Progress</h6>
                            <h3 class="mb-0"><?php echo $total - $open - $resolved; ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="fas fa-list me-2"></i>All Disputes</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Title</th>
                            <th>Parties</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Assigned To</th>
                            <th>Filed Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($disputes)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-balance-scale fa-3x d-block mb-3"></i>
                                No disputes found
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($disputes as $i => $d): ?>
                        <tr>
                            <td class="ps-4"><?php echo $i + 1; ?></td>
                            <td><strong><?php echo $d['title'] ?? ''; ?></strong></td>
                            <td><small><?php echo $d['party_a'] ?? ''; ?> <i class="fas fa-vs text-muted mx-1"></i> <?php echo $d['party_b'] ?? ''; ?></small></td>
                            <td><span class="badge bg-info-subtle text-info rounded-pill px-3"><?php echo $d['dispute_type'] ?? '-'; ?></span></td>
                            <td>
                                <span class="badge bg-<?php echo ($d['status'] ?? '') === 'open' ? 'warning' : (($d['status'] ?? '') === 'resolved' ? 'success' : 'primary'); ?>-subtle text-<?php echo ($d['status'] ?? '') === 'open' ? 'warning' : (($d['status'] ?? '') === 'resolved' ? 'success' : 'primary'); ?> rounded-pill px-3">
                                    <?php echo ucfirst($d['status'] ?? 'open'); ?>
                                </span>
                            </td>
                            <td><?php echo $d['assigned_name'] ?? '-'; ?></td>
                            <td><?php echo $d['filed_date'] ?? '-'; ?></td>
                            <td class="text-end pe-4">
                                <a href="<?php echo BASE_URL; ?>/admin/legal/disputes/<?php echo e($d['id']); ?>" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
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
