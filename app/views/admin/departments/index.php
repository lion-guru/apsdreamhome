ï»¿<!-- Department Management - Index -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-building mr-2"></i> Department Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Departments</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <?php if (!empty($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?= $_SESSION['success'] ?>
                </div>
                <?php unset($_SESSION['success']); ?>
            <?php endif; ?>
            <?php if (!empty($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible">
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    <?= $_SESSION['error'] ?>
                </div>
                <?php unset($_SESSION['error']); ?>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box" class="style-75630">
                        <div class="inner">
                            <h3><?= $stats['total'] ?? 0 ?></h3>
                            <p>Total Departments</p>
                        </div>
                        <div class="icon"><i class="fas fa-building"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box" class="style-55192">
                        <div class="inner">
                            <h3><?= $stats['active'] ?? 0 ?></h3>
                            <p>Active</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box" class="style-48582">
                        <div class="inner">
                            <h3><?= $stats['total_desig'] ?? 0 ?></h3>
                            <p>Designations</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-tag"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box" class="style-23498">
                        <div class="inner">
                            <h3><?= $stats['total_emp'] ?? 0 ?></h3>
                            <p>Total Employees</p>
                        </div>
                        <div class="icon"><i class="fas fa-users"></i></div>
                    </div>
                </div>
            </div>

            <!-- Action Bar -->
            <div class="mb-3">
                <a href="<?= BASE_URL ?>/admin/departments/create" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Add Department
                </a>
                <a href="<?= BASE_URL ?>/admin/designations" class="btn btn-outline-secondary">
                    <i class="fas fa-user-tag mr-1"></i> Manage Designations
                </a>
            </div>

            <!-- Department Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Departments</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Head</th>
                                <th>Budget</th>
                                <th>Designations</th>
                                <th>Employees</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($departments)): ?>
                                <tr><td colspan="9" class="text-center py-5">
                                    <i class="fas fa-building fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No departments yet</h5>
                                    <p class="text-muted mb-3">Create your first department to organize your team structure.</p>
                                    <a href="<?= BASE_URL ?>/admin/departments/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add Department</a>
                                </td></tr>
                            <?php else: ?>
                                <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td><span class="badge badge-primary"><?= htmlspecialchars($dept['code'] ?? '') ?></span></td>
                                        <td><strong><?= htmlspecialchars($dept['name'] ?? '') ?></strong></td>
                                        <td><?= htmlspecialchars(mb_strimwidth($dept['description'] ?? '', 0, 60, '...')) ?></td>
                                        <td><?= htmlspecialchars($dept['head_name'] ?? '—') ?></td>
                                        <td>₹<?= number_format($dept['dept_budget'] ?? 0) ?></td>
                                        <td><span class="badge badge-info"><?= $dept['designation_count'] ?? 0 ?></span></td>
                                        <td><span class="badge badge-success"><?= $dept['employee_count'] ?? 0 ?></span></td>
                                        <td>
                                            <?php if (($dept['status'] ?? '') === 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?= BASE_URL ?>/admin/departments/<?= $dept['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/departments/<?= $dept['id'] ?>/delete" class="style-71727" onsubmit="return confirm('Delete this department? Designations will be orphaned.')">
    <?php echo CSRFProtection::csrfField(); ?>
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
