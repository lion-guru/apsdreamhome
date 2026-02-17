<?php
require_once 'admin-functions.php';
use App\Core\Database;

// Check if user is admin
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$db = \App\Core\App::database();
$error = "";
$msg = "";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gata Master List</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
</head>
<body>
    <?php include("../includes/templates/header.php"); ?>
    
    <div class="container mt-4">
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="card-title mb-0">Gata Master List</h4>
                    <a href="gata_master.php" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Add New Gata
                    </a>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Sno.</th>
                                <th>Site Name</th>
                                <th>Gata No</th>
                                <th>Total Area (sqft)</th>
                                <th>Available Area (sqft)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            try {
                                $gata_records = $db->fetchAll("
                                    SELECT g.*, s.site_name 
                                    FROM gata_master g 
                                    LEFT JOIN site_master s ON g.site_id = s.site_id 
                                    ORDER BY s.site_name ASC, g.gata_no ASC
                                ");
                                
                                $i = 1;
                                foreach ($gata_records as $row_gata) {
                                    $arg = base64_encode(json_encode($row_gata['gata_id']));
                                ?>
                                    <tr>
                                        <td><?php echo h($i++); ?></td>
                                        <td><strong><?php echo h($row_gata['site_name'] ?? 'N/A'); ?></strong></td>
                                        <td><?php echo h($row_gata['gata_no']); ?></td>
                                        <td><?php echo h(number_format($row_gata['area'], 2)); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $row_gata['available_area'] > 0 ? 'success' : 'danger'; ?>">
                                                <?php echo h(number_format($row_gata['available_area'], 2)); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="gata_edit.php?id=<?php echo h($arg); ?>" class="btn btn-sm btn-info text-white">
                                                <i class="fa fa-edit"></i> Edit
                                            </a>
                                        </td>
                                    </tr>
                                <?php }
                            } catch (Exception $e) {
                                echo "<tr><td colspan='6' class='text-center text-danger'>Error loading gata records: " . h($e->getMessage()) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="<?php echo get_asset_url('js/jquery-3.2.1.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/popper.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/bootstrap.min.js', 'js'); ?>"></script>
    <script src="<?php echo get_asset_url('js/script.js', 'js'); ?>"></script>
</body>
</html>
