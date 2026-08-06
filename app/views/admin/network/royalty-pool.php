<?php
$month        = (int)($month        ?? date('n'));
$year         = (int)($year         ?? date('Y'));
$pool_total   = (float)($pool_total ?? 0);
$distribution = $distribution ?? [];
$eligible_count = (int)($eligible_count ?? 0);
$base         = defined('BASE_URL') ? BASE_URL : '';

$monthName    = date('F', mktime(0, 0, 0, $month, 1, $year));
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-coins me-2"></i>Royalty Pool Distribution</h4>
        <a href="<?= $base ?>/admin/network/ranks" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i>Rank Structure
        </a>
    </div>

    <!-- Month Selector -->
    <form method="GET" class="card shadow-sm mb-4">
        <div class="card-body py-3">
            <div class="row g-2 align-items-end">
                <div class="col-auto">
                    <label class="form-label small" for="sel_month">Month</label>
                    <select id="sel_month" name="month" class="form-select form-select-sm">
                        <?php for ($m = 1; $m <= 12; $m++): ?>
                            <option value="<?= $m ?>" <?= $m === $month ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <label class="form-label small" for="sel_year">Year</label>
                    <select id="sel_year" name="year" class="form-select form-select-sm">
                        <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                            <option value="<?= $y ?>" <?= $y === $year ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-outline-primary btn-sm">View</button>
                </div>
            </div>
        </div>
    </form>

    <!-- KPI Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0 bg-warning bg-opacity-10">
                <div class="card-body py-3">
                    <div class="small text-muted">Pool Collected — <?= $monthName ?> <?= $year ?></div>
                    <div class="h3 text-warning">₹<?= number_format($pool_total, 2) ?></div>
                    <div class="small text-muted">2% Track C escrow</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0 bg-success bg-opacity-10">
                <div class="card-body py-3">
                    <div class="small text-muted">Eligible Royalty Directors</div>
                    <div class="h3 text-success"><?= $eligible_count ?></div>
                    <div class="small text-muted">Members at Royalty Director+ rank</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center shadow-sm border-0 bg-primary bg-opacity-10">
                <div class="card-body py-3">
                    <div class="small text-muted">Distribution Status</div>
                    <div class="h3 text-primary">
                        <?= count($distribution) > 0 ? count($distribution) . ' Paid' : 'Pending' ?>
                    </div>
                    <div class="small text-muted">Records for this month</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribute Action -->
    <?php if ($pool_total > 0 && $eligible_count > 0): ?>
        <div class="card shadow-sm mb-4 border-success">
            <div class="card-body d-flex align-items-center justify-content-between">
                <div>
                    <strong>Trigger Distribution for <?= $monthName ?> <?= $year ?></strong><br>
                    <span class="text-muted small">Pool of ₹<?= number_format($pool_total, 2) ?> will be distributed to <?= $eligible_count ?> eligible members.</span>
                </div>
                <form method="POST" action="<?= $base ?>/admin/network/royalty/distribute">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <input type="hidden" name="month" value="<?= $month ?>">
                    <input type="hidden" name="year" value="<?= $year ?>">
                    <button type="submit" class="btn btn-success"
                            onclick="return confirm('Distribute ₹<?= number_format($pool_total, 2) ?> to <?= $eligible_count ?> members for <?= $monthName ?> <?= $year ?>?')">
                        <i class="fas fa-share me-1"></i>Distribute Now
                    </button>
                </form>
            </div>
        </div>
    <?php elseif ($pool_total <= 0): ?>
        <div class="alert alert-secondary">No contributions collected for <?= $monthName ?> <?= $year ?>.</div>
    <?php elseif ($eligible_count === 0): ?>
        <div class="alert alert-warning">No Royalty Director-level members found. Ensure at least one member has reached the required GBV.</div>
    <?php endif; ?>

    <!-- Distribution Table -->
    <?php if (!empty($distribution)): ?>
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <i class="fas fa-table me-2"></i>Distribution Records — <?= $monthName ?> <?= $year ?>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Member Name</th>
                            <th>Email</th>
                            <th class="text-end">Share %</th>
                            <th class="text-end">Amount (₹)</th>
                            <th class="text-center">Distributed At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalDistributed = 0;
                        foreach ($distribution as $d):
                            $totalDistributed += (float)$d['amount'];
                        ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($d['name'] ?? '') ?></strong></td>
                                <td><?= htmlspecialchars($d['email'] ?? '') ?></td>
                                <td class="text-end"><?= number_format((float)$d['share_pct'], 4) ?>%</td>
                                <td class="text-end text-success"><strong>₹<?= number_format((float)$d['amount'], 2) ?></strong></td>
                                <td class="text-center text-muted small"><?= date('d M Y H:i', strtotime($d['distributed_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <th colspan="3" class="text-end">Total Distributed:</th>
                            <th class="text-end">₹<?= number_format($totalDistributed, 2) ?></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>
