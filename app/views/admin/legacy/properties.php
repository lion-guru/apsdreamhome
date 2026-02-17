<?php
/**
 * Admin Properties Management Template
 * Displays and manages all properties
 */

?>

<!-- Admin Header -->
<section class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-0">
                    <i class="fas fa-home me-2"></i>
                    Properties Management
                </h1>
                <p class="mb-0 opacity-75">Manage all property listings in your system</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <a href="<?php echo BASE_URL; ?>admin/properties/create" class="btn btn-light btn-lg">
                    <i class="fas fa-plus me-2"></i>Add New Property
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Filters and Search -->
<section class="admin-filters py-4 bg-light">
    <div class="container">
        <div class="row g-3">
            <div class="col-md-3">
                <input type="text" class="form-control" placeholder="Search properties..." name="search"
                       value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
            </div>
            <div class="col-md-2">
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="available" <?php echo ($filters['status'] ?? '') === 'available' ? 'selected' : ''; ?>>Available</option>
                    <option value="sold" <?php echo ($filters['status'] ?? '') === 'sold' ? 'selected' : ''; ?>>Sold</option>
                    <option value="rented" <?php echo ($filters['status'] ?? '') === 'rented' ? 'selected' : ''; ?>>Rented</option>
                </select>
            </div>
            <div class="col-md-2">
                <select class="form-select" name="type">
                    <option value="">All Types</option>
                    <option value="apartment" <?php echo ($filters['type'] ?? '') === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                    <option value="villa" <?php echo ($filters['type'] ?? '') === 'villa' ? 'selected' : ''; ?>>Villa</option>
                    <option value="house" <?php echo ($filters['type'] ?? '') === 'house' ? 'selected' : ''; ?>>House</option>
                    <option value="plot" <?php echo ($filters['type'] ?? '') === 'plot' ? 'selected' : ''; ?>>Plot</option>
                </select>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1"
                           <?php echo ($filters['featured'] ?? false) ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="featured">
                        Featured Only
                    </label>
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-2"></i>Filter
                </button>
                <a href="<?php echo BASE_URL; ?>admin/properties" class="btn btn-outline-secondary">
                    <i class="fas fa-redo me-2"></i>Reset
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Properties Table -->
<section class="admin-properties py-5">
    <div class="container">
        <!-- Results Info -->
        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="mb-0">
                    <?php echo number_format($total_properties); ?> Properties Found
                </h5>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="d-flex align-items-center justify-content-md-end gap-3">
                    <span class="text-muted">Show:</span>
                    <select class="form-select form-select-sm w-auto">
                        <option value="10" <?php echo ($per_page == 10) ? 'selected' : ''; ?>>10</option>
                        <option value="20" <?php echo ($per_page == 20) ? 'selected' : ''; ?>>20</option>
                        <option value="50" <?php echo ($per_page == 50) ? 'selected' : ''; ?>>50</option>
                    </select>
                </div>
            </div>
        </div>

        <?php if (empty($properties)): ?>
            <div class="text-center py-5">
                <i class="fas fa-home fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Properties Found</h4>
                <p class="text-muted mb-4">No properties match your current filters.</p>
                <a href="<?php echo BASE_URL; ?>admin/properties/create" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Add Your First Property
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Agent</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $property): ?>
                            <tr>
                                <td><?php echo $property['id']; ?></td>
                                <td>
                                    <?php if ($property['main_image']): ?>
                                        <img src="<?php echo htmlspecialchars($property['main_image']); ?>"
                                             alt="Property" class="property-thumbnail">
                                    <?php else: ?>
                                        <div class="property-thumbnail bg-light d-flex align-items-center justify-content-center">
                                            <i class="fas fa-home text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($property['title']); ?></strong>
                                        <?php if ($property['featured']): ?>
                                            <span class="badge bg-warning ms-2">
                                                <i class="fas fa-star me-1"></i>Featured
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?php echo htmlspecialchars($property['address']); ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo htmlspecialchars($property['property_type'] ?? 'Property'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $price = $property['price'] ?? 0;
                                    echo $price > 0 ? '₹' . number_format($price) : 'Price on Request';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php
                                        echo $property['status'] === 'available' ? 'bg-success' :
                                             ($property['status'] === 'sold' ? 'bg-danger' : 'bg-warning');
                                    ?>">
                                        <?php echo ucfirst($property['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($property['agent_name'] ?? 'N/A'); ?>
                                </td>
                                <td>
                                    <small><?php echo date('M d, Y', strtotime($property['created_at'])); ?></small>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="<?php echo BASE_URL; ?>property?id=<?php echo $property['id']; ?>"
                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="<?php echo BASE_URL; ?>admin/properties/edit/<?php echo $property['id']; ?>"
                                           class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-outline-danger"
                                                onclick="deleteProperty(<?php echo $property['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if (isset($total_pages) && $total_pages > 1): ?>
                <nav aria-label="Properties pagination" class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php if (isset($current_page) && $current_page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL; ?>admin/properties?page=<?php echo ($current_page - 1); ?>">
                                    <i class="fas fa-chevron-left"></i> Previous
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $start_page = max(1, (isset($current_page) ? $current_page : 1) - 2);
                        $end_page = min((isset($total_pages) ? $total_pages : 1), (isset($current_page) ? $current_page : 1) + 2);

                        for ($i = $start_page; $i <= $end_page; $i++):
                        ?>
                            <li class="page-item <?php echo (isset($current_page) && $i === $current_page) ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo BASE_URL; ?>admin/properties?page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <?php if (isset($current_page, $total_pages) && $current_page < $total_pages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo BASE_URL; ?>admin/properties?page=<?php echo ($current_page + 1); ?>">
                                    Next <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- Bulk Actions -->
<?php if (!empty($properties)): ?>
<section class="bulk-actions py-3 bg-light border-top">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="selectAll">
                    <label class="form-check-label" for="selectAll">
                        Select All Properties
                    </label>
                </div>
            </div>
            <div class="col-md-6 text-md-end">
                <div class="btn-group">
                    <button class="btn btn-outline-primary btn-sm" onclick="bulkAction('feature')">
                        <i class="fas fa-star me-1"></i>Feature Selected
                    </button>
                    <button class="btn btn-outline-danger btn-sm" onclick="bulkAction('delete')">
                        <i class="fas fa-trash me-1"></i>Delete Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<script>
function deleteProperty(propertyId) {
    if (confirm('Are you sure you want to delete this property?')) {
        // Here you would implement the delete functionality
        alert('Delete functionality would be implemented here');
    }
}

function bulkAction(action) {
    const selectedProperties = document.querySelectorAll('input[name="property_ids[]"]:checked');
    if (selectedProperties.length === 0) {
        alert('Please select properties first');
        return;
    }

    if (confirm(`Are you sure you want to ${action} the selected properties?`)) {
        // Here you would implement the bulk action functionality
        alert(`Bulk ${action} functionality would be implemented here`);
    }
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('input[name="property_ids[]"]');
    checkboxes.forEach(checkbox => checkbox.checked = this.checked);
});
</script>
