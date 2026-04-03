<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Property Management') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>Admin Panel</span>
                    </h6>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/sites">
                                <i class="fas fa-map-marked-alt"></i> Sites
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="<?php echo BASE_URL; ?>/admin/properties">
                                <i class="fas fa-building"></i> Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/plots">
                                <i class="fas fa-th"></i> Plots
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/bookings">
                                <i class="fas fa-calendar-check"></i> Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo BASE_URL; ?>/admin/users">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Property Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="<?php echo BASE_URL; ?>/admin/properties/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Property
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <select name="site_id" class="form-select">
                                    <option value="">All Sites</option>
                                    <?php foreach ($sites as $site): ?>
                                        <option value="<?= $site['id'] ?>" <?= ($filters['site_id'] ?? '') == $site['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($site['site_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="search" class="form-control" placeholder="Search properties..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                                    <option value="sold" <?= ($filters['status'] ?? '') === 'sold' ? 'selected' : '' ?>>Sold</option>
                                    <option value="rented" <?= ($filters['status'] ?? '') === 'rented' ? 'selected' : '' ?>>Rented</option>
                                    <option value="pending" <?= ($filters['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Pending</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select name="type" class="form-select">
                                    <option value="">All Types</option>
                                    <option value="apartment" <?= ($filters['type'] ?? '') === 'apartment' ? 'selected' : '' ?>>Apartment</option>
                                    <option value="house" <?= ($filters['type'] ?? '') === 'house' ? 'selected' : '' ?>>House</option>
                                    <option value="land" <?= ($filters['type'] ?? '') === 'land' ? 'selected' : '' ?>>Land</option>
                                    <option value="commercial" <?= ($filters['type'] ?? '') === 'commercial' ? 'selected' : '' ?>>Commercial</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-outline-primary">
                                    <i class="fas fa-search"></i> Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Properties Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Property</th>
                                        <th>Site</th>
                                        <th>Type</th>
                                        <th>Price</th>
                                        <th>Area</th>
                                        <th>Bed/Bath</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($properties)): ?>
                                        <tr>
                                            <td colspan="9" class="text-center">No properties found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($properties as $property): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($property['title']) ?></strong>
                                                    <?php if (!empty($property['description'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars(substr($property['description'], 0, 100)) ?>...</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($property['site_name']) ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($property['site_location']) ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info text-dark">
                                                        <?= htmlspecialchars(ucfirst($property['type'])) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($property['price'] > 0): ?>
                                                        ₹<?= number_format($property['price'], 2) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not Set</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= number_format($property['area'], 2) ?> <?= htmlspecialchars($property['area_unit'] ?? 'sqft') ?>
                                                </td>
                                                <td>
                                                    <?= $property['bedrooms'] ?>B / <?= $property['bathrooms'] ?>B
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusColors = [
                                                        'active' => 'success',
                                                        'sold' => 'danger',
                                                        'rented' => 'warning',
                                                        'pending' => 'secondary'
                                                    ];
                                                    $color = $statusColors[$property['status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?= $color ?>">
                                                        <?= htmlspecialchars(ucfirst($property['status'])) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($property['created_at'])) ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="<?php echo BASE_URL; ?>/admin/properties/<?= $property['id'] ?>" class="btn btn-outline-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="<?php echo BASE_URL; ?>/admin/properties/<?= $property['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($property['status'] === 'active'): ?>
                                                            <button type="button" class="btn btn-outline-success" onclick="bookProperty(<?= $property['id'] ?>)" title="Book Property">
                                                                <i class="fas fa-calendar-plus"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $property['id'] ?>)" title="Delete">
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
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Property pagination">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&type=<?= urlencode($filters['type'] ?? '') ?>&site_id=<?= urlencode($filters['site_id'] ?? '') ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete this property? This action cannot be undone.</p>
                    <p class="text-danger"><strong>Note:</strong> You cannot delete a property that has existing bookings.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" id="deleteForm" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-danger">Delete Property</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var baseUrl = '<?php echo BASE_URL; ?>';

        function confirmDelete(propertyId) {
            document.getElementById('deleteForm').action = baseUrl + '/admin/properties/' + propertyId + '/destroy';
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function bookProperty(propertyId) {
            // Redirect to booking form with pre-selected property
            window.location.href = baseUrl + '/admin/bookings/create?property_id=' + propertyId;
        }

        // Auto-refresh availability status
        setInterval(function() {
            fetch(baseUrl + '/admin/properties/check-availability?' + new URLSearchParams(window.location.search))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update property statuses in real-time
                        document.querySelectorAll('table tbody tr').forEach(row => {
                            const propertyId = row.querySelector('a[href*="/admin/properties/"]').href.split('/').pop();
                            const statusBadge = row.querySelector('.badge');
                            if (statusBadge && data.properties) {
                                const property = data.properties.find(p => p.id == propertyId);
                                if (property) {
                                    statusBadge.textContent = property.status;
                                    statusBadge.className = 'badge bg-' + getStatusColor(property.status);
                                }
                            }
                        });
                    }
                });
        }, 30000); // Check every 30 seconds

        function getStatusColor(status) {
            const colors = {
                'active': 'success',
                'sold': 'danger',
                'rented': 'warning',
                'pending': 'secondary'
            };
            return colors[status] || 'secondary';
        }
    </script>
</body>
</html>
