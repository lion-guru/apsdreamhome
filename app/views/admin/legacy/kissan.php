<?php
/**
 * Kissan Land Management - Standardized Version
 * Handles detailed land record management for farmers
 */

require_once __DIR__ . '/core/init.php';

$db = \App\Core\App::database();

// Ensure user is admin
if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$message = "";
$message_type = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_land_details'])) {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid security token.";
        $message_type = "danger";
    } else {
        // Capture and sanitize input
        $farmer_name = h(trim($_POST['farmer_name']));
        $farmer_mobile = h(trim($_POST['farmer_mobile']));
        $bank_name = h(trim($_POST['bank_name']));
        $account_number = h(trim($_POST['account_number']));
        $bank_ifsc = h(trim($_POST['bank_ifsc']));
        $site_name = h(trim($_POST['site_name']));
        $land_area = filter_var(trim($_POST['land_area']), FILTER_VALIDATE_FLOAT);
        $total_land_price = filter_var(trim($_POST['total_land_price']), FILTER_VALIDATE_FLOAT);
        $gata_number = h(trim($_POST['gata_number']));
        $district = h(trim($_POST['district']));
        $tehsil = h(trim($_POST['tehsil']));
        $city = h(trim($_POST['city']));
        $gram = h(trim($_POST['gram']));
        $land_manager_name = h(trim($_POST['land_manager_name']));
        $land_manager_mobile = h(trim($_POST['land_manager_mobile']));
        $agreement_status = h(trim($_POST['agreement_status']));

        // Basic validation
        if (empty($farmer_name) || empty($farmer_mobile)) {
            $message = "Farmer name and mobile are required.";
            $message_type = "danger";
        } else {
            // Handle file upload
            $land_paper = $_FILES['land_paper'] ?? null;
            $file_path = "";
            
            if ($land_paper && $land_paper['error'] === UPLOAD_ERR_OK) {
                $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif'];
                $file_extension = strtolower(pathinfo($land_paper['name'], PATHINFO_EXTENSION));
                
                if (in_array($file_extension, $allowed_extensions)) {
                    $upload_dir = 'uploads/land_papers/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $file_name = \App\Helpers\SecurityHelper::generateRandomString(16, false) . '.' . $file_extension;
                    $file_path = $upload_dir . $file_name;
                    
                    if (!move_uploaded_file($land_paper['tmp_name'], $file_path)) {
                        $message = "Failed to move uploaded file.";
                        $message_type = "danger";
                    }
                } else {
                    $message = "Invalid file type. Allowed: " . implode(", ", $allowed_extensions);
                    $message_type = "danger";
                }
            }

            if ($message_type !== "danger") {
                // Prepare SQL statement for insertion
                $sql = "INSERT INTO kisaan_land_management (farmer_name, farmer_mobile, bank_name, account_number, bank_ifsc, site_name, land_area, total_land_price, gata_number, district, tehsil, city, gram, land_paper, land_manager_name, land_manager_mobile, agreement_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $success = $db->execute($sql, [
                    $farmer_name, $farmer_mobile, $bank_name, $account_number, $bank_ifsc, 
                    $site_name, $land_area, $total_land_price, $gata_number, $district, 
                    $tehsil, $city, $gram, $file_path, $land_manager_name, 
                    $land_manager_mobile, $agreement_status
                ]);
                
                if ($success) {
                    $admin_name = getAuthUsername();
                    
                    require_once __DIR__ . '/../includes/notification_manager.php';
                    require_once __DIR__ . '/../includes/email_service.php';
                    $nm = new NotificationManager(null, new EmailService());
                    $nm->send([
                        'user_id' => 1,
                        'template' => 'LAND_RECORD_CREATED',
                        'data' => [
                            'farmer_name' => $farmer_name,
                            'site_name' => $site_name,
                            'land_area' => $land_area,
                            'admin_name' => $admin_name
                        ],
                        'channels' => ['db']
                    ]);
                    
                    header("Location: view_kisaan.php?msg=" . urlencode("Land record added successfully."));
                    exit();
                } else {
                    $message = "Error adding record";
                    $message_type = "danger";
                }
            }
        }
    }
}

