<?php
$page_title = $page_title ?? 'Share Analytics';
$base = defined('BASE_URL') ? BASE_URL : '';
$funnel = $funnel ?? [];
$top_sharers = $top_sharers ?? [];
?>
<div class="container-fluid px-4 py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="m-0"><i class="fas fa-share-alt me-2 text-primary"></i>Share Analytics</h4>
        <a href="<?= $base ?>/admin/referrals" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <!-- Conversion Funnel -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="style-38176"><?= number_format($funnel['total_shares'] ?? 0) ?></div>
                <div class="text-muted small">Total Shares</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="style-23322"><?= number_format($funnel['total_signups'] ?? 0) ?></div>
                <div class="text-muted small">Signups</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="style-39581"><?= number_format($funnel['total_bookings'] ?? 0) ?></div>
                <div class="text-muted small">Bookings</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="style-50200"><?= ($funnel['conversion_rate'] ?? 0) ?>%</div>
                <div class="text-muted small">Conversion Rate</div>
            </div>
        </div>
    </div>

    <!-- Funnel Visualization -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white"><h5 class="m-0"><i class="fas fa-filter me-2"></i>Conversion Funnel</h5></div>
        <div class="card-body">
            <div class="d-flex align-items-center justify-content-center gap-2 style-41446">
                <div class="text-center p-3 rounded style-38779">
                    <div class="style-92139"><?= number_format($funnel['total_shares'] ?? 0) ?></div>
                    <div class="small text-muted">Shares</div>
                </div>
                <i class="fas fa-arrow-right text-muted"></i>
                <div class="text-center p-3 rounded style-62153">
                    <div class="style-9327"><?= number_format($funnel['total_signups'] ?? 0) ?></div>
                    <div class="small text-muted">Signups (<?= ($funnel['conversion_rate'] ?? 0) ?>%)</div>
                </div>
                <i class="fas fa-arrow-right text-muted"></i>
                <div class="text-center p-3 rounded style-77331">
                    <div class="style-33719"><?= number_format($funnel['total_bookings'] ?? 0) ?></div>
                    <div class="small text-muted">Bookings (<?= ($funnel['booking_rate'] ?? 0) ?>%)</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- By Platform -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="m-0"><i class="fas fa-chart-pie me-2"></i>Shares by Platform</h5></div>
                <div class="card-body">
                    <?php $platforms = $funnel['by_platform'] ?? []; if (empty($platforms)): ?>
                    <div class="text-center py-4 text-muted">No share data yet</div>
                    <?php else: ?>
                    <?php foreach ($platforms as $platform => $count): ?>
                    <div class="d-flex align-items-center mb-2">
                        <span class="text-capitalize style-73713">
                            <?php
                            $icons = ['whatsapp' => 'fab fa-whatsapp text-success', 'facebook' => 'fab fa-facebook text-primary', 'twitter' => 'fab fa-twitter text-info', 'telegram' => 'fab fa-telegram text-info', 'copy' => 'fas fa-copy text-secondary', 'link' => 'fas fa-link text-secondary', 'email' => 'fas fa-envelope text-warning', 'sms' => 'fas fa-sms text-success', 'other' => 'fas fa-share text-muted'];
                            $icon = $icons[$platform] ?? 'fas fa-share text-muted';
                            ?>
                            <i class="<?= $icon ?> me-1"></i><?= ucfirst($platform) ?>
                        </span>
                        <div class="flex-grow-1 mx-2">
                            <div class="progress style-87912">
                                <div class="progress-bar style-57855"></div>
                            </div>
                        </div>
                        <strong class="style-20402"><?= number_format($count) ?></strong>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top Sharers -->
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="m-0"><i class="fas fa-users me-2"></i>Top Sharers</h5></div>
                <div class="card-body">
                    <?php if (empty($top_sharers)): ?>
                    <div class="text-center py-4 text-muted">No sharers yet</div>
                    <?php else: ?>
                    <?php foreach ($top_sharers as $i => $sharer): ?>
                    <div class="d-flex align-items-center mb-3">
                        <div class="me-3">
                            <?php if ($i < 3): ?>
                            <span class="style-30322"><?= $i === 0 ? 'ðŸ¥‡' : ($i === 1 ? 'ðŸ¥ˆ' : 'ðŸ¥‰') ?></span>
                            <?php else: ?>
                            <span class="badge bg-light text-dark">#<?= $i + 1 ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-grow-1">
                            <strong><?= htmlspecialchars($sharer['name'] ?? '') ?></strong>
                            <br><small class="text-muted"><?= number_format($sharer['total_shares'] ?? 0) ?> shares</small>
                        </div>
                        <div>
                            <?php foreach (array_slice($sharer['platforms'] ?? [], 0, 3, true) as $p => $c): ?>
                            <span class="badge bg-light text-dark me-1 style-68658"><?= ucfirst($p) ?>: <?= $c ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
