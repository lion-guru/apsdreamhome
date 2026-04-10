<?php $this->layout = 'layouts/base'; ?>
<?php $this->title = 'Transaction History - APS Dream Home'; ?>

<style>
.transaction-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    margin-bottom: 20px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
    display: flex;
    align-items: center;
    justify-content: space-between;
    transition: all 0.3s ease;
}

.transaction-card:hover {
    transform: translateX(5px);
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
}

.transaction-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    margin-right: 25px;
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

.transaction-icon.bonus {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.transaction-details {
    flex: 1;
}

.transaction-title {
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
    font-size: 1.1rem;
}

.transaction-meta {
    font-size: 0.85rem;
    color: #888;
    margin-bottom: 3px;
}

.transaction-amount {
    font-weight: 700;
    font-size: 1.5rem;
}

.transaction-amount.credit {
    color: #11998e;
}

.transaction-amount.debit {
    color: #f5576c;
}

.transaction-status {
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}

.transaction-status.completed {
    background: #d4edda;
    color: #155724;
}

.transaction-status.pending {
    background: #fff3cd;
    color: #856404;
}

.transaction-status.failed {
    background: #f8d7da;
    color: #721c24;
}

.filter-btn {
    padding: 8px 20px;
    border-radius: 20px;
    border: 2px solid #e9ecef;
    background: white;
    color: #666;
    font-weight: 600;
    transition: all 0.3s ease;
    margin-right: 10px;
    margin-bottom: 10px;
}

.filter-btn:hover,
.filter-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: #667eea;
}

.pagination {
    display: flex;
    justify-content: center;
    gap: 5px;
    margin-top: 30px;
}

.pagination a,
.pagination span {
    padding: 10px 15px;
    border-radius: 10px;
    text-decoration: none;
    color: #666;
    font-weight: 600;
    transition: all 0.3s ease;
}

.pagination a:hover,
.pagination .active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.pagination .disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .transaction-card {
        flex-direction: column;
        text-align: center;
    }
    
    .transaction-icon {
        margin-right: 0;
        margin-bottom: 15px;
    }
    
    .transaction-amount {
        margin-top: 15px;
    }
}
</style>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="fas fa-history me-2 text-primary"></i>Transaction History</h2>
        <a href="/wallet" class="btn btn-outline-primary"><i class="fas fa-arrow-left me-2"></i>Back to Wallet</a>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <h5 class="card-title mb-3"><i class="fas fa-filter me-2"></i>Filter Transactions</h5>
            <div>
                <span class="fw-bold me-2">Type:</span>
                <a href="/wallet/transactions?type=credit" class="filter-btn <?php echo $type === 'credit' ? 'active' : ''; ?>">Credit</a>
                <a href="/wallet/transactions?type=debit" class="filter-btn <?php echo $type === 'debit' ? 'active' : ''; ?>">Debit</a>
            </div>
            <div class="mt-2">
                <span class="fw-bold me-2">Category:</span>
                <a href="/wallet/transactions?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                <a href="/wallet/transactions?filter=referral" class="filter-btn <?php echo $filter === 'referral' ? 'active' : ''; ?>">Referral</a>
                <a href="/wallet/transactions?filter=commission" class="filter-btn <?php echo $filter === 'commission' ? 'active' : ''; ?>">Commission</a>
                <a href="/wallet/transactions?filter=bonus" class="filter-btn <?php echo $filter === 'bonus' ? 'active' : ''; ?>">Bonus</a>
                <a href="/wallet/transactions?filter=emi_transfer" class="filter-btn <?php echo $filter === 'emi_transfer' ? 'active' : ''; ?>">EMI Transfer</a>
                <a href="/wallet/transactions?filter=withdrawal" class="filter-btn <?php echo $filter === 'withdrawal' ? 'active' : ''; ?>">Withdrawal</a>
            </div>
        </div>
    </div>

    <!-- Transactions List -->
    <?php if (!empty($transactions)): ?>
        <?php foreach ($transactions as $transaction): ?>
            <div class="transaction-card">
                <div class="d-flex align-items-center">
                    <div class="transaction-icon <?php echo $transaction['transaction_type']; ?>">
                        <?php if ($transaction['transaction_category'] == 'referral'): ?>
                            <i class="fas fa-user-plus"></i>
                        <?php elseif ($transaction['transaction_category'] == 'commission'): ?>
                            <i class="fas fa-percentage"></i>
                        <?php elseif ($transaction['transaction_category'] == 'bonus'): ?>
                            <i class="fas fa-gift"></i>
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
                        <div class="transaction-meta">
                            <i class="fas fa-calendar me-1"></i>
                            <?php echo date('M d, Y - h:i A', strtotime($transaction['created_at'])); ?>
                        </div>
                        <div class="transaction-meta">
                            <i class="fas fa-tag me-1"></i>
                            <?php echo ucfirst($transaction['transaction_category']); ?>
                        </div>
                        <div class="transaction-meta">
                            <i class="fas fa-wallet me-1"></i>
                            Balance: ₹<?php echo number_format($transaction['balance_after'], 2); ?>
                        </div>
                    </div>
                </div>
                <div class="text-end">
                    <div class="transaction-amount <?php echo $transaction['transaction_type']; ?>">
                        <?php if ($transaction['transaction_type'] == 'credit'): ?>
                            +₹<?php echo number_format($transaction['amount'], 2); ?>
                        <?php else: ?>
                            -₹<?php echo number_format($transaction['amount'], 2); ?>
                        <?php endif; ?>
                    </div>
                    <span class="transaction-status <?php echo $transaction['status']; ?>">
                        <?php echo ucfirst($transaction['status']); ?>
                    </span>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($currentPage > 1): ?>
                    <a href="/wallet/transactions?page=<?php echo $currentPage - 1; ?>&filter=<?php echo $filter; ?>&type=<?php echo $type; ?>">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-chevron-left"></i></span>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <?php if ($i == $currentPage): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="/wallet/transactions?page=<?php echo $i; ?>&filter=<?php echo $filter; ?>&type=<?php echo $type; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>

                <?php if ($currentPage < $totalPages): ?>
                    <a href="/wallet/transactions?page=<?php echo $currentPage + 1; ?>&filter=<?php echo $filter; ?>&type=<?php echo $type; ?>">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php else: ?>
                    <span class="disabled"><i class="fas fa-chevron-right"></i></span>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4>No Transactions Found</h4>
                <p class="text-muted">You don't have any transactions yet.</p>
                <a href="/wallet" class="btn btn-primary mt-3">Go to Wallet</a>
            </div>
        </div>
    <?php endif; ?>
</div>
