<?php
/**
 * Perfect Admin - Lead Management Content
 * This file is included by admin.php and enhanced_admin_system.php
 */

if (!isset($adminService)) {
    $adminService = new PerfectAdminService();
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filters = [
    'search' => $_GET['search'] ?? '',
    'status' => $_GET['status'] ?? ''
];

$leadData = $adminService->getLeadList($filters, $page);
$leads = $leadData['leads'];
$totalPages = $leadData['total_pages'];
$totalCount = $leadData['total_count'];
?>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-transparent border-0 py-3 d-flex align-items-center justify-content-between">
        <h5 class="card-title mb-0">Lead Management</h5>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addLeadModal">
            <i class="fas fa-plus me-2"></i>New Lead
        </button>
    </div>
    <div class="card-body">
        <!-- Filters -->
        <form method="GET" class="row g-3 mb-4">
            <input type="hidden" name="action" value="leads">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="fas fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search name, email or phone..." value="<?php echo h($filters['search']); ?>">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="new" <?php echo $filters['status'] == 'new' ? 'selected' : ''; ?>>New</option>
                    <option value="contacted" <?php echo $filters['status'] == 'contacted' ? 'selected' : ''; ?>>Contacted</option>
                    <option value="qualified" <?php echo $filters['status'] == 'qualified' ? 'selected' : ''; ?>>Qualified</option>
                    <option value="lost" <?php echo $filters['status'] == 'lost' ? 'selected' : ''; ?>>Lost</option>
                </select>
            </div>
            <div class="col-12 col-md-3">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </form>

        <!-- Leads Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Lead</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No leads found matching your criteria.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($leads as $lead): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo h($lead['name']); ?></div>
                                    <small class="text-muted">Ref: #LD-<?php echo h($lead['id']); ?></small>
                                </td>
                                <td>
                                    <div><i class="fas fa-envelope me-2 small opacity-50"></i><?php echo h($lead['email']); ?></div>
                                    <div><i class="fas fa-phone me-2 small opacity-50"></i><?php echo h($lead['phone']); ?></div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = 'secondary';
                                    switch(strtolower($lead['status'])) {
                                        case 'new': $statusClass = 'primary'; break;
                                        case 'contacted': $statusClass = 'info'; break;
                                        case 'qualified': $statusClass = 'success'; break;
                                        case 'lost': $statusClass = 'danger'; break;
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo h($statusClass); ?>-subtle text-<?php echo h($statusClass); ?> border border-<?php echo h($statusClass); ?>-subtle">
                                        <?php echo h(ucfirst($lead['status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo h(date('d M Y', strtotime($lead['created_at']))); ?></td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-info" title="View">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-primary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" title="Delete" onclick="confirmAction('Delete this lead?', () => { /* lead delete logic */ })">
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
                        <a class="page-link" href="?action=leads&page=<?php echo h($page - 1); ?>&search=<?php echo h($filters['search']); ?>&status=<?php echo h($filters['status']); ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                            <a class="page-link" href="?action=leads&page=<?php echo h($i); ?>&search=<?php echo h($filters['search']); ?>&status=<?php echo h($filters['status']); ?>"><?php echo h($i); ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?action=leads&page=<?php echo h($page + 1); ?>&search=<?php echo h($filters['search']); ?>&status=<?php echo h($filters['status']); ?>">Next</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
