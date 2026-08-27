<?php
$page_title = $page_title ?? 'Farmer Details';
$farmer = $farmer ?? [];
$agreements = $agreements ?? [];
$loans = $loans ?? [];
$transactions = $transactions ?? [];
$documents = $documents ?? [];
$isLegacy = $is_legacy ?? false;
?>
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-tractor text-success me-2"></i> Farmer Details</h4>
        <a href="<?php echo BASE_URL; ?>/admin/farmers" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <?php if ($msg = \App\Core\Session::flash('success')): ?>
        <div class="alert alert-success alert-dismissible fade show"><?php echo e($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>
    <?php if ($msg = \App\Core\Session::flash('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show"><?php echo e($msg); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-user me-2"></i>Farmer Info</h5></div>
                <div class="card-body aps-cp-card-body">
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Name</td><td><strong><?php echo htmlspecialchars($farmer['name'] ?? ''); ?></strong></td></tr>
                        <tr><td class="text-muted">Mobile</td><td><?php echo htmlspecialchars($farmer['phone'] ?? ''); ?></td></tr>
                        <?php if ($farmer['email'] ?? ''): ?>
                        <tr><td class="text-muted">Email</td><td><?php echo htmlspecialchars($farmer['email'] ?? ''); ?></td></tr>
                        <?php endif; ?>
                        <tr><td class="text-muted">Total Land Area</td><td><?php echo htmlspecialchars($farmer['total_land_area'] ?? '0'); ?> sq.ft</td></tr>
                        <tr><td class="text-muted">Holdings</td><td><?php echo htmlspecialchars($farmer['holdings_count'] ?? '0'); ?></td></tr>
                        <tr><td class="text-muted">Location</td><td><?php echo htmlspecialchars(($farmer['district'] ?? '') . ($farmer['city'] ? ', ' . $farmer['city'] : '')); ?></td></tr>
                        <?php if ($farmer['khasra_numbers'] ?? ''): ?><tr><td class="text-muted">Khasra Numbers</td><td><?php echo htmlspecialchars($farmer['khasra_numbers'] ?? ''); ?></td></tr><?php endif; ?>
                        <tr><td class="text-muted">Location</td><td><?php echo htmlspecialchars(($farmer['district'] ?? '') . ($farmer['city'] ? ', ' . $farmer['city'] : '')); ?></td></tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-rupee-sign me-2"></i>Financials</h5></div>
                <div class="card-body aps-cp-card-body">
                    <table class="table table-sm table-borderless">
                        <?php if ($farmer['bank_name'] ?? ''): ?>
                        <tr><td class="text-muted">Bank</td><td><?php echo htmlspecialchars($farmer['bank_name'] ?? ''); ?></td></tr>
                        <tr><td class="text-muted">IFSC</td><td><?php echo htmlspecialchars($farmer['bank_ifsc'] ?? ''); ?></td></tr>
                        <tr><td class="text-muted">A/C No</td><td><?php echo htmlspecialchars($farmer['account_number'] ?? ''); ?></td></tr>
                        <?php endif; ?>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-people-arrows me-2"></i>Holdings</h5></div>
                <div class="card-body aps-cp-card-body">
                    <table class="table table-sm table-borderless">
                        <tr><td class="text-muted">Holdings Count</td><td><?php echo htmlspecialchars($farmer['holdings_count'] ?? '0'); ?></td></tr>
                        <tr><td class="text-muted">Total Land Area</td><td><?php echo htmlspecialchars($farmer['total_land_area'] ?? '0'); ?> sq.ft</td></tr>
                        <tr><td class="text-muted">Khasra Numbers</td><td><?php echo htmlspecialchars($farmer['khasra_numbers'] ?? 'N/A'); ?></td></tr>
                        <tr><td class="text-muted">Status</td>
                            <td><?php $s = $farmer['agreement_status'] ?? 'N/A'; ?>
                                <?php if ($s === 'active'): ?><span class="badge bg-success">Active</span>
                                <?php elseif ($s === 'completed'): ?><span class="badge bg-info">Completed</span>
                                <?php elseif ($s === 'terminated'): ?><span class="badge bg-danger">Terminated</span>
                                <?php else: ?><span class="badge bg-secondary"><?php echo htmlspecialchars($s ?? ''); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($transactions)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-exchange-alt me-2"></i>Recent Transactions</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead class="table-light"><tr><th>Type</th><th>Amount</th><th>Date</th><th>Notes</th></tr></thead>
                    <tbody>
                        <?php foreach ($transactions as $t): ?>
                        <tr>
                            <td><span class="badge bg-<?php echo ($t['transaction_type'] ?? '') === 'payment' ? 'success' : 'info'; ?>"><?php echo htmlspecialchars($t['transaction_type'] ?? ''); ?></span></td>
                            <td>₹<?php echo number_format($t['amount'] ?? 0); ?></td>
                            <td><?php echo htmlspecialchars($t['payment_date'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($t['notes'] ?? ''); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-file-signature me-2"></i>Agreements (<?php echo count($agreements); ?>)</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light"><tr><th>#</th><th>Type</th><th>Amount</th><th>Status</th><th>Start</th></tr></thead>
                            <tbody>
                                <?php foreach ($agreements as $a): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($a['agreement_number'] ?? 'N/A'); ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $a['agreement_type'] ?? '')); ?></td>
                                    <td>₹<?php echo number_format($a['total_amount'] ?? 0); ?></td>
                                    <td>
                                        <?php $as = $a['status'] ?? ''; ?>
                                        <?php if ($as === 'active'): ?><span class="badge bg-success">Active</span>
                                        <?php elseif ($as === 'completed'): ?><span class="badge bg-info">Completed</span>
                                        <?php elseif ($as === 'terminated'): ?><span class="badge bg-danger">Terminated</span>
                                        <?php elseif ($as === 'draft'): ?><span class="badge bg-secondary">Draft</span>
                                        <?php else: ?><span class="badge bg-light text-dark"><?php echo htmlspecialchars($as ?? ''); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($a['start_date'] ?? ''); ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($agreements)): ?><tr><td colspan="5" class="text-center text-muted py-3">No agreements</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2"></i>Loans (<?php echo count($loans); ?>)</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light"><tr><th>#</th><th>Amount</th><th>Interest</th><th>EMI</th><th>Status</th></tr></thead>
                            <tbody>
                                <?php foreach ($loans as $l): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($l['loan_number'] ?? 'N/A'); ?></td>
                                    <td>₹<?php echo number_format($l['loan_amount'] ?? 0); ?></td>
                                    <td><?php echo htmlspecialchars($l['interest_rate'] ?? '0'); ?>%</td>
                                    <td>₹<?php echo number_format($l['emi_amount'] ?? 0); ?></td>
                                    <td>
                                        <?php $ls = $l['status'] ?? ''; ?>
                                        <?php if (in_array($ls, ['active','disbursed'])): ?><span class="badge bg-success"><?php echo ucfirst($ls); ?></span>
                                        <?php elseif ($ls === 'sanctioned'): ?><span class="badge bg-primary">Sanctioned</span>
                                        <?php elseif ($ls === 'closed'): ?><span class="badge bg-info">Closed</span>
                                        <?php elseif ($ls === 'defaulted'): ?><span class="badge bg-danger">Defaulted</span>
                                        <?php elseif ($ls === 'applied'): ?><span class="badge bg-secondary">Applied</span>
                                        <?php else: ?><span class="badge bg-light text-dark"><?php echo htmlspecialchars($ls ?? ''); ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php if (empty($loans)): ?><tr><td colspan="5" class="text-center text-muted py-3">No loans</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (!empty($documents)): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-file me-2"></i>Documents</h5></div>
        <div class="card-body aps-cp-card-body">
            <div class="row">
                <?php foreach ($documents as $d): ?>
                <div class="col-md-3 mb-3">
                    <div class="card aps-cp-card">
                        <div class="card-body text-center">
                            <i class="fas fa-file-pdf text-danger fa-3x mb-2"></i>
                            <p class="mb-1 small"><?php echo htmlspecialchars($d['document_type'] ?? ''); ?></p>
                            <a href="<?php echo htmlspecialchars($d['document_url'] ?? '#'); ?>" class="btn btn-sm btn-outline-primary" target="_blank">View</a>
                        </div>
                        <div class="card-footer text-muted small text-center"><?php echo htmlspecialchars($d['uploaded_at'] ?? ''); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
