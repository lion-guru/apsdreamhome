
<?php 
$page_title = $page_title ?? 'Transfer to EMI - APS Dream Home';
$walletBalance = (float)($wallet['points_balance'] ?? 0);
$isAssociate = (($_SESSION['role'] ?? '') === 'associate');
$walletHomeUrl = defined('BASE_URL') ? BASE_URL . ($isAssociate ? '/associate/wallet' : '/wallet') : ($isAssociate ? '/associate/wallet' : '/wallet');
?>

<style>
.emi-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border-left: 4px solid #0d9488;
}

.emi-card.overdue {
    border-left-color: #f5576c;
}

.emi-card.due-soon {
    border-left-color: #f39c12;
}

.wallet-balance {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    color: white;
    border-radius: 15px;
    padding: 30px;
    text-align: center;
    margin-bottom: 30px;
}

.wallet-balance .amount {
    font-size: 2.5rem;
    font-weight: 700;
    margin: 10px 0;
}

.form-control:focus {
    border-color: #0d9488;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-transfer {
    background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);
    border: none;
    padding: 15px 40px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    font-size: 1.1rem;
    transition: all 0.3s ease;
    width: 100%;
}

.btn-transfer:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.emi-status {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.emi-status.pending {
    background: #fff3cd;
    color: #856404;
}

.emi-status.overdue {
    background: #f8d7da;
    color: #721c24;
}

.emi-status.paid {
    background: #d4edda;
    color: #155724;
}

.info-box {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.info-box i {
    color: #0d9488;
    margin-right: 10px;
}
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-exchange-alt me-2 text-primary"></i>Transfer Wallet to EMI</h2>
        <a href="<?= $walletHomeUrl ?>" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to Wallet</a>
    </div>

    <!-- Wallet Balance -->
    <div class="wallet-balance">
        <h5 class="mb-1">Available Wallet Balance</h5>
        <div class="amount">₹<?= number_format($walletBalance, 2) ?></div>
        <p class="mb-0 opacity-75">Use your accumulated wallet points to pay upcoming or overdue plot EMIs</p>
    </div>

    <div class="row">
        <!-- EMI Selection -->
        <div class="col-lg-8 mb-4">
            <h4 class="mb-4"><i class="fas fa-calendar-alt me-2 text-primary"></i>Select EMI to Pay</h4>
            
            <?php if (!empty($emis)): ?>
                <?php foreach ($emis as $emi): 
                    $dueAmt = (float)($emi['due_amount'] ?? $emi['amount']);
                    $isOverdue = (strtotime($emi['due_date']) < strtotime(date('Y-m-d')));
                    $isDueSoon = (!$isOverdue && strtotime($emi['due_date']) <= strtotime('+7 days'));
                    $cardClass = $isOverdue ? 'overdue' : ($isDueSoon ? 'due-soon' : '');
                ?>
                    <div class="emi-card <?= $cardClass ?>">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <h5 class="mb-2 fw-bold">EMI #<?= (int)($emi['emi_number'] ?: $emi['id']) ?></h5>
                                <p class="mb-1"><strong>Due Amount:</strong> <span class="text-primary fw-bold">₹<?= number_format($dueAmt, 2) ?></span>
                                    <?php if (!empty($emi['late_fee']) && (float)$emi['late_fee'] > 0): ?>
                                        <small class="badge bg-danger ms-1">+ ₹<?= number_format((float)$emi['late_fee'], 2) ?> Late Fee</small>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1"><strong>Due Date:</strong> <?= date('M d, Y', strtotime($emi['due_date'])) ?>
                                    <?php if ($isOverdue): ?>
                                        <span class="badge bg-danger ms-1">Overdue</span>
                                    <?php elseif ($isDueSoon): ?>
                                        <span class="badge bg-warning text-dark ms-1">Due Soon</span>
                                    <?php endif; ?>
                                </p>
                                <p class="mb-1"><strong>Property / Plot:</strong> <?= htmlspecialchars($emi['property_name'] ?? 'APS Township') ?></p>
                                <?php if (!empty($emi['booking_number'])): ?>
                                    <p class="mb-1"><small class="text-muted">Booking Reference: <?= htmlspecialchars($emi['booking_number']) ?></small></p>
                                <?php endif; ?>
                                <span class="emi-status <?= htmlspecialchars($emi['status']) ?>">
                                    <?= ucfirst(htmlspecialchars($emi['status'])) ?>
                                </span>
                            </div>
                            <div>
                                <button type="button" class="btn btn-primary" onclick="selectEMI(<?= (int)$emi['id'] ?>, <?= $dueAmt ?>, 'EMI #<?= (int)($emi['emi_number'] ?: $emi['id']) ?> (<?= htmlspecialchars(addslashes($emi['property_name'] ?? 'Plot')) ?>)')">
                                    <i class="fas fa-check-circle me-1"></i>Pay This EMI
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4>No Pending EMIs Found</h4>
                        <p class="text-muted">You do not have any pending or overdue plot EMIs under your account.</p>
                        <a href="<?= $walletHomeUrl ?>" class="btn btn-outline-primary mt-2">
                            <i class="fas fa-wallet me-1"></i>Go to Wallet
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Transfer Form -->
        <div class="col-lg-4 mb-4">
            <h4 class="mb-4"><i class="fas fa-paper-plane me-2 text-primary"></i>Transfer Details</h4>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <form id="transferForm">
                        <?php echo CSRFProtection::csrfField(); ?>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Selected EMI</label>
                            <input aria-label="Selected emi" type="text" class="form-control" id="selectedEmi" readonly placeholder="Click 'Pay This EMI' to select">
                            <input type="hidden" id="emiId" name="emi_id">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Total Remaining Due</label>
                            <input aria-label="Emi amount" type="text" class="form-control" id="emiAmount" readonly placeholder="₹0.00">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Wallet Points to Use (₹)</label>
                            <input aria-label="Transfer amount" type="number" class="form-control" id="transferAmount" name="amount" placeholder="Enter amount to apply" min="1" step="0.01">
                            <div class="d-flex justify-content-between mt-1">
                                <small class="text-muted">Available: ₹<?= number_format($walletBalance, 2) ?></small>
                                <a href="javascript:void(0)" onclick="useMaxPoints()" class="small text-decoration-none fw-bold text-primary">Use Max</a>
                            </div>
                        </div>
                        
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <small>
                                <strong>Rate:</strong> 1 Wallet Point = ₹1<br>
                                <strong>Deduction:</strong> Instant & direct ledger update
                            </small>
                        </div>
                        
                        <button type="button" class="btn btn-transfer" onclick="processTransfer()">
                            <i class="fas fa-exchange-alt me-2"></i>Confirm Transfer to EMI
                        </button>
                    </form>
                </div>
            </div>

            <!-- Transfer Info -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body aps-cp-card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-shield-alt me-2 text-primary"></i>Transaction Safety</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">Settlement Speed:</small>
                            <div class="fw-bold">Instant (Real-time)</div>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Transaction Fee:</small>
                            <div class="fw-bold text-success">Zero Fee (₹0)</div>
                        </li>
                        <li>
                            <small class="text-muted">Status:</small>
                            <div class="fw-bold text-primary">Directly updates Plot EMI Schedule</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let currentSelectedDue = 0;
const maxWalletBalance = <?= (float)$walletBalance ?>;
const walletRedirectUrl = '<?= $walletHomeUrl ?>';

function selectEMI(emiId, amount, label) {
    currentSelectedDue = parseFloat(amount) || 0;
    document.getElementById('emiId').value = emiId;
    document.getElementById('selectedEmi').value = label || ('EMI #' + emiId);
    document.getElementById('emiAmount').value = '₹' + currentSelectedDue.toLocaleString('en-IN', { minimumFractionDigits: 2 });
    
    // Auto-fill transfer amount with min of due amount and available wallet balance
    const suggested = Math.min(currentSelectedDue, maxWalletBalance);
    document.getElementById('transferAmount').value = suggested > 0 ? suggested : currentSelectedDue;
    document.getElementById('transferAmount').max = currentSelectedDue;
}

function useMaxPoints() {
    if (!currentSelectedDue) {
        alert('Please select an EMI first');
        return;
    }
    const maxUsable = Math.min(currentSelectedDue, maxWalletBalance);
    document.getElementById('transferAmount').value = maxUsable;
}

function processTransfer() {
    const emiId = document.getElementById('emiId').value;
    const amountVal = document.getElementById('transferAmount').value;
    const amount = parseFloat(amountVal);
    
    if (!emiId) {
        alert('Please select an EMI first');
        return;
    }
    
    if (isNaN(amount) || amount <= 0) {
        alert('Please enter a valid transfer amount');
        return;
    }
    
    if (amount > maxWalletBalance) {
        alert('Insufficient wallet balance. You have ₹' + maxWalletBalance.toFixed(2) + ' available.');
        return;
    }
    
    if (amount > currentSelectedDue + 0.05) {
        alert('Transfer amount cannot exceed remaining due: ₹' + currentSelectedDue.toFixed(2));
        return;
    }

    const minAllowed = Math.min(100, currentSelectedDue);
    if (amount < minAllowed) {
        alert('Minimum transfer amount is ₹' + minAllowed.toFixed(2));
        return;
    }
    
    if (!confirm('Transfer ₹' + amount.toLocaleString('en-IN', { minimumFractionDigits: 2 }) + ' from your wallet to this EMI? This will directly reduce your outstanding balance.')) {
        return;
    }

    const btn = document.querySelector('.btn-transfer');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Processing Transfer...';
    }

    const formData = new FormData();
    formData.append('emi_id', emiId);
    formData.append('amount', amount);
    formData.append('csrf_token', '<?= $_SESSION['csrf_token'] ?? '' ?>');
    
    fetch('<?= BASE_URL ?>/wallet/transfer-emi/process', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Transfer successful!');
            window.location.href = walletRedirectUrl;
        } else {
            alert('Transfer failed: ' + (data.message || 'Unknown error'));
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-exchange-alt me-2"></i>Confirm Transfer to EMI';
            }
        }
    })
    .catch(error => {
        alert('Error processing transfer: ' + error);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-exchange-alt me-2"></i>Confirm Transfer to EMI';
        }
    });
}
</script>
