<?php
/**
 * Perfect Admin - Property Management Content
 * This file is included by admin.php and enhanced_admin_system.php
 */

if (!isset($adminService)) {
    $adminService = new PerfectAdminService();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filters = [
    'search' => $_GET['search'] ?? '',
    'type' => $_GET['type'] ?? '',
    'status' => $_GET['status'] ?? ''
];

$propertyData = $adminService->getPropertyList($filters, $page);
$properties = $propertyData['properties'];
$totalPages = $propertyData['total_pages'];
$totalCount = $propertyData['total_count'];
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Property Management</h5>
        <a href="property_add.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus me-2"></i>Add New Property
        </a>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="row g-3 mb-4">
            <input type="hidden" name="action" value="properties">
            <div class="col-12 col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search properties..." value="<?php echo h($filters['search']); ?>">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="residential" <?php echo $filters['type'] == 'residential' ? 'selected' : ''; ?>>Residential</option>
                    <option value="commercial" <?php echo $filters['type'] == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                    <option value="land" <?php echo $filters['type'] == 'land' ? 'selected' : ''; ?>>Land</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="available" <?php echo $filters['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="sold" <?php echo $filters['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                    <option value="under_contract" <?php echo $filters['status'] == 'under_contract' ? 'selected' : ''; ?>>Under Contract</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Properties Grid/Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Property</th>
                        <th>Type</th>
                        <th>Price</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($properties)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No properties found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0">
                                            <?php if (!empty($property['pimage'])): ?>
                                                <img src="../upload/<?php echo h($property['pimage']); ?>" class="rounded" style="width: 50px; height: 50px; object-fit: cover;" alt="Property">
                                            <?php else: ?>
                                                <div class="bg-secondary-subtle text-secondary rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                    <i class="fas fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-bold text-truncate" style="max-width: 250px;"><?php echo h($property['title']); ?></div>
                                            <small class="text-muted">ID: #<?php echo h($property['id']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo h(ucfirst($property['type'])); ?></td>
                                <td>
                                    <div class="fw-bold text-primary">₹<?php echo number_format($property['price'], 2); ?></div>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;">
                                        <i class="fas fa-map-marker-alt me-1 small text-muted"></i>
                                        <?php echo h($property['location']); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'secondary';
                                    switch($property['status']) {
                                        case 'available': $statusClass = 'success'; break;
                                        case 'sold': $statusClass = 'danger'; break;
                                        case 'under_contract': $statusClass = 'warning'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo h($statusClass); ?>-subtle text-<?php echo h($statusClass); ?> border border-<?php echo h($statusClass); ?>-subtle">
                                        <?php echo h(ucfirst(str_replace('_', ' ', $property['status']))); ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="property_view.php?id=<?php echo h($property['id']); ?>" class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="property_edit.php?id=<?php echo h($property['id']); ?>" class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmAction('Are you sure you want to delete this property?', () => { window.location.href = 'property_delete.php?id=<?php echo h($property['id']); ?>'; })">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=properties&page=<?php echo h($page - 1); ?>&search=<?php echo h($filters['search']); ?>&type=<?php echo h($filters['type']); ?>&status=<?php echo h($filters['status']); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?action=properties&page=<?php echo h($i); ?>&search=<?php echo h($filters['search']); ?>&type=<?php echo h($filters['type']); ?>&status=<?php echo h($filters['status']); ?>"><?php echo h($i); ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=properties&page=<?php echo h($page + 1); ?>&search=<?php echo h($filters['search']); ?>&type=<?php echo h($filters['type']); ?>&status=<?php echo h($filters['status']); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
