<?php
/**
 * Employees Management Template
 * Admin interface for managing employees
 */
?>

<!-- Admin Header -->
<section class="admin-header py-4 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="mb-0">
                    <i class="fas fa-user-shield me-2"></i>
                    Employees Management
                </h1>
                <p class="mb-0 opacity-75">Manage all office staff and employees</p>
            </div>
            <div class="col-lg-6 text-lg-end">
                <a href="<?php echo BASE_URL; ?>admin/employees/create" class="btn btn-light btn-lg">
                    <i class="fas fa-plus me-2"></i>Add New Employee
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Employees Management -->
<section class="employees-management py-5">
    <div class="container">
        <!-- Filters and Search -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="filter-card">
                    <form method="GET" class="row g-3">
                        <div class="col-md-6">
                            <label for="search" class="form-label fw-medium">
                                <i class="fas fa-search text-primary me-2"></i>
                                Search Employees
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="search"
                                   name="search"
                                   placeholder="Search by name, email or phone"
                                   value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="status" class="form-label fw-medium">
                                <i class="fas fa-toggle-on text-primary me-2"></i>
                                Status
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" <?php echo ($filters['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo ($filters['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label fw-medium">&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter me-2"></i>
                                    Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Employees Table -->
        <div class="row">
            <div class="col-12">
                <div class="table-card">
                    <!-- Table Header -->
                    <div class="table-header d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-list text-primary me-2"></i>
                                Employees (<?php echo number_format($total_employees ?? 0); ?>)
                            </h5>
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center">
                                <label for="per_page" class="form-label mb-0 me-2">Show:</label>
                                <select class="form-select form-select-sm" id="per_page" name="per_page"
                                        onchange="window.location.href = this.value">
                                    <option value="<?php echo BASE_URL; ?>admin/employees?<?php echo http_build_query(array_merge($_GET, ['per_page' => 10])); ?>" <?php echo ($filters['per_page'] ?? 10) == 10 ? 'selected' : ''; ?>>10</option>
                                    <option value="<?php echo BASE_URL; ?>admin/employees?<?php echo http_build_query(array_merge($_GET, ['per_page' => 25])); ?>" <?php echo ($filters['per_page'] ?? 10) == 25 ? 'selected' : ''; ?>>25</option>
                                    <option value="<?php echo BASE_URL; ?>admin/employees?<?php echo http_build_query(array_merge($_GET, ['per_page' => 50])); ?>" <?php echo ($filters['per_page'] ?? 10) == 50 ? 'selected' : ''; ?>>50</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Table Responsive -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Designation</th>
                                    <th>Contact Information</th>
                                    <th>Department</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($employees)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="no-data">
                                                <i class="fas fa-user-shield fa-3x text-muted mb-3"></i>
                                                <h5 class="text-muted">No employees found</h5>
                                                <p class="text-muted">Try adjusting your search or filter criteria</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($employees as $employee): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-circle bg-warning text-dark me-2" style="width: 32px; height: 32px; font-size: 14px; line-height: 32px; text-align: center; border-radius: 50%;">
                                                        <?php echo strtoupper(substr($employee['name'], 0, 1)); ?>
                                                    </div>
                                                    <div class="fw-medium"><?php echo htmlspecialchars($employee['name']); ?></div>
                                                </div>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars($employee['designation'] ?? 'N/A'); ?></small>
                                            </td>
                                            <td>
                                                <small class="d-block"><i class="fas fa-envelope me-1 text-muted"></i><?php echo htmlspecialchars($employee['email']); ?></small>
                                                <small class="text-muted"><i class="fas fa-phone me-1 text-muted"></i><?php echo htmlspecialchars($employee['phone']); ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border">
                                                    <?php echo htmlspecialchars($employee['department'] ?? 'General'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-<?php
                                                    echo match($employee['status']) {
                                                        'active' => 'success',
                                                        'inactive' => 'secondary',
                                                        default => 'secondary'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($employee['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    <?php echo date('M d, Y', strtotime($employee['created_at'])); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="<?php echo BASE_URL; ?>admin/employees/view/<?php echo $employee['id']; ?>"
                                                       class="btn btn-sm btn-outline-info"
                                                       title="View Profile">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="<?php echo BASE_URL; ?>admin/employees/edit/<?php echo $employee['id']; ?>"
                                                       class="btn btn-sm btn-outline-primary"
                                                       title="Edit Employee">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
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
                        <div class="pagination-wrapper mt-4">
                            <nav aria-label="Employees pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($filters['page'] > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="<?php echo BASE_URL; ?>admin/employees?<?php echo http_build_query(array_merge($_GET, ['page' => $filters['page'] - 1])); ?>">
                                                <i class="fas fa-chevron-left"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php
                                    $start_page = max(1, $filters['page'] - 2);
                                    $end_page = min($total_pages, $filters['page'] + 2);

                                    for ($i = $start_page; $i <= $end_page; $i++):
                                    ?>
                                        <li class="page-item <?php echo $i === $filters['page'] ? 'active' : ''; ?>">
                                            <a class="page-link"
                                               href="<?php echo BASE_URL; ?>admin/employees?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($filters['page'] < $total_pages): ?>
                                        <li class="page-item">
                                            <a class="page-link"
                                               href="<?php echo BASE_URL; ?>admin/employees?<?php echo http_build_query(array_merge($_GET, ['page' => $filters['page'] + 1])); ?>">
                                                <i class="fas fa-chevron-right"></i>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.filter-card, .table-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    padding: 1.5rem;
}
.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}
.avatar-circle {
    display: inline-block;
}
</style>
