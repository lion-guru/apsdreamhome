<?php
/**
 * Referral Earnings Widget
 * Include in dashboards: <?php include __DIR__ . '/../components/referral_earnings_widget.php'; ?>
 * 
 * @var int $userId - Current user ID
 * @var string $base - BASE_URL
 * @var array $referralEarnings - From ReferralService::getReferralEarningsBreakdown($userId)
 */
$base = defined('BASE_URL') ? BASE_URL : '/' . trim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
$userId = $userId ?? ($_SESSION['user_id'] ?? 0);

if (!$userId) return;

// Controllers pass $referral_earnings_breakdown; accept both names so the
// widget never silently renders empty on a variable-name mismatch.
$referralEarnings = $referralEarnings ?? $referral_earnings_breakdown ?? [];
$summary = $referralEarnings['summary'] ?? [];
$byType = $referralEarnings['by_type'] ?? [];
$recent = $referralEarnings['recent'] ?? [];

// Helpers are include-guarded: dashboards/layouts may include this partial
// more than once per request; a bare function definition would fatal.
if (!function_exists('getTypeBadgeClass')) {
    function getTypeBadgeClass($type) {
        if (str_contains($type, 'customer')) return 'warning';
        if (str_contains($type, 'associate')) return 'success';
        if (str_contains($type, 'wallet')) return 'info';
        if (str_contains($type, 'signup')) return 'primary';
        return 'secondary';
    }
}

if (!function_exists('getTypeIcon')) {
    function getTypeIcon($type) {
        if (str_contains($type, 'customer')) return 'user';
        if (str_contains($type, 'associate')) return 'user-tie';
        if (str_contains($type, 'wallet')) return 'wallet';
        if (str_contains($type, 'signup')) return 'user-plus';
        return 'calendar';
    }
}

if (!function_exists('getTypeIconClass')) {
    function getTypeIconClass($type) {
        if (str_contains($type, 'customer')) return 'text-warning';
        if (str_contains($type, 'associate')) return 'text-success';
        if (str_contains($type, 'wallet')) return 'text-info';
        if (str_contains($type, 'signup')) return 'text-primary';
        return 'text-secondary';
    }
}
?>

