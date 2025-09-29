<?php
session_start();

// Check if user is logged in and is of type 'builder'
if (!isset($_SESSION['uid']) || $_SESSION['usertype'] !== 'builder') {
    require_once(__DIR__ . '/includes/config/base_url.php');
    header("Location: " . $base_url . "login.php"); // Redirect to login if not logged in
    exit();
}

// Include necessary configuration
include("config.php");

// Fetch project details
if (isset($_GET['id'])) {
    $project_id = $_GET['id'];
    $query = "SELECT * FROM projects WHERE id = '$project_id'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $project = mysqli_fetch_assoc($result);
    } else {
        echo "<p class='alert alert-warning'>Project not found.</p>";
        exit();
    }
} else {
    echo "<p class='alert alert-warning'>Invalid project ID.</p>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $project['project_name']; ?> - Project Details</title>
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
</head>

<body>
    <div class="container">
        <h1><?php echo $project['project_name']; ?></h1>
        <p><strong>Status:</strong> <?php echo $project['status']; ?></p>
        <p><strong>Description:</strong> <?php echo $project['description']; ?></p>

        <h2>Project Details</h2>
        <ul>
            <li><strong>Start Date:</strong> <?php echo $project['start_date']; ?></li>
            <li><strong>End Date:</strong> <?php echo $project['end_date']; ?></li>
            <li><strong>Budget:</strong> <?php echo $project['budget']; ?></li>
            <!-- Add more fields as necessary -->
        </ul>

        <a href="builder_dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        <a href="<?php echo $base_url; ?>logout.php" class="btn btn-danger">Logout</a>
    </div>

    <script src="<?php echo get_asset_url('js/bootstrap.bundle.min.js', 'js'); ?>"></script>
</body>

</html>
