<?php
require_once(__DIR__ . '/core/init.php');

$db = \App\Core\App::database();

// Check admin authentication
if (!isAdmin()) {
    header('Location: login.php');
    exit();
}

$page_title = "Site Master";
include('admin_header.php');
include('admin_sidebar.php');

// Initialize variables
$error = "";
$msg = "";

if (isset($_POST['add_site'])) {
    // Validate CSRF token
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = "Invalid CSRF token.";
    } else {
        $site_name = h(trim($_POST['site_name']));
        if (!preg_match('/^[A-Za-z\s]+$/', $site_name)) {
            $error = "Site name must contain letters only.";
        } else {
            $district = h(trim($_POST['district']));
            $tehsil = h(trim($_POST['tehsil']));
            $gram = h(trim($_POST['gram']));
            $area = h(trim($_POST['area']));

            // Validate and sanitize numeric inputs
            $area = filter_var(trim($area), FILTER_VALIDATE_FLOAT);

            $sql = "INSERT INTO site_master (site_name, district, tehsil, gram, area) VALUES (:site_name, :district, :tehsil, :gram, :area)";
            $success = $db->execute($sql, [
                'site_name' => $site_name,
                'district' => $district,
                'tehsil' => $tehsil,
                'gram' => $gram,
                'area' => $area
            ]);

            if ($success) {
                $msg = "Record added Successfully";
            } else {
                $error = "Error while adding record";
            }
        }
    }
}
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo $page_title; ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item active"><?php echo $page_title; ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Add New Site</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $error; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>
                        <?php if ($msg): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo $msg; ?>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        <?php endif; ?>

                        <form method="post" id="myForm" enctype="multipart/form-data">
                            <?php echo getCsrfField(); ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Site Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="site_name" required>
                                    </div>
                                    <div class="form-group">
                                        <label>District <span class="text-danger">*</span></label>
                                        <select class="form-control" name="district" required>
                                            <option value="">Select District</option>
                                            <option value="Gorakhpur">Gorakhpur</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Tehsil <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="tehsil" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Gram <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="gram" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Total Land Area (sqft) <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="area" required>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <button type="submit" name="add_site" class="btn btn-primary">Add Site</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include('admin_footer.php'); ?>


