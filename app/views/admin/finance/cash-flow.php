<?php $page_title = $page_title ?? 'Cash Flow Forecast'; $page_heading = $page_heading ?? 'Cash Flow Forecast'; $days = $days ?? 30; $summary = $summary ?? []; $rows = $rows ?? []; ?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0"><i class="fas fa-chart-line me-2 text-primary"></i><?= htmlspecialchars($page_heading) ?></h2>
        <a href="<?= BASE_URL ?>/admin/finance/dashboard" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Finance</a>
    </div>

    <div class="aps-cp-card mb-4">
        <div class="aps-cp-card-body">
            <form method="get" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small">Forecast Period</label>
                    <select name="days" class="form-select form-select-sm">
                        <option value="7" <?= $days == 7 ? 'selected' : '' ?>>Next 7 days</option>
                        <option value="14" <?= $days == 14 ? 'selected' : '' ?>>Next 14 days</option>
                        <option value="30" <?= $days == 30 ? 'selected' : '' ?>>Next 30 days</option>
                        <option value="60" <?= $days == 60 ? 'selected' : '' ?>>Next 60 days</option>
                        <option value="90" <?= $days == 90 ? 'selected' : '' ?>>Next 90 days</option>
                    </select>
                </div>
                <div class="col-md-2"><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Update</button></div>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Expected Inflow</div>
                    <div class="aps-cp-stat-value text-success">₹<?= number_format((float)($summary['inflow_total'] ?? 0), 0) ?></div>
                    <small class="text-muted">Weighted: ₹<?= number_format((float)($summary['weighted_inflow'] ?? 0), 0) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Expected Outflow</div>
                    <div class="aps-cp-stat-value text-danger">₹<?= number_format((float)($summary['outflow_total'] ?? 0), 0) ?></div>
                    <small class="text-muted">Weighted: ₹<?= number_format((float)($summary['weighted_outflow'] ?? 0), 0) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Net Cash Flow</div>
                    <div class="aps-cp-stat-value <?= ((float)($summary['net'] ?? 0)) >= 0 ? 'text-success' : 'text-danger' ?>">₹<?= number_format((float)($summary['net'] ?? 0), 0) ?></div>
                    <small class="text-muted">Weighted net</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="aps-cp-card">
                <div class="aps-cp-card-body">
                    <div class="aps-cp-stat-label">Forecast Entries</div>
                    <div class="aps-cp-stat-value"><?= count($rows) ?></div>
                    <small class="text-muted"><?= htmlspecialchars($from) ?> to <?= htmlspecialchars($to) ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="aps-cp-card">
        <div class="aps-cp-card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Direction</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Probability</th>
                        <th class="text-end">Weighted</th>
                        <th>Days Ahead</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="8" class="text-center text-muted py-4">No forecast entries for this period. Records will appear here from EMI schedules, vendor payments, and receivables.</td></tr>
                <?php else: foreach ($rows as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['expected_date'] ?? '') ?></td>
                        <td><span class="badge bg-<?= ($r['type'] ?? '') === 'inflow' ? 'success' : 'danger' ?>"><?= htmlspecialchars($r['type'] ?? '') ?></span></td>
                        <td><small class="text-muted"><?= htmlspecialchars($r['category'] ?? '-') ?></small></td>
                        <td><?= htmlspecialchars($r['description'] ?? '-') ?></td>
                        <td class="text-end fw-bold <?= ($r['type'] ?? '') === 'inflow' ? 'text-success' : 'text-danger' ?>">₹<?= number_format((float)($r['expected_amount'] ?? 0), 2) ?></td>
                        <td class="text-end"><?= (int)($r['probability_pct'] ?? 100) ?>%</td>
                        <td class="text-end">₹<?= number_format((float)($r['weighted_amount'] ?? 0), 2) ?></td>
                        <td><span class="badge bg-secondary"><?= (int)($r['days_ahead'] ?? 0) ?>d</span></td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
