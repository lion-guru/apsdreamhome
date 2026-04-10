<?php $this->layout = 'layouts/base'; ?>
<?php $this->title = 'Bank Accounts - APS Dream Home'; ?>

<style>
.bank-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    border-left: 4px solid #667eea;
    position: relative;
}

.bank-card.primary {
    border-left-color: #28a745;
}

.bank-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    margin-right: 20px;
}

.primary-badge {
    background: #28a745;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.verified-badge {
    background: #17a2b8;
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.account-number {
    font-family: 'Courier New', monospace;
    font-size: 1.2rem;
    font-weight: 700;
    letter-spacing: 2px;
}

.btn-add-bank {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    padding: 15px 30px;
    border-radius: 25px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-add-bank:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
}

.btn-set-primary {
    background: #28a745;
    border: none;
    padding: 8px 20px;
    border-radius: 15px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-set-primary:hover {
    background: #218838;
}

.btn-delete {
    background: #dc3545;
    border: none;
    padding: 8px 20px;
    border-radius: 15px;
    color: white;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-delete:hover {
    background: #c82333;
}

.form-control:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
}

.bank-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.bank-modal.show {
    display: flex;
}

.bank-modal-content {
    background: white;
    border-radius: 20px;
    padding: 30px;
    max-width: 500px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
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
        <h2><i class="fas fa-university me-2 text-primary"></i>Bank Accounts</h2>
        <div>
            <a href="/wallet" class="btn btn-outline-primary me-2"><i class="fas fa-arrow-left me-2"></i>Back to Wallet</a>
            <button class="btn btn-add-bank" onclick="showAddBankModal()">
                <i class="fas fa-plus me-2"></i>Add Bank Account
            </button>
        </div>
    </div>

    <!-- Info Box -->
    <div class="info-box">
        <i class="fas fa-shield-alt"></i>
        <span>Your bank account information is securely stored. We use bank-grade encryption to protect your data.</span>
    </div>

    <!-- Bank Accounts List -->
    <?php if (!empty($bankAccounts)): ?>
        <?php foreach ($bankAccounts as $account): ?>
            <div class="bank-card <?php echo $account['is_primary'] ? 'primary' : ''; ?>">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="d-flex align-items-center">
                        <div class="bank-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <div>
                            <h4 class="mb-2"><?php echo htmlspecialchars($account['bank_name']); ?></h4>
                            <p class="mb-1">
                                <strong>Account Holder:</strong> <?php echo htmlspecialchars($account['account_holder']); ?>
                            </p>
                            <p class="mb-1">
                                <strong>Account Number:</strong> 
                                <span class="account-number">XXXXXX<?php echo substr($account['account_number'], -4); ?></span>
                            </p>
                            <p class="mb-1">
                                <strong>IFSC Code:</strong> <?php echo htmlspecialchars($account['ifsc_code']); ?>
                            </p>
                            <?php if ($account['branch_name']): ?>
                                <p class="mb-1">
                                    <strong>Branch:</strong> <?php echo htmlspecialchars($account['branch_name']); ?>
                                </p>
                            <?php endif; ?>
                            <p class="mb-0">
                                <strong>Account Type:</strong> <?php echo ucfirst($account['account_type']); ?>
                            </p>
                            <div class="mt-2">
                                <?php if ($account['is_primary']): ?>
                                    <span class="primary-badge"><i class="fas fa-star me-1"></i>Primary</span>
                                <?php endif; ?>
                                <?php if ($account['is_verified']): ?>
                                    <span class="verified-badge"><i class="fas fa-check me-1"></i>Verified</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Not Verified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <?php if (!$account['is_primary']): ?>
                            <button class="btn btn-set-primary mb-2" onclick="setPrimary(<?php echo $account['id']; ?>)">
                                <i class="fas fa-star me-1"></i>Set Primary
                            </button>
                        <?php endif; ?>
                        <button class="btn btn-delete" onclick="deleteBank(<?php echo $account['id']; ?>)">
                            <i class="fas fa-trash me-1"></i>Delete
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-university fa-4x text-muted mb-3"></i>
                <h4>No Bank Accounts Added</h4>
                <p class="text-muted mb-4">Add a bank account to withdraw your wallet earnings.</p>
                <button class="btn btn-add-bank" onclick="showAddBankModal()">
                    <i class="fas fa-plus me-2"></i>Add Bank Account
                </button>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Add Bank Account Modal -->
<div class="bank-modal" id="addBankModal">
    <div class="bank-modal-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0"><i class="fas fa-plus-circle me-2 text-primary"></i>Add Bank Account</h4>
            <button type="button" class="btn-close" onclick="hideAddBankModal()"></button>
        </div>
        
        <form id="addBankForm">
            <div class="mb-3">
                <label class="form-label fw-bold">Bank Name *</label>
                <input type="text" class="form-control" id="bankName" name="bank_name" required placeholder="e.g., State Bank of India">
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Account Number *</label>
                <input type="text" class="form-control" id="accountNumber" name="account_number" required placeholder="Enter your account number" pattern="[0-9]{9,18}">
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Confirm Account Number *</label>
                <input type="text" class="form-control" id="confirmAccountNumber" required placeholder="Confirm account number" pattern="[0-9]{9,18}">
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Account Holder Name *</label>
                <input type="text" class="form-control" id="accountHolder" name="account_holder" required placeholder="Name as per bank records">
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">IFSC Code *</label>
                <input type="text" class="form-control" id="ifscCode" name="ifsc_code" required placeholder="e.g., SBIN0001234" pattern="[A-Z]{4}[0-9]{7}" style="text-transform: uppercase;">
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Branch Name</label>
                <input type="text" class="form-control" id="branchName" name="branch_name" placeholder="e.g., Main Branch, New Delhi">
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Account Type</label>
                <select class="form-select" id="accountType" name="account_type">
                    <option value="savings">Savings</option>
                    <option value="current">Current</option>
                    <option value="salary">Salary</option>
                </select>
            </div>
            
            <div class="mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="isPrimary" name="is_primary">
                    <label class="form-check-label" for="isPrimary">
                        Set as primary bank account
                    </label>
                </div>
            </div>
            
            <button type="button" class="btn btn-add-bank w-100" onclick="addBankAccount()">
                <i class="fas fa-plus me-2"></i>Add Bank Account
            </button>
        </form>
    </div>
</div>

<script>
function showAddBankModal() {
    document.getElementById('addBankModal').classList.add('show');
}

function hideAddBankModal() {
    document.getElementById('addBankModal').classList.remove('show');
    document.getElementById('addBankForm').reset();
}

function addBankAccount() {
    const bankName = document.getElementById('bankName').value;
    const accountNumber = document.getElementById('accountNumber').value;
    const confirmAccountNumber = document.getElementById('confirmAccountNumber').value;
    const accountHolder = document.getElementById('accountHolder').value;
    const ifscCode = document.getElementById('ifscCode').value;
    const branchName = document.getElementById('branchName').value;
    const accountType = document.getElementById('accountType').value;
    const isPrimary = document.getElementById('isPrimary').checked ? '1' : '0';
    
    // Validation
    if (!bankName || !accountNumber || !confirmAccountNumber || !accountHolder || !ifscCode) {
        alert('Please fill all required fields');
        return;
    }
    
    if (accountNumber !== confirmAccountNumber) {
        alert('Account numbers do not match');
        return;
    }
    
    if (accountNumber.length < 9 || accountNumber.length > 18) {
        alert('Account number must be 9-18 digits');
        return;
    }
    
    const formData = new FormData();
    formData.append('bank_name', bankName);
    formData.append('account_number', accountNumber);
    formData.append('account_holder', accountHolder);
    formData.append('ifsc_code', ifscCode);
    formData.append('branch_name', branchName);
    formData.append('account_type', accountType);
    formData.append('is_primary', isPrimary);
    
    fetch('/wallet/bank-accounts/add', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Bank account added successfully!');
            window.location.href = '/wallet/bank-accounts';
        } else {
            alert('Failed to add bank account: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error);
    });
}

function setPrimary(bankAccountId) {
    if (confirm('Are you sure you want to set this bank account as primary?')) {
        // This would need a separate endpoint, for now just reload
        alert('Feature coming soon: Set primary account');
    }
}

function deleteBank(bankAccountId) {
    if (confirm('Are you sure you want to delete this bank account? This action cannot be undone.')) {
        // This would need a separate endpoint, for now just show message
        alert('Feature coming soon: Delete bank account');
    }
}

// Close modal when clicking outside
document.getElementById('addBankModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideAddBankModal();
    }
});

// Format IFSC code to uppercase
document.getElementById('ifscCode').addEventListener('input', function() {
    this.value = this.value.toUpperCase();
});
</script>
