<?php
$page_title = 'Site Management';
$active_page = 'sites';
include APP_PATH . '/views/admin/layouts/header.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Site Management</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="/admin/sites/create" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Site
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Search sites..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="planning" <?= ($filters['status'] ?? '') === 'planning' ? 'selected' : '' ?>>Planning</option>
                    <option value="under_development" <?= ($filters['status'] ?? '') === 'under_development' ? 'selected' : '' ?>>Under Development</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="completed" <?= ($filters['status'] ?? '') === 'completed' ? 'selected' : '' ?>>Completed</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="residential" <?= ($filters['type'] ?? '') === 'residential' ? 'selected' : '' ?>>Residential</option>
                    <option value="commercial" <?= ($filters['type'] ?? '') === 'commercial' ? 'selected' : '' ?>>Commercial</option>
                    <option value="mixed" <?= ($filters['type'] ?? '') === 'mixed' ? 'selected' : '' ?>>Mixed</option>
                    <option value="industrial" <?= ($filters['type'] ?? '') === 'industrial' ? 'selected' : '' ?>>Industrial</option>
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

<!-- Sites Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Site Name</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Total Area</th>
                        <th>Plots</th>
                        <th>Properties</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($sites)): ?>
                        <tr>
                            <td colspan="9" class="text-center">No sites found</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($sites as $site): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars(site['site_name'] ?? '') ?></strong>
                                    <?php if (!empty($site['description'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(substr($site['description'], 0, 100)) ?>...</small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars(site['location'] ?? '') ?>
                                    <?php if (!empty($site['city'])): ?>
                                        <br><small class="text-muted"><?= htmlspecialchars(site['city'] ?? '') ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-info text-dark">
                                        <?= htmlspecialchars(ucfirst($site['site_type'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $statusColors = [
                                        'planning' => 'secondary',
                                        'under_development' => 'warning',
                                        'active' => 'success',
                                        'completed' => 'primary',
                                        'inactive' => 'danger'
                                    ];
                                    $color = $statusColors[$site['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $color ?>">
                                        <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $site['status']))) ?>
                                    </span>
                                </td>
                                <td><?= number_format(floatval(site['total_area'] ?? 0), 2) ?></td>
                                <td>
                                    <span class="badge bg-primary"><?= $site['total_plots'] ?></span>
                                    <br><small class="text-muted">
                                        <?= $site['available_plots'] ?> available
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?= $site['total_properties'] ?></span>
                                </td>
                                <td><?= date('M j, Y', strtotime($site['created_at'])) ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="/admin/sites/<?= $site['id'] ?>" class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="/admin/sites/<?= $site['id'] ?>/edit" class="btn btn-outline-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-outline-danger" onclick="confirmDelete(<?= $site['id'] ?>)" title="Delete">
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
            <nav aria-label="Site pagination">
                <ul class="pagination justify-content-center">
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($filters['search'] ?? '') ?>&status=<?= urlencode($filters['status'] ?? '') ?>&type=<?= urlencode($filters['type'] ?? '') ?>">
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
                <p>Are you sure you want to delete this site? This action cannot be undone.</p>
                <p class="text-danger"><strong>Note:</strong> You cannot delete a site that has existing plots or properties.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form method="POST" id="deleteForm" style="display: inline;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <button type="submit" class="btn btn-danger">Delete Site</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function confirmDelete(siteId) {
        document.getElementById('deleteForm').action = '/admin/sites/' + siteId + '/destroy';
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }
</script>

<?php include APP_PATH . '/views/admin/layouts/footer.php'; ?>