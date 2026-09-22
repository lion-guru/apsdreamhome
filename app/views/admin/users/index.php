<?php $layout = "admin/layouts/admin"; $active_page = "index"; ?>
<?php $csrf = $_SESSION['csrf_token'] ?? ''; ?>
<?php
// Build filter query string for pagination & sorting
$filterQs = '';
foreach (['search','role','status','date_from','date_to','sort_by','sort_order','per_page'] as $k) {
    if (!empty($filters[$k])) $filterQs .= '&' . $k . '=' . urlencode($filters[$k]);
}
// Sort toggle helper
function sortLink($col, $label, $filters) {
    $currentSort = $filters['sort_by'] ?? 'created_at';
    $currentOrder = $filters['sort_order'] ?? 'DESC';
    $newOrder = ($currentSort === $col && $currentOrder === 'DESC') ? 'ASC' : 'DESC';
    $icon = '';
    if ($currentSort === $col) {
        $icon = $currentOrder === 'DESC' ? '<i class="fas fa-sort-down ms-1"></i>' : '<i class="fas fa-sort-up ms-1"></i>';
    } else {
        $icon = '<i class="fas fa-sort ms-1 text-muted"></i>';
    }
    $qs = '';
    foreach (['search','role','status','date_from','date_to','per_page'] as $k) {
        if (!empty($filters[$k])) $qs .= '&' . $k . '=' . urlencode($filters[$k]);
    }
    return BASE_URL . '/admin/users?sort_by=' . $col . '&sort_order=' . $newOrder . $qs;
}
?>
<?php
$stats = $stats ?? ['total'=>0,'active'=>0,'new_this_month'=>0,'pending'=>0];
$perPageOptions = $per_page_options ?? [10,20,50,100];
$perPage = $per_page ?? 20;
?>

<!-- Stats Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-white-50 small">Total Users</p>
                        <h3 class="mb-0"><?= number_format($stats['total'] ?? 0) ?></h3>
                    </div>
                    <i class="fas fa-users fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-white-50 small">Active Users</p>
                        <h3 class="mb-0"><?= number_format($stats['active'] ?? 0) ?></h3>
                    </div>
                    <i class="fas fa-user-check fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-white-50 small">New This Month</p>
                        <h3 class="mb-0"><?= number_format($stats['new_this_month'] ?? 0) ?></h3>
                    </div>
                    <i class="fas fa-user-plus fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 text-muted small">Pending Approval</p>
                        <h3 class="mb-0"><?= number_format($stats['pending'] ?? 0) ?></h3>
                    </div>
                    <i class="fas fa-clock fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Analytics Dashboard (Collapsible) -->
<div class="card border-0 shadow-sm mb-4" id="analyticsDashboard">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Analytics Dashboard</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="toggleAnalytics()" id="analyticsToggleBtn">
                <i class="fas fa-chevron-up" id="analyticsToggleIcon"></i>
            </button>
            <button class="btn btn-sm btn-outline-primary" onclick="refreshAnalytics()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
        </div>
    </div>
    <div class="card-body" id="analyticsBody">
        <div class="row g-3">
            <!-- Users by Role -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white"><h6 class="mb-0">Users by Role</h6></div>
                    <div class="card-body d-flex align-items-center justify-content-center p-4">
                        <canvas id="roleChart" width="250" height="250"></canvas>
                    </div>
                    <div class="card-footer bg-white p-3" id="roleLegend"></div>
                </div>
            </div>
            <!-- Users by Status -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white"><h6 class="mb-0">Users by Status</h6></div>
                    <div class="card-body d-flex align-items-center justify-content-center p-4">
                        <canvas id="statusChart" width="250" height="250"></canvas>
                    </div>
                    <div class="card-footer bg-white p-3" id="statusLegend"></div>
                </div>
            </div>
            <!-- Registrations Trend (Last 30 Days) -->
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white"><h6 class="mb-0">Registrations (30 Days)</h6></div>
                    <div class="card-body">
                        <canvas id="trendChart" width="250" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Activity Feed Widget -->
<div class="card border-0 shadow-sm mb-4" id="activityFeedWidget">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-activity me-2 text-primary"></i>Recent Activity</h5>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary" onclick="toggleActivityFeed()" id="activityToggleBtn">
                <i class="fas fa-chevron-up" id="activityToggleIcon"></i>
            </button>
            <a href="<?= BASE_URL ?>/admin/activity-log" class="btn btn-sm btn-primary" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i>Full Log
            </a>
        </div>
    </div>
    <div class="card-body p-0" id="activityFeedBody">
        <div class="list-group list-group-flush" id="activityFeedList">
            <div class="text-center py-4" id="activityLoading">
                <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                <span class="text-muted">Loading activity...</span>
            </div>
        </div>
    </div>
</div>

