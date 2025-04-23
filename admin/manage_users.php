<?php
require_once(__DIR__ . '/../includes/SessionManager.php');
$sessionManager = new SessionManager();
$sessionManager->requireSuperAdmin();
require_once(__DIR__ . '/../includes/db_config.php');
$conn = getDbConnection();

$page_title = 'Manage Users & Admins';
include __DIR__ . '/../includes/templates/dynamic_header.php';

// Fetch admins/superadmins/finance/associates
$admins = [];
$admin_query = "SELECT aid AS id, auser AS name, email, role, status FROM admin";
$result = $conn->query($admin_query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['source'] = 'admin';
        $admins[] = $row;
    }
}

// Fetch users (customers, investors, tenants)
$users = [];
$user_query = "SELECT id, name, email, type AS role, status FROM users";
$result = $conn->query($user_query);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $row['source'] = 'user';
        $users[] = $row;
    }
}

// Merge for display
$all_users = array_merge($admins, $users);
?>
<div class="container py-5">
    <div class="row mb-4">
        <div class="col-lg-10 mx-auto">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h1 class="h3 text-primary"><i class="fas fa-users-cog me-2"></i>Manage Users & Admins</h1>
                <a href="#" class="btn btn-success disabled">
                    <i class="fas fa-user-plus me-1"></i> Add Admin
                    <span class="badge bg-secondary ms-2">Coming Soon</span>
                </a>
            </div>
            <div class="input-group mb-3">
                <input type="text" class="form-control" placeholder="Search by name, email, or role..." disabled>
                <button class="btn btn-outline-secondary" type="button" disabled><i class="fas fa-search"></i></button>
            </div>
            <div class="table-responsive rounded shadow-sm">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role/Type</th>
                            <th>Status</th>
                            <th>Source</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_users)): ?>
                            <tr><td colspan="7" class="text-center text-muted">No users found.</td></tr>
                        <?php else: ?>
                        <?php foreach ($all_users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo ($user['role']==='superadmin'||$user['role']==='super_admin') ? 'danger' : ($user['role']==='admin' ? 'primary' : ($user['role']==='associate' ? 'info' : ($user['role']==='finance' ? 'warning' : 'secondary'))); ?> text-uppercase">
                                    <?php echo $user['role']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['status']==='active' ? 'success' : 'secondary'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $user['source']==='admin' ? 'primary' : 'secondary'; ?>">
                                    <?php echo ucfirst($user['source']); ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-outline-primary btn-sm disabled me-1" title="Edit User"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-outline-warning btn-sm disabled me-1" title="Promote/Demote"><i class="fas fa-user-tag"></i></button>
                                <button class="btn btn-outline-danger btn-sm disabled" title="Deactivate/Delete"><i class="fas fa-user-slash"></i></button>
                                <span class="badge bg-secondary ms-2">Coming Soon</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="alert alert-info mt-4 text-center">
                <i class="fas fa-info-circle me-2"></i>
                All user management actions are restricted to Super Admins.<br>
                Full functionality coming soon.
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
