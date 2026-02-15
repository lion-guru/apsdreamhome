<?php
if (!isAdmin()) {
    header("Location: /admin/login");
    exit();
}
$page_title = $page_title ?? $mlSupport->translate("Edit Booking");
require_once ABSPATH . '/resources/views/admin/layouts/header.php';
?>

<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row">
                <div class="col">
                    <h3 class="page-title"><?php echo h($page_title); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="/admin/dashboard"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="/admin/bookings"><?php echo h($mlSupport->translate('Bookings')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Edit Booking')); ?></li>
                    </ul>
                </div>
            </div>
        </div>
        <!-- /Page Header -->

        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-body">
                        <form action="/admin/bookings/update/<?php echo h($booking['id']); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Customer')); ?> <span class="text-danger">*</span></label>
                                        <select name="customer_id" class="form-select select2" required>
                                            <option value=""><?php echo h($mlSupport->translate('Select Customer')); ?></option>
                                            <?php foreach ($customers as $customer): ?>
                                                <option value="<?php echo h($customer['id']); ?>" <?php echo ($booking['customer_id'] == $customer['id']) ? 'selected' : ''; ?>>
                                                    <?php echo h($customer['name']); ?> (<?php echo h($customer['id']); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Property')); ?> <span class="text-danger">*</span></label>
                                        <select name="property_id" class="form-select select2" required>
                                            <option value=""><?php echo h($mlSupport->translate('Select Property')); ?></option>
                                            <?php foreach ($properties as $property): ?>
                                                <option value="<?php echo h($property['id']); ?>" <?php echo ($booking['property_id'] == $property['id']) ? 'selected' : ''; ?>>
                                                    <?php echo h($property['title']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Booking Type')); ?></label>
                                        <select name="booking_type" class="form-select">
                                            <option value="site_visit" <?php echo ($booking['booking_type'] === 'site_visit') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Site Visit')); ?></option>
                                            <option value="online_consultation" <?php echo ($booking['booking_type'] === 'online_consultation') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Online Consultation')); ?></option>
                                            <option value="direct_booking" <?php echo ($booking['booking_type'] === 'direct_booking') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Direct Booking')); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Status')); ?></label>
                                        <select name="status" class="form-select">
                                            <option value="pending" <?php echo ($booking['status'] === 'pending') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Pending')); ?></option>
                                            <option value="confirmed" <?php echo ($booking['status'] === 'confirmed') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Confirmed')); ?></option>
                                            <option value="cancelled" <?php echo ($booking['status'] === 'cancelled') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Cancelled')); ?></option>
                                            <option value="completed" <?php echo ($booking['status'] === 'completed') ? 'selected' : ''; ?>><?php echo h($mlSupport->translate('Completed')); ?></option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Booking Date')); ?> <span class="text-danger">*</span></label>
                                        <input type="date" name="booking_date" class="form-control" required value="<?php echo h($booking['booking_date']); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label"><?php echo h($mlSupport->translate('Visit Time')); ?></label>
                                        <input type="time" name="visit_time" class="form-control" value="<?php echo h($booking['visit_time']); ?>">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Budget Range')); ?></label>
                                <input type="text" name="budget_range" class="form-control" placeholder="e.g. 50L - 75L" value="<?php echo h($booking['budget_range']); ?>">
                            </div>

                            <div class="form-group mb-3">
                                <label class="form-label"><?php echo h($mlSupport->translate('Special Requirements')); ?></label>
                                <textarea name="special_requirements" class="form-control" rows="4"><?php echo h($booking['special_requirements']); ?></textarea>
                            </div>

                            <div class="submit-section text-center">
                                <button type="submit" class="btn btn-primary submit-btn"><?php echo h($mlSupport->translate('Update')); ?></button>
                                <a href="/admin/bookings" class="btn btn-secondary submit-btn"><?php echo h($mlSupport->translate('Cancel')); ?></a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once ABSPATH . '/resources/views/admin/layouts/footer.php'; ?>
