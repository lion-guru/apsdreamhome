<?php
/**
 * Add Expense - Standardized Version
 */

require_once __DIR__ . "/core/init.php";

// Ensure user is admin (handled by core/init.php for most pages, but we can be explicit)
if (!isAdmin()) {
    header("Location: dashboard.php");
    exit();
}

$message = "";
$message_type = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF validation
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $message = "Invalid security token.";
        $message_type = "danger";
    } else {
        $amount = floatval($_POST['amount'] ?? 0);
        $expense_date = $_POST['expense_date'] ?? date('Y-m-d');
        $source = $_POST['source'] ?? '';
        $description = $_POST['description'] ?? '';
        $user_id = getAuthUserId();

        if ($amount <= 0 || empty($source) || empty($expense_date)) {
            $message = "Please fill in all required fields correctly.";
            $message_type = "danger";
        } else {
            try {
                $db = \App\Core\App::database();
                $sql = "INSERT INTO expenses (user_id, amount, source, expense_date, description) VALUES (?, ?, ?, ?, ?)";
                
                if ($db->execute($sql, [$user_id, $amount, $source, $expense_date, $description])) {
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
                            'type' => 'Expense',
                            'amount' => $amount,
                            'id' => "EXP-" . $id,
                            'admin_name' => $admin_name
                        ],
                        'channels' => ['db']
                    ]);

                    header("Location: dashboard.php?msg=" . urlencode("Expense added successfully!"));
                    exit();
                } else {
                    $message = "Error saving expense.";
                    $message_type = "danger";
                }
            } catch (Exception $e) {
                $message = "Database error: " . $e->getMessage();
                $message_type = "danger";
            }
        }
    }
}

$page_title = "Add New Expense";
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
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add Expense')); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-danger text-white">
                        <h5 class="card-title mb-0"><?php echo h($mlSupport->translate('Record New Expense')); ?></h5>
                    </div>
                    <div class="card-body">
                        <?php if ($message): ?>
                            <div class="alert alert-<?= h($message_type) ?> alert-dismissible fade show">
                                <?= h($mlSupport->translate($message)) ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" class="needs-validation" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= h(SecurityUtility::generateCSRFToken()) ?>">

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
                                    <input type="date" class="form-control" name="expense_date" value="<?php echo date('Y-m-d'); ?>" required>
                                    <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a date.')); ?></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label font-weight-bold"><?php echo h($mlSupport->translate('Expense Source / Vendor')); ?> <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="source" placeholder="<?php echo h($mlSupport->translate('e.g. Electricity Bill, Stationery, Contractor Payment')); ?>" required>
                                <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please provide a source or vendor name.')); ?></div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label font-weight-bold"><?php echo h($mlSupport->translate('Description')); ?></label>
                                <textarea class="form-control" name="description" rows="3" placeholder="<?php echo h($mlSupport->translate('Enter details about this expense...')); ?>"></textarea>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-danger btn-lg px-5 rounded-pill">
                                    <i class="fa fa-save mr-2"></i> <?php echo h($mlSupport->translate('Record Expense')); ?>
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
});
</script>


