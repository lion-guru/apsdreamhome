<?php
require_once __DIR__ . '/../core/init.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: emi.php');
    exit();
}

$emiPlanId = intval($_GET['id']);

// Get EMI plan details
$query = "SELECT ep.*, c.name as customer_name, c.phone as customer_phone,
                 p.title as property_title, p.address as property_address
          FROM emi_plans ep
          LEFT JOIN customers c ON ep.customer_id = c.id
          LEFT JOIN properties p ON ep.property_id = p.id
          WHERE ep.id = ?";
$emiPlan = \App\Core\App::database()->fetchOne($query, [$emiPlanId]);

if (!$emiPlan) {
    header('Location: emi.php');
    exit();
}

// Get installments
$query = "SELECT * FROM emi_installments WHERE emi_plan_id = ? ORDER BY installment_number";
$installments = \App\Core\App::database()->fetchAll($query, [$emiPlanId]);

$page_title = "EMI Plan Details";
include '../admin_header.php';
?>

<div class="page-wrapper">
    <div class="container-fluid">
        <div class="row">
            <?php include '../admin_sidebar.php'; ?>

            <main class="main-content col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2 fw-bold"><?php echo h($mlSupport->translate('EMI Plan Details')); ?></h1>
                    <div>
                        <?php if ($emiPlan['status'] === 'active'): ?>
                        <button onclick="foreclosePlan(<?php echo $emiPlan['id']; ?>)" class="btn btn-warning rounded-pill px-4 me-2">
                            <i class="fas fa-hand-holding-usd me-1"></i> <?php echo h($mlSupport->translate('Foreclose Plan')); ?>
                        </button>
                        <?php endif; ?>
                        <a href="emi.php" class="btn btn-outline-secondary rounded-pill px-4">
                            <i class="fas fa-arrow-left me-1"></i> <?php echo h($mlSupport->translate('Back to EMI Plans')); ?>
                        </a>
                    </div>
                </div>

                <?php if ($emiPlan['status'] === 'completed' && $emiPlan['foreclosure_date']): ?>
                <!-- Foreclosure Details Card -->
                <div class="card shadow-sm border-0 mb-4 bg-warning text-white">
                    <div class="card-header bg-transparent border-0 py-3">
                        <h5 class="card-title mb-0 fw-bold"><?php echo h($mlSupport->translate('Foreclosure Details')); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="small opacity-75 d-block"><?php echo h($mlSupport->translate('Foreclosure Date')); ?></label>
                                <span class="fw-bold"><?php echo date('d M Y', strtotime($emiPlan['foreclosure_date'])); ?></span>
                            </div>
                            <div class="col-md-4">
                                <label class="small opacity-75 d-block"><?php echo h($mlSupport->translate('Foreclosure Amount')); ?></label>
                                <span class="fw-bold">₹<?php echo number_format($emiPlan['foreclosure_amount'], 2); ?></span>
                            </div>
                            <div class="col-md-4">
                                <label class="small opacity-75 d-block"><?php echo h($mlSupport->translate('Transaction ID')); ?></label>
                                <span class="fw-bold">
                                <?php
                                $query = "SELECT transaction_id FROM payments WHERE id = ?";
                                $payment_data = $db->fetchOne($query, [$emiPlan['foreclosure_payment_id']]);
                                $transactionId = $payment_data['transaction_id'] ?? '';
                                echo h($transactionId);
                                ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- EMI Plan Details Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-xl-4 col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h6 class="text-muted text-uppercase small fw-bold mb-3"><?php echo h($mlSupport->translate('Customer Details')); ?></h6>
                                <div class="mb-2">
                                    <label class="text-muted small d-block"><?php echo h($mlSupport->translate('Name')); ?></label>
                                    <span class="fw-bold"><?php echo h($emiPlan['customer_name']); ?></span>
                                </div>
                                <div>
                                    <label class="text-muted small d-block"><?php echo h($mlSupport->translate('Phone')); ?></label>
                                    <span class="fw-bold"><?php echo h($emiPlan['customer_phone']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h6 class="text-muted text-uppercase small fw-bold mb-3"><?php echo h($mlSupport->translate('Property Details')); ?></h6>
                                <div class="mb-2">
                                    <label class="text-muted small d-block"><?php echo h($mlSupport->translate('Title')); ?></label>
                                    <span class="fw-bold"><?php echo h($emiPlan['property_title']); ?></span>
                                </div>
                                <div>
                                    <label class="text-muted small d-block"><?php echo h($mlSupport->translate('Address')); ?></label>
                                    <span class="fw-bold"><?php echo h($emiPlan['property_address']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-4 col-md-6">
                        <div class="card shadow-sm border-0 h-100">
                            <div class="card-body">
                                <h6 class="text-muted text-uppercase small fw-bold mb-3"><?php echo h($mlSupport->translate('EMI Details')); ?></h6>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="text-muted small d-block"><?php echo h($mlSupport->translate('Total Amount')); ?></label>
                                        <span class="fw-bold">₹<?php echo h(number_format($emiPlan['total_amount'], 2)); ?></span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block"><?php echo h($mlSupport->translate('EMI Amount')); ?></label>
                                        <span class="fw-bold text-primary">₹<?php echo h(number_format($emiPlan['emi_amount'], 2)); ?></span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block"><?php echo h($mlSupport->translate('Interest Rate')); ?></label>
                                        <span class="fw-bold"><?php echo h($emiPlan['interest_rate']); ?>%</span>
                                    </div>
                                    <div class="col-6">
                                        <label class="text-muted small d-block"><?php echo h($mlSupport->translate('Tenure')); ?></label>
                                        <span class="fw-bold"><?php echo h($emiPlan['tenure_months']); ?> <?php echo h($mlSupport->translate('months')); ?></span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <label class="text-muted small d-block"><?php echo h($mlSupport->translate('Status')); ?></label>
                                        <?php
                                        $statusClass = '';
                                        switch($emiPlan['status']) {
                                            case 'active': $statusClass = 'bg-success'; break;
                                            case 'completed': $statusClass = 'bg-primary'; break;
                                            case 'defaulted': $statusClass = 'bg-danger'; break;
                                            case 'cancelled': $statusClass = 'bg-secondary'; break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $statusClass; ?> rounded-pill px-3"><?php echo h(ucfirst($mlSupport->translate($emiPlan['status']))); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Installments Table -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-0">
                        <h5 class="card-title mb-0 fw-bold text-primary"><?php echo h($mlSupport->translate('Installments')); ?></h5>
                        <?php if ($emiPlan['status'] === 'active'): ?>
                        <button class="btn btn-primary btn-sm rounded-pill px-3" data-toggle="modal" data-target="#recordPaymentModal">
                            <i class="fas fa-plus me-1"></i> <?php echo h($mlSupport->translate('Record Payment')); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-0 px-4"><?php echo h($mlSupport->translate('No.')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Due Date')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Amount')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Principal')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Interest')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Status')); ?></th>
                                        <th class="border-0"><?php echo h($mlSupport->translate('Payment Date')); ?></th>
                                        <th class="border-0 text-end px-4"><?php echo h($mlSupport->translate('Actions')); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($installments as $installment): ?>
                                    <tr>
                                        <td class="px-4"><?php echo h($installment['installment_number']); ?></td>
                                        <td><?php echo h(date('d M Y', strtotime($installment['due_date']))); ?></td>
                                        <td class="fw-bold text-primary">₹<?php echo h(number_format($installment['amount'], 2)); ?></td>
                                        <td>₹<?php echo h(number_format($installment['principal_component'], 2)); ?></td>
                                        <td>₹<?php echo h(number_format($installment['interest_component'], 2)); ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            switch($installment['payment_status']) {
                                                case 'paid': $statusClass = 'bg-success'; break;
                                                case 'pending': $statusClass = 'bg-warning'; break;
                                                case 'overdue': $statusClass = 'bg-danger'; break;
                                            }
                                            ?>
                                            <span class="badge <?php echo $statusClass; ?> rounded-pill px-3 small"><?php echo h(ucfirst($mlSupport->translate($installment['payment_status']))); ?></span>
                                        </td>
                                        <td><?php echo $installment['payment_date'] ? h(date('d M Y', strtotime($installment['payment_date']))) : '-'; ?></td>
                                        <td class="text-end px-4">
                                            <div class="btn-group">
                                                <?php if ($emiPlan['status'] === 'active' && $installment['payment_status'] !== 'paid'): ?>
                                                <button class="btn btn-outline-primary btn-sm rounded-circle p-2 me-1" onclick="recordPayment(<?php echo $installment['id']; ?>)" title="<?php echo h($mlSupport->translate('Record Payment')); ?>">
                                                    <i class="fas fa-money-bill"></i>
                                                </button>
                                                <?php endif; ?>
                                                <?php if ($installment['payment_status'] === 'paid'): ?>
                                                <button class="btn btn-outline-info btn-sm rounded-circle p-2" onclick="viewReceipt(<?php echo $installment['id']; ?>)" title="<?php echo h($mlSupport->translate('View Receipt')); ?>">
                                                    <i class="fas fa-receipt"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" role="dialog" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold" id="recordPaymentModalLabel"><?php echo h($mlSupport->translate('Record EMI Payment')); ?></h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="recordPaymentForm">
                <div class="modal-body p-4">
                    <?php echo getCsrfField(); ?>
                    <input type="hidden" id="installmentId" name="installment_id">
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="paymentDate"><?php echo h($mlSupport->translate('Payment Date')); ?></label>
                        <input type="date" class="form-control shadow-sm" id="paymentDate" name="payment_date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label fw-bold" for="paymentMethod"><?php echo h($mlSupport->translate('Payment Method')); ?></label>
                        <select class="form-control shadow-sm" id="paymentMethod" name="payment_method" required>
                            <option value="cash"><?php echo h($mlSupport->translate('Cash')); ?></option>
                            <option value="bank_transfer"><?php echo h($mlSupport->translate('Bank Transfer')); ?></option>
                            <option value="cheque"><?php echo h($mlSupport->translate('Cheque')); ?></option>
                            <option value="upi"><?php echo h($mlSupport->translate('UPI')); ?></option>
                        </select>
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label fw-bold" for="transactionDetails"><?php echo h($mlSupport->translate('Transaction Details')); ?></label>
                        <textarea class="form-control shadow-sm" id="transactionDetails" name="transaction_details" rows="3" placeholder="<?php echo h($mlSupport->translate('Enter transaction reference, cheque number, etc.')); ?>"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary px-4" data-dismiss="modal"><?php echo h($mlSupport->translate('Close')); ?></button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold"><?php echo h($mlSupport->translate('Record Payment')); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$page_specific_js = '
<!-- Custom scripts for this page -->
<script src="js/emi.js"></script>
';
include '../admin_footer.php';
?>
