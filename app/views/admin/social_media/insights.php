ï»¿<?php
$page_title = $page_title ?? 'Insights';
$account = $account ?? [];
$insights = $insights ?? [];
$period = $period ?? '7d';
$csrf = $_SESSION['csrf_token'] ?? '';
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chart-line me-2"></i>Insights â€” <?= htmlspecialchars($account['account_name'] ?? '') ?></h2>
    <a href="<?= BASE_URL ?>/admin/social-media" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Accounts</a>
</div>

<div class="row mb-3">
    <div class="col-12">
        <div class="btn-group" role="group">
            <?php foreach (['1d' => '24h', '7d' => '7 Days', '30d' => '30 Days', '90d' => '90 Days'] as $p => $label): ?>
                <a href="?period=<?= $p ?>" class="btn btn-<?= $period === $p ? 'primary' : 'outline-primary' ?>"><?= $label ?></a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-uppercase small">Total Leads</h6>
                <h3 class="mb-0"><?= number_format($insights['total_leads'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h6 class="text-uppercase small">New Leads</h6>
                <h3 class="mb-0"><?= number_format($insights['new_leads'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-uppercase small">Converted</h6>
                <h3 class="mb-0"><?= number_format($insights['converted'] ?? 0) ?></h3>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-uppercase small">Conversion Rate</h6>
                <h3 class="mb-0"><?= $insights['conversion_rate'] ?? 0 ?>%</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Leads Trend (<?= $period ?>)</h5></div>
            <div class="card-body">
                <?php
                $trend = $insights['leads_trend'] ?? [];
                $counts = array_map(fn($t) => (int)($t['count'] ?? 0), $trend);
                $max = max(1, ...(empty($counts) ? [1] : $counts));
                ?>
                <?php if (empty($trend)): ?>
                    <p class="text-muted text-center py-4">No lead activity in this period.</p>
                <?php else: ?>
                    <?php foreach ($trend as $t): ?>
                        <div class="d-flex align-items-center mb-2">
                            <div class="style-11044" class="small text-muted"><?= date('M d', strtotime($t['date'])) ?></div>
                            <div class="flex-grow-1 mx-2">
                                <div class="progress" class="style-43706">
                                    <div class="progress-bar bg-primary" class="style-11521"><?= $t['count'] ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Leads by Status</h5></div>
            <div class="card-body">
                <?php foreach (($insights['leads_by_status'] ?? []) as $s): ?>
                    <div class="d-flex justify-content-between border-bottom py-2">
                        <span class="text-capitalize"><?= ucfirst($s['status']) ?></span>
                        <span class="badge bg-secondary"><?= $s['count'] ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($insights['leads_by_status'])): ?>
                    <p class="text-muted text-center py-3">No data.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
