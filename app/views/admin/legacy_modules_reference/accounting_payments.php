<?php
/**
 * Accounting - Payment Management
 * Standardized UI for APS Dream Home
 */

require_once __DIR__ . '/core/init.php';

$page_title = "Payment Management";
$include_datatables = true;

include 'admin_header.php';
include 'admin_sidebar.php';
?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col">
                    <h3 class="page-title"><?php echo h($mlSupport->translate($page_title)); ?></h3>
                    <ul class="breadcrumb">
                        <li class="breadcrumb-item"><a href="admin_dashboard.php"><?php echo h($mlSupport->translate('Dashboard')); ?></a></li>
                        <li class="breadcrumb-item"><a href="accounting_dashboard.php"><?php echo h($mlSupport->translate('Accounting')); ?></a></li>
                        <li class="breadcrumb-item active"><?php echo h($mlSupport->translate('Payments')); ?></li>
                    </ul>
                </div>
                <div class="col-auto float-right ml-auto">
                    <button class="btn btn-primary add-btn" data-toggle="modal" data-target="#addPaymentModal">
                        <i class="fa fa-plus"></i> <?php echo h($mlSupport->translate('Add New Payment')); ?>
                    </button>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-money"></i></span>
                        <div class="dash-widget-info">
                            <h3>₹<span id="total_collection">0</span></h3>
                            <span><?php echo h($mlSupport->translate('Total Collection')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-6 col-lg-6 col-xl-3">
                <div class="card dash-widget">
                    <div class="card-body">
                        <span class="dash-widget-icon"><i class="fa fa-clock-o"></i></span>
                        <div class="dash-widget-info">
                            <h3><span id="pending_count">0</span></h3>
                            <span><?php echo h($mlSupport->translate('Pending Payments')); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Filters -->
        <div class="card filter-card">
            <div class="card-body">
                <form id="paymentFilters">
                    <div class="row filter-row">
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group form-focus">
                                <input type="text" class="form-control floating" id="dateRange" name="dateRange">
                                <label class="focus-label"><?php echo h($mlSupport->translate('Date Range')); ?></label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group form-focus select-focus">
                                <select class="select floating" id="paymentStatus" name="status">
                                    <option value=""><?php echo h($mlSupport->translate('All Status')); ?></option>
                                    <option value="pending"><?php echo h($mlSupport->translate('Pending')); ?></option>
                                    <option value="completed"><?php echo h($mlSupport->translate('Completed')); ?></option>
                                    <option value="failed"><?php echo h($mlSupport->translate('Failed')); ?></option>
                                </select>
                                <label class="focus-label"><?php echo h($mlSupport->translate('Payment Status')); ?></label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <div class="form-group form-focus select-focus">
                                <select class="select floating" id="paymentType" name="type">
                                    <option value=""><?php echo h($mlSupport->translate('All Types')); ?></option>
                                    <option value="booking"><?php echo h($mlSupport->translate('Booking')); ?></option>
                                    <option value="installment"><?php echo h($mlSupport->translate('Installment')); ?></option>
                                    <option value="full"><?php echo h($mlSupport->translate('Full Payment')); ?></option>
                                </select>
                                <label class="focus-label"><?php echo h($mlSupport->translate('Payment Type')); ?></label>
                            </div>
                        </div>
                        <div class="col-sm-6 col-md-3">
                            <button type="submit" class="btn btn-success btn-block"> <?php echo h($mlSupport->translate('SEARCH')); ?> </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped custom-table mb-0" id="paymentsTable">
                                <thead>
                                    <tr>
                                        <th><?php echo h($mlSupport->translate('Date')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Transaction ID')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Customer')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Type')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Amount')); ?></th>
                                        <th><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <th class="text-right"><?php echo h($mlSupport->translate('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div id="addPaymentModal" class="modal custom-modal fade" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo h($mlSupport->translate('Add New Payment')); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addPaymentForm">
                    <?php echo getCsrfField(); ?>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo h($mlSupport->translate('Customer')); ?> <span class="text-danger">*</span></label>
                                <select class="select" id="customer" name="customer_id" required>
                                    <option value=""><?php echo h($mlSupport->translate('Select Customer')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo h($mlSupport->translate('Amount')); ?> <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="amount" required step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo h($mlSupport->translate('Payment Type')); ?> <span class="text-danger">*</span></label>
                                <select class="select" name="payment_type" required>
                                    <option value="booking"><?php echo h($mlSupport->translate('Booking')); ?></option>
                                    <option value="installment"><?php echo h($mlSupport->translate('Installment')); ?></option>
                                    <option value="full"><?php echo h($mlSupport->translate('Full Payment')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><?php echo h($mlSupport->translate('Payment Method')); ?> <span class="text-danger">*</span></label>
                                <select class="select" name="payment_method" required>
                                    <option value="cash"><?php echo h($mlSupport->translate('Cash')); ?></option>
                                    <option value="bank_transfer"><?php echo h($mlSupport->translate('Bank Transfer')); ?></option>
                                    <option value="cheque"><?php echo h($mlSupport->translate('Cheque')); ?></option>
                                    <option value="upi"><?php echo h($mlSupport->translate('UPI')); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?php echo h($mlSupport->translate('Description')); ?></label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="submit-section">
                        <button type="submit" class="btn btn-primary submit-btn"><?php echo h($mlSupport->translate('Save Payment')); ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'admin_footer.php'; ?>

<!-- Page Custom JS -->
<script src="assets/js/accounting/payments.js"></script>
