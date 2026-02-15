<?php
require_once __DIR__ . "/core/init.php";
require_once __DIR__ . "/admin-functions.php";

$db = \App\Core\App::database();

// Ensure session is started and user is admin
if (!isAdmin()) {
    header("Location: /admin/login");
    exit();
}

$error = "";
$success = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? '')) {
        $error = $mlSupport->translate("Invalid security token.");
    } else {
        $property_id = intval($_POST['property_id'] ?? 0);
        $customer_id = intval($_POST['customer_id'] ?? 0);
        $booking_date = $_POST['booking_date'] ?? date('Y-m-d');
        $amount = floatval($_POST['amount'] ?? 0);
        $status = $_POST['status'] ?? 'pending';

        if ($property_id <= 0 || $customer_id <= 0 || $amount <= 0) {
            $error = $mlSupport->translate("Please fill in all required fields.");
        } else {
            try {
                if ($db->execute("INSERT INTO bookings (property_id, customer_id, booking_date, amount, status) VALUES (?, ?, ?, ?, ?)", [$property_id, $customer_id, $booking_date, $amount, $status])) {
                    // Add notification for audit log and customer
                    require_once ABSPATH . '/includes/notification_manager.php';
                    require_once ABSPATH . '/includes/email_service.php';

                    $emailService = new EmailService();
                    $nm = new NotificationManager($db, $emailService);

                    // Notify Customer
                    $nm->send([
                        'user_id' => $customer_id,
                        'template' => 'BOOKING_CONFIRMATION',
                        'data' => [
                            'property_id' => $property_id,
                            'amount' => $amount,
                            'date' => $booking_date
                        ],
                        'channels' => ['db', 'email']
                    ]);

                    // Internal Notification
                    $admin_name = getAuthUsername();
                    $nm->send([
                        'user_id' => 1, // Notify super admin
                        'type' => 'success',
                        'title' => $mlSupport->translate('New Booking Added'),
                        'message' => sprintf($mlSupport->translate("New booking added for property ID %d by %s for customer ID %d."), $property_id, $admin_name, $customer_id),
                        'channels' => ['db']
                    ]);

                    $success = $mlSupport->translate("Booking added successfully!");
                    // Redirect after success
                    header("Location: bookings.php?msg=" . urlencode($success));
                    exit();
                } else {
                    $error = $mlSupport->translate("Error adding booking.");
                }
            } catch (Exception $e) {
                $error = $mlSupport->translate("Error adding booking:") . " " . h($e->getMessage());
            }
        }
    }
}

// Fetch properties for dropdown
$status_available = 'available';
$properties = $db->fetchAll("SELECT id, title FROM properties WHERE status = ? ORDER BY title", [$status_available]);

// Fetch customers for dropdown
$utype_customer = 'customer';
$customers = $db->fetchAll("SELECT uid, uname FROM user WHERE utype = ? ORDER BY uname", [$utype_customer]);

$page_title = $mlSupport->translate("Add Booking");
require_once ABSPATH . "/resources/views/admin/layouts/header.php";
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col-sm-12">
                    <h3 class="page-title"><?php echo h($mlSupport->translate('Add New Booking')); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="bookings.php"><?php echo h($mlSupport->translate('Bookings')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Add Booking')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo h($error); ?></div>
                        <?php endif; ?>

                        <form method="POST" class="needs-validation" novalidate>
                            <?php echo getCsrfField(); ?>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Select Property')); ?> <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="property_id" required>
                                            <option value=""><?php echo h($mlSupport->translate('-- Select Property --')); ?></option>
                                            <?php foreach ($properties as $prop): ?>
                                                <option value="<?php echo h($prop['id']); ?>"><?php echo h($prop['title']); ?> (ID: <?php echo h($prop['id']); ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a property.')); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Select Customer')); ?> <span class="text-danger">*</span></label>
                                        <select class="form-control select2" name="customer_id" required>
                                            <option value=""><?php echo h($mlSupport->translate('-- Select Customer --')); ?></option>
                                            <?php foreach ($customers as $cust): ?>
                                                <option value="<?php echo h($cust['uid']); ?>"><?php echo h($cust['uname']); ?> (ID: <?php echo h($cust['uid']); ?>)</option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a customer.')); ?></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Booking Date')); ?> <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" name="booking_date" value="<?php echo date('Y-m-d'); ?>" required>
                                        <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please select a booking date.')); ?></div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Amount')); ?> <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">₹</span>
                                            </div>
                                            <input type="number" step="0.01" class="form-control" name="amount" placeholder="0.00" required>
                                            <div class="invalid-feedback"><?php echo h($mlSupport->translate('Please enter a valid amount.')); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Status')); ?> <span class="text-danger">*</span></label>
                                        <select class="form-control" name="status" required>
                                            <option value="pending"><?php echo h($mlSupport->translate('Pending')); ?></option>
                                            <option value="confirmed"><?php echo h($mlSupport->translate('Confirmed')); ?></option>
                                            <option value="cancelled"><?php echo h($mlSupport->translate('Cancelled')); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end mt-4">
                                <a href="bookings.php" class="btn btn-secondary me-2"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                                <button type="submit" class="btn btn-primary"><?php echo h($mlSupport->translate('Add Booking')); ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ABSPATH . "/resources/views/admin/layouts/footer.php"; ?>
