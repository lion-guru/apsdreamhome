<?php $this->layout = 'layouts/base'; ?>
<?php $this->title = 'Wallet Analytics - APS Dream Home'; ?>

<style>
.analytics-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    text-align: center;
    height: 100%;
    transition: all 0.3s ease;
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

.stat-icon.balance {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.stat-icon.earned {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    color: white;
}

.stat-icon.used {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.stat-icon.transferred {
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

.chart-bar {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.chart-bar:hover {
    transform: scaleY(1.05);
    transform-origin: bottom;
}

.category-bar {
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 15px;
    overflow: hidden;
}

.category-progress {
    height: 30px;
    border-radius: 10px;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    padding: 0 15px;
    color: white;
    font-weight: 600;
}

.category-progress.referral {
    background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
}

.category-progress.commission {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.category-progress.bonus {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.category-progress.other {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

@media (max-width: 768px) {
    .stat-value {
        font-size: 1.5rem;
    }
}
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-chart-line me-2 text-primary"></i>Wallet Analytics</h2>
        <a href="/wallet" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to Wallet</a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-5">
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon balance">
                    <i class="fas fa-wallet"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($wallet['points_balance'], 2); ?></div>
                <div class="stat-label">Current Balance</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon earned">
                    <i class="fas fa-arrow-up"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($wallet['total_earned'], 2); ?></div>
                <div class="stat-label">Total Earned</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon used">
                    <i class="fas fa-arrow-down"></i>
                </div>
                <div class="stat-value">₹<?php echo number_format($wallet['total_used'], 2); ?></div>
                <div class="stat-label">Total Used</div>
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
    </div>

    <div class="row">
        <!-- Monthly Earnings Chart -->
        <div class="col-lg-8 mb-4">
            <div class="analytics-card">
                <h4 class="mb-4"><i class="fas fa-chart-bar me-2 text-primary"></i>Monthly Earnings (Last 6 Months)</h4>
                
                <?php if (!empty($monthlyEarnings)): ?>
                    <div class="d-flex align-items-end justify-content-between" style="height: 250px;">
                        <?php 
                        $maxCredit = max(array_column($monthlyEarnings, 'credits'));
                        foreach ($monthlyEarnings as $earning): 
                            $height = $maxCredit > 0 ? ($earning['credits'] / $maxCredit) * 100 : 0;
                            $monthName = date('M', strtotime($earning['month'] . '-01'));
                        ?>
                            <div class="text-center" style="flex: 1;">
                                <div class="fw-bold mb-2">₹<?php echo number_format($earning['credits'], 0); ?></div>
                                <div class="chart-bar" style="height: <?php echo $height; ?>%; min-height: 20px;"></div>
                                <div class="mt-2 small text-muted"><?php echo $monthName; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="mt-4">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Credits:</span>
                            <span class="fw-bold text-success">₹<?php echo number_format(array_sum(array_column($monthlyEarnings, 'credits')), 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Total Debits:</span>
                            <span class="fw-bold text-danger">₹<?php echo number_format(array_sum(array_column($monthlyEarnings, 'debits')), 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Net Earnings:</span>
                            <span class="fw-bold text-primary">₹<?php echo number_format(array_sum(array_column($monthlyEarnings, 'credits')) - array_sum(array_column($monthlyEarnings, 'debits')), 2); ?></span>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Earnings by Category -->
        <div class="col-lg-4 mb-4">
            <div class="analytics-card">
                <h4 class="mb-4"><i class="fas fa-pie-chart me-2 text-primary"></i>Earnings by Category</h4>
                
                <?php if (!empty($earningsByCategory)): ?>
                    <?php 
                    $totalEarnings = array_sum(array_column($earningsByCategory, 'total'));
                    foreach ($earningsByCategory as $category): 
                        $percentage = $totalEarnings > 0 ? ($category['total'] / $totalEarnings) * 100 : 0;
                        $categoryClass = $category['transaction_category'];
                    ?>
                        <div class="category-bar">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="fw-bold"><?php echo ucfirst($category['transaction_category']); ?></span>
                                <span>₹<?php echo number_format($category['total'], 2); ?></span>
                            </div>
                            <div class="category-progress <?php echo $categoryClass; ?>" style="width: <?php echo $percentage; ?>%;">
                                <?php echo number_format($percentage, 1); ?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total Earnings:</span>
                        <span class="text-success">₹<?php echo number_format($totalEarnings, 2); ?></span>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-pie-chart fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No data available</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Earnings Breakdown -->
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="analytics-card">
                <h4 class="mb-4"><i class="fas fa-coins me-2 text-primary"></i>Referral Earnings</h4>
                <div class="text-center">
                    <div class="fs-2 fw-bold text-success">₹<?php echo number_format($wallet['referral_earnings'], 2); ?></div>
                    <p class="text-muted mb-0">From <?php echo number_format($wallet['referral_earnings'] / 100); ?> referrals</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="analytics-card">
                <h4 class="mb-4"><i class="fas fa-percentage me-2 text-primary"></i>Commission Earnings</h4>
                <div class="text-center">
                    <div class="fs-2 fw-bold text-primary">₹<?php echo number_format($wallet['commission_earnings'], 2); ?></div>
                    <p class="text-muted mb-0">From sales and services</p>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 mb-4">
            <div class="analytics-card">
                <h4 class="mb-4"><i class="fas fa-gift me-2 text-primary"></i>Bonus Earnings</h4>
                <div class="text-center">
                    <div class="fs-2 fw-bold text-warning">₹<?php echo number_format($wallet['bonus_earnings'], 2); ?></div>
                    <p class="text-muted mb-0">From promotions and rewards</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Wallet Tips -->
    <div class="analytics-card">
        <h4 class="mb-4"><i class="fas fa-lightbulb me-2 text-primary"></i>Wallet Tips</h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="d-flex">
                    <div class="text-success me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold">Use Wallet for EMI</h6>
                        <p class="text-muted mb-0">Transfer wallet points to EMI and save on taxes</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="d-flex">
                    <div class="text-success me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold">Refer More Users</h6>
                        <p class="text-muted mb-0">Earn up to ₹250 per referral depending on user type</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="d-flex">
                    <div class="text-success me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold">Track Earnings</h6>
                        <p class="text-muted mb-0">Monitor your wallet analytics regularly</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="d-flex">
                    <div class="text-success me-3">
                        <i class="fas fa-check-circle fa-2x"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold">Withdraw Wisely</h6>
                        <p class="text-muted mb-0">Minimum withdrawal is ₹500 with 10% tax</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
