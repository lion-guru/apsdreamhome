<?php
require_once __DIR__ . '/core/init.php';

// Check authentication
adminAccessControl(['Company Owner', 'super_admin', 'superadmin', 'admin']);

require_once 'admin-functions.php';

// Initialize variables
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!validateCsrfToken()) {
        die('Invalid CSRF token. Action blocked.');
    }

    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_resource':
                $project_id = $_POST['project_id'];
                $name = $_POST['resource_name'];
                $type = $_POST['resource_type'];
                $quantity = $_POST['quantity'];
                $status = $_POST['resource_status'];

                $sql = "INSERT INTO resources (project_id, name, type, quantity, status)
                        VALUES (?, ?, ?, ?, ?)";
                if (\App\Core\App::database()->execute($sql, [$project_id, $name, $type, $quantity, $status])) {
                    $success = 'Resource added successfully';
                } else {
                    $error = 'Error adding resource';
                }
                break;

            case 'add_daily_log':
                $project_id = $_POST['project_id'];
                $date = $_POST['log_date'];
                $description = $_POST['log_description'];
                $weather = $_POST['weather_conditions'];
                $work_completed = $_POST['work_completed'];
                $issues = $_POST['issues_faced'];

                $sql = "INSERT INTO daily_logs (project_id, date, description, weather_conditions, work_completed, issues_faced)
                        VALUES (?, ?, ?, ?, ?, ?)";
                if (\App\Core\App::database()->execute($sql, [$project_id, $date, $description, $weather, $work_completed, $issues])) {
                    $success = 'Daily log added successfully';
                } else {
                    $error = 'Error adding daily log';
                }
                break;

            case 'add_checklist':
                $project_id = $_POST['project_id'];
                $title = $_POST['checklist_title'];
                $description = $_POST['checklist_description'];
                $status = $_POST['checklist_status'];
                $completion_date = $_POST['completion_date'];

                $sql = "INSERT INTO quality_checklists (project_id, title, description, status, completion_date)
                        VALUES (?, ?, ?, ?, ?)";
                if (\App\Core\App::database()->execute($sql, [$project_id, $title, $description, $status, $completion_date])) {
                    $success = 'Quality checklist added successfully';
                } else {
                    $error = 'Error adding checklist';
                }
                break;

            case 'add_contractor':
                $name = $_POST['contractor_name'];
                $specialization = $_POST['specialization'];
                $contact_info = $_POST['contact_info'];
                $status = $_POST['contractor_status'];

                $sql = "INSERT INTO contractors (name, specialization, contact_info, status)
                        VALUES (?, ?, ?, ?)";
                if (\App\Core\App::database()->execute($sql, [$name, $specialization, $contact_info, $status])) {
                    $success = 'Contractor added successfully';
                } else {
                    $error = 'Error adding contractor';
                }
                break;

            case 'assign_contractor':
                $contractor_id = $_POST['contractor_id'];
                $project_id = $_POST['project_id'];
                $start_date = $_POST['assignment_start_date'];
                $end_date = $_POST['assignment_end_date'];
                $payment_terms = $_POST['payment_terms'];
                $status = $_POST['assignment_status'];

                $sql = "INSERT INTO contractor_assignments (contractor_id, project_id, start_date, end_date, payment_terms, status)
                        VALUES (?, ?, ?, ?, ?, ?)";
                if (\App\Core\App::database()->execute($sql, [$contractor_id, $project_id, $start_date, $end_date, $payment_terms, $status])) {
                    $success = 'Contractor assigned successfully';
                } else {
                    $error = 'Error assigning contractor';
                }
                break;
        }
    }
}

// Fetch all projects
$projects = \App\Core\App::database()->fetchAll("SELECT * FROM projects ORDER BY start_date DESC");

