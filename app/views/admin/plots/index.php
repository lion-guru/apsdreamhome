<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title ?? 'Plot Management') ?></title>
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
                            <a class="nav-link" href="/admin/dashboard">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/sites">
                                <i class="fas fa-map-marked-alt"></i> Sites
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/properties">
                                <i class="fas fa-building"></i> Properties
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/admin/plots">
                                <i class="fas fa-th"></i> Plots
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/bookings">
                                <i class="fas fa-calendar-check"></i> Bookings
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/admin/users">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Plot Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="/admin/plots/create" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add New Plot
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
                                <input type="text" name="search" class="form-control" placeholder="Search plots..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
                            </div>
                            <div class="col-md-2">
                                <select name="status" class="form-select">
                                    <option value="">All Status</option>
                                    <option value="available" <?= ($filters['status'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="sold" <?= ($filters['status'] ?? '') === 'sold' ? 'selected' : '' ?>>Sold</option>
                                    <option value="reserved" <?= ($filters['status'] ?? '') === 'reserved' ? 'selected' : '' ?>>Reserved</option>
                                    <option value="under_process" <?= ($filters['status'] ?? '') === 'under_process' ? 'selected' : '' ?>>Under Process</option>
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

                <!-- Plots Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Plot No</th>
                                        <th>Site</th>
                                        <th>Area (sq ft)</th>
                                        <th>Available Area</th>
                                        <th>Dimensions</th>
                                        <th>Facing</th>
                                        <th>Price</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($plots)): ?>
                                        <tr>
                                            <td colspan="10" class="text-center">No plots found</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($plots as $plot): ?>
                                            <tr>
                                                <td>
                                                    <strong><?= htmlspecialchars($plot['plot_no']) ?></strong>
                                                    <?php if (!empty($plot['plot_dimension'])): ?>
                                                        <br><small class="text-muted"><?= htmlspecialchars($plot['plot_dimension']) ?></small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?= htmlspecialchars($plot['site_name']) ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars($plot['site_location']) ?></small>
                                                </td>
                                                <td><?= number_format($plot['area'], 2) ?></td>
                                                <td><?= number_format($plot['available_area'], 2) ?></td>
                                                <td><?= htmlspecialchars($plot['plot_dimension'] ?? 'N/A') ?></td>
                                                <td><?= htmlspecialchars($plot['plot_facing'] ?? 'N/A') ?></td>
                                                <td>
                                                    <?php if ($plot['plot_price'] > 0): ?>
                                                        ₹<?= number_format($plot['plot_price'], 2) ?>
                                                    <?php else: ?>
                                                        <span class="text-muted">Not Set</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusColors = [
                                                        'available' => 'success',
                                                        'sold' => 'danger',
                                                        'reserved' => 'warning',
                                                        'under_process' => 'info'
                                                    ];
                                                    $color = $statusColors[$plot['plot_status']] ?? 'secondary';
                                                    ?>
                                                    <span class="badge bg-<?= $color ?>">
                                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $plot['plot_status']))) ?>
                                                    </span>
                                                </td>
                                                <td><?= date('M j, Y', strtotime($plot['created_at'] ?? 'now')) ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="/admin/plots/<?= $plot['plot_id'] ?>" class="btn btn-outline-primary" title="View">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="/admin/plots/<?= $plot['plot_id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <?php if ($plot['plot_status'] === 'available'): ?>
                                                            <button type="button" class="btn btn-outline-success" onclick="bookPlot(<?= $plot['plot_id'] ?>)" title="Book Plot">
                                                                <i class="fas fa-calendar-plus"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $plot['plot_id'] ?>)" title="Delete">
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
                            <nav aria-label="Plot pagination">
                                <ul class="pagination justify-content-center">
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&site_id=<?= urlencode($filters['site_id'] ?? '') ?>">
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
                    <p>Are you sure you want to delete this plot? This action cannot be undone.</p>
                    <p class="text-danger"><strong>Note:</strong> You cannot delete a plot that has existing bookings.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <form method="POST" id="deleteForm" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                        <button type="submit" class="btn btn-danger">Delete Plot</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmDelete(plotId) {
            document.getElementById('deleteForm').action = '/admin/plots/' + plotId + '/destroy';
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function bookPlot(plotId) {
            // Redirect to booking form with pre-selected plot
            window.location.href = '/admin/bookings/create?plot_id=' + plotId;
        }

        // Auto-refresh availability status
        setInterval(function() {
            fetch('/admin/plots/check-availability?' + new URLSearchParams(window.location.search))
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update plot statuses in real-time
                        document.querySelectorAll('table tbody tr').forEach(row => {
                            const plotId = row.querySelector('a[href*="/admin/plots/"]').href.split('/').pop();
                            const statusBadge = row.querySelector('.badge');
                            if (statusBadge && data.plots) {
                                const plot = data.plots.find(p => p.plot_id == plotId);
                                if (plot) {
                                    statusBadge.textContent = plot.plot_status.replace('_', ' ');
                                    statusBadge.className = 'badge bg-' + getStatusColor(plot.plot_status);
                                }
                            }
                        });
                    }
                });
        }, 30000); // Check every 30 seconds

        function getStatusColor(status) {
            const colors = {
                'available': 'success',
                'sold': 'danger',
                'reserved': 'warning',
                'under_process': 'info'
            };
            return colors[status] || 'secondary';
        }
    </script>
</body>
</html>
