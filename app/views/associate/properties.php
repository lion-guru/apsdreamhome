<?php
$page_title = $page_title ?? 'My Properties - APS Dream Home';
$properties = $properties ?? [];
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-building text-primary me-2"></i>My Properties</h4>
        <a href="<?php echo BASE_URL; ?>/associate/add-property" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i>Add Property</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($properties)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-building fa-4x text-muted mb-3"></i>
                    <p class="text-muted">You haven't listed any properties yet.</p>
                    <a href="<?php echo BASE_URL; ?>/associate/add-property" class="btn btn-primary">List Your First Property</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Views</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['title'] ?? 'N/A'); ?></td>
                                    <td><span class="badge bg-secondary"><?php echo htmlspecialchars($p['property_type'] ?? 'N/A'); ?></span></td>
                                    <td>₹<?php echo number_format($p['price'] ?? 0); ?></td>
                                    <td><span class="badge bg-<?php echo ($p['status'] ?? 'pending') === 'approved' ? 'success' : (($p['status'] ?? 'pending') === 'rejected' ? 'danger' : 'warning'); ?>"><?php echo ucfirst($p['status'] ?? 'Pending'); ?></span></td>
                                    <td><?php echo (int)($p['views'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars($p['date'] ?? ''); ?></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