<!-- Users Header -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h1 class="h3 mb-1">User Management</h1>
        <p class="text-muted mb-0">Manage all system users &middot; <strong><?= number_format($total ?? 0) ?></strong> total</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= BASE_URL ?>/admin/users/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add User
        </a>
        <a href="<?= BASE_URL ?>/admin/users/import" class="btn btn-outline-info">
            <i class="fas fa-file-import me-2"></i>Import CSV
        </a>
        <div class="btn-group">
            <a href="<?= BASE_URL ?>/admin/users/export?<?= ltrim($filterQs, '&') ?>&format=csv" class="btn btn-outline-secondary">
                <i class="fas fa-file-csv me-2"></i>Export CSV
            </a>
            <a href="<?= BASE_URL ?>/admin/users/export?<?= ltrim($filterQs, '&') ?>&format=xlsx" class="btn btn-outline-secondary">
                <i class="fas fa-file-excel me-2"></i>Excel
            </a>
        </div>
        <!-- Column Toggle -->
        <div class="btn-group">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-columns me-1"></i>Columns
            </button>
            <ul class="dropdown-menu dropdown-menu-end p-3" style="width: 220px;" onclick="event.stopPropagation();">
                <li><h6 class="dropdown-header">Visible Columns</h6></li>
                <li><label class="dropdown-item p-1 m-0"><input type="checkbox" class="col-toggle" value="email" checked> Email</label></li>
                <li><label class="dropdown-item p-1 m-0"><input type="checkbox" class="col-toggle" value="role" checked> Role</label></li>
                <li><label class="dropdown-item p-1 m-0"><input type="checkbox" class="col-toggle" value="status" checked> Status</label></li>
                <li><label class="dropdown-item p-1 m-0"><input type="checkbox" class="col-toggle" value="reg_status" checked> Reg. Status</label></li>
                <li><label class="dropdown-item p-1 m-0"><input type="checkbox" class="col-toggle" value="phone" checked> Phone</label></li>
                <li><label class="dropdown-item p-1 m-0"><input type="checkbox" class="col-toggle" value="joined" checked> Joined</label></li>
                <li><label class="dropdown-item p-1 m-0"><input type="checkbox" class="col-toggle" value="properties" checked> Properties</label></li>
                <li><label class="dropdown-item p-1 m-0"><input type="checkbox" class="col-toggle" value="bookings" checked> Bookings</label></li>
                <li><label class="dropdown-item p-1 m-0"><input type="checkbox" class="col-toggle" value="actions" checked> Actions</label></li>
                <li><hr class="dropdown-divider"></li>
                <li><button class="dropdown-item btn btn-sm btn-outline-primary w-100" onclick="resetColumns()"><i class="fas fa-undo me-1"></i>Reset to Default</button></li>
            </ul>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" class="row g-2 align-items-end" id="userFilterForm">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Search</label>
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search name, email, phone..." value="<?= htmlspecialchars($filters['search'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Role</label>
                <select name="role" class="form-select form-select-sm">
                    <option value="">All Roles</option>
                    <?php foreach (['admin','super_admin','manager','employee','telecaller','associate','agent','customer','user'] as $r): ?>
                    <option value="<?= $r ?>" <?= ($filters['role'] ?? '') === $r ? 'selected' : '' ?>><?= ucfirst(str_replace('_', ' ', $r)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Status</option>
                    <option value="active" <?= ($filters['status'] ?? '') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($filters['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    <option value="suspended" <?= ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' ?>>Suspended</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Date From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_from'] ?? '') ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted mb-1">Date To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= htmlspecialchars($filters['date_to'] ?? '') ?>">
            </div>
            <div class="col-md-1">
                <label class="form-label small text-muted mb-1">Per Page</label>
                <select name="per_page" class="form-select form-select-sm" onchange="this.form.submit()">
                    <?php foreach ($perPageOptions as $opt): ?>
                    <option value="<?= $opt ?>" <?= ($perPage == $opt) ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-sm btn-outline-secondary">Clear</a>
            </div>
        </form>
    </div>
</div>

<!-- Bulk Actions Toolbar (hidden until selection) -->
<div id="bulkToolbar" class="card border-0 shadow-sm mb-3 d-none">
    <div class="card-body py-2 d-flex align-items-center gap-3 flex-wrap">
        <span class="text-muted"><strong id="selectedCount">0</strong> users selected</span>
        <div class="btn-group btn-group-sm">
            <button onclick="bulkAction('activate')" class="btn btn-outline-success"><i class="fas fa-check me-1"></i>Activate</button>
            <button onclick="bulkAction('deactivate')" class="btn btn-outline-warning"><i class="fas fa-ban me-1"></i>Deactivate</button>
            <button onclick="bulkAction('suspend')" class="btn btn-outline-danger"><i class="fas fa-times me-1"></i>Suspend</button>
        </div>
        <div class="btn-group btn-group-sm ms-2">
            <button onclick="exportSelected('csv')" class="btn btn-outline-primary" title="Export selected to CSV">
                <i class="fas fa-file-csv me-1"></i>Export CSV
            </button>
            <button onclick="exportSelected('xlsx')" class="btn btn-outline-primary" title="Export selected to Excel">
                <i class="fas fa-file-excel me-1"></i>Excel
            </button>
        </div>
        <button onclick="clearSelection()" class="btn btn-sm btn-outline-secondary ms-auto">Clear Selection</button>
    </div>
</div>

<?php if (isset($success) && $success): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="fas fa-check-circle me-2"></i><?php echo e($success); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- Users Table -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" id="usersTable">
                <thead class="bg-light">
                    <tr>
                        <th class="border-0 ps-4" style="width: 40px;" data-col="select">
                            <input type="checkbox" id="selectAll" class="form-check-input" title="Select all">
                        </th>
                        <th class="border-0" data-col="user"><a href="<?= sortLink('name', 'User', $filters) ?>" class="text-decoration-none text-dark">User<?= $iconName ?? '' ?></a></th>
                        <th class="border-0" data-col="email"><a href="<?= sortLink('email', 'Email', $filters) ?>" class="text-decoration-none text-dark">Email<?= $iconEmail ?? '' ?></a></th>
                        <th class="border-0" data-col="role"><a href="<?= sortLink('role', 'Role', $filters) ?>" class="text-decoration-none text-dark">Role<?= $iconRole ?? '' ?></a></th>
                        <th class="border-0" data-col="status"><a href="<?= sortLink('status', 'Status', $filters) ?>" class="text-decoration-none text-dark">Status<?= $iconStatus ?? '' ?></a></th>
                        <th class="border-0" data-col="reg_status"><a href="<?= sortLink('registration_status', 'Reg. Status', $filters) ?>" class="text-decoration-none text-dark">Reg. Status<?= $iconReg ?? '' ?></a></th>
                        <th class="border-0" data-col="phone"><a href="<?= sortLink('phone', 'Phone', $filters) ?>" class="text-decoration-none text-dark">Phone<?= $iconPhone ?? '' ?></a></th>
                        <th class="border-0" data-col="joined"><a href="<?= sortLink('created_at', 'Joined', $filters) ?>" class="text-decoration-none text-dark">Joined<?= $iconCreated ?? '' ?></a></th>
                        <th class="border-0" data-col="properties"><a href="<?= sortLink('property_count', 'Properties', $filters) ?>" class="text-decoration-none text-dark">Properties<?= $iconProperties ?? '' ?></a></th>
                        <th class="border-0" data-col="bookings"><a href="<?= sortLink('booking_count', 'Bookings', $filters) ?>" class="text-decoration-none text-dark">Bookings<?= $iconBookings ?? '' ?></a></th>
                        <th class="border-0 text-end pe-4" data-col="actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($users)): ?>
                    <?php foreach ($users as $user): ?>
                    <?php
                        $regStatus = $user['registration_status'] ?? 'approved';
                        $regBadgeClass = match($regStatus) {
                            'approved' => 'success',
                            'pending' => 'warning',
                            'rejected' => 'danger',
                            default => 'secondary'
                        };
                        $statusBadgeClass = ($user['status'] ?? 'active') === 'active' ? 'success' : 'secondary';
                        $isAdminRole = in_array($user['role'] ?? '', ['admin', 'super_admin']);
                        $isOnline = isset($user['last_login_at']) && strtotime($user['last_login_at']) > strtotime('-5 minutes');
                    ?>
                    <tr data-user-id="<?= $user['id'] ?>">
                        <td class="ps-4" data-col="select">
                            <input type="checkbox" class="form-check-input user-checkbox" value="<?= $user['id'] ?>" data-role="<?= htmlspecialchars($user['role'] ?? '') ?>">
                        </td>
                        <td data-col="user">
                            <div class="d-flex align-items-center">
                                <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 position-relative" style="width:36px;height:36px;">
                                    <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                                    <?php if ($isOnline): ?>
                                    <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width:10px;height:10px;"></span>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <span class="inline-edit fw-semibold text-decoration-none" data-field="name" data-user-id="<?= $user['id'] ?>" title="Click to edit"><?php echo htmlspecialchars($user['name'] ?? 'Unknown'); ?></span>
                                    <br><small class="text-muted inline-edit" data-field="phone" data-user-id="<?= $user['id'] ?>" title="Click to edit"><?php echo htmlspecialchars($user['phone'] ?? ''); ?></small>
                                </div>
                            </div>
                        </td>
                        <td data-col="email"><?php echo htmlspecialchars($user['email'] ?? ''); ?></td>
                        <td data-col="role">
                            <span class="badge bg-primary bg-opacity-75 inline-edit" data-field="role" data-user-id="<?= $user['id'] ?>" title="Click to edit" style="cursor: pointer;">
                                <?php
                                    $roleLabels = [
                                        'super_admin' => 'Super Admin',
                                        'sales_director' => 'Sales Director',
                                        'marketing_director' => 'Marketing Director',
                                    ];
                                    echo $roleLabels[$user['role'] ?? ''] ?? ucfirst(str_replace('_', ' ', $user['role'] ?? 'user'));
                                ?>
                            </span>
                        </td>
                        <td data-col="status">
                            <span class="badge bg-<?= $statusBadgeClass ?> inline-edit" data-field="status" data-user-id="<?= $user['id'] ?>" title="Click to edit" style="cursor: pointer;"><?= ucfirst($user['status'] ?? 'active') ?></span>
                        </td>
                        <td data-col="reg_status"><span class="badge bg-<?= $regBadgeClass ?>"><?= ucfirst($regStatus) ?></span></td>
                        <td data-col="phone"><span class="inline-edit" data-field="phone" data-user-id="<?= $user['id'] ?>" title="Click to edit"><?php echo htmlspecialchars($user['phone'] ?? ''); ?></span></td>
                        <td data-col="joined"><?= date('M d, Y', strtotime($user['created_at'] ?? 'now')) ?></td>
                        <td data-col="properties"><?= $user['property_count'] ?? 0 ?></td>
                        <td data-col="bookings"><?= $user['booking_count'] ?? 0 ?></td>
                        <td class="text-end pe-4" data-col="actions">
                            <div class="btn-group btn-group-sm">
                                <a href="<?= BASE_URL ?>/admin/users/<?php echo e($user['id']); ?>" class="btn btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                                <a href="<?= BASE_URL ?>/admin/users/<?php echo e($user['id']); ?>/edit" class="btn btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="<?= BASE_URL ?>/admin/users/<?php echo e($user['id']); ?>/wallet" class="btn btn-outline-success" title="Wallet"><i class="fas fa-wallet"></i></a>
                                <?php if (!$isAdminRole): ?>
                                <a href="<?= BASE_URL ?>/admin/users/<?php echo e($user['id']); ?>/impersonate" class="btn btn-outline-warning" title="Login as User" onclick="return confirm('Login as this user?')"><i class="fas fa-user-secret"></i></a>
                                <button class="btn btn-outline-danger" title="Force Password Reset" onclick="forcePasswordReset(<?= $user['id'] ?>)"><i class="fas fa-key"></i></button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <tr>
                        <td colspan="11" class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
                            <h5 class="text-muted">No users found</h5>
                            <p class="text-muted mb-3">Add your first user or adjust your filters.</p>
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="<?= BASE_URL ?>/admin/users/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add User</a>
                                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary">Clear Filters</a>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Mobile Card View (hidden on desktop) -->
<div class="d-lg-none" id="mobileCardView">
    <?php if (!empty($users)): ?>
    <div class="row g-3" id="mobileCardsContainer">
        <?php foreach ($users as $user): ?>
        <?php
            $regStatus = $user['registration_status'] ?? 'approved';
            $regBadgeClass = match($regStatus) {
                'approved' => 'success',
                'pending' => 'warning',
                'rejected' => 'danger',
                default => 'secondary'
            };
            $statusBadgeClass = ($user['status'] ?? 'active') === 'active' ? 'success' : 'secondary';
            $isAdminRole = in_array($user['role'] ?? '', ['admin', 'super_admin']);
            $isOnline = isset($user['last_login_at']) && strtotime($user['last_login_at']) > strtotime('-5 minutes');
        ?>
        <div class="col-12" data-user-id="<?= $user['id'] ?>">
            <div class="card border-0 shadow-sm h-100 mobile-user-card">
                <div class="card-body">
                    <!-- Header with avatar and status -->
                    <div class="d-flex align-items-start justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 position-relative" style="width:44px;height:44px;">
                                <?php echo strtoupper(substr($user['name'] ?? 'U', 0, 1)); ?>
                                <?php if ($isOnline): ?>
                                <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" style="width:12px;height:12px;"></span>
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-semibold"><?= htmlspecialchars($user['name'] ?? 'Unknown') ?></h6>
                                <small class="text-muted"><?= htmlspecialchars($user['email'] ?? '') ?></small>
                            </div>
                        </div>
                        <div class="d-flex gap-1">
                            <span class="badge bg-<?= $statusBadgeClass ?>"><?= ucfirst($user['status'] ?? 'active') ?></span>
                            <span class="badge bg-<?= $regBadgeClass ?>"><?= ucfirst($regStatus) ?></span>
                        </div>
                    </div>

                    <!-- Details grid -->
                    <div class="row g-2 mb-3 text-small">
                        <div class="col-6">
                            <span class="text-muted">Role:</span>
                            <div class="fw-medium">
                                <?php
                                    $roleLabels = [
                                        'super_admin' => 'Super Admin',
                                        'sales_director' => 'Sales Director',
                                        'marketing_director' => 'Marketing Director',
                                    ];
                                    echo $roleLabels[$user['role'] ?? ''] ?? ucfirst(str_replace('_', ' ', $user['role'] ?? 'user'));
                                ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Phone:</span>
                            <div class="fw-medium"><?= htmlspecialchars($user['phone'] ?? '—') ?></div>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Joined:</span>
                            <div class="fw-medium"><?= date('M d, Y', strtotime($user['created_at'] ?? 'now')) ?></div>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Reg. Status:</span>
                            <div class="fw-medium"><span class="badge bg-<?= $regBadgeClass ?>"><?= ucfirst($regStatus) ?></span></div>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Properties:</span>
                            <div class="fw-medium"><?= $user['property_count'] ?? 0 ?></div>
                        </div>
                        <div class="col-6">
                            <span class="text-muted">Bookings:</span>
                            <div class="fw-medium"><?= $user['booking_count'] ?? 0 ?></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="d-flex gap-2 flex-wrap pt-2 border-top">
                        <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>" class="btn btn-sm btn-outline-info flex-fill" title="View">
                            <i class="fas fa-eye me-1"></i>View
                        </a>
                        <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>/edit" class="btn btn-sm btn-outline-primary flex-fill" title="Edit">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>/wallet" class="btn btn-sm btn-outline-success flex-fill" title="Wallet">
                            <i class="fas fa-wallet me-1"></i>Wallet
                        </a>
                        <?php if (!$isAdminRole): ?>
                        <a href="<?= BASE_URL ?>/admin/users/<?= $user['id'] ?>/impersonate" class="btn btn-sm btn-outline-warning flex-fill" title="Login as User" onclick="return confirm('Login as this user?')">
                            <i class="fas fa-user-secret me-1"></i>Impersonate
                        </a>
                        <button class="btn btn-sm btn-outline-danger flex-fill" title="Force Password Reset" onclick="forcePasswordReset(<?= $user['id'] ?>)">
                            <i class="fas fa-key me-1"></i>Reset PWD
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="fas fa-users fa-3x text-muted mb-3 d-block"></i>
            <h5 class="text-muted">No users found</h5>
            <p class="text-muted mb-3">Add your first user or adjust your filters.</p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="<?= BASE_URL ?>/admin/users/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>Add User</a>
                <a href="<?= BASE_URL ?>/admin/users" class="btn btn-outline-secondary">Clear Filters</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if (!empty($total_pages) && $total_pages > 1): ?>
<nav class="mt-3"><ul class="pagination justify-content-center">
    <?php $currentPage = $page ?? 1; ?>
    <?php if ($currentPage > 1): ?>
    <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>/admin/users?page=<?= $currentPage - 1 ?><?= $filterQs ?>">Prev</a></li>
    <?php endif; ?>
    <?php for ($i = max(1, $currentPage - 2); $i <= min($total_pages, $currentPage + 2); $i++): ?>
    <li class="page-item <?= $currentPage == $i ? 'active' : '' ?>">
        <a class="page-link" href="<?= BASE_URL ?>/admin/users?page=<?= $i ?><?= $filterQs ?>"><?= $i ?></a>
    </li>
    <?php endfor; ?>
    <?php if ($currentPage < $total_pages): ?>
    <li class="page-item"><a class="page-link" href="<?= BASE_URL ?>/admin/users?page=<?= $currentPage + 1 ?><?= $filterQs ?>">Next</a></li>
    <?php endif; ?>
</ul></nav>
<?php endif; ?>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const CSRF = '<?= $csrf ?>';

// Sort icons - determine current sort column from URL
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const sortBy = urlParams.get('sort_by') || 'created_at';
    const sortOrder = urlParams.get('sort_order') || 'DESC';
    const icons = {
        'name': 'iconName',
        'email': 'iconEmail',
        'role': 'iconRole',
        'status': 'iconStatus',
        'registration_status': 'iconReg',
        'created_at': 'iconCreated',
        'phone': 'iconPhone',
        'property_count': 'iconProperties',
        'booking_count': 'iconBookings'
    };
    if (icons[sortBy]) {
        window[icons[sortBy]] = sortOrder === 'DESC' ? '<i class="fas fa-sort-down ms-1"></i>' : '<i class="fas fa-sort-up ms-1"></i>';
    }
});

// Select All
document.getElementById('selectAll')?.addEventListener('change', function() {
    document.querySelectorAll('.user-checkbox').forEach(cb => { cb.checked = this.checked; });
    updateBulkToolbar();
});

// Individual checkboxes
document.querySelectorAll('.user-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkToolbar);
});

function updateBulkToolbar() {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const count = checked.length;
    document.getElementById('selectedCount').textContent = count;
    document.getElementById('bulkToolbar').style.display = count > 0 ? 'block' : 'none';
    const all = document.querySelectorAll('.user-checkbox');
    const selAll = document.getElementById('selectAll');
    if (selAll) selAll.checked = all.length > 0 && count === all.length;
}

function clearSelection() {
    document.querySelectorAll('.user-checkbox').forEach(cb => { cb.checked = false; });
    document.getElementById('selectAll').checked = false;
    updateBulkToolbar();
}

function bulkAction(action) {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const ids = Array.from(checked).map(cb => parseInt(cb.value));
    if (!ids.length) return showToast('No users selected', 'info');

    const adminRoles = ['admin', 'super_admin'];
    const hasAdmin = Array.from(checked).some(cb => adminRoles.includes(cb.dataset.role));
    if (hasAdmin && (action === 'deactivate' || action === 'suspend')) {
        return showToast('Cannot ' + action + ' admin users', 'info');
    }

    apsConfirm('Bulk ' + action + ' ' + ids.length + ' user(s)?').then(function(ok) {
        if (!ok) return;
        showLoader();

        fetch(BASE_URL + '/admin/users/bulk-operation', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: 'csrf_token=' + encodeURIComponent(CSRF) + '&bulk_action=' + action + '&' + ids.map(id => 'user_ids[]=' + id).join('&')
        }).then(r => r.json()).then(d => {
            if (d.success) { location.reload(); } else { showToast(d.message || 'Failed', 'danger'); }
}).catch(() => showToast('Network error', 'danger')).finally(() => hideLoader());
    }
}

function exportSelected(format) {
    const checked = document.querySelectorAll('.user-checkbox:checked');
    const ids = Array.from(checked).map(cb => parseInt(cb.value));
    if (!ids.length) return showToast('No users selected', 'info');

    apsConfirm('Export ' + ids.length + ' selected user(s) to ' + format.toUpperCase() + '?').then(function(ok) {
        if (!ok) return;
        showLoader();

        const formData = new URLSearchParams();
        formData.append('csrf_token', CSRF);
        formData.append('format', format);
        ids.forEach(id => formData.append('user_ids[]', id));

        fetch(BASE_URL + '/admin/users/export-selected', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
            body: formData
        }).then(r => r.blob()).then(blob => {
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'users-selected-' + new Date().toISOString().split('T')[0] + '.' + (format === 'xlsx' ? 'xls' : 'csv');
            document.body.appendChild(a);
            a.click();
            a.remove();
            window.URL.revokeObjectURL(url);
            showToast('Export downloaded', 'success');
        }).catch(() => showToast('Export failed', 'danger')).finally(() => hideLoader());
    });
}
    if (!confirm('Force password reset for this user? They will be required to change password on next login.')) return;
    showLoader();
    fetch(BASE_URL + '/admin/users/' + userId + '/force-password-reset', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent(CSRF)
    }).then(r => r.json()).then(d => {
        if (d.success) { showToast(d.message, 'success'); } else { showToast(d.message || 'Failed', 'danger'); }
    }).catch(() => showToast('Network error', 'danger')).finally(() => hideLoader());
}

