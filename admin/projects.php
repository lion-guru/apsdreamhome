<?php
require_once __DIR__ . '/includes/session_manager.php';
require_once __DIR__ . '/../includes/db_config.php';
initAdminSession();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit();
}
$error = $msg = '';
$conn = getDbConnection();
// Handle deleting a project
if ($conn && isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql = "DELETE FROM project_master WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $delete_id);
    if ($stmt->execute()) {
        $msg = "Project deleted successfully.";
    } else {
        $error = "Error deleting project.";
    }
    $stmt->close();
}
// Fetch projects from the database
$projects = [];
if ($conn) {
    $sql = "SELECT * FROM projects ORDER BY location, name";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $projects[] = $row;
        }
        $result->free();
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Project Management</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body { background: #f8f9fa; }
        .main-content { margin-left: 220px; padding: 2rem 1rem; }
    </style>
</head>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
<?php include __DIR__ . '/includes/admin_sidebar.php'; ?>
<div class="main-content">
    <h1 class="mb-4">Project Management</h1>
    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <a href="add_project.php" class="btn btn-primary mb-3"><i class="fas fa-plus me-1"></i> Add New Project</a>
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>City</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projects)): ?>
                            <tr><td colspan="6" class="text-center">No projects found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($projects as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['id']); ?></td>
                                    <td><?php echo htmlspecialchars($p['name']); ?>
                                        <?php if (!empty($p['brochure_drive_id'])): ?>
                                            <a href="https://drive.google.com/file/d/<?php echo htmlspecialchars($p['brochure_drive_id']); ?>/view" target="_blank" title="View Brochure on Google Drive">
                                                <i class="fab fa-google-drive text-success ms-2"></i>
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['city']); ?></td>
                                    <td><?php echo htmlspecialchars($p['location']); ?></td>
                                    <td><?php echo $p['status'] ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-secondary">Inactive</span>'; ?></td>
                                    <td>
                                        <a href="edit_project.php?id=<?php echo htmlspecialchars($p['id']); ?>" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i></a>
                                        <a href="projects.php?delete_id=<?php echo htmlspecialchars($p['id']); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this project?');"><i class="fas fa-trash-alt"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
