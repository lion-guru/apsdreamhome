<?php $page_title = $page_title ?? 'Compliance Dashboard';
$reraFilings = $reraFilings ?? [];
$totalRera = $totalRera ?? 0;
$pendingRera = $pendingRera ?? 0;
$acceptedRera = $acceptedRera ?? 0;
$pendingKyc = $pendingKyc ?? 0;
$totalKyc = $totalKyc ?? 0;
$verifiedKyc = $verifiedKyc ?? 0;
$gstReturns = $gstReturns ?? [];
$totalGstFiled = $totalGstFiled ?? 0;
$pendingGst = $pendingGst ?? 0;
$tdsSummary = $tdsSummary ?? [];
$totalTdsAmount = $totalTdsAmount ?? 0;
$pendingTds = $pendingTds ?? 0;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Compliance Dashboard</h2>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-primary rounded-pill p-2"><i class="fas fa-file-alt"></i></span></div>
                    <div><div class="aps-cp-stat-label">RERA Filings</div><div class="aps-cp-stat-value"><?= $totalRera ?></div><div class="aps-cp-stat-meta">Pending: <?= $pendingRera ?> | Accepted: <?= $acceptedRera ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-warning rounded-pill p-2"><i class="fas fa-user-check"></i></span></div>
                    <div><div class="aps-cp-stat-label">KYC Requests</div><div class="aps-cp-stat-value"><?= $totalKyc ?></div><div class="aps-cp-stat-meta">Pending: <?= $pendingKyc ?> | Verified: <?= $verifiedKyc ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-info rounded-pill p-2"><i class="fas fa-receipt"></i></span></div>
                    <div><div class="aps-cp-stat-label">GST Returns</div><div class="aps-cp-stat-value"><?= $totalGstFiled + $pendingGst ?></div><div class="aps-cp-stat-meta">Filed: <?= $totalGstFiled ?> | Pending: <?= $pendingGst ?></div></div>
                </div>
            </div></div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card"><div class="aps-cp-card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0 me-3"><span class="badge bg-danger rounded-pill p-2"><i class="fas fa-percentage"></i></span></div>
                    <div><div class="aps-cp-stat-label">TDS Summary</div><div class="aps-cp-stat-value">â‚¹<?= number_format($totalTdsAmount/1000,1) ?>K</div><div class="aps-cp-stat-meta">Pending: <?= $pendingTds ?></div></div>
                </div>
            </div></div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-7">
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-file-alt me-2"></i>RERA Compliance Status</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($reraFilings)): ?>
                        <div class="text-center text-muted py-4"><i class="fas fa-info-circle fa-2x mb-2"></i><p>No RERA filings recorded</p></div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead><tr><th>Colony</th><th>Quarter</th><th>Year</th><th>Progress</th><th>Amount Withdrawn</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($reraFilings as $r): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($r['colony_name'] ?? 'N/A') ?></strong></td>
                                        <td><?= htmlspecialchars($r['quarter']) ?></td>
                                        <td><?= $r['year'] ?></td>
                                        <td>
                                            <div class="progress" class="style-32026"><div class="progress-bar bg-<?= $r['progress_percent'] >= 70 ? 'success' : 'warning' ?>" class="style-47424"></div></div>
                                            <small><?= $r['progress_percent'] ?>%</small>
                                        </td>
                                        <td>â‚¹<?= number_format($r['amount_withdrawn']) ?></td>
                                        <td><span class="aps-cp-badge badge bg-<?= $r['status'] === 'accepted' ? 'success' : ($r['status'] === 'rejected' ? 'danger' : ($r['status'] === 'submitted' ? 'info' : 'warning')) ?>"><?= ucfirst(htmlspecialchars($r['status'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="aps-cp-card mb-3">
                <div class="aps-cp-card-header"><i class="fas fa-receipt me-2"></i>GST Return Filing Status</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($gstReturns)): ?>
                        <div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No GST returns filed</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Period</th><th>Type</th><th>Tax</th><th>Status</th></tr></thead>
                                <tbody>
                                <?php foreach ($gstReturns as $g): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($g['return_period']) ?></td>
                                        <td><span class="aps-cp-badge badge bg-info"><?= strtoupper(htmlspecialchars($g['return_type'])) ?></span></td>
                                        <td>â‚¹<?= number_format($g['total_tax_amount']) ?></td>
                                        <td><span class="aps-cp-badge badge bg-<?= $g['filing_status'] === 'filed' ? 'success' : ($g['filing_status'] === 'late_filed' ? 'danger' : 'warning') ?>"><?= ucfirst(htmlspecialchars($g['filing_status'])) ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="aps-cp-card">
                <div class="aps-cp-card-header"><i class="fas fa-percentage me-2"></i>TDS Deduction Summary</div>
                <div class="aps-cp-card-body">
                    <?php if (empty($tdsSummary)): ?>
                        <div class="text-center text-muted py-3"><i class="fas fa-info-circle me-1"></i>No TDS deductions</div>
                    <?php else: ?>
                        <?php foreach ($tdsSummary as $t): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span><span class="aps-cp-badge badge bg-<?= $t['status'] === 'deposited' ? 'success' : ($t['status'] === 'verified' ? 'info' : 'warning') ?>"><?= ucfirst(htmlspecialchars($t['status'])) ?></span></span>
                                <span><strong>â‚¹<?= number_format($t['total']) ?></strong> (<?= $t['cnt'] ?> entries)</span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
