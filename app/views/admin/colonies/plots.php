<?php $colony = $colony ?? []; $plots = $plots ?? []; ?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-map text-success me-2"></i>Plots in <?php echo htmlspecialchars($colony['name'] ?? ''); ?></h4>
        <div>
            <a href="<?php echo BASE_URL; ?>/admin/colonies/<?php echo $colony['id'] ?? 0; ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-eye me-1"></i>View Colony</a>
            <a href="<?php echo BASE_URL; ?>/admin/colonies" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
        </div>
    </div>

    <?php if (empty($plots)): ?>
    <div class="text-center py-5 bg-white rounded-3 shadow-sm">
        <i class="fas fa-map-marked-alt fa-4x text-muted mb-3"></i>
        <p class="text-muted">No plots assigned to this colony yet.</p>
        <a href="<?php echo BASE_URL; ?>/admin/plots/create?colony_id=<?php echo $colony['id'] ?? 0; ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i>Add Plot
        </a>
    </div>
    <?php else: ?>
    <div class="row mb-3">
        <div class="col-md-4"><a href="<?php echo BASE_URL; ?>/admin/plots/create?colony_id=<?php echo $colony['id'] ?? 0; ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add New Plot</a></div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="table-dark">
                <tr>
                    <th>Plot #</th>
                    <th>Block</th>
                    <th>Area (sqft)</th>
                    <th>Price (₹)</th>
                    <th>Rate/sqft</th>
                    <th>Status</th>
                    <th>Facing</th>
                    <th>Customer</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($plots as $p): ?>
                <tr>
                    <td><strong><?php echo htmlspecialchars($p['plot_number'] ?? 'N/A'); ?></strong></td>
                    <td><?php echo htmlspecialchars($p['block'] ?? '-'); ?></td>
                    <td><?php echo number_format($p['area_sqft'] ?? 0); ?></td>
                    <td>₹<?php echo number_format($p['total_price'] ?? 0); ?></td>
                    <td>₹<?php echo number_format($p['price_per_sqft'] ?? 0); ?></td>
                    <td>
                        <span class="badge bg-<?php echo ($p['status'] ?? '') === 'available' ? 'success' : (($p['status'] ?? '') === 'booked' ? 'warning' : (($p['status'] ?? '') === 'sold' ? 'danger' : 'secondary')); ?>">
                            <?php echo ucfirst($p['status'] ?? 'N/A'); ?>
                        </span>
                    </td>
                    <td><?php echo htmlspecialchars($p['facing'] ?? '-'); ?></td>
                    <td><?php echo $p['customer_id'] ? ('#' . $p['customer_id']) : '-'; ?></td>
                    <td class="text-nowrap">
                        <a href="<?php echo BASE_URL; ?>/admin/plots/<?php echo e($p['id']); ?>" class="btn btn-sm btn-info" title="View"><i class="fas fa-eye"></i></a>
                        <a href="<?php echo BASE_URL; ?>/admin/plots/<?php echo e($p['id']); ?>/edit" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>