// Column Toggle Logic
const COL_STORAGE_KEY = 'user_table_columns';
const DEFAULT_VISIBLE = ['user','email','role','status','reg_status','phone','joined','properties','bookings','actions'];

function getVisibleColumns() {
    try {
        const stored = localStorage.getItem(COL_STORAGE_KEY);
        return stored ? JSON.parse(stored) : DEFAULT_VISIBLE;
    } catch { return DEFAULT_VISIBLE; }
}

function setVisibleColumns(cols) {
    localStorage.setItem(COL_STORAGE_KEY, JSON.stringify(cols));
}

function applyColumnVisibility() {
    const visible = getVisibleColumns();
    document.querySelectorAll('[data-col]').forEach(el => {
        const col = el.getAttribute('data-col');
        const isVisible = visible.includes(col);
        el.style.display = isVisible ? '' : 'none';
    });
    // Update checkboxes
    document.querySelectorAll('.col-toggle').forEach(cb => {
        cb.checked = visible.includes(cb.value);
    });
}

function resetColumns() {
    setVisibleColumns(DEFAULT_VISIBLE);
    applyColumnVisibility();
    showToast('Columns reset to default', 'info');
}

// Initialize on load
document.addEventListener('DOMContentLoaded', function() {
    applyColumnVisibility();
    
    // Column toggle checkboxes
    document.querySelectorAll('.col-toggle').forEach(cb => {
        cb.addEventListener('change', function() {
            const visible = getVisibleColumns();
            if (this.checked) {
                if (!visible.includes(this.value)) visible.push(this.value);
            } else {
                const idx = visible.indexOf(this.value);
                if (idx > -1) visible.splice(idx, 1);
            }
            setVisibleColumns(visible);
            applyColumnVisibility();
        });
    });
});

