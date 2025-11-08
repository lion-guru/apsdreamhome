<?php
require_once '../../includes/config.php';
require_once '../../includes/db_connection.php';
require_once '../../includes/auth_check.php';

// Check if user is admin
if (!isset($_SESSION['admin_id'])) {
    header('Location: ../login.php');
    exit();
}

$page_title = "Payment Management";
require_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Payment Management</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#addPaymentModal">
            <i class="fas fa-plus"></i> Add New Payment
        </button>
    </div>

    <!-- Payment Filters -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Payment Filters</h6>
        </div>
        <div class="card-body">
            <form id="paymentFilters" class="row">
                <div class="col-md-3 mb-3">
                    <label for="dateRange">Date Range</label>
                    <input type="text" class="form-control" id="dateRange" name="dateRange">
                </div>
                <div class="col-md-3 mb-3">
                    <label for="paymentStatus">Payment Status</label>
                    <select class="form-control" id="paymentStatus" name="status">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="paymentType">Payment Type</label>
                    <select class="form-control" id="paymentType" name="type">
                        <option value="">All</option>
                        <option value="booking">Booking</option>
                        <option value="installment">Installment</option>
                        <option value="full">Full Payment</option>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">All Payments</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="paymentsTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction ID</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Modal -->
<div class="modal fade" id="addPaymentModal" tabindex="-1" role="dialog" aria-labelledby="addPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentModalLabel">Add New Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addPaymentForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="customer">Customer</label>
                                <select class="form-control" id="customer" name="customer_id" required>
                                    <option value="">Select Customer</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="amount">Amount</label>
                                <input type="number" class="form-control" id="amount" name="amount" required step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="paymentType">Payment Type</label>
                                <select class="form-control" id="paymentType" name="payment_type" required>
                                    <option value="booking">Booking</option>
                                    <option value="installment">Installment</option>
                                    <option value="full">Full Payment</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="paymentMethod">Payment Method</label>
                                <select class="form-control" id="paymentMethod" name="payment_method" required>
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="upi">UPI</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Page level plugins -->
<script src="../vendor/datatables/jquery.dataTables.min.js"></script>
<script src="../vendor/datatables/dataTables.bootstrap4.min.js"></script>
<script src="../vendor/daterangepicker/daterangepicker.js"></script>

<!-- Page level custom scripts -->
<script src="js/payments.js"></script>

<?php require_once '../includes/admin_footer.php'; ?>
