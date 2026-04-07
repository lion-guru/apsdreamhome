<?php include __DIR__ . '/../../layouts/admin_header.php'; ?>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-building me-2"></i>User Properties
        </h1>
        <a href="<?php echo BASE_URL; ?>/list-property" target="_blank" class="btn btn-sm btn-primary">
            <i class="fas fa-external-link-alt me-1"></i> View Listing Page
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            Property updated successfully!
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            Something went wrong. Please try again.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Status Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link <?php echo !$status ? 'active' : ''; ?>" href="?">
                All <span class="badge bg-secondary"><?php echo $statusCounts['all']; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $status === 'pending' ? 'active' : ''; ?>" href="?status=pending">
                Pending <span class="badge bg-warning"><?php echo $statusCounts['pending']; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $status === 'verified' ? 'active' : ''; ?>" href="?status=verified">
                Verified <span class="badge bg-info"><?php echo $statusCounts['verified']; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $status === 'approved' ? 'active' : ''; ?>" href="?status=approved">
                Approved <span class="badge bg-success"><?php echo $statusCounts['approved']; ?></span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $status === 'rejected' ? 'active' : ''; ?>" href="?status=rejected">
                Rejected <span class="badge bg-danger"><?php echo $statusCounts['rejected']; ?></span>
            </a>
        </li>
    </ul>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by name, phone, email..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="plot" <?php echo $type === 'plot' ? 'selected' : ''; ?>>Plot</option>
                        <option value="house" <?php echo $type === 'house' ? 'selected' : ''; ?>>House</option>
                        <option value="flat" <?php echo $type === 'flat' ? 'selected' : ''; ?>>Flat</option>
                        <option value="shop" <?php echo $type === 'shop' ? 'selected' : ''; ?>>Shop</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Filter
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="?" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Properties Table -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($properties)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No properties found.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Property</th>
                                <th>Owner</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $p): ?>
                                <tr>
                                    <td><?php echo $p['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                        <br><small class="text-muted"><?php echo $p['area_sqft']; ?> sq ft</small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($p['name']); ?></strong>
                                        <br><small><?php echo htmlspecialchars($p['phone']); ?></small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($p['city_name'] ?? ''); ?>,
                                        <?php echo htmlspecialchars($p['district_name'] ?? ''); ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($p['state_name'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <strong class="text-success">₹<?php echo number_format($p['price']); ?></strong>
                                        <br><small class="text-muted"><?php echo ucfirst($p['price_type']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo ucfirst($p['property_type']); ?></span>
                                        <br><small class="text-muted"><?php echo ucfirst($p['listing_type']); ?></small>
                                    </td>
                                    <td>
                                        <?php
                                        $statusClass = match($p['status']) {
                                            'pending' => 'warning',
                                            'verified' => 'info',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'sold' => 'dark',
                                            default => 'secondary'
                                        };
                                        ?>
                                        <span class="badge bg-<?php echo $statusClass; ?>"><?php echo ucfirst($p['status']); ?></span>
                                    </td>
                                    <td>
                                        <?php echo date('d M Y', strtotime($p['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo BASE_URL; ?>/admin/user-properties/verify/<?php echo $p['id']; ?>" class="btn btn-primary" title="View & Verify">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <?php if ($p['status'] === 'pending'): ?>
                                                <form method="POST" action="<?php echo BASE_URL; ?>/admin/user-properties/action" class="d-inline" onsubmit="return confirm('Approve this property?');">
                                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-success btn-sm" title="Approve">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                </form>
                                                <form method="POST" action="<?php echo BASE_URL; ?>/admin/user-properties/action" class="d-inline" onsubmit="return confirm('Reject this property?');">
                                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Reject">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status); ?>&search=<?php echo urlencode($search); ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../layouts/admin_footer.php'; ?>