<!-- Referral Earnings Widget -->
<div class="referral-earnings-widget" data-user-id="<?= $userId ?>">
    <div class="widget-header d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><i class="fas fa-share-alt text-warning me-2"></i>Referral Earnings</h5>
        <a href="<?= $base ?>/user/referrals" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-medium">
            View Details <i class="fas fa-arrow-right ms-1"></i>
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card-glass h-100" style="--icon-bg: linear-gradient(135deg, #f59e0b, #d97706); --icon-shadow: rgba(245,158,11,0.4);">
                <div class="stat-icon-wrapper"><i class="fas fa-users"></i></div>
                <div class="stat-value"><?= number_format($summary['total_referrals'] ?? 0) ?></div>
                <div class="stat-label">Total Referrals</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-glass h-100" style="--icon-bg: linear-gradient(135deg, #10b981, #059669); --icon-shadow: rgba(16,185,129,0.4);">
                <div class="stat-icon-wrapper"><i class="fas fa-user-check"></i></div>
                <div class="stat-value"><?= number_format($summary['active_referrals'] ?? 0) ?></div>
                <div class="stat-label">Active (Booked)</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-glass h-100" style="--icon-bg: linear-gradient(135deg, #3b82f6, #1d4ed8); --icon-shadow: rgba(59,130,246,0.4);">
                <div class="stat-icon-wrapper"><i class="fas fa-rupee-sign"></i></div>
                <div class="stat-value">₹<?= number_format((float)($summary['total_earned'] ?? 0), 2) ?></div>
                <div class="stat-label">Total Earned</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card-glass h-100" style="--icon-bg: linear-gradient(135deg, #8b5cf6, #5b21b6); --icon-shadow: rgba(139,92,246,0.4);">
                <div class="stat-icon-wrapper"><i class="fas fa-calendar-alt"></i></div>
                <div class="stat-value">₹<?= number_format((float)($summary['this_month'] ?? 0), 2) ?></div>
                <div class="stat-label">This Month</div>
            </div>
        </div>
    </div>

    <!-- Earnings by Type -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fas fa-chart-pie text-info me-2"></i>Earnings Breakdown</h6>
            <span class="badge bg-info bg-opacity-10 text-info small">All Sources</span>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="text-muted small">
                            <th>Source</th>
                            <th class="text-center">Count</th>
                            <th class="text-end">Earned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($byType as $type => $row):
                            if (($row['count'] ?? 0) > 0):
                                $badgeClass = getTypeBadgeClass($type);
                                $icon = getTypeIcon($type);
                                $iconClass = getTypeIconClass($type);
                        ?>
                            <tr>
                                <td class="small">
                                    <span class="badge bg-<?= $badgeClass ?> bg-opacity-10 <?= $iconClass ?> rounded-pill px-2 py-1 me-2" style="font-size: 0.65rem;">
                                        <i class="fas fa-<?= getTypeIcon($type) ?> me-1"></i>
                                    </span>
                                    <?= htmlspecialchars($row['label']) ?>
                                </td>
                                <td class="text-center small fw-medium"><?= number_format($row['count']) ?></td>
                                <td class="text-end small fw-bold text-success">₹<?= number_format((float)($row['amount'] ?? 0), 2) ?></td>
                            </tr>
                        <?php endif; endforeach; ?>
                        <?php if (empty(array_filter($byType, fn($d) => ($d['count'] ?? 0) > 0))): ?>
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    <i class="fas fa-seedling fa-2x mb-2 d-block opacity-50"></i>
                                    <p class="small mb-0">No referral earnings yet. Start sharing your code!</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Earnings -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0"><i class="fas fa-history text-primary me-2"></i>Recent Activity</h6>
            <a href="<?= $base ?>/user/referrals/history" class="small text-decoration-none">View All</a>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($recent)): ?>
                <div class="list-group list-group-flush">
                    <?php foreach (array_slice($recent, 0, 5) as $item): 
                        $statusClass = $item['status'] === 'paid' ? 'success' : ($item['status'] === 'pending' ? 'warning' : 'secondary');
                        $icon = getTypeIcon($item['commission_type']);
                        $statusText = $item['status'] === 'paid' ? '+' : '⏳';
                    ?>
                        <div class="list-group-item px-3 py-2 border-0 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-<?= $statusClass ?> bg-opacity-10 text-<?= $statusClass ?> rounded-pill px-2 py-1 small">
                                        <i class="fas fa-<?= getTypeIcon($item['commission_type']) ?> me-1"></i>
                                        <?= ucfirst(str_replace('_', ' ', $item['commission_type'])) ?>
                                    </span>
                                    <span class="small text-muted"><?= date('d M Y', strtotime($item['created_at'])) ?></span>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-<?= $item['status'] === 'paid' ? 'success' : 'warning' ?>">
                                        <?= $statusText ?>₹<?= number_format((float)($item['amount'] ?? 0), 2) ?>
                                    </span>
                                    <?php if (!empty($item['booking_id'])): ?>
                                        <span class="small text-muted ms-2">#<?= $item['booking_id'] ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="fas fa-history fa-2x mb-2 opacity-50"></i>
                    <p class="small mb-0">No recent referral activity</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="d-flex gap-2 mt-3 flex-wrap">
        <a href="<?= $base ?>/user/referrals/share" class="btn btn-sm btn-outline-warning rounded-pill px-3">
            <i class="fas fa-share-alt me-1"></i>Share Code
        </a>
        <a href="<?= $base ?>/user/referrals/leaderboard" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            <i class="fas fa-trophy me-1"></i>Leaderboard
        </a>
        <a href="<?= $base ?>/user/referrals/tier" class="btn btn-sm btn-outline-info rounded-pill px-3">
            <i class="fas fa-medal me-1"></i>My Tier
        </a>
    </div>
</div>

<style>
.referral-earnings-widget .stat-card-glass {
    background: var(--glass-bg, rgba(255,255,255,0.9));
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid var(--glass-border, rgba(255,255,255,0.2));
    border-radius: 14px;
    padding: 20px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--card-shadow, 0 8px 32px rgba(31, 38, 135, 0.07));
    height: 100%;
    position: relative;
    overflow: hidden;
}
.referral-earnings-widget .stat-icon-wrapper {
    width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem; color: #fff; margin-bottom: 12px;
    background: var(--icon-bg); box-shadow: 0 6px 16px var(--icon-shadow);
}
.referral-earnings-widget .stat-value {
    font-size: 1.5rem; font-weight: 800; color: #0f172a; letter-spacing: -0.5px;
}
.referral-earnings-widget .stat-label {
    font-size: 0.78rem; color: #64748b; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.5px; margin-top: 6px;
}
</style>