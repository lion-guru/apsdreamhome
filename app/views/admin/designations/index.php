<!-- Designation Management - Index -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-tag mr-2"></i> Designation Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                        <li class="breadcrumb-item active">Designations</li>
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

            <!-- Stats Row -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
                        <div class="inner">
                            <h3><?= $stats['total'] ?? 0 ?></h3>
                            <p>Total Designations</p>
                        </div>
                        <div class="icon"><i class="fas fa-user-tag"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: #fff;">
                        <div class="inner">
                            <h3><?= $stats['active'] ?? 0 ?></h3>
                            <p>Active</p>
                        </div>
                        <div class="icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                </div>
            </div>

            <!-- Filter + Action Bar -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <a href="/admin/designations/create" class="btn btn-primary">
                        <i class="fas fa-plus mr-1"></i> Add Designation
                    </a>
                    <a href="/admin/departments" class="btn btn-outline-secondary ml-2">
                        <i class="fas fa-building mr-1"></i> Departments
                    </a>
                </div>
                <div class="col-md-4 text-right">
                    <form method="GET" action="/admin/designations" class="form-inline justify-content-end">
                        <label class="mr-2">Filter by Dept:</label>
                        <select name="department_id" class="form-control form-control-sm" onchange="this.form.submit()">
                            <option value="">All Departments</option>
                            <?php foreach ($departments as $d): ?>
                                <option value="<?= $d['id'] ?>" <?= ($filter_dept ?? '') == $d['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d['code'] . ' — ' . $d['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Designation Table -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">All Designations</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Level</th>
                                <th>Salary Band</th>
                                <th>Sub-Role</th>
                                <th>Dashboard</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($designations)): ?>
                                <tr><td colspan="8" class="text-center text-muted py-4">No designations found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($designations as $desig): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($desig['name']) ?></strong></td>
                                        <td><span class="badge badge-info"><?= htmlspecialchars($desig['department_code'] ?? '') ?></span> <?= htmlspecialchars($desig['department_name'] ?? '') ?></td>
                                        <td>
                                            <?php
                                            $levelLabels = [1 => 'Junior', 2 => 'Executive', 3 => 'Senior', 4 => 'Manager', 5 => 'Director'];
                                            $levelColors = [1 => 'secondary', 2 => 'info', 3 => 'primary', 4 => 'warning', 5 => 'danger'];
                                            $lvl = $desig['level'] ?? 1;
                                            ?>
                                            <span class="badge badge-<?= $levelColors[$lvl] ?? 'secondary' ?>">
                                                L<?= $lvl ?> · <?= $levelLabels[$lvl] ?? 'Unknown' ?>
                                            </span>
                                        </td>
                                        <td>₹<?= number_format($desig['min_salary'] ?? 0) ?> — ₹<?= number_format($desig['max_salary'] ?? 0) ?></td>
                                        <td><code><?= htmlspecialchars($desig['sub_role'] ?? '') ?></code></td>
                                        <td><?= htmlspecialchars($desig['dashboard_view'] ?? '—') ?></td>
                                        <td>
                                            <?php if (($desig['status'] ?? '') === 'active'): ?>
                                                <span class="badge badge-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="/admin/designations/<?= $desig['id'] ?>/edit" class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="/admin/designations/<?= $desig['id'] ?>/delete" style="display:inline" onsubmit="return confirm('Delete this designation?')">
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