// Inline Edit Logic
document.addEventListener('click', function(e) {
    const target = e.target.closest('.inline-edit');
    if (!target) return;
    
    const field = target.dataset.field;
    const userId = target.dataset.userId;
    const currentValue = target.textContent.trim();
    
    if (field === 'status') {
        // Show dropdown for status
        const select = document.createElement('select');
        select.className = 'form-select form-select-sm';
        ['active','inactive','suspended'].forEach(s => {
            const opt = document.createElement('option');
            opt.value = s;
            opt.textContent = s.charAt(0).toUpperCase() + s.slice(1);
            if (s === currentValue.toLowerCase()) opt.selected = true;
            select.appendChild(opt);
        });
        target.replaceWith(select);
        select.focus();
        
        select.addEventListener('change', function() {
            saveInlineEdit(userId, field, this.value, target);
        });
        select.addEventListener('blur', function() {
            saveInlineEdit(userId, field, this.value, target);
        });
        return;
    }
    
    if (field === 'role') {
        // Show dropdown for role
        const select = document.createElement('select');
        select.className = 'form-select form-select-sm';
        ['customer','associate','agent','employee','telecaller','user','manager','admin','super_admin'].forEach(r => {
            const opt = document.createElement('option');
            opt.value = r;
            opt.textContent = r.charAt(0).toUpperCase() + r.slice(1).replace('_', ' ');
            if (r === currentValue.toLowerCase()) opt.selected = true;
            select.appendChild(opt);
        });
        target.replaceWith(select);
        select.focus();
        
        select.addEventListener('change', function() {
            saveInlineEdit(userId, field, this.value, target);
        });
        select.addEventListener('blur', function() {
            saveInlineEdit(userId, field, this.value, target);
        });
        return;
    }
    
    // Text input for name, phone
    const input = document.createElement('input');
    input.type = 'text';
    input.value = currentValue;
    input.className = 'form-control form-control-sm';
    target.replaceWith(input);
    input.focus();
    input.select();
    
    input.addEventListener('blur', function() {
        saveInlineEdit(userId, field, this.value, target);
    });
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            this.blur();
        } else if (e.key === 'Escape') {
            this.replaceWith(target);
        }
    });
});

