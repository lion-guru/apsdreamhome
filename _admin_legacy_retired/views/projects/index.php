<?php
require_once '../controllers/ProjectController.php';

$projectController = new Admin\Controllers\ProjectController($conn);
$projects = $projectController->index();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Projects - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <?php include '../includes/templates/dynamic_header.php'; ?>
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6>Projects Management</h6>
                        <a href="create.php" class="btn btn-primary btn-sm">Add New Project</a>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0" id="projectsTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Location</th>
                                        <th>Status</th>
                                        <th>Units</th>
                                        <th>Featured</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($projects as $project): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <?php if($project['thumbnail']): ?>
                                                <div class="me-3">
                                                    <img src="<?php echo htmlspecialchars($project['thumbnail']); ?>" 
                                                         class="avatar avatar-sm me-3">
                                                </div>
                                                <?php endif; ?>
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm"><?php echo htmlspecialchars($project['name']); ?></h6>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($project['location']); ?></td>
                                        <td>
                                            <span class="badge badge-sm bg-<?php echo $project['status'] === 'completed' ? 'success' : ($project['status'] === 'ongoing' ? 'info' : 'warning'); ?>">
                                                <?php echo ucfirst(htmlspecialchars($project['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($project['available_units']); ?> / <?php echo htmlspecialchars($project['total_units']); ?>
                                        </td>
                                        <td>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" 
                                                       <?php echo $project['featured'] ? 'checked' : ''; ?>
                                                       onchange="toggleFeatured(<?php echo htmlspecialchars($project['id']); ?>, this)">
                                            </div>
                                        </td>
                                        <td>
                                            <a href="edit.php?id=<?php echo htmlspecialchars($project['id']); ?>" 
                                               class="btn btn-sm btn-info">Edit</a>
                                            <button type="button" class="btn btn-sm btn-danger"
                                                    onclick="deleteProject(<?php echo htmlspecialchars($project['id']); ?>)">Delete</button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/jquery.dataTables.min.js"></script>
    <script src="../assets/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#projectsTable').DataTable();
        });

        function toggleFeatured(id, element) {
            fetch(`api/projects/toggle-featured.php?id=${id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    featured: element.checked
                })
            })
            .then(response => response.json())
            .then(data => {
                if(!data.success) {
                    element.checked = !element.checked;
                    alert('Failed to update featured status');
                }
            })
            .catch(error => {
                element.checked = !element.checked;
                alert('An error occurred');
            });
        }

        function deleteProject(id) {
            if(confirm('Are you sure you want to delete this project?')) {
                fetch(`api/projects/delete.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if(data.success) {
                        location.reload();
                    } else {
                        alert('Failed to delete project');
                    }
                })
                .catch(error => {
                    alert('An error occurred');
                });
            }
        }
    </script>
</body>
</html>