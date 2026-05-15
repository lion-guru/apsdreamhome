<?php
$page_title = $page_title ?? 'Pending Deals - APS Dream Home';
$properties = $properties ?? [];
?>
<div class="container-fluid px-4">
    <h4 class="mb-4"><i class="fas fa-clock text-warning me-2"></i>Pending Deals</h4>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($properties)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-clock fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No pending deals. All your properties are processed.</p>
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
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['title'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($p['property_type'] ?? 'N/A'); ?></td>
                                    <td>₹<?php echo number_format($p['price'] ?? 0); ?></td>
                                    <td><span class="badge bg-warning"><?php echo ucfirst($p['status'] ?? 'Pending'); ?></span></td>
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