function saveInlineEdit(userId, field, value, originalEl) {
    if (!value || !value.trim()) {
        showToast('Value cannot be empty', 'warning');
        originalEl.replaceWith(originalEl);
        return;
    }
    
    showLoader();
    fetch(BASE_URL + '/admin/users/' + userId + '/inline-update', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest'},
        body: 'csrf_token=' + encodeURIComponent(CSRF) + '&field=' + field + '&value=' + encodeURIComponent(value.trim())
    }).then(r => r.json()).then(d => {
        if (d.success) {
            showToast(d.message, 'success');
            // Update the element with new value
            if (field === 'status' || field === 'role') {
                const badgeClass = field === 'status' ? 
                    (value === 'active' ? 'success' : (value === 'inactive' ? 'secondary' : 'danger')) : 
                    'primary bg-opacity-75';
                originalEl.outerHTML = `<span class="badge bg-${badgeClass} inline-edit" data-field="${field}" data-user-id="${userId}" title="Click to edit" style="cursor: pointer;">${value.charAt(0).toUpperCase() + value.slice(1)}</span>`;
            } else {
                originalEl.textContent = value;
                originalEl.classList.add('inline-edit');
            }
        } else {
            showToast(d.message || 'Failed', 'danger');
            originalEl.replaceWith(originalEl);
        }
    }).catch(() => {
        showToast('Network error', 'danger');
        originalEl.replaceWith(originalEl);
    }).finally(() => hideLoader());
}

