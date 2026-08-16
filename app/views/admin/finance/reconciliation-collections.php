ï»¿<?php
$page_title = $page_title ?? 'Collection Reconciliation';
$page_heading = $page_heading ?? 'Cash Collection Reconciliation';
$sessions = $sessions ?? [];
$collectors = $collectors ?? [];
$filters = $filters ?? [];
?>
<div class="aps-cp-container">
    <div class="aps-cp-page-header">
        <h1 class="aps-cp-page-title"><?= htmlspecialchars($page_heading ?? '') ?></h1>
    </div>

    <!-- Start New Reconciliation -->
    <div class="aps-cp-card" class="style-46748">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-play-circle"></i> Start New Reconciliation Session</span>
        </div>
        <div class="aps-cp-card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/finance/reconciliation-collections/start" class="style-68981">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <div class="aps-cp-form-group" class="style-52775">
                    <label class="aps-cp-form-label">Collector</label>
                    <select name="collector_id" class="aps-cp-form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($collectors as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="aps-cp-form-group" class="style-52775">
                    <label class="aps-cp-form-label">Date</label>
                    <input type="date" name="session_date" class="aps-cp-form-input" required value="<?= date('Y-m-d') ?>">
                </div>
                <button type="submit" class="aps-cp-btn aps-cp-btn-primary" onclick="return confirm('Start reconciliation for this collector+date?')">
                    <i class="fas fa-play"></i> Start Session
                </button>
            </form>
        </div>
    </div>

    <!-- Filters -->
    <div class="aps-cp-card" class="style-46748">
        <div class="aps-cp-card-body">
            <form method="GET" action="<?= BASE_URL ?>/admin/finance/reconciliation-collections" class="style-68981">
                <div class="aps-cp-form-group" class="style-57352">
                    <label class="aps-cp-form-label">Status</label>
                    <select name="status" class="aps-cp-form-select">
                        <option value="">All</option>
                        <option value="open" <?= ($filters['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="closed" <?= ($filters['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="discrepancy" <?= ($filters['status'] ?? '') === 'discrepancy' ? 'selected' : '' ?>>Discrepancy</option>
                    </select>
                </div>
                <div class="aps-cp-form-group" class="style-57352">
                    <label class="aps-cp-form-label">Collector</label>
                    <select name="collector_id" class="aps-cp-form-select">
                        <option value="">All</option>
                        <?php foreach ($collectors as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (int)($filters['collector_id'] ?? 0) === (int)$c['id'] ? 'selected' : '' ?>><?= htmlspecialchars($c['name'] ?? '') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="aps-cp-btn aps-cp-btn-primary"><i class="fas fa-filter"></i> Filter</button>
            </form>
        </div>
    </div>

    <!-- Sessions Table -->
    <div class="aps-cp-card">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-balance-scale"></i> Reconciliation Sessions (<?= count($sessions) ?>)</span>
        </div>
        <div class="aps-cp-card-body" class="style-97767">
            <?php if (empty($sessions)): ?>
                <div class="aps-cp-empty-state" class="style-85973">
                    <i class="fas fa-inbox" class="style-3949"></i>
                    <p>No reconciliation sessions found.</p>
                </div>
            <?php else: ?>
                <div class="aps-cp-table-responsive">
                    <table class="aps-cp-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Collector</th>
                                <th>Submitted</th>
                                <th>Verified</th>
                                <th>Rejected</th>
                                <th>Discrepancy</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sessions as $s): ?>
                                <tr>
                                    <td><?= htmlspecialchars($s['session_date'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($s['collector_name'] ?? 'N/A') ?></td>
                                    <td>₹<?= number_format($s['total_submitted'], 2) ?></td>
                                    <td class="style-45683">₹<?= number_format($s['total_verified'], 2) ?></td>
                                    <td class="style-78245">₹<?= number_format($s['total_rejected'], 2) ?></td>
                                    <td>
                                        <?php if ((float)$s['discrepancy_amount'] > 0): ?>
                                            <span class="style-60540">₹<?= number_format($s['discrepancy_amount'], 2) ?></span>
                                        <?php else: ?>
                                            <span class="style-7250">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $statusMap = ['open'=>'warning','closed'=>'success','discrepancy'=>'danger'];
                                        $color = $statusMap[$s['status']] ?? 'default';
                                        ?>
                                        <span class="aps-cp-badge aps-cp-badge-<?= $color ?>"><?= ucfirst($s['status']) ?></span>
                                    </td>
                                    <td>
                                        <?php if ($s['status'] !== 'closed'): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/admin/finance/reconciliation-collections/close" class="style-35851">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                                <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                                                <button type="submit" class="aps-cp-btn aps-cp-btn-sm aps-cp-btn-primary" onclick="return confirm('Close this session?')"><i class="fas fa-lock"></i> Close</button>
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
