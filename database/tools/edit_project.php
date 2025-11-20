<?php
session_start();
include("config.php");

if (!isset($_SESSION['uid'])) {
    header("location:login.php");
    exit();
}

// Generate CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$project_id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM projects WHERE bid = ?");
$stmt->bind_param("i", $project_id);
$stmt->execute();
$result = $stmt->get_result();
$project = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('Invalid CSRF token');
    }
    $project_name = $_POST['project_name'];
    $status = $_POST['status'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $budget = $_POST['budget'];

    $stmt = $conn->prepare("UPDATE projects SET project_name=?, status=?, description=?, start_date=?, end_date=?, budget=? WHERE bid=?");
    $stmt->bind_param("ssssssi", $project_name, $status, $description, $start_date, $end_date, $budget, $project_id);
    
    if ($stmt->execute()) {
        header("location:builder_dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Project</title>
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
</head>
<body>
    <div class="container">
        <h1>Edit Project</h1>
        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <label for="project_name">Project Name</label>
                <input type="text" class="form-control" name="project_name" value="<?php echo htmlspecialchars($project['project_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <input type="text" class="form-control" name="status" value="<?php echo htmlspecialchars($project['status']); ?>" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" name="description" required><?php echo htmlspecialchars($project['description']); ?></textarea>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" class="form-control" name="start_date" value="<?php echo htmlspecialchars($project['start_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" class="form-control" name="end_date" value="<?php echo htmlspecialchars($project['end_date']); ?>" required>
            </div>
            <div class="form-group">
                <label for="budget">Budget</label>
                <input type="number" class="form-control" name="budget" value="<?php echo htmlspecialchars($project['budget']); ?>" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Update Project</button>
            <a href="builder_dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="<?php echo get_asset_url('js/bootstrap.bundle.min.js', 'js'); ?>"></script>
</body>
</html>