// Mobile Card View Sync Logic
function syncMobileCards() {
    // Sync checkboxes from table to mobile cards
    document.querySelectorAll('.user-checkbox').forEach(tableCb => {
        const userId = tableCb.value;
        const mobileCb = document.querySelector(`#mobileCardView [data-user-id="${userId}"] .form-check-input`);
        if (mobileCb) {
            mobileCb.checked = tableCb.checked;
            mobileCb.dataset.role = tableCb.dataset.role;
        }
    });
    
    // Sync select all
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        const mobileSelectAll = document.getElementById('mobileSelectAll');
        if (mobileSelectAll) mobileSelectAll.checked = selectAll.checked;
    }
}

function initMobileCardCheckboxes() {
    // Add select all checkbox to mobile view if not exists
    const mobileContainer = document.getElementById('mobileCardsContainer');
    if (!mobileContainer) return;
    
    // Add select all checkbox at top of mobile cards
    const selectAllHtml = `
        <div class="col-12 mb-3" id="mobileSelectAllWrapper" style="display: none;">
            <div class="card border-0 bg-light">
                <div class="card-body py-2 d-flex align-items-center">
                    <input type="checkbox" id="mobileSelectAll" class="form-check-input me-2">
                    <span class="text-muted small"><strong id="mobileSelectedCount">0</strong> users selected</span>
                    <div class="ms-auto btn-group btn-group-sm">
                        <button onclick="bulkAction('activate')" class="btn btn-outline-success"><i class="fas fa-check me-1"></i>Activate</button>
                        <button onclick="bulkAction('deactivate')" class="btn btn-outline-warning"><i class="fas fa-ban me-1"></i>Deactivate</button>
                        <button onclick="bulkAction('suspend')" class="btn btn-outline-danger"><i class="fas fa-times me-1"></i>Suspend</button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    if (!document.getElementById('mobileSelectAllWrapper')) {
        mobileContainer.insertAdjacentHTML('afterbegin', selectAllHtml);
    }
    
    // Mobile checkboxes
    document.querySelectorAll('#mobileCardView .user-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            // Sync with table
            const tableCb = document.querySelector(`.user-checkbox[value="${this.value}"]`);
            if (tableCb) tableCb.checked = this.checked;
            updateBulkToolbar();
            updateMobileBulkToolbar();
        });
    });
    
    // Mobile select all
    const mobileSelectAll = document.getElementById('mobileSelectAll');
    if (mobileSelectAll) {
        mobileSelectAll.addEventListener('change', function() {
            document.querySelectorAll('#mobileCardView .user-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
            document.querySelectorAll('.user-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkToolbar();
            updateMobileBulkToolbar();
        });
    }
}

function updateMobileBulkToolbar() {
    const checked = document.querySelectorAll('#mobileCardView .user-checkbox:checked');
    const count = checked.length;
    const wrapper = document.getElementById('mobileSelectAllWrapper');
    const countEl = document.getElementById('mobileSelectedCount');
    const selectAll = document.getElementById('mobileSelectAll');
    
    if (countEl) countEl.textContent = count;
    if (wrapper) wrapper.style.display = count > 0 ? 'block' : 'none';
    if (selectAll) {
        const all = document.querySelectorAll('#mobileCardView .user-checkbox');
        selectAll.checked = all.length > 0 && count === all.length;
    }
}

// Override updateBulkToolbar to also sync mobile
const originalUpdateBulkToolbar = updateBulkToolbar;
updateBulkToolbar = function() {
    originalUpdateBulkToolbar();
    syncMobileCards();
    updateMobileBulkToolbar();
};

// Initialize mobile cards
document.addEventListener('DOMContentLoaded', function() {
    initMobileCardCheckboxes();
    
    // Re-init after bulk actions
    const observer = new MutationObserver(() => {
        initMobileCardCheckboxes();
    });
    observer.observe(document.getElementById('mobileCardsContainer'), { childList: true, subtree: true });
});

// Keyboard Shortcuts
document.addEventListener('keydown', function(e) {
    // Ignore if typing in input/textarea/select
    if (['INPUT', 'TEXTAREA', 'SELECT'].includes(e.target.tagName)) return;
    
    // Ctrl/Cmd + F = Focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
        e.preventDefault();
        document.querySelector('input[name="search"]')?.focus();
    }
    
    // Ctrl/Cmd + N = New user
    if ((e.ctrlKey || e.metaKey) && e.key === 'n') {
        e.preventDefault();
        window.location.href = BASE_URL + '/admin/users/create';
    }
    
    // Ctrl/Cmd + E = Export
    if ((e.ctrlKey || e.metaKey) && e.key === 'e') {
        e.preventDefault();
        window.location.href = BASE_URL + '/admin/users/export?' + new URLSearchParams(new URLSearchParams(window.location.search)).toString() + '&format=csv';
    }
    
    // Escape = Clear selection / Close modals
    if (e.key === 'Escape') {
        clearSelection();
        document.querySelectorAll('.modal.show').forEach(m => bootstrap.Modal.getInstance(m)?.hide());
    }
    
    // Ctrl/Cmd + A = Select all (when not in input)
    if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
        e.preventDefault();
        document.getElementById('selectAll')?.click();
    }
    
    // / (forward slash) = Focus search
    if (e.key === '/' && !e.ctrlKey && !e.metaKey) {
        e.preventDefault();
        document.querySelector('input[name="search"]')?.focus();
    }
    
    // Arrow keys for pagination (when not in input)
    if (e.key === 'ArrowRight') {
        const nextLink = document.querySelector('.pagination .page-item:not(.disabled) .page-link[href*="page="]:last-of-type');
        if (nextLink && nextLink.textContent.trim() === 'Next') nextLink.click();
    }
    if (e.key === 'ArrowLeft') {
        const prevLink = document.querySelector('.pagination .page-item:not(.disabled) .page-link[href*="page="]');
        if (prevLink && prevLink.textContent.trim() === 'Prev') prevLink.click();
    }
});

// Show keyboard shortcuts help on Shift+?
document.addEventListener('keydown', function(e) {
    if (e.shiftKey && e.key === '?') {
        e.preventDefault();
        const shortcuts = [
            ['/', 'Focus search'],
            ['Ctrl+N', 'New user'],
            ['Ctrl+E', 'Export CSV'],
            ['Ctrl+F', 'Focus search'],
            ['Ctrl+A', 'Select all'],
            ['Esc', 'Clear selection / Close modals'],
            ['→', 'Next page'],
            ['←', 'Previous page'],
            ['Shift+?', 'Show this help']
        ];
        const html = shortcuts.map(([k, v]) => `<div class="d-flex justify-content-between"><kbd class="me-2">${k}</kbd><span>${v}</span></div>`).join('');
        Swal.fire({
            title: 'Keyboard Shortcuts',
            html: '<div class="text-start">' + html + '</div>',
            confirmButtonText: 'Got it',
            width: 400
        });
    }
});
});
}

// Activity Feed Widget
let activityFeedCollapsed = false;
let activityFeedInterval = null;

function toggleActivityFeed() {
    const body = document.getElementById('activityFeedBody');
    const icon = document.getElementById('activityToggleIcon');
    
    activityFeedCollapsed = !activityFeedCollapsed;
    body.style.display = activityFeedCollapsed ? 'none' : 'block';
    icon.className = activityFeedCollapsed ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
}

function loadActivityFeed() {
    const list = document.getElementById('activityFeedList');
    const loading = document.getElementById('activityLoading');
    
    fetch(BASE_URL + '/admin/api/activity-feed?limit=10')
        .then(r => r.json())
        .then(data => {
            loading.style.display = 'none';
            
            if (!data.success || !data.activities?.length) {
                list.innerHTML += '<div class="list-group-item text-center text-muted py-3">No recent activity</div>';
                return;
            }
            
            const html = data.activities.map(a => `
                <a href="${BASE_URL}/admin/users/${a.user_id}" class="list-group-item list-group-item-action px-4 py-2">
                    <div class="d-flex align-items-start">
                        <div class="avatar bg-${getActivityColor(a.action)} text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width:32px;height:32px;font-size:0.7rem;">
                            ${getActivityIcon(a.action)}
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-medium small">${escapeHtml(a.description)}</div>
                            <div class="text-muted small">${escapeHtml(a.admin_name || 'System')} &middot; ${timeAgo(a.created_at)}</div>
                        </div>
                    </div>
                </a>
            `).join('');
            
            list.innerHTML += html;
        })
        .catch(() => {
            loading.style.display = 'none';
            list.innerHTML += '<div class="list-group-item text-center text-muted py-3">Failed to load activity</div>';
        });
}

function getActivityColor(action) {
    const colors = {
        'user_created': 'success',
        'user_updated': 'primary',
        'user_deleted': 'danger',
        'user_impersonated': 'warning',
        'user_approved': 'success',
        'user_rejected': 'danger',
        'password_force_reset': 'danger',
        'wallet_credit': 'success',
        'wallet_debit': 'warning',
        '2fa_enabled': 'info',
        '2fa_disabled': 'secondary',
        'bulk_activate': 'success',
        'bulk_deactivate': 'warning',
        'bulk_suspend': 'danger',
        'login': 'primary',
        'logout': 'secondary'
    };
    return colors[action] || 'primary';
}

function getActivityIcon(action) {
    const icons = {
        'user_created': 'fas fa-user-plus',
        'user_updated': 'fas fa-edit',
        'user_deleted': 'fas fa-user-minus',
        'user_impersonated': 'fas fa-user-secret',
        'user_approved': 'fas fa-check-circle',
        'user_rejected': 'fas fa-times-circle',
        'password_force_reset': 'fas fa-key',
        'wallet_credit': 'fas fa-coins',
        'wallet_debit': 'fas fa-coins',
        '2fa_enabled': 'fas fa-shield-alt',
        '2fa_disabled': 'fas fa-shield-alt',
        'bulk_activate': 'fas fa-users',
        'bulk_deactivate': 'fas fa-users',
        'bulk_suspend': 'fas fa-users',
        'login': 'fas fa-sign-in-alt',
        'logout': 'fas fa-sign-out-alt'
    };
    return icons[action] || 'fas fa-circle';
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function timeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const seconds = Math.floor((now - date) / 1000);
    
    if (seconds < 60) return 'just now';
    const minutes = Math.floor(seconds / 60);
    if (minutes < 60) return minutes + 'm ago';
    const hours = Math.floor(minutes / 60);
    if (hours < 24) return hours + 'h ago';
    const days = Math.floor(hours / 24);
    return days + 'd ago';
}

});
});

// Analytics Dashboard
let analyticsCollapsed = false;
let charts = { role: null, status: null, trend: null };

function toggleAnalytics() {
    const body = document.getElementById('analyticsBody');
    const icon = document.getElementById('analyticsToggleIcon');
    
    analyticsCollapsed = !analyticsCollapsed;
    body.style.display = analyticsCollapsed ? 'none' : 'block';
    icon.className = analyticsCollapsed ? 'fas fa-chevron-down' : 'fas fa-chevron-up';
}

function refreshAnalytics() {
    const btn = event.target.closest('button');
    const originalHTML = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Refreshing...';
    btn.disabled = true;
    
    loadAnalytics().finally(() => {
        btn.innerHTML = originalHTML;
        btn.disabled = false;
    });
}

async function loadAnalytics() {
    try {
        const response = await fetch(BASE_URL + '/admin/api/user-analytics');
        const data = await response.json();
        
        if (!data.success) throw new Error(data.message || 'Failed');
        
        renderRoleChart(data.by_role || []);
        renderStatusChart(data.by_status || []);
        renderTrendChart(data.trend || []);
        
    } catch (err) {
        console.error('Analytics load failed:', err);
    }
}

function renderRoleChart(roleData) {
    const ctx = document.getElementById('roleChart');
    if (!ctx) return;
    
    if (charts.role) charts.role.destroy();
    
    const labels = roleData.map(r => r.role.charAt(0).toUpperCase() + r.role.slice(1).replace('_', ' '));
    const values = roleData.map(r => parseInt(r.count));
    const colors = ['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#ec4899', '#f97316', '#64748b'];
    
    charts.role = new Chart(ctx, {
        type: 'doughnut',
        data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 0 }] },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw}` } } },
            cutout: '65%'
        }
    });
    
    // Render legend
    const legend = document.getElementById('roleLegend');
    if (legend) {
        legend.innerHTML = labels.map((l, i) => `
            <span class="badge me-1 mb-1" style="background:${colors[i]};font-size:0.75rem;padding:0.35rem 0.6rem;">
                ${l} (${values[i]})
            </span>
        `).join(' ');
    }
}

