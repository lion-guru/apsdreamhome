ï»¿<?php
$page_title = $page_title ?? 'Cash Collections';
$page_heading = $page_heading ?? 'On-Field Cash Collections';
$collections = $collections ?? [];
$stats = $stats ?? [];
$collectors = $collectors ?? [];
$filters = $filters ?? [];
?>
<div class="aps-cp-container">
    <div class="aps-cp-page-header">
        <h1 class="aps-cp-page-title"><?= htmlspecialchars($page_heading ?? '') ?></h1>
        <a href="<?= BASE_URL ?>/admin/finance/collection-form" class="aps-cp-btn aps-cp-btn-primary">
            <i class="fas fa-plus"></i> Record Collection
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="aps-cp-stats-grid" class="style-43624">
        <div class="aps-cp-stat-card">
            <div class="aps-cp-stat-icon" class="style-6196">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="aps-cp-stat-info">
                <div class="aps-cp-stat-label">Today's Collections</div>
                <div class="aps-cp-stat-value">₹<?= number_format($stats['today_total'] ?? 0) ?></div>
                <div class="aps-cp-stat-subtext"><?= ($stats['today_count'] ?? 0) ?> entries</div>
            </div>
        </div>
        <div class="aps-cp-stat-card">
            <div class="aps-cp-stat-icon" class="style-82361">
                <i class="fas fa-clock"></i>
            </div>
            <div class="aps-cp-stat-info">
                <div class="aps-cp-stat-label">Pending Verification</div>
                <div class="aps-cp-stat-value"><?= ($stats['pending_verification'] ?? 0) ?></div>
                <div class="aps-cp-stat-subtext">₹<?= number_format($stats['pending_amount'] ?? 0) ?></div>
            </div>
        </div>
        <div class="aps-cp-stat-card">
            <div class="aps-cp-stat-icon" class="style-92749">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="aps-cp-stat-info">
                <div class="aps-cp-stat-label">This Month</div>
                <div class="aps-cp-stat-value">₹<?= number_format($stats['month_total'] ?? 0) ?></div>
                <div class="aps-cp-stat-subtext"><?= ($stats['month_count'] ?? 0) ?> collections</div>
            </div>
        </div>
        <div class="aps-cp-stat-card">
            <div class="aps-cp-stat-icon" class="style-99507">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="aps-cp-stat-info">
                <div class="aps-cp-stat-label">Rejected Total</div>
                <div class="aps-cp-stat-value">₹<?= number_format($stats['rejected_total'] ?? 0) ?></div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="aps-cp-card" class="style-98782">
        <div class="aps-cp-card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/finance/collections" class="aps-cp-form-row" class="style-68981">
                <div class="aps-cp-form-group" class="style-57352">
                    <label class="aps-cp-form-label">Status</label>
                    <select name="status" class="aps-cp-form-select">
                        <option value="">All</option>
                        <option value="submitted" <?= ($filters['status'] ?? '') === 'submitted' ? 'selected' : '' ?>>Submitted</option>
                        <option value="verified" <?= ($filters['status'] ?? '') === 'verified' ? 'selected' : '' ?>>Verified</option>
                        <option value="rejected" <?= ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                        <option value="reconciled" <?= ($filters['status'] ?? '') === 'reconciled' ? 'selected' : '' ?>>Reconciled</option>
                    </select>
                </div>
                <div class="aps-cp-form-group" class="style-57352">
                    <label class="aps-cp-form-label">Collector</label>
                    <select name="collector_id" class="aps-cp-form-select">
                        <option value="">All Collectors</option>
                        <?php foreach ($collectors as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (int)($filters['collector_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="aps-cp-form-group" class="style-57352">
                    <label class="aps-cp-form-label">From Date</label>
                    <input type="date" name="from_date" class="aps-cp-form-input" value="<?= htmlspecialchars($filters['from_date'] ?? '') ?>">
                </div>
                <div class="aps-cp-form-group" class="style-57352">
                    <label class="aps-cp-form-label">To Date</label>
                    <input type="date" name="to_date" class="aps-cp-form-input" value="<?= htmlspecialchars($filters['to_date'] ?? '') ?>">
                </div>
                <button type="submit" class="aps-cp-btn aps-cp-btn-primary"><i class="fas fa-filter"></i> Filter</button>
                <a href="<?= BASE_URL ?>/admin/finance/collections" class="aps-cp-btn aps-cp-btn-outline">Reset</a>
            </form>
        </div>
    </div>

    <!-- Collections Table -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-list"></i> Collections (<?= count($collections) ?>)</span>
        </div>
        <div class="aps-cp-card-body" class="style-97767">
            <?php if (empty($collections)): ?>
                <div class="aps-cp-empty-state" class="style-85973">
                    <i class="fas fa-inbox" class="style-3949"></i>
                    <p>No collections found.</p>
                </div>
            <?php else: ?>
                <div class="aps-cp-table-responsive">
                    <table class="aps-cp-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Collector</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($collections as $c): ?>
                                <tr>
                                    <td><?= htmlspecialchars($c['collection_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($c['customer_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($c['collector_name'] ?? 'N/A') ?></td>
                                    <td class="style-24039">₹<?= number_format($c['amount'], 2) ?></td>
                                    <td><span class="aps-cp-badge aps-cp-badge-info"><?= ucfirst($c['payment_method']) ?></span></td>
                                    <td>
                                        <?php
                                        $statusColors = ['submitted'=>'warning','verified'=>'success','rejected'=>'danger','reconciled'=>'primary'];
                                        $color = $statusColors[$c['status']] ?? 'default';
                                        ?>
                                        <span class="aps-cp-badge aps-cp-badge-<?= $color ?>"><?= ucfirst($c['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($c['status'] === 'submitted'): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/finance/collections/verify" class="style-35851">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <button type="submit" class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-success" data-aps-confirm="Verify this collection?" aria-label="Confirm"><i class="fas fa-check"></i></button>
                                            </form>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/finance/collections/reject" class="style-35851">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="id" value="<?= $c['id'] ?>">
                                                <input type="text" name="reason" placeholder="Reason" required class="style-42587">
                                                <button type="submit" class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-danger" data-aps-confirm="Reject this collection?" aria-label="Close"><i class="fas fa-times"></i></button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
