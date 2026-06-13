<?php
$extraHead = '<link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<style>
    .status-badge { min-width: 80px; text-align: center; }
    .action-form { display: inline; }
    .action-form button { margin: 2px; }
</style>';
?>
<div class="container-fluid py-4">
    <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo htmlspecialchars($_SESSION['flash_success'] ?? ''); unset($_SESSION['flash_success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo htmlspecialchars($_SESSION['flash_error'] ?? ''); unset($_SESSION['flash_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-building text-primary me-2"></i>User Properties</h4>
        <div>
            <a href="?status=pending" class="btn btn-sm btn-outline-warning me-1">Pending</a>
            <a href="?status=verified" class="btn btn-sm btn-outline-info me-1">Verified</a>
            <a href="?status=approved" class="btn btn-sm btn-outline-success me-1">Approved</a>
            <a href="?status=rejected" class="btn btn-sm btn-outline-danger me-1">Rejected</a>
            <a href="?" class="btn btn-sm btn-outline-secondary">All</a>
        </div>
    </div>

    <form method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Search by name, phone, email, or address..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
            <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Search</button>
            <?php if (!empty($search)): ?>
            <a href="?" class="btn btn-outline-secondary">Clear</a>
            <?php endif; ?>
        </div>
    </form>

    <?php if (empty($properties)): ?>
    <div class="card aps-cp-card">
        <div class="card-body text-center py-5">
            <i class="fas fa-home fa-4x text-muted mb-3"></i>
            <h5 class="text-muted">No properties found</h5>
        </div>
    </div>
    <?php else: ?>
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Posted By</th>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($properties as $p): ?>
                        <tr>
                            <td>#<?php echo $p['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($p['name'] ?? 'N/A'); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($p['phone'] ?? ''); ?></small>
                                <?php if (!empty($p['email'])): ?>
                                <br><small class="text-muted"><?php echo htmlspecialchars($p['email']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars(ucfirst($p['property_type'] ?? '')); ?></span>
                                <br><small class="text-muted"><?php echo htmlspecialchars(ucfirst($p['listing_type'] ?? '')); ?></small>
                            </td>
                            <td><strong>Rs.<?php echo number_format($p['price'] ?? 0); ?></strong>
                                <?php if (!empty($p['area_sqft'])): ?>
                                <br><small class="text-muted"><?php echo number_format($p['area_sqft']); ?> sqft</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($p['address'] ?? ''); ?>
                                <?php if (!empty($p['district_name'])): ?><br><small class="text-muted"><?php echo htmlspecialchars($p['district_name']); ?><?php echo !empty($p['state_name']) ? ', ' . htmlspecialchars($p['state_name']) : ''; ?></small><?php endif; ?>
                            </td>
                            <td>
                                <?php
                                $sc = match($p['status'] ?? 'pending') { 'pending' => 'warning', 'verified' => 'info', 'approved' => 'success', 'rejected' => 'danger', 'sold' => 'dark', default => 'secondary' };
                                ?>
                                <span class="badge bg-<?php echo $sc; ?> status-badge"><?php echo ucfirst($p['status'] ?? 'pending'); ?></span>
                                <?php if (!empty($p['admin_notes'])): ?>
                                <br><small class="text-muted" title="<?php echo htmlspecialchars($p['admin_notes']); ?>"><i class="fas fa-sticky-note"></i> Notes</small>
                                <?php endif; ?>
                            </td>
                            <td><small class="text-nowrap"><?php echo date('d M Y', strtotime($p['created_at'])); ?></small></td>
                            <td>
                                <?php if ($p['status'] === 'pending'): ?>
                                <form method="POST" action="<?php echo BASE_URL; ?>/employee/user-properties/action" class="action-form">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="action" value="verify">
                                    <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Mark as verified?')"><i class="fas fa-check"></i></button>
                                </form>
                                <?php endif; ?>
                                <?php if (in_array($p['status'] ?? '', ['pending', 'verified'])): ?>
                                <form method="POST" action="<?php echo BASE_URL; ?>/employee/user-properties/action" class="action-form">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve this listing?')"><i class="fas fa-thumbs-up"></i></button>
                                </form>
                                <form method="POST" action="<?php echo BASE_URL; ?>/employee/user-properties/action" class="action-form">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Reject this listing?')"><i class="fas fa-thumbs-down"></i></button>
                                </form>
                                <?php endif; ?>
                                <?php if (($p['status'] ?? '') === 'approved'): ?>
                                <form method="POST" action="<?php echo BASE_URL; ?>/employee/user-properties/action" class="action-form">
                                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                                    <input type="hidden" name="action" value="mark_sold">
                                    <button type="submit" class="btn btn-sm btn-dark" onclick="return confirm('Mark as sold?')"><i class="fas fa-tag"></i></button>
                                </form>
                                <?php endif; ?>
                                <a href="<?php echo BASE_URL; ?>/listing/<?php echo $p['id']; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-external-link-alt"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php if ($totalPages > 1): ?>
    <nav class="mt-3">
        <ul class="pagination justify-content-center">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo urlencode($status ?? ''); ?>"><?php echo $i; ?></a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
</div>
