<?php
$page_title = $page_title ?? 'My Cash Collections - APS Dream Home';
$stats = $stats ?? [];
$collections = $collections ?? [];
$filters = $filters ?? ['status' => '', 'from' => '', 'to' => ''];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><i class="fas fa-hand-holding-usd text-success me-2"></i>My Cash Collections</h4>
        <a href="<?php echo $base; ?>/associate/collections/create" class="btn btn-success btn-sm">
            <i class="fas fa-plus me-1"></i>New Collection
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-primary text-white h-100">
                <div class="card-body text-center">
                    <h6 class="mb-1"><i class="fas fa-calendar-day me-1"></i>Today</h6>
                    <h3 class="mb-0">₹<?php echo number_format((float)($stats['today_amount'] ?? 0)); ?></h3>
                    <small><?php echo (int)($stats['today_count'] ?? 0); ?> collections</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-info text-white h-100">
                <div class="card-body text-center">
                    <h6 class="mb-1"><i class="fas fa-calendar-alt me-1"></i>This Month</h6>
                    <h3 class="mb-0">₹<?php echo number_format((float)($stats['this_month_amount'] ?? 0)); ?></h3>
                    <small><?php echo (int)($stats['this_month_count'] ?? 0); ?> collections</small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-warning text-dark h-100">
                <div class="card-body text-center">
                    <h6 class="mb-1"><i class="fas fa-hourglass-half me-1"></i>Pending</h6>
                    <h3 class="mb-0"><?php echo (int)($stats['pending_count'] ?? 0); ?></h3>
                    <small>₹<?php echo number_format((float)($stats['pending_amount'] ?? 0)); ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-success text-white h-100">
                <div class="card-body text-center">
                    <h6 class="mb-1"><i class="fas fa-check-circle me-1"></i>Verified</h6>
                    <h3 class="mb-0"><?php echo (int)($stats['verified_count'] ?? 0); ?></h3>
                    <small>₹<?php echo number_format((float)($stats['verified_amount'] ?? 0)); ?></small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Row -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-3">
            <form method="GET" class="row g-2 align-items-end">
    <?php echo CSRFProtection::csrfField(); ?>
                <div class="col-auto">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="submitted" <?php echo ($filters['status'] ?? '') === 'submitted' ? 'selected' : ''; ?>>Pending</option>
                        <option value="verified" <?php echo ($filters['status'] ?? '') === 'verified' ? 'selected' : ''; ?>>Verified</option>
                        <option value="rejected" <?php echo ($filters['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">From</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['from'] ?? ''); ?>">
                </div>
                <div class="col-auto">
                    <label class="form-label small mb-1">To</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="<?php echo htmlspecialchars($filters['to'] ?? ''); ?>">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-filter me-1"></i>Filter</button>
                    <a href="<?php echo $base; ?>/associate/collections" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Collections Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($collections)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-hand-holding-usd fa-4x text-muted mb-3"></i>
                    <p class="text-muted">No collections found.</p>
                    <a href="<?php echo $base; ?>/associate/collections/create" class="btn btn-success">Record Your First Collection</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>#</th>
                                <th>Date</th>
                                <th>Customer</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Receipt</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($collections as $c): ?>
                            <tr>
                                <td><?php echo (int)($c['id'] ?? 0); ?></td>
                                <td><?php echo htmlspecialchars($c['collection_date'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($c['customer_name'] ?? '-'); ?></td>
                                <td><strong>₹<?php echo number_format((float)($c['amount'] ?? 0)); ?></strong></td>
                                <td>
                                    <?php
                                    $method = $c['payment_method'] ?? 'cash';
                                    $icon = $method === 'cash' ? 'money-bill-wave' : ($method === 'cheque' ? 'fa-check' : ($method === 'bank_transfer' ? 'university' : 'credit-card'));
                                    ?>
                                    <span class="badge bg-secondary"><i class="fas fa-<?php echo $icon; ?> me-1"></i><?php echo ucfirst(str_replace('_', ' ', $method)); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $s = $c['status'] ?? 'submitted';
                                    $b = $s === 'verified' ? 'success' : ($s === 'rejected' ? 'danger' : 'warning');
                                    ?>
                                    <span class="badge bg-<?php echo $b; ?>"><?php echo ucfirst($s); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($c['receipt_photo'])): ?>
                                        <a href="<?php echo $base . '/' . ltrim($c['receipt_photo'], '/'); ?>" target="_blank" class="btn btn-sm btn-outline-info"><i class="fas fa-image"></i></a>
                                    <?php else: ?>
                                        <span class="text-muted small">No photo</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo $base; ?>/associate/collections/<?php echo (int)($c['id'] ?? 0); ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white text-muted small text-end">
                    Total: <?php echo count($collections); ?> collection(s)
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
