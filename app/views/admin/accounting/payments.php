<?php
require_once __DIR__ . '/../core/init.php';

$page_title = "Payment Management";
$include_datatables = true;
include '../admin_header.php';
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <?php include '../admin_sidebar.php'; ?>

            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fw-bold"><?php echo h($mlSupport->translate('Payment Management')); ?></h1>
                    <button class="btn btn-primary rounded-pill px-4" data-toggle="modal" data-target="#addPaymentModal">
                        <i class="fas fa-plus me-1"></i> <?php echo h($mlSupport->translate('Add New Payment')); ?>
                    </button>
                </div>

                <!-- Payment Filters -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-primary"><?php echo h($mlSupport->translate('Payment Filters')); ?></h5>
                    </div>
                    <div class="card-body">
                        <form id="paymentFilters" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="dateRange"><?php echo h($mlSupport->translate('Date Range')); ?></label>
                                <input type="text" class="form-control shadow-sm" id="dateRange" name="dateRange">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="paymentStatus"><?php echo h($mlSupport->translate('Payment Status')); ?></label>
                                <select class="form-control shadow-sm" id="paymentStatus" name="status">
                                    <option value=""><?php echo h($mlSupport->translate('All')); ?></option>
                                    <option value="pending"><?php echo h($mlSupport->translate('Pending')); ?></option>
                                    <option value="completed"><?php echo h($mlSupport->translate('Completed')); ?></option>
                                    <option value="failed"><?php echo h($mlSupport->translate('Failed')); ?></option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="paymentType"><?php echo h($mlSupport->translate('Payment Type')); ?></label>
                                <select class="form-control shadow-sm" id="paymentType" name="type">
                                    <option value=""><?php echo h($mlSupport->translate('All')); ?></option>
                                    <option value="booking"><?php echo h($mlSupport->translate('Booking')); ?></option>
                                    <option value="installment"><?php echo h($mlSupport->translate('Installment')); ?></option>
                                    <option value="full"><?php echo h($mlSupport->translate('Full Payment')); ?></option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm"><?php echo h($mlSupport->translate('Apply Filters')); ?></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Payments Table -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-primary"><?php echo h($mlSupport->translate('All Payments')); ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="paymentsTable" width="100%" cellspacing="0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4"><?php echo h($mlSupport->translate('Date')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Transaction ID')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Customer')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Type')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Amount')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <th class="border-0 px-4"><?php echo h($mlSupport->translate('Actions')); ?></th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addPaymentModalLabel"><?php echo h($mlSupport->translate('Add New Payment')); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addPaymentForm">
                <div class="modal-body p-4">
                    <?php echo getCsrfField(); ?>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="customer"><?php echo h($mlSupport->translate('Customer')); ?></label>
                                <select class="form-control shadow-sm" id="customer" name="customer_id" required>
                                    <option value=""><?php echo h($mlSupport->translate('Select Customer')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="amount"><?php echo h($mlSupport->translate('Amount')); ?></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="amount" name="amount" required step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="paymentTypeModal"><?php echo h($mlSupport->translate('Payment Type')); ?></label>
                                <select class="form-control shadow-sm" id="paymentTypeModal" name="payment_type" required>
                                    <option value="booking"><?php echo h($mlSupport->translate('Booking')); ?></option>
                                    <option value="installment"><?php echo h($mlSupport->translate('Installment')); ?></option>
                                    <option value="full"><?php echo h($mlSupport->translate('Full Payment')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="paymentMethod"><?php echo h($mlSupport->translate('Payment Method')); ?></label>
                                <select class="form-control shadow-sm" id="paymentMethod" name="payment_method" required>
                                    <option value="cash"><?php echo h($mlSupport->translate('Cash')); ?></option>
                                    <option value="bank_transfer"><?php echo h($mlSupport->translate('Bank Transfer')); ?></option>
                                    <option value="cheque"><?php echo h($mlSupport->translate('Cheque')); ?></option>
                                    <option value="upi"><?php echo h($mlSupport->translate('UPI')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="form-group mb-0">
                                <label class="form-label fw-bold" for="description"><?php echo h($mlSupport->translate('Description')); ?></label>
                                <textarea class="form-control shadow-sm" id="description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal"><?php echo h($mlSupport->translate('Close')); ?></button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><?php echo h($mlSupport->translate('Save Payment')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$page_specific_js = '
<!-- Page level plugins -->
<script src="' . ADMIN_URL . '/vendor/daterangepicker/moment.min.js"></script>
<script src="' . ADMIN_URL . '/vendor/daterangepicker/daterangepicker.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Page level custom scripts -->
<script src="' . ADMIN_URL . '/assets/js/accounting/payments.js"></script>
';
include '../admin_footer.php';
?>
