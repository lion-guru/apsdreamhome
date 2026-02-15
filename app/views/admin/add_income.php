<?php
/**
 * Add Income - Standardized Version
 */

require_once __DIR__ . '/core/init.php';

use App\Core\Database;

$db = \App\Core\App::database();

$message = "";
$message_type = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid security token.";
        $message_type = "danger";
    } else {
        $amount = floatval($_POST['amount'] ?? 0);
        $income_date = $_POST['income_date'] ?? date('Y-m-d');
        $category = $_POST['category'] ?? '';
        $description = $_POST['description'] ?? '';
        $payment_method = $_POST['payment_method'] ?? 'cash';
        $customer_id = !empty($_POST['customer_id']) ? intval($_POST['customer_id']) : null;
        $project_id = !empty($_POST['project_id']) ? intval($_POST['project_id']) : null;
        $created_by = getAuthUserId();

        if ($amount <= 0 || empty($category) || empty($income_date)) {
            $message = "Please fill in all required fields correctly.";
            $message_type = "danger";
        } else {
            // Generate unique income number
            $income_number = "INC-" . date('Ymd') . "-" . strtoupper(\App\Helpers\SecurityHelper::generateRandomString(4, false));

            try {
                $sql = "INSERT INTO income_records (income_number, income_date, income_category, amount, description, payment_method, customer_id, project_id, created_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'received')";
                
                if ($db->execute($sql, [$income_number, $income_date, $category, $amount, $description, $payment_method, $customer_id, $project_id, $created_by])) {
                    $id = $db->lastInsertId();
                    require_once __DIR__ . "/../includes/notification_manager.php";
                    require_once __DIR__ . "/../includes/email_service.php";
                    
                    $nm = new NotificationManager(null, new EmailService());
                    $admin_name = getAuthUsername();
                    
                    // Standardized Notification
                    $nm->send([
                        'user_id' => 1, // Admin
                        'template' => 'FINANCIAL_TRANSACTION',
                        'data' => [
                            'type' => 'Income',
                            'amount' => $amount,
                            'id' => $income_number,
                            'admin_name' => $admin_name
                        ],
                        'channels' => ['db']
                    ]);

                    // Log activity
                    logAdminActivity("Income Added", "Recorded income of " . formatCurrency($amount) . " (Ref: $income_number)");

                    header("Location: dashboard.php?msg=" . urlencode("Income added successfully! Number: $income_number"));
                    exit();
                } else {
                    $message = "Error saving income.";
                    $message_type = "danger";
                }
            } catch (Exception $e) {
                $message = "Database error: " . $e->getMessage();
                $message_type = "danger";
            }
        }
    }
}

// Fetch customers and projects for dropdowns using singleton
$customers = $db->fetchAll("SELECT id, name FROM customers ORDER BY name ASC");
$projects = $db->fetchAll("SELECT id, pname FROM projects ORDER BY pname ASC");

$page_title = "Add New Income";
include 'admin_header.php';
include 'admin_sidebar.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Financial Management')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="dashboard.php"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add Income')); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0"><?php echo h($mlSupport->translate('Record New Income')); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= h($message_type) ?> alert-dismissible fade show">
                                <?= h($mlSupport->translate($message)) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <?= getCsrfField() ?>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold"><?php echo h($mlSupport->translate('Amount (₹)')); ?> <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fa fa-rupee-sign"></i></span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control" name="amount" placeholder="0.00" required>
                                        <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter a valid amount.')); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold"><?php echo h($mlSupport->translate('Date')); ?> <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" name="income_date" value="<?= date('Y-m-d') ?>" required>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a date.')); ?></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold"><?php echo h($mlSupport->translate('Category')); ?> <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="category" required>
                                        <option value=""><?php echo h($mlSupport->translate('Select Category')); ?></option>
                                        <option value="Booking Amount"><?php echo h($mlSupport->translate('Booking Amount')); ?></option>
                                        <option value="EMI Payment"><?php echo h($mlSupport->translate('EMI Payment')); ?></option>
                                        <option value="Maintenance Fee"><?php echo h($mlSupport->translate('Maintenance Fee')); ?></option>
                                        <option value="Legal Charges"><?php echo h($mlSupport->translate('Legal Charges')); ?></option>
                                        <option value="Other"><?php echo h($mlSupport->translate('Other')); ?></option>
                                    </select>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a category.')); ?></div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold"><?php echo h($mlSupport->translate('Payment Method')); ?></label>
                                    <select class="form-control" name="payment_method">
                                        <option value="cash"><?php echo h($mlSupport->translate('Cash')); ?></option>
                                        <option value="bank_transfer"><?php echo h($mlSupport->translate('Bank Transfer')); ?></option>
                                        <option value="upi"><?php echo h($mlSupport->translate('UPI')); ?></option>
                                        <option value="cheque"><?php echo h($mlSupport->translate('Cheque')); ?></option>
                                        <option value="online"><?php echo h($mlSupport->translate('Online')); ?></option>
                                        <option value="card"><?php echo h($mlSupport->translate('Card')); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold"><?php echo h($mlSupport->translate('Customer (Optional)')); ?></label>
                                    <select class="form-control select2" name="customer_id">
                                        <option value=""><?php echo h($mlSupport->translate('Select Customer')); ?></option>
                                        <?php foreach($customers as $cust): ?>
                                            <option value="<?php echo (int)$cust['id']; ?>"><?php echo h($cust['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label font-weight-bold"><?php echo h($mlSupport->translate('Project (Optional)')); ?></label>
                                    <select class="form-control select2" name="project_id">
                                        <option value=""><?php echo h($mlSupport->translate('Select Project')); ?></option>
                                        <?php foreach($projects as $proj): ?>
                                            <option value="<?php echo (int)$proj['id']; ?>"><?php echo h($proj['pname']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold"><?php echo h($mlSupport->translate('Description')); ?></label>
                                <textarea class="form-control" name="description" rows="3" placeholder="<?php echo h($mlSupport->translate('Enter details about this income...')); ?>"></textarea>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5 rounded-pill">
                                    <i class="fa fa-save mr-2"></i> <?php echo h($mlSupport->translate('Record Income')); ?>
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary btn-lg px-5 rounded-pill ml-2"><?php echo h($mlSupport->translate('Cancel')); ?></a>
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

    // Initialize Select2 if available
    if (typeof jQuery !== 'undefined' && jQuery().select2) {
        jQuery('.select2').select2({
            width: '100%'
        });
    }
});
</script>

