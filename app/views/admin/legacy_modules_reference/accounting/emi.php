<?php
require_once __DIR__ . '/../core/init.php';

$page_title = "EMI Management";
$include_datatables = true;
include '../admin_header.php';
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <?php include '../admin_sidebar.php'; ?>

            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fw-bold"><?php echo h($mlSupport->translate('EMI Management')); ?></h1>
                    <button class="btn btn-primary rounded-pill px-4" data-toggle="modal" data-target="#addEMIPlanModal">
                        <i class="fas fa-plus me-1"></i> <?php echo h($mlSupport->translate('Create New EMI Plan')); ?>
                    </button>
                </div>

                <!-- EMI Overview Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card shadow-sm border-0 h-100 bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-subtitle opacity-75"><?php echo h($mlSupport->translate('Active EMI Plans')); ?></h6>
                                    <i class="fas fa-calendar fa-2x opacity-50"></i>
                                </div>
                                <h2 class="card-title mb-0 fw-bold" id="activeEMICount"><?php echo h($mlSupport->translate('Loading...')); ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card shadow-sm border-0 h-100 bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-subtitle opacity-75"><?php echo h($mlSupport->translate('Total EMI Collection (Monthly)')); ?></h6>
                                    <i class="fas fa-rupee-sign fa-2x opacity-50"></i>
                                </div>
                                <h2 class="card-title mb-0 fw-bold" id="monthlyEMICollection"><?php echo h($mlSupport->translate('Loading...')); ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card shadow-sm border-0 h-100 bg-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-subtitle opacity-75"><?php echo h($mlSupport->translate('Pending EMIs')); ?></h6>
                                    <i class="fas fa-clock fa-2x opacity-50"></i>
                                </div>
                                <h2 class="card-title mb-0 fw-bold" id="pendingEMICount"><?php echo h($mlSupport->translate('Loading...')); ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card shadow-sm border-0 h-100 bg-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-subtitle opacity-75"><?php echo h($mlSupport->translate('Overdue EMIs')); ?></h6>
                                    <i class="fas fa-exclamation-circle fa-2x opacity-50"></i>
                                </div>
                                <h2 class="card-title mb-0 fw-bold" id="overdueEMICount"><?php echo h($mlSupport->translate('Loading...')); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EMI Plans Table -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-primary"><?php echo h($mlSupport->translate('EMI Plans')); ?></h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" id="emiPlansTable" width="100%" cellspacing="0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4"><?php echo h($mlSupport->translate('Customer')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Property')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Total Amount')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('EMI Amount')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Tenure')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Start Date')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Actions')); ?></th>
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

<!-- Add EMI Plan Modal -->
<div class="modal fade" id="addEMIPlanModal" tabindex="-1" role="dialog" aria-labelledby="addEMIPlanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="addEMIPlanModalLabel"><?php echo h($mlSupport->translate('Create New EMI Plan')); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addEMIPlanForm">
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
                                <label class="form-label fw-bold" for="property"><?php echo h($mlSupport->translate('Property')); ?></label>
                                <select class="form-control shadow-sm" id="property" name="property_id" required>
                                    <option value=""><?php echo h($mlSupport->translate('Select Property')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="totalAmount"><?php echo h($mlSupport->translate('Total Amount')); ?></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="totalAmount" name="total_amount" required step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="downPayment"><?php echo h($mlSupport->translate('Down Payment')); ?></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" class="form-control" id="downPayment" name="down_payment" required step="0.01">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="interestRate"><?php echo h($mlSupport->translate('Interest Rate (%)')); ?></label>
                                <input type="number" class="form-control shadow-sm" id="interestRate" name="interest_rate" required step="0.01">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="tenureMonths"><?php echo h($mlSupport->translate('Tenure (Months)')); ?></label>
                                <select class="form-control shadow-sm" id="tenureMonths" name="tenure_months" required>
                                    <option value=""><?php echo h($mlSupport->translate('Select Tenure')); ?></option>
                                    <option value="12">12 <?php echo h($mlSupport->translate('Months')); ?></option>
                                    <option value="24">24 <?php echo h($mlSupport->translate('Months')); ?></option>
                                    <option value="36">36 <?php echo h($mlSupport->translate('Months')); ?></option>
                                    <option value="48">48 <?php echo h($mlSupport->translate('Months')); ?></option>
                                    <option value="60">60 <?php echo h($mlSupport->translate('Months')); ?></option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <label class="form-label fw-bold" for="startDate"><?php echo h($mlSupport->translate('Start Date')); ?></label>
                                <input type="date" class="form-control shadow-sm" id="startDate" name="start_date" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded shadow-sm">
                                <label class="form-label fw-bold text-muted small mb-1"><?php echo h($mlSupport->translate('Calculated EMI Amount')); ?></label>
                                <div class="h4 mb-0 fw-bold text-primary" id="calculatedEMI">₹0.00</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal"><?php echo h($mlSupport->translate('Close')); ?></button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><?php echo h($mlSupport->translate('Create EMI Plan')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$page_specific_js = '
<!-- Page level plugins -->
<script src="' . ADMIN_URL . '/vendor/select2/js/select2.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Custom scripts for this page -->
<script src="js/emi.js"></script>
';
include '../admin_footer.php';
?>
