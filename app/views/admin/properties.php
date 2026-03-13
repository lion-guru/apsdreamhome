<?php
$page_title = 'Properties Management - APS Dream Home';
$active_page = 'properties';
include APP_PATH . '/views/admin/layouts/header.php';
?>

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4>Properties Management</h4>
                <div>
                    <a href="<?php echo url('/admin/properties/create'); ?>" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-2"></i>Add Property
                    </a>
                    <button class="btn btn-outline-secondary ms-2">
                        <i class="bi bi-download me-2"></i>Export
                    </button>
                </div>
            </div>

            <!-- Search and Filter -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search properties..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="apartment" <?php echo ($_GET['type'] ?? '') == 'apartment' ? 'selected' : ''; ?>>Apartments</option>
                                <option value="villa" <?php echo ($_GET['type'] ?? '') == 'villa' ? 'selected' : ''; ?>>Villas</option>
                                <option value="commercial" <?php echo ($_GET['type'] ?? '') == 'commercial' ? 'selected' : ''; ?>>Commercial</option>
                                <option value="plots" <?php echo ($_GET['type'] ?? '') == 'plots' ? 'selected' : ''; ?>>Plots</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" <?php echo ($_GET['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="pending" <?php echo ($_GET['status'] ?? '') == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="sold" <?php echo ($_GET['status'] ?? '') == 'sold' ? 'selected' : ''; ?>>Sold</option>
                                <option value="inactive" <?php echo ($_GET['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Featured</label>
                            <select name="featured" class="form-select">
                                <option value="">All</option>
                                <option value="1" <?php echo ($_GET['featured'] ?? '') == '1' ? 'selected' : ''; ?>>Featured</option>
                                <option value="0" <?php echo ($_GET['featured'] ?? '') == '0' ? 'selected' : ''; ?>>Not Featured</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="bi bi-search me-1"></i>Search
                                </button>
                                <a href="<?php echo url('/admin/properties'); ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Reset
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Properties Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Property</th>
                                    <th>Location</th>
                                    <th>Type</th>
                                    <th>Price</th>
                                    <th>Specs</th>
                                    <th>Status</th>
                                    <th>Featured</th>
                                    <th>Created</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($properties ?? [] as $property): ?>
                                <tr>
                                    <td><?php echo $property->id; ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-primary rounded d-flex align-items-center justify-content-center text-white me-3" style="width: 40px; height: 40px; font-size: 12px;">
                                                <?php echo strtoupper(substr($property->type, 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h6 class="mb-0"><?php echo htmlspecialchars($property->title); ?></h6>
                                                <small class="text-muted">ID: #PROP<?php echo str_pad($property->id, 4, '0', STR_PAD_LEFT); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($property->location); ?></td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo ucfirst($property->type); ?></span>
                                    </td>
                                    <td>
                                        <strong>₹<?php echo number_format($property->price); ?></strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo $property->bedrooms ?? 0; ?> Beds<br>
                                            <?php echo $property->bathrooms ?? 0; ?> Baths<br>
                                            <?php echo $property->area ?? 0; ?> Sq.ft
                                        </small>
                                    </td>
                                    <td>
                                        <?php
                                            $statusClass = match($property->status) {
                                                'active' => 'success',
                                                'pending' => 'warning',
                                                'sold' => 'danger',
                                                'inactive' => 'secondary',
                                                default => 'secondary'
                                            };
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($property->status); ?></span>
                                    </td>
                                    <td>
                                        <?php if($property->featured ?? false): ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-star-fill me-1"></i>Featured
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?php echo $property->created_at; ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="<?php echo url('/admin/properties/' . $property->id); ?>" class="btn btn-outline-primary" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?php echo url('/admin/properties/' . $property->id . '/edit'); ?>" class="btn btn-outline-secondary" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <button class="btn btn-outline-danger" title="Delete" onclick="confirmDelete('<?php echo $property->id; ?>');">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Properties pagination">
                        <ul class="pagination justify-content-center mt-4">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1">Previous</a>
                            </li>
                            <li class="page-item active"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this property? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(propertyId) {
    document.getElementById('deleteForm').action = '/admin/properties/' + propertyId + '/delete';
    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>
