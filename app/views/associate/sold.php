<?php
$page_title = $page_title ?? 'Sold Properties - APS Dream Home';
$properties = $properties ?? [];
?>
<div class="container-fluid px-4">
    <h4 class="mb-4"><i class="fas fa-check-circle text-success me-2"></i>Sold Properties</h4>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($properties)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No sold properties yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <div class="table-responsive"><table class="table table-hover mb-0 table-responsive">
                        <thead class="bg-light">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Price</th>
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
                                    <td><?php echo htmlspecialchars($p['date'] ?? ''); ?></td>
                                    <td><a href="#" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table></div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
