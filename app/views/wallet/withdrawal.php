<?php $this->layout = 'layouts/base'; ?>
<?php $this->title = 'Withdrawal Request - APS Dream Home'; ?>

<style>
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

.withdrawal-info {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
}

.withdrawal-info i {
    color: #667eea;
    margin-right: 10px;
}

.bank-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border: 2px solid #e9ecef;
    cursor: pointer;
    transition: all 0.3s ease;
}

.bank-card:hover,
.bank-card.selected {
    border-color: #667eea;
    transform: translateY(-2px);
}

.bank-card.selected {
    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
}

.bank-card .bank-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 20px;
}

.bank-card .primary-badge {
    background: #28a745;
    color: white;
    padding: 3px 10px;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 600;
}

.withdrawal-history {
    background: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
}

.withdrawal-status {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.withdrawal-status.pending {
    background: #fff3cd;
    color: #856404;
}

.withdrawal-status.processing {
    background: #cce5ff;
    color: #004085;
}

.withdrawal-status.completed {
    background: #d4edda;
    color: #155724;
}

.withdrawal-status.rejected {
    background: #f8d7da;
    color: #721c24;
}

.btn-withdraw {
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

.btn-withdraw:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.btn-withdraw:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-wallet me-2 text-primary"></i>Withdrawal Request</h2>
        <a href="/wallet" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to Wallet</a>
    </div>

    <!-- Wallet Balance -->
    <div class="wallet-balance">
        <h5 class="mb-1">Available Balance</h5>
        <div class="amount">₹<?php echo number_format($wallet['points_balance'], 2); ?></div>
        <p class="mb-0 opacity-75">Withdraw your earnings to bank account</p>
    </div>

    <div class="row">
        <!-- Withdrawal Form -->
        <div class="col-lg-8 mb-4">
            <h4 class="mb-4"><i class="fas fa-paper-plane me-2"></i>Request Withdrawal</h4>
            
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <form id="withdrawalForm">
                        <!-- Select Bank Account -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Select Bank Account</label>
                            <?php if (!empty($bankAccounts)): ?>
                                <?php foreach ($bankAccounts as $account): ?>
                                    <div class="bank-card <?php echo $account['is_primary'] ? 'selected' : ''; ?>" 
                                         onclick="selectBank(<?php echo $account['id']; ?>, this)">
                                        <div class="d-flex align-items-center">
                                            <div class="bank-icon">
                                                <i class="fas fa-university"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold"><?php echo htmlspecialchars($account['bank_name']); ?></div>
                                                <small class="text-muted">
                                                    A/C: XXXXXX<?php echo substr($account['account_number'], -4); ?> 
                                                    • IFSC: <?php echo htmlspecialchars($account['ifsc_code']); ?>
                                                </small>
                                            </div>
                                            <?php if ($account['is_primary']): ?>
                                                <span class="primary-badge">Primary</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-3">
                                    <p class="text-muted mb-2">No bank accounts added</p>
                                    <a href="/wallet/bank-accounts" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus me-2"></i>Add Bank Account
                                    </a>
                                </div>
                            <?php endif; ?>
                            <input type="hidden" id="bankAccountId" name="bank_account_id">
                        </div>
                        
                        <!-- Withdrawal Amount -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">Withdrawal Amount</label>
                            <div class="input-group">
                                <span class="input-group-text">₹</span>
                                <input type="number" class="form-control form-control-lg" 
                                       id="withdrawalAmount" name="amount" 
                                       placeholder="Enter amount" 
                                       min="500" 
                                       max="<?php echo $wallet['points_balance']; ?>"
                                       step="0.01"
                                       oninput="calculateTax()">
                            </div>
                            <small class="text-muted">
                                Min: ₹500 | Max: ₹<?php echo number_format($wallet['points_balance'], 2); ?>
                            </small>
                        </div>
                        
                        <!-- Tax Calculation -->
                        <div class="withdrawal-info">
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="fas fa-coins"></i>Withdrawal Amount:</span>
                                <span class="fw-bold" id="grossAmount">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="fas fa-percentage"></i>Tax (10%):</span>
                                <span class="fw-bold text-danger" id="taxAmount">₹0.00</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span><i class="fas fa-shipping-fast"></i>Processing Fee (1%):</span>
                                <span class="fw-bold text-danger" id="feeAmount">₹0.00</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold"><i class="fas fa-wallet"></i>Net Amount:</span>
                                <span class="fw-bold text-success fs-5" id="netAmount">₹0.00</span>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-withdraw" onclick="processWithdrawal()" id="withdrawBtn" disabled>
                            <i class="fas fa-paper-plane me-2"></i>Request Withdrawal
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Withdrawal History & Info -->
        <div class="col-lg-4 mb-4">
            <h4 class="mb-4"><i class="fas fa-history me-2"></i>Withdrawal History</h4>
            
            <?php if (!empty($withdrawals)): ?>
                <?php foreach ($withdrawals as $withdrawal): ?>
                    <div class="withdrawal-history">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <div class="fw-bold">₹<?php echo number_format($withdrawal['amount'], 2); ?></div>
                                <small class="text-muted">
                                    <?php echo date('M d, Y', strtotime($withdrawal['created_at'])); ?>
                                </small>
                            </div>
                            <span class="withdrawal-status <?php echo $withdrawal['status']; ?>">
                                <?php echo ucfirst($withdrawal['status']); ?>
                            </span>
                        </div>
                        <?php if ($withdrawal['status'] == 'rejected' && $withdrawal['rejection_reason']): ?>
                            <small class="text-danger">
                                <i class="fas fa-exclamation-circle me-1"></i>
                                <?php echo htmlspecialchars($withdrawal['rejection_reason']); ?>
                            </small>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-2"></i>
                        <p class="text-muted mb-0">No withdrawal history</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Withdrawal Info -->
            <div class="card border-0 shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Withdrawal Info</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">Processing Time:</small>
                            <div class="fw-bold">3-5 Business Days</div>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Tax Rate:</small>
                            <div class="fw-bold">10% (TDS)</div>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Processing Fee:</small>
                            <div class="fw-bold">1%</div>
                        </li>
                        <li>
                            <small class="text-muted">Min Withdrawal:</small>
                            <div class="fw-bold">₹500</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedBankId = null;

function selectBank(bankId, element) {
    selectedBankId = bankId;
    document.getElementById('bankAccountId').value = bankId;
    
    // Remove selected class from all cards
    document.querySelectorAll('.bank-card').forEach(card => {
        card.classList.remove('selected');
    });
    
    // Add selected class to clicked card
    element.classList.add('selected');
    
    checkFormValidity();
}

function calculateTax() {
    const amount = parseFloat(document.getElementById('withdrawalAmount').value) || 0;
    const taxRate = 0.10; // 10%
    const feeRate = 0.01; // 1%
    
    const tax = amount * taxRate;
    const fee = amount * feeRate;
    const net = amount - tax - fee;
    
    document.getElementById('grossAmount').textContent = '₹' + amount.toFixed(2);
    document.getElementById('taxAmount').textContent = '₹' + tax.toFixed(2);
    document.getElementById('feeAmount').textContent = '₹' + fee.toFixed(2);
    document.getElementById('netAmount').textContent = '₹' + net.toFixed(2);
    
    checkFormValidity();
}

function checkFormValidity() {
    const amount = parseFloat(document.getElementById('withdrawalAmount').value) || 0;
    const btn = document.getElementById('withdrawBtn');
    
    if (selectedBankId && amount >= 500) {
        btn.disabled = false;
    } else {
        btn.disabled = true;
    }
}

function processWithdrawal() {
    const bankAccountId = document.getElementById('bankAccountId').value;
    const amount = document.getElementById('withdrawalAmount').value;
    
    if (!bankAccountId) {
        alert('Please select a bank account');
        return;
    }
    
    if (!amount || amount < 500) {
        alert('Minimum withdrawal amount is ₹500');
        return;
    }
    
    const maxAmount = <?php echo $wallet['points_balance']; ?>;
    if (parseFloat(amount) > maxAmount) {
        alert('Insufficient wallet balance');
        return;
    }
    
    // Confirm withdrawal
    if (confirm('Are you sure you want to request withdrawal of ₹' + amount + '?')) {
        const formData = new FormData();
        formData.append('bank_account_id', bankAccountId);
        formData.append('amount', amount);
        
        fetch('/wallet/withdrawal/process', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Withdrawal request submitted successfully!');
                window.location.href = '/wallet/withdrawal';
            } else {
                alert('Withdrawal failed: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error: ' + error);
        });
    }
}

// Select primary bank account by default
<?php if (!empty($bankAccounts)): ?>
    <?php foreach ($bankAccounts as $account): ?>
        <?php if ($account['is_primary']): ?>
            document.addEventListener('DOMContentLoaded', function() {
                const primaryCard = document.querySelector('.bank-card.selected');
                if (primaryCard) {
                    selectBank(<?php echo $account['id']; ?>, primaryCard);
                }
            });
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
</script>
