<?php
require_once(__DIR__ . '/core/init.php');

$db = \App\Core\App::database();

// Check admin authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$error = "";
$msg = "";

if (isset($_POST['add_gata'])) {
    // Validate CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $site_id = filter_var($_POST['site_name'], FILTER_VALIDATE_INT);
        $gata_no = h(trim($_POST['gata_no']));
        $area_gata = filter_var(trim($_POST['area']), FILTER_VALIDATE_FLOAT);

        if (!$site_id || !$gata_no || $area_gata === false) {
            $error = "Please provide valid inputs.";
        } else {
            // Check available area in site
            $site_data = $db->fetchOne("SELECT available_area FROM site_master WHERE site_id = ?", [$site_id]);

            if ($site_data && $area_gata <= $site_data['available_area']) {
                $db->beginTransaction();
                try {
                    // Insert Gata
                    $db->execute("INSERT INTO gata_master (site_id, gata_no, area, available_area) VALUES (?, ?, ?, ?)", [$site_id, $gata_no, $area_gata, $area_gata]);

                    // Update Site Available Area
                    $db->execute("UPDATE site_master SET available_area = available_area - ? WHERE site_id = ?", [$area_gata, $site_id]);

                    $db->commit();
                    $msg = "Gata details added successfully.";
                } catch (Exception $e) {
                    $db->rollBack();
                    $error = "Error while adding record: " . $e->getMessage();
                }
            } else {
                $error = "Gata Area is larger than available Site area (" . ($site_data['available_area'] ?? 0) . " sqft).";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gata Master</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="shortcut icon" type="image/x-icon" href="assets/<?php echo get_asset_url('favicon.png', 'images'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/style.css', 'css'); ?>">
    <link rel="stylesheet" href="<?php echo get_asset_url('css/font-awesome.min.css', 'css'); ?>">
</head>
<body>
    <?php include(ABSPATH . "/includes/templates/header.php"); ?>

    <div class="container-fluid px-1 py-5 mx-auto">
        <div class="row d-flex justify-content-center">
            <div class="col-xl-7 col-lg-8 col-md-9 col-11 text-center">
                <div class="card shadow-sm border-0" style="background-color: #f8f9fa;">
                    <h3 class="mb-4 text-primary">Add New Gata</h3>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?php echo h($error); ?></div>
                    <?php endif; ?>

                    <?php if ($msg): ?>
                        <div class="alert alert-success"><?php echo h($msg); ?></div>
                    <?php endif; ?>

                    <form method="post" id="myForm">
                        <?php echo getCsrfField(); ?>

                        <div class="row text-left">
                            <div class="form-group col-sm-6">
                                <label class="form-label font-weight-bold">Site Name <span class="text-danger">*</span></label>
                                <select class="form-control" id="site_name" name="site_name" required>
                                    <option value="">Select Site</option>
                                    <?php
                                    $sites = $db->fetchAll("SELECT site_id, site_name FROM site_master ORDER BY site_name ASC");
                                    foreach ($sites as $row) {
                                        echo '<option value="' . h($row['site_id']) . '">' . h($row['site_name']) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="gata_no" class="form-label font-weight-bold">Gata Number <span class="text-danger">*</span></label>
                                <input type="text" id="gata_no" name="gata_no" class="form-control" placeholder="e.g. 123A" required>
                            </div>
                        </div>

                        <div class="row text-left">
                            <div class="form-group col-sm-6">
                                <label class="form-label font-weight-bold">Total Land Area (sqft) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" id="area" name="area" class="form-control" placeholder="e.g. 1500.00" required>
                            </div>
                        </div>

                        <div class="row justify-content-center mt-4">
                            <div class="form-group col-sm-6">
                                <button type="submit" name="add_gata" class="btn btn-primary btn-block">Add Gata</button>
                            </div>
                        </div>
                    </form>
                    <div class="mt-3">
                        <a href="update_gata.php" class="btn btn-link">View Gata List</a>
                    </div>
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
