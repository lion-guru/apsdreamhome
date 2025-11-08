<?php
require_once '../../includes/config.php';
require_once '../../includes/db_connection.php';
require_once '../../includes/auth_check.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: emi.php');
    exit();
}

$emiPlanId = intval($_GET['id']);
$conn = getDbConnection();

// Get EMI plan details
$query = "SELECT ep.*, c.name as customer_name, c.phone as customer_phone, 
                 p.title as property_title, p.address as property_address
          FROM emi_plans ep
          LEFT JOIN customers c ON ep.customer_id = c.id
          LEFT JOIN properties p ON ep.property_id = p.id
          WHERE ep.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $emiPlanId);
$stmt->execute();
$emiPlan = $stmt->get_result()->fetch_assoc();

if (!$emiPlan) {
    header('Location: emi.php');
    exit();
}

// Get installments
$query = "SELECT * FROM emi_installments WHERE emi_plan_id = ? ORDER BY installment_number";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $emiPlanId);
$stmt->execute();
$installments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$page_title = "EMI Plan Details";
require_once '../includes/admin_header.php';
?>

<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">EMI Plan Details</h1>
        <div>
            <?php if ($emiPlan['status'] === 'active'): ?>
            <button onclick="foreclosePlan(<?php echo $emiPlan['id']; ?>)" class="btn btn-warning mr-2">
                <i class="fas fa-hand-holding-usd"></i> Foreclose Plan
            </button>
            <?php endif; ?>
            <a href="emi.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to EMI Plans
            </a>
        </div>
    </div>

    <?php if ($emiPlan['status'] === 'completed' && $emiPlan['foreclosure_date']): ?>
    <!-- Foreclosure Details Card -->
    <div class="card shadow mb-4 border-left-warning">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-warning">Foreclosure Details</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p><strong>Foreclosure Date:</strong><br>
                    <?php echo date('d M Y', strtotime($emiPlan['foreclosure_date'])); ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Foreclosure Amount:</strong><br>
                    ₹<?php echo number_format($emiPlan['foreclosure_amount'], 2); ?></p>
                </div>
                <div class="col-md-4">
                    <p><strong>Transaction ID:</strong><br>
                    <?php 
                    $query = "SELECT transaction_id FROM payments WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("i", $emiPlan['foreclosure_payment_id']);
                    $stmt->execute();
                    $transactionId = $stmt->get_result()->fetch_assoc()['transaction_id'];
                    echo $transactionId;
                    ?></p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- EMI Plan Details Card -->
    <div class="row">
        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Customer Details
                            </div>
                            <hr>
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($emiPlan['customer_name']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($emiPlan['customer_phone']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                Property Details
                            </div>
                            <hr>
                            <p><strong>Title:</strong> <?php echo htmlspecialchars($emiPlan['property_title']); ?></p>
                            <p><strong>Address:</strong> <?php echo htmlspecialchars($emiPlan['property_address']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                EMI Details
                            </div>
                            <hr>
                            <p><strong>Total Amount:</strong> ₹<?php echo number_format($emiPlan['total_amount'], 2); ?></p>
                            <p><strong>EMI Amount:</strong> ₹<?php echo number_format($emiPlan['emi_amount'], 2); ?></p>
                            <p><strong>Interest Rate:</strong> <?php echo $emiPlan['interest_rate']; ?>%</p>
                            <p><strong>Tenure:</strong> <?php echo $emiPlan['tenure_months']; ?> months</p>
                            <p><strong>Status:</strong> 
                                <?php
                                $statusClass = '';
                                switch($emiPlan['status']) {
                                    case 'active':
                                        $statusClass = 'success';
                                        break;
                                    case 'completed':
                                        $statusClass = 'primary';
                                        break;
                                    case 'defaulted':
                                        $statusClass = 'danger';
                                        break;
                                    case 'cancelled':
                                        $statusClass = 'secondary';
                                        break;
                                }
                                echo '<span class="badge badge-'.$statusClass.'">'.ucfirst($emiPlan['status']).'</span>';
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Installments Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Installments</h6>
            <?php if ($emiPlan['status'] === 'active'): ?>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#recordPaymentModal">
                <i class="fas fa-plus"></i> Record Payment
            </button>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Due Date</th>
                            <th>Amount</th>
                            <th>Principal</th>
                            <th>Interest</th>
                            <th>Status</th>
                            <th>Payment Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($installments as $installment): ?>
                        <tr>
                            <td><?php echo $installment['installment_number']; ?></td>
                            <td><?php echo date('d M Y', strtotime($installment['due_date'])); ?></td>
                            <td>₹<?php echo number_format($installment['amount'], 2); ?></td>
                            <td>₹<?php echo number_format($installment['principal_component'], 2); ?></td>
                            <td>₹<?php echo number_format($installment['interest_component'], 2); ?></td>
                            <td>
                                <?php
                                $statusClass = '';
                                switch($installment['payment_status']) {
                                    case 'paid':
                                        $statusClass = 'success';
                                        break;
                                    case 'pending':
                                        $statusClass = 'warning';
                                        break;
                                    case 'overdue':
                                        $statusClass = 'danger';
                                        break;
                                }
                                echo '<span class="badge badge-'.$statusClass.'">'.ucfirst($installment['payment_status']).'</span>';
                                ?>
                            </td>
                            <td><?php echo $installment['payment_date'] ? date('d M Y', strtotime($installment['payment_date'])) : '-'; ?></td>
                            <td>
                                <?php if ($emiPlan['status'] === 'active' && $installment['payment_status'] !== 'paid'): ?>
                                <button class="btn btn-primary btn-sm" onclick="recordPayment(<?php echo $installment['id']; ?>)">
                                    <i class="fas fa-money-bill"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($installment['payment_status'] === 'paid'): ?>
                                <button class="btn btn-info btn-sm" onclick="viewReceipt(<?php echo $installment['id']; ?>)">
                                    <i class="fas fa-receipt"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1" role="dialog" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="recordPaymentModalLabel">Record EMI Payment</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="recordPaymentForm">
                <div class="modal-body">
                    <input type="hidden" id="installmentId" name="installment_id">
                    <div class="form-group">
                        <label for="paymentDate">Payment Date</label>
                        <input type="date" class="form-control" id="paymentDate" name="payment_date" required>
                    </div>
                    <div class="form-group">
                        <label for="paymentMethod">Payment Method</label>
                        <select class="form-control" id="paymentMethod" name="payment_method" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="transactionDetails">Transaction Details</label>
                        <textarea class="form-control" id="transactionDetails" name="transaction_details" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Record Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function recordPayment(installmentId) {
    $('#installmentId').val(installmentId);
    $('#recordPaymentModal').modal('show');
}

function viewReceipt(installmentId) {
    window.open('generate_receipt.php?id=' + installmentId, '_blank');
}

$(document).ready(function() {
    $('#recordPaymentForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: 'ajax/record_emi_payment.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Failed to record payment');
                }
            },
            error: function() {
                alert('Failed to record payment');
            }
        });
    });
});
</script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php require_once '../includes/admin_footer.php'; ?>