function renderStatusChart(statusData) {
    const ctx = document.getElementById('statusChart');
    if (!ctx) return;
    
    if (charts.status) charts.status.destroy();
    
    const labels = statusData.map(s => s.status.charAt(0).toUpperCase() + s.status.slice(1));
    const values = statusData.map(s => parseInt(s.count));
    const colorMap = { active: '#10b981', inactive: '#64748b', suspended: '#ef4444', pending: '#f59e0b' };
    const colors = labels.map(l => colorMap[l.toLowerCase()] || '#6366f1');
    
    charts.status = new Chart(ctx, {
        type: 'pie',
        data: { labels, datasets: [{ data: values, backgroundColor: colors, borderWidth: 0 }] },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw}` } } }
        }
    });
    
    const legend = document.getElementById('statusLegend');
    if (legend) {
        legend.innerHTML = labels.map((l, i) => `
            <span class="badge me-1 mb-1" style="background:${colors[i]};font-size:0.75rem;padding:0.35rem 0.6rem;">
                ${l} (${values[i]})
            </span>
        `).join(' ');
    }
}

function renderTrendChart(trendData) {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;
    
    if (charts.trend) charts.trend.destroy();
    
    // Fill missing days with 0
    const last30Days = [];
    const labels = [];
    const values = [];
    for (let i = 29; i >= 0; i--) {
        const date = new Date();
        date.setDate(date.getDate() - i);
        const key = date.toISOString().split('T')[0];
        labels.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }));
        const found = trendData.find(t => t.date === key);
        values.push(found ? parseInt(t.count) : 0);
    }
    
    charts.trend = new Chart(ctx, {
        type: 'line',
        data: { 
            labels, 
            datasets: [{ 
                label: 'New Users', 
                data: values, 
                borderColor: '#6366f1', 
                backgroundColor: 'rgba(99, 102, 241, 0.1)', 
                fill: true, 
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 5
            }] 
        },
        options: {
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            },
            interaction: { intersect: false, mode: 'index' }
        }
    });
}

function destroyCharts() {
    Object.values(charts).forEach(c => c && c.destroy());
    charts = { role: null, status: null, trend: null };
}

// Initialize analytics on load
document.addEventListener('DOMContentLoaded', function() {
    // Load analytics after a short delay
    setTimeout(loadAnalytics, 500);
    
    // Re-load on filter changes
    document.getElementById('userFilterForm')?.addEventListener('submit', () => {
        setTimeout(loadAnalytics, 500);
    });
    
    // Cleanup on unload
    window.addEventListener('beforeunload', destroyCharts);
});
</script>