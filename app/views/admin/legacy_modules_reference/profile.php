<?php
require_once __DIR__ . '/core/init.php';

$user_id = getAuthUserId();

$msg = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid CSRF token.';
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        if (empty($name) || empty($email)) {
            $error = "Name and email are required.";
        } else {
            $db = \App\Core\App::database();
            // Check if email exists for another user
            $existing_user = $db->fetch("SELECT id FROM admin WHERE email = :email AND id != :id", ['email' => $email, 'id' => $user_id]);
            if ($existing_user) {
                $error = "Email already registered to another user.";
            } else {
                if ($db->update("UPDATE admin SET auser = :name, email = :email, phone = :phone WHERE id = :id", ['name' => $name, 'email' => $email, 'phone' => $phone, 'id' => $user_id])) {
                    // Update session data if needed
                    $_SESSION['auth']['name'] = $name;
                    $_SESSION['auth']['email'] = $email;

                    // Log the update
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                    $details = 'Updated own profile (ID: ' . $user_id . ')';
                    $db->insert("INSERT INTO audit_log (user_id, action, details, ip_address) VALUES (:user_id, 'Update Profile', :details, :ip)", ['user_id' => $user_id, 'details' => $details, 'ip' => $ip]);

                    $msg = 'Profile updated successfully.';
                } else {
                    $error = 'Error updating profile.';
                }
            }
        }
    }
}

// Fetch user data
$db = \App\Core\App::database();
$user = $db->fetch("SELECT * FROM admin WHERE id = :id", ['id' => $user_id]);

if (!$user) {
    die('User not found');
}

$page_title = "My Profile";
$breadcrumbs = ["Dashboard" => "dashboard.php", "My Profile" => ""];

include('admin_header.php');
include('admin_sidebar.php');
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">My Profile</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <?php if ($msg): ?>
                            <div class="alert alert-success"><?php echo h($msg); ?></div>
                        <?php endif; ?>
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo h($error); ?></div>
                        <?php endif; ?>

                        <form method="post">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label>Full Name <span class="text-danger">*</span></label>
                                        <input class="form-control" type="text" name="name" value="<?php echo h($user['auser']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Email <span class="text-danger">*</span></label>
                                        <input class="form-control" type="email" name="email" value="<?php echo h($user['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Phone</label>
                                        <input class="form-control" type="text" name="phone" value="<?php echo h($user['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Role</label>
                                        <input class="form-control" type="text" value="<?php echo h($user['role']); ?>" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <input class="form-control" type="text" value="<?php echo h($user['status']); ?>" readonly>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Security</h4>
                    </div>
                    <div class="card-body">
                        <p>To change your password, please go to the <a href="change_password.php">Change Password</a> page.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('admin_footer.php'); ?>