<?php
session_start();
include("config.php");

if (!isset($_SESSION['uid']) || $_SESSION['usertype'] !== 'builder') {
    header("location:login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $project_name = $_POST['project_name'];
    $status = $_POST['status'];
    $description = $_POST['description'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $budget = $_POST['budget'];
    $builder_id = $_SESSION['uid'];

    $stmt = $conn->prepare("INSERT INTO projects (builder_id, project_name, status, description, start_date, end_date, budget) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssssd", $builder_id, $project_name, $status, $description, $start_date, $end_date, $budget);
    
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
    <title>Add Project</title>
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
</head>
<body>
    <div class="container">
        <h1>Add New Project</h1>
        <form method="POST" action="">
            <div class="form-group">
                <label for="project_name">Project Name</label>
                <input type="text" class="form-control" name="project_name" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <input type="text" class="form-control" name="status" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" name="description" required></textarea>
            </div>
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input type="date" class="form-control" name="start_date" required>
            </div>
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input type="date" class="form-control" name="end_date" required>
            </div>
            <div class="form-group">
                <label for="budget">Budget</label>
                <input type="number" class="form-control" name="budget" step="0.01" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Project</button>
            <a href="builder_dashboard.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <script src="<?php echo get_asset_url('js/bootstrap.bundle.min.js', 'js'); ?>"></script>
</body>
</html>
