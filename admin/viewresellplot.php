<?php
// Include database connection
require_once("config.php"); // Use the existing config file for database connection

// Fetch resell plots from the database
$query = "SELECT * FROM resell_plots";
$result = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>APS DREAM HOMES | View Resell Plots</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
</head>
<body>
    <?php include("../includes/templates/dynamic_header.php"); ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Resell Plots</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">View Resell Plots</li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">List of Resell Plots</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($result->num_rows > 0): ?>
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Title</th>
                                            <th>Content</th>
                                            <th>Property Type</th>
                                            <th>Selling Type</th>
                                            <th>Plot Location</th>
                                            <th>Plot Size</th>
                                            <th>Price</th>
                                            <th>Contact Name</th>
                                            <th>Contact Number</th>
                                            <th>Email</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                                <td><?php echo htmlspecialchars($row['content']); ?></td>
                                                <td><?php echo htmlspecialchars($row['property_type']); ?></td>
                                                <td><?php echo htmlspecialchars($row['selling_type']); ?></td>
                                                <td><?php echo htmlspecialchars($row['plot_location']); ?></td>
                                                <td><?php echo htmlspecialchars($row['plot_size']); ?></td>
                                                <td><?php echo htmlspecialchars($row['price']); ?></td>
                                                <td><?php echo htmlspecialchars($row['contact_name']); ?></td>
                                                <td><?php echo htmlspecialchars($row['contact_number']); ?></td>
                                                <td><?php echo htmlspecialchars($row['contact_email']); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <div class="alert alert-warning">No resell plots found.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="<?php echo get_asset_url('js/jquery.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/bootstrap.bundle.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>
    <?php include("../includes/templates/new_footer.php"); ?>
</body>
</html>

<?php
// Close the database connection
$con->close();
?>
