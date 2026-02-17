<?php
/**
 * Edit Land Record - Standardized Version
 */

require_once __DIR__ . "/core/init.php";
require_once __DIR__ . "/admin-functions.php";

$db = \App\Core\App::database();

// Ensure session is started and user is admin
if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$message = "";
$message_type = "";
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

if (!$id) {
    header("Location: view_kisaan.php?error=" . urlencode("No record ID provided."));
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid security token.";
        $message_type = "danger";
    } else {
        $farmer_name = h(trim($_POST['farmer_name'] ?? ''));
        $farmer_mobile = h(trim($_POST['farmer_mobile'] ?? ''));
        $bank_name = h(trim($_POST['bank_name'] ?? ''));
        $account_number = h(trim($_POST['account_number'] ?? ''));
        $bank_ifsc = h(trim($_POST['bank_ifsc'] ?? ''));
        $site_name = h(trim($_POST['site_name'] ?? ''));
        $land_area = filter_var($_POST['land_area'] ?? 0, FILTER_VALIDATE_FLOAT);
        $total_land_price = filter_var($_POST['total_land_price'] ?? 0, FILTER_VALIDATE_FLOAT);
        $total_paid_amount = filter_var($_POST['total_paid_amount'] ?? 0, FILTER_VALIDATE_FLOAT);
        $amount_pending = $total_land_price - $total_paid_amount;
        $gata_number = h(trim($_POST['gata_number'] ?? ''));
        $district = h(trim($_POST['district'] ?? ''));
        $tehsil = h(trim($_POST['tehsil'] ?? ''));
        $city = h(trim($_POST['city'] ?? ''));
        $gram = h(trim($_POST['gram'] ?? ''));
        $land_manager_name = h(trim($_POST['land_manager_name'] ?? ''));
        $land_manager_mobile = h(trim($_POST['land_manager_mobile'] ?? ''));
        $agreement_status = h(trim($_POST['agreement_status'] ?? 'Pending'));

        if (empty($farmer_name) || empty($farmer_mobile)) {
            $message = "Farmer name and mobile are required.";
            $message_type = "danger";
        } else {
            $sql = "UPDATE kisaan_land_management SET
                farmer_name = ?,
                farmer_mobile = ?,
                bank_name = ?,
                account_number = ?,
                bank_ifsc = ?,
                site_name = ?,
                land_area = ?,
                total_land_price = ?,
                total_paid_amount = ?,
                amount_pending = ?,
                gata_number = ?,
                district = ?,
                tehsil = ?,
                city = ?,
                gram = ?,
                land_manager_name = ?,
                land_manager_mobile = ?,
                agreement_status = ?
                WHERE id = ?";

            $params = [
                $farmer_name,
                $farmer_mobile,
                $bank_name,
                $account_number,
                $bank_ifsc,
                $site_name,
                $land_area,
                $total_land_price,
                $total_paid_amount,
                $amount_pending,
                $gata_number,
                $district,
                $tehsil,
                $city,
                $gram,
                $land_manager_name,
                $land_manager_mobile,
                $agreement_status,
                $id
            ];

            if ($db->execute($sql, $params)) {
                $admin_name = getAuthUsername();

                require_once __DIR__ . '/../includes/notification_manager.php';
                require_once __DIR__ . '/../includes/email_service.php';
                $nm = new NotificationManager(null, new EmailService());
                $nm->send([
                    'user_id' => 1,
                    'template' => 'LAND_RECORD_UPDATED',
                    'data' => [
                        'farmer_name' => $farmer_name,
                        'id' => $id,
                        'admin_name' => $admin_name
                    ],
                    'channels' => ['db']
                ]);

                header("Location: view_kisaan.php?msg=" . urlencode("Record updated successfully."));
                exit();
            } else {
                $message = "Error updating record.";
                $message_type = "danger";
            }
        }
    }
}

// Fetch existing record
$record = $db->fetchOne("SELECT * FROM kisaan_land_management WHERE id = :id", ['id' => $id]);

if (!$record) {
    header("Location: view_kisaan.php?error=" . urlencode("Record not found."));
    exit();
}

