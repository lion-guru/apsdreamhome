<?php
require_once 'admin-functions.php';
require_once 'src/Database/Database.php';

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_project':
                $land_id = $_POST['land_id'];
                $name = $_POST['name'];
                $description = $_POST['description'];
                $start_date = $_POST['start_date'];
                $expected_end_date = $_POST['expected_end_date'];
                $status = $_POST['status'];

                $sql = "INSERT INTO projects (land_id, name, description, start_date, expected_end_date, status) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("isssss", $land_id, $name, $description, $start_date, $expected_end_date, $status);

                if ($stmt->execute()) {
                    $success = 'Project added successfully';
                } else {
                    $error = 'Error adding project: ' . $conn->error;
                }
                break;

            case 'add_milestone':
                $project_id = $_POST['project_id'];
                $title = $_POST['milestone_title'];
                $description = $_POST['milestone_description'];
                $due_date = $_POST['milestone_due_date'];
                $status = $_POST['milestone_status'];

                $sql = "INSERT INTO project_milestones (project_id, title, description, due_date, status) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issss", $project_id, $title, $description, $due_date, $status);

                if ($stmt->execute()) {
                    $success = 'Milestone added successfully';
                } else {
                    $error = 'Error adding milestone: ' . $conn->error;
                }
                break;

            case 'add_task':
                $milestone_id = $_POST['milestone_id'];
                $title = $_POST['task_title'];
                $description = $_POST['task_description'];
                $assigned_to = $_POST['assigned_to'];
                $due_date = $_POST['task_due_date'];
                $status = $_POST['task_status'];

                $sql = "INSERT INTO project_tasks (milestone_id, title, description, assigned_to, due_date, status) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ississ", $milestone_id, $title, $description, $assigned_to, $due_date, $status);

                if ($stmt->execute()) {
                    $success = 'Task added successfully';
                } else {
                    $error = 'Error adding task: ' . $conn->error;
                }
                break;
        }
    }
}

// Fetch all projects with their land records
$sql = "SELECT p.*, l.title as land_title 
        FROM projects p 
        LEFT JOIN land_records l ON p.land_id = l.id 
        ORDER BY p.start_date DESC";
$result = $conn->query($sql);
$projects = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all land records for the dropdown
$sql = "SELECT * FROM land_records";
$result = $conn->query($sql);
$land_records = $result->fetch_all(MYSQLI_ASSOC);

// Fetch all team members for task assignment
$sql = "SELECT id, name FROM clients WHERE id IN (SELECT assigned_to FROM project_tasks)";
$result = $conn->query($sql);
$team_members = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Development Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/gantt-chart@0.3.5/dist/gantt-chart.min.css">
</head>
<body>
<?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
    <div class="container mt-4">
        <h2>Project Development Management</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Add New Project Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Add New Project</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_project">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="land_id" class="form-label">Select Land</label>
                            <select class="form-control" id="land_id" name="land_id" required>
                                <?php foreach ($land_records as $record): ?>
                                    <option value="<?php echo $record['id']; ?>"><?php echo $record['title']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Project Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="expected_end_date" class="form-label">Expected End Date</label>
                            <input type="date" class="form-control" id="expected_end_date" name="expected_end_date" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" id="status" name="status" required>
                                <option value="planning">Planning</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="on_hold">On Hold</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Project</button>
                </form>
            </div>
        </div>

        <!-- Add Milestone Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Add Milestone</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_milestone">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="project_id" class="form-label">Select Project</label>
                            <select class="form-control" id="project_id" name="project_id" required>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="milestone_title" class="form-label">Milestone Title</label>
                            <input type="text" class="form-control" id="milestone_title" name="milestone_title" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="milestone_description" class="form-label">Description</label>
                        <textarea class="form-control" id="milestone_description" name="milestone_description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="milestone_due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="milestone_due_date" name="milestone_due_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="milestone_status" class="form-label">Status</label>
                            <select class="form-control" id="milestone_status" name="milestone_status" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Milestone</button>
                </form>
            </div>
        </div>

        <!-- Add Task Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Add Task</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="add_task">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="milestone_id" class="form-label">Select Milestone</label>
                            <select class="form-control" id="milestone_id" name="milestone_id" required>
                                <?php 
                                $sql = "SELECT m.id, m.title, p.name as project_name 
                                        FROM project_milestones m 
                                        JOIN projects p ON m.project_id = p.id";
                                $result = $conn->query($sql);
                                $milestones = $result->fetch_all(MYSQLI_ASSOC);
                                foreach ($milestones as $milestone): 
                                ?>
                                    <option value="<?php echo $milestone['id']; ?>">
                                        <?php echo $milestone['project_name'] . ' - ' . $milestone['title']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="task_title" class="form-label">Task Title</label>
                            <input type="text" class="form-control" id="task_title" name="task_title" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="task_description" class="form-label">Description</label>
                        <textarea class="form-control" id="task_description" name="task_description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="assigned_to" class="form-label">Assign To</label>
                            <select class="form-control" id="assigned_to" name="assigned_to" required>
                                <?php foreach ($team_members as $member): ?>
                                    <option value="<?php echo $member['id']; ?>"><?php echo $member['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="task_due_date" class="form-label">Due Date</label>
                            <input type="date" class="form-control" id="task_due_date" name="task_due_date" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="task_status" class="form-label">Status</label>
                            <select class="form-control" id="task_status" name="task_status" required>
                                <option value="todo">To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Task</button>
                </form>
            </div>
        </div>

        <!-- Projects Overview -->
        <div class="card">
            <div class="card-header">
                <h4>Projects Overview</h4>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Land</th>
                                <th>Start Date</th>
                                <th>Expected End Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($projects as $project): ?>
                                <tr>
                                    <td><?php echo $project['name']; ?></td>
                                    <td><?php echo $project['land_title']; ?></td>
                                    <td><?php echo $project['start_date']; ?></td>
                                    <td><?php echo $project['expected_end_date']; ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?></td>
                                    <td>
                                        <a href="view_project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-info">View</a>
                                        <a href="edit_project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="delete_project.php?id=<?php echo $project['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this project?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>