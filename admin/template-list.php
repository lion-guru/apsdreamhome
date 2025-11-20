<?php
require_once 'config.php';
require_once 'admin-functions.php';

$db = $con;
$layoutTemplate = new Admin\Models\LayoutTemplate($db);

// Get all templates, including inactive ones
$templates = $layoutTemplate->getAll(false);

// Handle template deletion
if (isset($_POST['delete']) && isset($_POST['id'])) {
    try {
        $layoutTemplate->delete($_POST['id']);
        header('Location: template-list.php?success=1');
        exit;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layout Templates</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Layout Templates</h2>
            <a href="template-builder.php" class="btn btn-primary">Create New Template</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Operation completed successfully.</div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <table id="templates-table" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($templates as $template): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($template['name']); ?></td>
                                <td><?php echo htmlspecialchars($template['description']); ?></td>
                                <td>
                                    <span class="badge <?php echo $template['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $template['is_active'] ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($template['created_at'])); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <a href="template-builder.php?id=<?php echo $template['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">Edit</a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                                onclick="previewTemplate(<?php echo $template['id']; ?>)">Preview</button>
                                        <form method="POST" style="display: inline;" 
                                              onsubmit="return confirm('Are you sure you want to delete this template?');">
                                            <input type="hidden" name="id" value="<?php echo $template['id']; ?>">
                                            <button type="submit" name="delete" class="btn btn-sm btn-outline-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="js/jquery.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#templates-table').DataTable({
                order: [[3, 'desc']],
                pageLength: 25
            });
        });

        function previewTemplate(id) {
            window.open(`template-preview.php?id=${id}`, '_blank');
        }
    </script>

    <?php include 'includes/footer.php'; ?>
</body>
</html>