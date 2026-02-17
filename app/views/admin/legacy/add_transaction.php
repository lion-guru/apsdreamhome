<?php
require_once __DIR__ . '/core/init.php';

// Check authentication
adminAccessControl();

$db = \App\Core\App::database();
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Token Validation
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = $mlSupport->translate('Invalid security token. Action blocked.');
    } else {
        $kisan_id = intval($_POST['kisan_id'] ?? 0);
        $amount = floatval($_POST['amount'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $description = trim($_POST['description'] ?? '');

        if ($kisan_id <= 0 || $amount <= 0 || empty($description)) {
            $error = $mlSupport->translate('Please fill in all required fields.');
        } else {
            $sql = "INSERT INTO transactions (kisaan_id, amount, date, description) VALUES (?, ?, ?, ?)";

            try {
                if ($db->execute($sql, [$kisan_id, $amount, $date, $description])) {
                    $success = $mlSupport->translate('Transaction added successfully.');
                    // Using redirect helper if available, or maintaining standard header redirect
                    header("Location: kissan.php?msg=" . urlencode($success));
                    exit();
                } else {
                    $error = $mlSupport->translate('Error: Failed to record transaction.');
                }
            } catch (Exception $e) {
                $error = $mlSupport->translate('Error:') . " " . h($e->getMessage());
            }
        }
    }
}

$page_title = $mlSupport->translate('Add Transaction');
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Add Transaction')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="kissan.php"><?php echo h($mlSupport->translate('Kissan Management')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add Transaction')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo h($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <?php echo getCsrfField(); ?>

                            <div class="form-group mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Kisan ID')); ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-id-card"></i></span>
                                    <input type="number" class="form-control" id="kisan_id" name="kisan_id" required>
                                </div>
                                <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter the Kisan ID.')); ?></div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Amount')); ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                                </div>
                                <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter the amount.')); ?></div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Date')); ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a date.')); ?></div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="form-label"><?php echo h($mlSupport->translate('Description')); ?> <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fa fa-align-left"></i></span>
                                    <input type="text" class="form-control" id="description" name="description" required>
                                </div>
                                <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter a description.')); ?></div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg rounded-pill">
                                    <i class="fa fa-plus me-1"></i> <?php echo h($mlSupport->translate('Add Transaction')); ?>
                                </button>
                                <a href="kissan.php" class="btn btn-light btn-lg rounded-pill">
                                    <?php echo h($mlSupport->translate('Cancel')); ?>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>
