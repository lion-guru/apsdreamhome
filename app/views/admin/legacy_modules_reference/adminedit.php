<?php
require_once __DIR__ . '/core/init.php';

$my_role = getAuthSubRole() ?? 'admin';
$my_id = getAuthUserId() ?? 0;

// Only allow edit if:
// - Super Admin can edit anyone
// - Admin can edit anyone except super_admin
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: adminlist.php?error=' . urlencode('Invalid admin ID'));
    exit();
}
$edit_id = (int)$_GET['id'];

$db = \App\Core\App::database();
$msg = $error = '';

// Fetch roles from roles table
$roles = [];
try {
    $roles_data = $db->fetchAll('SELECT name FROM roles');
    foreach ($roles_data as $row) {
        $roles[] = $row['name'];
    }
} catch (Exception $e) {
    $error = "Error fetching roles: " . $e->getMessage();
}

// Fetch admin details
try {
    $user = $db->fetchOne('SELECT id, auser, email, apass, phone, role, status FROM admin WHERE id = :id', ['id' => $edit_id]);
    if (!$user) {
        header('Location: adminlist.php?error=' . urlencode('Admin/Employee not found'));
        exit();
    }
} catch (Exception $e) {
    header('Location: adminlist.php?error=' . urlencode('Error fetching admin details: ' . $e->getMessage()));
    exit();
}

// Prevent admin from editing super_admin
if ($my_role === 'admin' && $user['role'] === 'super_admin') {
    header('Location: adminlist.php?error=' . urlencode('You are not authorized to edit Super Admin'));
    exit();
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $auser = $_POST['auser'] ?? $user['auser'];
        $email = $_POST['email'] ?? $user['email'];
        $phone = $_POST['phone'] ?? $user['phone'];
        $role = $_POST['role'] ?? $user['role'];
        $status = $_POST['status'] ?? $user['status'];
        $newpass = $_POST['newpass'] ?? '';
        $update_pass = false;

        if (!empty($newpass)) {
            $apass = password_hash($newpass, PASSWORD_DEFAULT);
            $update_pass = true;
        } else {
            $apass = $user['apass'];
        }

        // Prevent making anyone super_admin except by super_admin
        if ($my_role === 'admin' && $role === 'super_admin') {
            $role = $user['role']; // admin cannot promote anyone to super_admin
        }

        try {
            // Update both password and apass for consistency
            if ($update_pass) {
                $success = $db->execute(
                    'UPDATE admin SET auser=:auser, email=:email, password=:password, apass=:apass, phone=:phone, role=:role, status=:status WHERE id=:id',
                    [
                        'auser' => $auser,
                        'email' => $email,
                        'password' => $apass,
                        'apass' => $apass,
                        'phone' => $phone,
                        'role' => $role,
                        'status' => $status,
                        'id' => $edit_id
                    ]
                );
            } else {
                $success = $db->execute(
                    'UPDATE admin SET auser=:auser, email=:email, phone=:phone, role=:role, status=:status WHERE id=:id',
                    [
                        'auser' => $auser,
                        'email' => $email,
                        'phone' => $phone,
                        'role' => $role,
                        'status' => $status,
                        'id' => $edit_id
                    ]
                );
            }

            if ($success) {
                $_SESSION['msg'] = 'Admin/Employee updated successfully!';
                header('Location: adminlist.php');
                exit();
            } else {
                $error = 'Update failed!';
            }
        } catch (Exception $e) {
            $error = 'Update error: ' . $e->getMessage();
        }
    }
}

$page_title = "Edit Admin/Employee";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title">Edit Admin/Employee</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="adminlist.php">Admin List</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo h($error); ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <?php echo getCsrfField(); ?>
                            <div class="form-group">
                                <label>Username</label>
                                <input type="text" name="auser" class="form-control" value="<?php echo h($user['auser']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" name="email" class="form-control" value="<?php echo h($user['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Phone</label>
                                <input type="text" name="phone" class="form-control" value="<?php echo h($user['phone']); ?>">
                            </div>
                            <div class="form-group">
                                <label>Role</label>
                                <select name="role" class="form-control select" <?php echo ($my_role === 'admin' && $user['role'] === 'super_admin' ? 'disabled' : ''); ?>>
                                    <?php foreach ($roles as $r): ?>
                                        <?php if ($my_role === 'admin' && $r === 'super_admin') continue; ?>
                                        <option value="<?php echo h($r); ?>" <?php echo ($user['role'] === $r ? 'selected' : ''); ?>><?php echo h($r); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($my_role === 'admin' && $user['role'] === 'super_admin'): ?>
                                    <input type="hidden" name="role" value="<?php echo h($user['role']); ?>">
                                <?php endif; ?>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-control select">
                                    <option value="active" <?php echo ($user['status'] === 'active' ? 'selected' : ''); ?>>Active</option>
                                    <option value="inactive" <?php echo ($user['status'] === 'inactive' ? 'selected' : ''); ?>>Inactive</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>New Password (leave blank to keep existing)</label>
                                <input type="password" name="newpass" class="form-control">
                            </div>
                            <div class="text-right mt-4">
                                <button type="submit" class="btn btn-primary">Update Admin</button>
                                <a href="adminlist.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>