// Fetch all contractors
$contractors = \App\Core\App::database()->fetchAll("SELECT * FROM contractors ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Construction Workflow Management</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>

<body>
    <?php include("../includes/templates/dynamic_header.php"); ?>
    <div class="container mt-4">
        <h2>Construction Workflow Management</h2>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Resource Management -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Resource Management</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="add_resource">
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
                            <label for="resource_name" class="form-label">Resource Name</label>
                            <input type="text" class="form-control" id="resource_name" name="resource_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="resource_type" class="form-label">Type</label>
                            <select class="form-control" id="resource_type" name="resource_type" required>
                                <option value="material">Material</option>
                                <option value="equipment">Equipment</option>
                                <option value="labor">Labor</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="quantity" class="form-label">Quantity</label>
                            <input type="number" class="form-control" id="quantity" name="quantity" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="resource_status" class="form-label">Status</label>
                            <select class="form-control" id="resource_status" name="resource_status" required>
                                <option value="available">Available</option>
                                <option value="in_use">In Use</option>
                                <option value="maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Resource</button>
                </form>
            </div>
        </div>

        <!-- Daily Log Entry -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Daily Log Entry</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="add_daily_log">
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
                            <label for="log_date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="log_date" name="log_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="log_description" class="form-label">Description</label>
                        <textarea class="form-control" id="log_description" name="log_description" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="weather_conditions" class="form-label">Weather Conditions</label>
                            <input type="text" class="form-control" id="weather_conditions" name="weather_conditions" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="work_completed" class="form-label">Work Completed</label>
                            <textarea class="form-control" id="work_completed" name="work_completed" rows="2" required></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="issues_faced" class="form-label">Issues Faced</label>
                            <textarea class="form-control" id="issues_faced" name="issues_faced" rows="2"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Daily Log</button>
                </form>
            </div>
        </div>

        <!-- Quality Control Checklist -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Quality Control Checklist</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="add_checklist">
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
                            <label for="checklist_title" class="form-label">Checklist Title</label>
                            <input type="text" class="form-control" id="checklist_title" name="checklist_title" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="checklist_description" class="form-label">Description</label>
                        <textarea class="form-control" id="checklist_description" name="checklist_description" rows="2" required></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="checklist_status" class="form-label">Status</label>
                            <select class="form-control" id="checklist_status" name="checklist_status" required>
                                <option value="pending">Pending</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="completion_date" class="form-label">Completion Date</label>
                            <input type="date" class="form-control" id="completion_date" name="completion_date">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Checklist</button>
                </form>
            </div>
        </div>

        <!-- Contractor Management -->
        <div class="card mb-4">
            <div class="card-header">
                <h4>Contractor Management</h4>
            </div>
            <div class="card-body">
                <!-- Add Contractor Form -->
                <form method="POST" action="" class="mb-4">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" name="action" value="add_contractor">
                    <h5>Add New Contractor</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contractor_name" class="form-label">Contractor Name</label>
                            <input type="text" class="form-control" id="contractor_name" name="contractor_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="specialization" class="form-label">Specialization</label>
                            <input type="text" class="form-control" id="specialization" name="specialization" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contact_info" class="form-label">Contact Information</label>
                            <textarea class="form-control" id="contact_info" name="contact_info" rows="2" required></textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="contractor_status" class="form-label">Status</label>
                            <select class="form-control" id="contractor_status" name="contractor_status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Add Contractor</button>
                </form>

                <!-- Assign Contractor Form -->
                <form method="POST" action="">
                    <input type="hidden" name="action" value="assign_contractor">
                    <h5>Assign Contractor to Project</h5>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="contractor_id" class="form-label">Select Contractor</label>
                            <select class="form-control" id="contractor_id" name="contractor_id" required>
                                <?php foreach ($contractors as $contractor): ?>
                                    <option value="<?php echo $contractor['id']; ?>"><?php echo $contractor['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="project_id" class="form-label">Select Project</label>
                            <select class="form-control" id="project_id" name="project_id" required>
                                <?php foreach ($projects as $project): ?>
                                    <option value="<?php echo $project['id']; ?>"><?php echo $project['name']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label for="assignment_start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="assignment_start_date" name="assignment_start_date" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="assignment_end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="assignment_end_date" name="assignment_end_date" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="payment_terms" class="form-label">Payment Terms</label>
                            <textarea class="form-control" id="payment_terms" name="payment_terms" rows="2" required></textarea>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="assignment_status" class="form-label">Status</label>
                            <select class="form-control" id="assignment_status" name="assignment_status" required>
                                <option value="scheduled">Scheduled</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Assign Contractor</button>
                </form>
            </div>
        </div>
    </div>
    <?php include("../includes/templates/new_footer.php"); ?>
</body>

</html>