$page_title = "Kissan Land Management";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title">Kissan Land Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="view_kisaan.php">Land Records</a></li>
                        <li class="breadcrumb-item active">Add Land Details</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom-0">
                        <h4 class="card-title mb-0">Add New Land Details</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo h($message_type); ?> alert-dismissible fade show">
                                <?php echo h($message); ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <form method="post" id="landForm" class="needs-validation" novalidate enctype="multipart/form-data">
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="card-title text-primary">Farmer Details</h5>
                                    <div class="form-group">
                                        <label>Farmer Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="farmer_name" required pattern="[A-Za-z\s]+">
                                        <div class="invalid-feedback">Please enter a valid farmer name (letters and spaces only).</div>
                                    </div>
                                    <div class="form-group">
                                        <label>Farmer Mobile <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="farmer_mobile" required pattern="[0-9]{10}">
                                        <div class="invalid-feedback">Please enter a valid 10-digit mobile number.</div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="card-title text-primary">Bank Details</h5>
                                    <div class="form-group">
                                        <label>Bank Name</label>
                                        <input type="text" class="form-control" name="bank_name">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Account Number</label>
                                                <input type="text" class="form-control" name="account_number">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Bank IFSC</label>
                                                <input type="text" class="form-control" name="bank_ifsc">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="card-title text-primary">Land & Location Details</h5>
                                    <div class="form-group">
                                        <label>Site Name <span class="text-danger">*</span></label>
                                        <select class="form-control select" name="site_name" id="site_name" required>
                                            <option value="">Select Site</option>
                                            <?php 
                                            $sites = $db->fetchAll("SELECT site_name FROM site_master ORDER BY site_name ASC");
                                            foreach ($sites as $site): ?>
                                                <option value="<?php echo h($site['site_name']); ?>"><?php echo h($site['site_name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">Please select a site.</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Land Area (in decimal) <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control" name="land_area" required>
                                                <div class="invalid-feedback">Valid land area is required.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Total Land Price <span class="text-danger">*</span></label>
                                                <input type="number" step="0.01" class="form-control" name="total_land_price" required>
                                                <div class="invalid-feedback">Valid price is required.</div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Gata Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="gata_number" required>
                                        <div class="invalid-feedback">Gata number is required.</div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>District</label>
                                                <input type="text" class="form-control" name="district">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Tehsil</label>
                                                <input type="text" class="form-control" name="tehsil">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>City</label>
                                                <input type="text" class="form-control" name="city">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Gram/Village</label>
                                                <input type="text" class="form-control" name="gram">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="card-title text-primary">Management Details</h5>
                                    <div class="form-group">
                                        <label>Land Paper (Upload DOC, Excel, PDF, or Image) <span class="text-danger">*</span></label>
                                        <div class="custom-file">
                                            <input type="file" class="custom-file-input" name="land_paper" id="land_paper" accept=".doc,.docx,.xls,.xlsx,.pdf,.jpg,.jpeg,.png,.gif" required>
                                            <label class="custom-file-label" for="land_paper">Choose file...</label>
                                            <div class="invalid-feedback">Please upload land papers.</div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Land Manager Name</label>
                                        <input type="text" class="form-control" name="land_manager_name" pattern="[A-Za-z\s]+">
                                    </div>
                                    <div class="form-group">
                                        <label>Land Manager Mobile</label>
                                        <input type="text" class="form-control" name="land_manager_mobile" pattern="[0-9]{10}">
                                    </div>
                                    <div class="form-group">
                                        <label>Agreement Status <span class="text-danger">*</span></label>
                                        <select class="form-control select" name="agreement_status" required>
                                            <option value="">Select Status</option>
                                            <option value="Registered">Registered</option>
                                            <option value="On Agreement">On Agreement</option>
                                            <option value="Pending">Pending</option>
                                        </select>
                                        <div class="invalid-feedback">Please select agreement status.</div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-right mt-4">
                                <a href="view_kisaan.php" class="btn btn-secondary mr-2">Cancel</a>
                                <button type="submit" name="add_land_details" class="btn btn-primary">Add Land Details</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Bootstrap validation
(function() {
    'use strict';
    window.addEventListener('load', function() {
        var forms = document.getElementsByClassName('needs-validation');
        var validation = Array.prototype.filter.call(forms, function(form) {
            form.addEventListener('submit', function(event) {
                if (form.checkValidity() === false) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
        
        // Custom file input label update
        $('.custom-file-input').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });
    }, false);
})();
</script>

<?php include 'admin_footer.php'; ?>

