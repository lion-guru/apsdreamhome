<?php $this->layout = 'layouts/base'; ?>
<?php $this->title = 'Transfer to EMI - APS Dream Home'; ?>

<style>
.emi-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border-left: 4px solid #667eea;
}

.emi-card.overdue {
    border-left-color: #f5576c;
}

.emi-card.due-soon {
    border-left-color: #f39c12;
}

.wallet-balance {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.btn-transfer {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
    color: #667eea;
    margin-right: 10px;
}
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-exchange-alt me-2 text-primary"></i>Transfer Wallet to EMI</h2>
        <a href="/wallet" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to Wallet</a>
    </div>

    <!-- Wallet Balance -->
    <div class="wallet-balance">
        <h5 class="mb-1">Available Balance</h5>
        <div class="amount">₹<?php echo number_format($wallet['points_balance'], 2); ?></div>
        <p class="mb-0 opacity-75">Use your wallet points to pay EMIs</p>
    </div>

    <div class="row">
        <!-- EMI Selection -->
        <div class="col-lg-8 mb-4">
            <h4 class="mb-4"><i class="fas fa-calendar-alt me-2"></i>Select EMI to Pay</h4>
            
            <?php if (!empty($emis)): ?>
                <?php foreach ($emis as $emi): ?>
                    <div class="emi-card <?php 
                        if (strtotime($emi['due_date']) < strtotime(date('Y-m-d'))) echo 'overdue';
                        elseif (strtotime($emi['due_date']) <= strtotime('+7 days')) echo 'due-soon';
                    ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h5 class="mb-2">EMI #<?php echo $emi['id']; ?></h5>
                                <p class="mb-1"><strong>Due Amount:</strong> ₹<?php echo number_format($emi['due_amount'], 2); ?></p>
                                <p class="mb-1"><strong>Due Date:</strong> <?php echo date('M d, Y', strtotime($emi['due_date'])); ?></p>
                                <p class="mb-1"><strong>Property:</strong> <?php echo htmlspecialchars($emi['property_name'] ?? 'N/A'); ?></p>
                                <span class="emi-status <?php echo $emi['status']; ?>">
                                    <?php echo ucfirst($emi['status']); ?>
                                </span>
                            </div>
                            <button class="btn btn-primary" onclick="selectEMI(<?php echo $emi['id']; ?>, <?php echo $emi['due_amount']; ?>)">
                                Pay This EMI
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                        <h4>No Pending EMIs</h4>
                        <p class="text-muted">You don't have any pending EMIs to pay.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Transfer Form -->
        <div class="col-lg-4 mb-4">
            <h4 class="mb-4"><i class="fas fa-paper-plane me-2"></i>Transfer Details</h4>
            
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="transferForm">
                        <div class="mb-3">
                            <label class="form-label">Selected EMI</label>
                            <input type="text" class="form-control" id="selectedEmi" readonly placeholder="Select an EMI">
                            <input type="hidden" id="emiId" name="emi_id">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">EMI Amount</label>
                            <input type="text" class="form-control" id="emiAmount" readonly placeholder="₹0.00">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Transfer Amount</label>
                            <input type="number" class="form-control" id="transferAmount" name="amount" placeholder="Enter amount" min="1" step="0.01">
                            <small class="text-muted">Max: ₹<?php echo number_format($wallet['points_balance'], 2); ?></small>
                        </div>
                        
                        <div class="info-box">
                            <i class="fas fa-info-circle"></i>
                            <small>
                                Conversion Rate: 1 Point = ₹1<br>
                                Minimum transfer: ₹500
                            </small>
                        </div>
                        
                        <button type="button" class="btn btn-transfer" onclick="processTransfer()">
                            <i class="fas fa-exchange-alt me-2"></i>Transfer to EMI
                        </button>
                    </form>
                </div>
            </div>

            <!-- Transfer Info -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Transfer Info</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">Processing Time:</small>
                            <div class="fw-bold">Instant</div>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Transaction Fee:</small>
                            <div class="fw-bold">₹0</div>
                        </li>
                        <li>
                            <small class="text-muted">Tax Benefit:</small>
                            <div class="fw-bold text-success">Yes (Tax Saving)</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function selectEMI(emiId, amount) {
    document.getElementById('emiId').value = emiId;
    document.getElementById('selectedEmi').value = 'EMI #' + emiId;
    document.getElementById('emiAmount').value = '₹' + amount.toFixed(2);
    document.getElementById('transferAmount').value = amount;
    document.getElementById('transferAmount').max = amount;
}

function processTransfer() {
    const emiId = document.getElementById('emiId').value;
    const amount = document.getElementById('transferAmount').value;
    const maxAmount = <?php echo $wallet['points_balance']; ?>;
    
    if (!emiId) {
        alert('Please select an EMI first');
        return;
    }
    
    if (!amount || amount <= 0) {
        alert('Please enter a valid amount');
        return;
    }
    
    if (parseFloat(amount) > maxAmount) {
        alert('Insufficient wallet balance');
        return;
    }
    
    if (parseFloat(amount) < 500) {
        alert('Minimum transfer amount is ₹500');
        return;
    }
    
    // Confirm transfer
    if (confirm('Are you sure you want to transfer ₹' + amount + ' to EMI #' + emiId + '?')) {
        const formData = new FormData();
        formData.append('emi_id', emiId);
        formData.append('amount', amount);
        
        fetch('/wallet/transfer-emi/process', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Transfer successful!');
                window.location.href = '/wallet';
            } else {
                alert('Transfer failed: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}
</script>
