<?php $this->layout = 'layouts/base'; ?>
<?php $this->title = 'Wallet Dashboard - APS Dream Home'; ?>

<style>
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --success-gradient: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    --warning-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    --info-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.wallet-card {
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    overflow: hidden;
    margin-bottom: 30px;
}

.wallet-header {
    background: var(--primary-gradient);
    padding: 40px 30px;
    color: white;
    position: relative;
}

.wallet-header::before {
    content: '';
    position: absolute;
    top: 0;
    right: 0;
    width: 200px;
    height: 200px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    transform: translate(50%, -50%);
}

.balance-display {
    font-size: 3rem;
    font-weight: 700;
    margin: 10px 0;
}

.wallet-nav {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.wallet-nav a {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    padding: 10px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.wallet-nav a:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    text-align: center;
    transition: all 0.3s ease;
    height: 100%;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    font-size: 1.5rem;
}

.stat-icon.earnings {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.stat-icon.referrals {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-icon.transferred {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.stat-icon.available {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.stat-value {
    font-size: 1.8rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 0.9rem;
}

.transaction-item {
    background: white;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.transaction-item:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
}

.transaction-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    margin-right: 20px;
}

.transaction-icon.credit {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.transaction-icon.debit {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.transaction-icon.transfer {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

.transaction-details {
    flex: 1;
}

.transaction-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.transaction-date {
    font-size: 0.85rem;
    color: #888;
}

.transaction-amount {
    font-weight: 700;
    font-size: 1.2rem;
}

.transaction-amount.credit {
    color: #11998e;
}

.transaction-amount.debit {
    color: #f5576c;
}

.referral-box {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 25px;
    color: white;
    margin-bottom: 30px;
}

.referral-code {
    background: rgba(255, 255, 255, 0.2);
    padding: 15px 25px;
    border-radius: 10px;
    font-size: 1.5rem;
    font-weight: 700;
    letter-spacing: 2px;
    display: inline-block;
    margin: 15px 0;
}

.copy-btn {
    background: white;
    color: #667eea;
    border: none;
    padding: 10px 25px;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-left: 10px;
}

.copy-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
}

.section-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #333;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
}

.section-title i {
    margin-right: 10px;
    color: #667eea;
}

@media (max-width: 768px) {
    .balance-display {
        font-size: 2.5rem;
    }
    
    .wallet-nav {
        flex-wrap: wrap;
    }
    
    .stat-value {
        font-size: 1.5rem;
    }
}
</style>

<div class="container py-5">
    <!-- Wallet Header -->
    <div class="wallet-card">
        <div class="wallet-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-1">Welcome, <?php echo htmlspecialchars($user_name); ?>!</h5>
                    <p class="mb-0 opacity-75">Your Wallet Balance</p>
                    <div class="balance-display">₹<?php echo number_format($wallet['points_balance'], 2); ?></div>
                </div>
                <div class="text-end">
                    <div class="small opacity-75">Total Earned</div>
                    <div class="fs-4 fw-bold">₹<?php echo number_format($wallet['total_earned'], 2); ?></div>
                </div>
            </div>
            
            <div class="wallet-nav">
                <a href="/wallet/transactions"><i class="fas fa-history me-2"></i>Transactions</a>
                <a href="/wallet/transfer-emi"><i class="fas fa-exchange-alt me-2"></i>Transfer to EMI</a>
                <a href="/wallet/withdrawal"><i class="fas fa-wallet me-2"></i>Withdraw</a>
                <a href="/wallet/bank-accounts"><i class="fas fa-university me-2"></i>Bank Accounts</a>
                <a href="/wallet/referral-network"><i class="fas fa-users me-2"></i>Referrals</a>
                <a href="/wallet/analytics"><i class="fas fa-chart-line me-2"></i>Analytics</a>
            </div>
        </div>
    </div>

    <!-- Referral Code Box -->
    <div class="referral-box">
        <h4 class="mb-3"><i class="fas fa-gift me-2"></i>Your Referral Code</h4>
        <p class="mb-0 opacity-75">Share this code with friends and earn points when they register!</p>
        <div class="mt-3">
            <span class="referral-code"><?php echo htmlspecialchars($user_referral_code); ?></span>
            <button class="copy-btn" onclick="copyReferralCode()"><i class="fas fa-copy me-2"></i>Copy</button>
        </div>
        <div class="mt-3">
            <small class="opacity-75">
                <i class="fas fa-info-circle me-1"></i>
                Earn 100 points for customer, 200 for associate, 250 for agent referrals
            </small>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon earnings">
                    <i class="fas fa-rupee-sign"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($wallet['referral_earnings'], 2); ?></div>
                <div class="stat-label">Referral Earnings</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon referrals">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="stat-value"><?php echo $referralStats['total_referrals'] ?? 0; ?></div>
                <div class="stat-label">Total Referrals</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon transferred">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($wallet['total_transferred_to_emi'], 2); ?></div>
                <div class="stat-label">Transferred to EMI</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon available">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($wallet['points_balance'], 2); ?></div>
                <div class="stat-label">Available Balance</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Transactions -->
        <div class="col-lg-8 mb-4">
            <h4 class="section-title">
                <i class="fas fa-history"></i>Recent Transactions
            </h4>
            
            <?php if (!empty($recentTransactions)): ?>
                <?php foreach ($recentTransactions as $transaction): ?>
                    <div class="transaction-item">
                        <div class="d-flex align-items-center">
                            <div class="transaction-icon <?php echo $transaction['transaction_type']; ?>">
                                <?php if ($transaction['transaction_category'] == 'referral'): ?>
                                    <i class="fas fa-user-plus"></i>
                                <?php elseif ($transaction['transaction_category'] == 'commission'): ?>
                                    <i class="fas fa-percentage"></i>
                                <?php elseif ($transaction['transaction_category'] == 'emi_transfer'): ?>
                                    <i class="fas fa-exchange-alt"></i>
                                <?php elseif ($transaction['transaction_category'] == 'withdrawal'): ?>
                                    <i class="fas fa-arrow-down"></i>
                                <?php else: ?>
                                    <i class="fas fa-coins"></i>
                                <?php endif; ?>
                            </div>
                            <div class="transaction-details">
                                <div class="transaction-title">
                                    <?php echo htmlspecialchars($transaction['description']); ?>
                                </div>
                                <div class="transaction-date">
                                    <?php echo date('M d, Y - h:i A', strtotime($transaction['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="transaction-amount <?php echo $transaction['transaction_type']; ?>">
                            <?php if ($transaction['transaction_type'] == 'credit'): ?>
                                +₹<?php echo number_format($transaction['amount'], 2); ?>
                            <?php else: ?>
                                -₹<?php echo number_format($transaction['amount'], 2); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="text-center mt-3">
                    <a href="/wallet/transactions" class="btn btn-outline-primary">View All Transactions</a>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No transactions yet</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Quick Actions -->
        <div class="col-lg-4 mb-4">
            <h4 class="section-title">
                <i class="fas fa-bolt"></i>Quick Actions
            </h4>
            
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body">
                    <a href="/wallet/withdrawal" class="d-flex align-items-center text-decoration-none text-dark mb-3">
                        <div class="bg-success text-white rounded-circle p-3 me-3">
                            <i class="fas fa-wallet"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Request Withdrawal</div>
                            <small class="text-muted">Transfer to bank account</small>
                        </div>
                    </a>
                    
                    <a href="/wallet/transfer-emi" class="d-flex align-items-center text-decoration-none text-dark mb-3">
                        <div class="bg-primary text-white rounded-circle p-3 me-3">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Transfer to EMI</div>
                            <small class="text-muted">Use wallet for EMI payments</small>
                        </div>
                    </a>
                    
                    <a href="/wallet/referral-network" class="d-flex align-items-center text-decoration-none text-dark mb-3">
                        <div class="bg-info text-white rounded-circle p-3 me-3">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div class="fw-bold">View Referrals</div>
                            <small class="text-muted">See your referral network</small>
                        </div>
                    </a>
                    
                    <a href="/wallet/bank-accounts" class="d-flex align-items-center text-decoration-none text-dark">
                        <div class="bg-warning text-white rounded-circle p-3 me-3">
                            <i class="fas fa-university"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Manage Banks</div>
                            <small class="text-muted">Add or remove bank accounts</small>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Wallet Info -->
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3"><i class="fas fa-info-circle me-2 text-primary"></i>Wallet Info</h5>
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <small class="text-muted">Conversion Rate:</small>
                            <div class="fw-bold">1 Point = ₹<?php echo $config['point_to_rupee_conversion'] ?? 1; ?></div>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Min Withdrawal:</small>
                            <div class="fw-bold">₹<?php echo $config['minimum_withdrawal'] ?? 500; ?></div>
                        </li>
                        <li class="mb-2">
                            <small class="text-muted">Processing Time:</small>
                            <div class="fw-bold"><?php echo $config['withdrawal_processing_days'] ?? 3; ?> days</div>
                        </li>
                        <li>
                            <small class="text-muted">Tax on Withdrawal:</small>
                            <div class="fw-bold"><?php echo $config['tax_on_withdrawal'] ?? 10; ?>%</div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyReferralCode() {
    const referralCode = '<?php echo $user_referral_code; ?>';
    navigator.clipboard.writeText(referralCode).then(() => {
        alert('Referral code copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}
</script>
