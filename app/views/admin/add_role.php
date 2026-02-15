<?php
/**
 * Add Role
 *
 * Interface to add a new system role.
 */

require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Check if user has permission to manage roles (Superadmin only)
if (!isSuperAdmin()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF Token
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid security token. Please try again.";
    } else {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $error = "Role name is required.";
        } else {
            // Check if role already exists
            $check = $db->fetch("SELECT id FROM roles WHERE name = :name", ['name' => $name]);
            if ($check) {
                $error = "A role with this name already exists.";
            } else {
                try {
                    $db->execute("INSERT INTO roles (name, description) VALUES (:name, :description)", [
                        'name' => $name,
                        'description' => $description
                    ]);
                    header('Location: roles.php?msg=' . urlencode('Role added successfully.'));
                    exit();
                } catch (Exception $e) {
                    $error = "Error adding role: " . $e->getMessage();
                }
            }
        }
    }
}


$page_title = 'Add Role';
require_once __DIR__ . '/admin_header.php';
require_once __DIR__ . '/admin_sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="roles.php">Roles</a></li>
                        <li class="breadcrumb-item active">Add Role</li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo h($error); ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <?php echo getCsrfField(); ?>
                            <div class="form-group">
                                <label>Role Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="<?php echo isset($_POST['name']) ? h($_POST['name']) : ''; ?>" required>
                                <small class="form-text text-muted">e.g., Editor, Manager, Support</small>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea name="description" class="form-control" rows="4"><?php echo isset($_POST['description']) ? h($_POST['description']) : ''; ?></textarea>
                            </div>
                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary">Save Role</button>
                                <a href="roles.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/admin_footer.php';
?>
