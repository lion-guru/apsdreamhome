<?php
// Include database connection
include("config.php"); // Ensure you have this file to connect to your database

// Initialize variables
$error = "";
$msg = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capture and sanitize input
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_STRING);
    $property_type = filter_input(INPUT_POST, 'ptype', FILTER_SANITIZE_STRING);
    $selling_type = filter_input(INPUT_POST, 'stype', FILTER_SANITIZE_STRING);
    $plot_location = filter_input(INPUT_POST, 'plot_location', FILTER_SANITIZE_STRING);
    $plot_size = filter_input(INPUT_POST, 'plot_size', FILTER_VALIDATE_FLOAT);
    $plot_dimensions = filter_input(INPUT_POST, 'plot_dimensions', FILTER_SANITIZE_STRING);
    $plot_facing = filter_input(INPUT_POST, 'plot_facing', FILTER_SANITIZE_STRING);
    $road_access = filter_input(INPUT_POST, 'road_access', FILTER_SANITIZE_STRING);
    $price = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $plot_category = filter_input(INPUT_POST, 'plot_category', FILTER_SANITIZE_STRING);
    $full_address = filter_input(INPUT_POST, 'full_address', FILTER_SANITIZE_STRING);
    $contact_name = filter_input(INPUT_POST, 'contact_name', FILTER_SANITIZE_STRING);
    $contact_number = filter_input(INPUT_POST, 'contact_number', FILTER_SANITIZE_STRING);
    $contact_email = filter_input(INPUT_POST, 'contact_email', FILTER_SANITIZE_EMAIL);

    // Prepare SQL statement for insertion
    $stmt = $con->prepare("INSERT INTO resell_plots (title, content, property_type, selling_type, plot_location, plot_size, plot_dimensions, plot_facing, road_access, price, plot_category, full_address, contact_name, contact_number, contact_email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind parameters
    $stmt->bind_param("ssssdsdssdsdss", $title, $content, $property_type, $selling_type, $plot_location, $plot_size, $plot_dimensions, $plot_facing, $road_access, $price, $plot_category, $full_address, $contact_name, $contact_number, $contact_email);
    
    // Execute the statement
    if ($stmt->execute()) {
        $msg = "Plot added successfully!";
    } else {
        $error = "Error: " . $stmt->error;
    }
}

// Fetch resell plots from the database
$query = "SELECT * FROM resell_plots";
$result = $con->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>APS DREAM HOMES | Resell Plots</title>
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/bootstrap.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
</head>
<body>
    <?php include("header.php"); ?>
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Resell Plots</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                            <li class="breadcrumb-item active">Resell Plots</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Form to Add New Resell Plot -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Add Resell Plot</h4>
                        </div>
                        <div class="card-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            <?php if ($msg): ?>
                                <div class="alert alert-success"><?php echo $msg; ?></div>
                            <?php endif; ?>
                            <form method="post">
                                <div class="form-group">
                                    <label for="title">Title</label>
                                    <input type="text" name="title" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="content">Content</label>
                                    <textarea name="content" class="form-control" required></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="ptype">Property Type</label>
                                    <input type="text" name="ptype" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="stype">Selling Type</label>
                                    <input type="text" name="stype" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="plot_location">Plot Location</label>
                                    <input type="text" name="plot_location" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="plot_size">Plot Size (in sq ft)</label>
                                    <input type="number" name="plot_size" class="form-control" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="plot_dimensions">Plot Dimensions</label>
                                    <input type="text" name="plot_dimensions" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="plot_facing">Plot Facing</label>
                                    <input type="text" name="plot_facing" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="road_access">Road Access</label>
                                    <input type="text" name="road_access" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="price">Price</label>
                                    <input type="number" name="price" class="form-control" step="0.01" required>
                                </div>
                                <div class="form-group">
                                    <label for="plot_category">Plot Category</label>
                                    <input type="text" name="plot_category" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="full_address">Full Address</label>
                                    <input type="text" name="full_address" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="contact_name">Contact Name</label>
                                    <input type="text" name="contact_name" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="contact_number">Contact Number</label>
                                    <input type="text" name="contact_number" class="form-control" required>
                                </div>
                                <div class="form-group">
                                    <label for="contact_email">Contact Email</label>
                                    <input type="email" name="contact_email" class="form-control" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Add Plot</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Display Existing Resell Plots -->
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
</body>
</html>

<?php
// Close the database connection
$con->close();
?>