$page_title = "Edit Land Record";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title">Land Management</h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="view_kisaan.php">Land Records</a></li>
                        <li class="breadcrumb-item active">Edit Record</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">Edit Record for: <?php echo h($record['farmer_name']); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                                <?php echo h($message); ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken(); ?>">
                            <input type="hidden" name="id" value="<?php echo $id; ?>">

                            <div class="row">
                                <div class="col-md-12">
                                    <h4 class="card-title text-primary border-bottom pb-2 mb-3">Farmer Information</h4>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Farmer Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="farmer_name" value="<?php echo h($record['farmer_name']); ?>" required>
                                    <div class="invalid-feedback">Farmer name is required.</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Farmer Mobile <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="farmer_mobile" value="<?php echo h($record['farmer_mobile']); ?>" required>
                                    <div class="invalid-feedback">Farmer mobile is required.</div>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h4 class="card-title text-primary border-bottom pb-2 mb-3">Bank Details</h4>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Bank Name</label>
                                    <input type="text" class="form-control" name="bank_name" value="<?php echo h($record['bank_name']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Account Number</label>
                                    <input type="text" class="form-control" name="account_number" value="<?php echo h($record['account_number']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">IFSC Code</label>
                                    <input type="text" class="form-control" name="bank_ifsc" value="<?php echo h($record['bank_ifsc']); ?>">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h4 class="card-title text-primary border-bottom pb-2 mb-3">Land & Site Details</h4>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Site Name</label>
                                    <select class="form-control" name="site_name" required>
                                        <option value="">Select Site</option>
                                        <?php
                                        $site_query = "SELECT site_name FROM site_master ORDER BY site_name ASC";
                                        $sites = $db->fetchAll($site_query);
                                        $site_found = false;
                                        if (!empty($sites)) {
                                            foreach ($sites as $site):
                                                $selected = ($site['site_name'] == $record['site_name']) ? 'selected' : '';
                                                if ($selected) $site_found = true;
                                        ?>
                                            <option value="<?php echo h($site['site_name']); ?>" <?php echo $selected; ?>><?php echo h($site['site_name']); ?></option>
                                        <?php
                                            endforeach;
                                        }

                                        // If the current site name is not in site_master, still show it as an option
                                        if (!$site_found && !empty($record['site_name'])) {
                                            echo '<option value="'.h($record['site_name']).'" selected>'.h($record['site_name']).' (Custom)</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold">Gata Number</label>
                                    <input type="text" class="form-control" name="gata_number" value="<?php echo h($record['gata_number']); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label font-weight-bold">District</label>
                                    <input type="text" class="form-control" name="district" value="<?php echo h($record['district']); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label font-weight-bold">Tehsil</label>
                                    <input type="text" class="form-control" name="tehsil" value="<?php echo h($record['tehsil']); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label font-weight-bold">City</label>
                                    <input type="text" class="form-control" name="city" value="<?php echo h($record['city']); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label font-weight-bold">Gram (Village)</label>
                                    <input type="text" class="form-control" name="gram" value="<?php echo h($record['gram']); ?>">
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h4 class="card-title text-primary border-bottom pb-2 mb-3">Pricing & Area</h4>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label font-weight-bold">Land Area (sq ft)</label>
                                    <input type="number" step="0.01" class="form-control pricing-input" name="land_area" value="<?php echo h($record['land_area']); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label font-weight-bold">Total Price (₹)</label>
                                    <input type="number" step="0.01" class="form-control pricing-input" id="total_land_price" name="total_land_price" value="<?php echo h($record['total_land_price']); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label font-weight-bold">Paid Amount (₹)</label>
                                    <input type="number" step="0.01" class="form-control pricing-input" id="total_paid_amount" name="total_paid_amount" value="<?php echo h($record['total_paid_amount']); ?>">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label font-weight-bold">Pending Amount (₹)</label>
                                    <input type="number" step="0.01" class="form-control" id="amount_pending" name="amount_pending" value="<?php echo h($record['amount_pending']); ?>" readonly>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <h4 class="card-title text-primary border-bottom pb-2 mb-3">Management</h4>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Land Manager Name</label>
                                    <input type="text" class="form-control" name="land_manager_name" value="<?php echo h($record['land_manager_name']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Manager Mobile</label>
                                    <input type="text" class="form-control" name="land_manager_mobile" value="<?php echo h($record['land_manager_mobile']); ?>">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label font-weight-bold">Agreement Status</label>
                                    <select class="form-control" name="agreement_status">
                                        <option value="Pending" <?php echo $record['agreement_status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                        <option value="Registered" <?php echo $record['agreement_status'] == 'Registered' ? 'selected' : ''; ?>>Registered</option>
                                        <option value="On Agreement" <?php echo $record['agreement_status'] == 'On Agreement' ? 'selected' : ''; ?>>On Agreement</option>
                                        <option value="Cancelled" <?php echo $record['agreement_status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-center mt-5">
                                <button type="submit" class="btn btn-info btn-lg px-5 rounded-pill text-white">
                                    <i class="fa fa-save mr-2"></i> Update Record
                                </button>
                                <a href="view_kisaan.php" class="btn btn-secondary btn-lg px-5 rounded-pill ml-2">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    var forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function(form) {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Auto-calculate pending amount
    const pricingInputs = document.querySelectorAll('.pricing-input');
    const totalInput = document.getElementById('total_land_price');
    const paidInput = document.getElementById('total_paid_amount');
    const pendingInput = document.getElementById('amount_pending');

    pricingInputs.forEach(input => {
        input.addEventListener('input', function() {
            const total = parseFloat(totalInput.value) || 0;
            const paid = parseFloat(paidInput.value) || 0;
            pendingInput.value = (total - paid).toFixed(2);
        });
    });
});
</script>
