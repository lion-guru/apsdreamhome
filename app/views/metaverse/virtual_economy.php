<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>metaverse">Metaverse</a></li>
                    <li class="breadcrumb-item active">Economy</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-coins me-3 text-warning"></i><?= ($page_title ?? 'Virtual Economy') ?></h1>
        </div>
    </div>

    <?php $economy_data = $economy_data ?? []; ?>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <i class="fas fa-money-bill-wave fa-3x text-success mb-3"></i>
                    <h5>Currency</h5>
                    <h3 class="text-success"><?= ($economy_data['virtual_currency'] ?? 'VRC') ?></h3>
                    <small class="text-muted"><?= ($economy_data['exchange_rate'] ?? '') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <i class="fas fa-chart-line fa-3x text-primary mb-3"></i>
                    <h5>Market Cap</h5>
                    <h3 class="text-primary"><?= ($economy_data['market_cap'] ?? '₹0') ?></h3>
                    <small class="text-muted">Daily Volume: <?= ($economy_data['daily_volume'] ?? '₹0') ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-center mb-2">
                        <i class="fas fa-coins fa-3x text-warning"></i>
                    </div>
                    <h5>Your Wallet</h5>
                    <a href="<?= $base ?? BASE_URL ?>wallet" class="btn btn-warning"><i class="fas fa-wallet me-2"></i>View Wallet</a>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0">
            <h5 class="mb-0"><i class="fas fa-star me-2 text-warning"></i>Top Traded Assets</h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="table-responsive">
                <div class="table-responsive"><table class="table table-hover table-responsive">
                    <thead class="table-light">
                        <tr>
                            <th>Asset Type</th>
                            <th>Volume</th>
                            <th>Change</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (($economy_data['top_traded_assets'] ?? []) as $asset): ?>
                        <tr>
                            <td><?= ($asset['name'] ?? '') ?></td>
                            <td><?= ($asset['volume'] ?? '₹0') ?></td>
                            <td class="text-<?= (strpos($asset['change'] ?? '', '+') === 0) ? 'success' : 'danger' ?>"><?= ($asset['change'] ?? '0%') ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($economy_data['top_traded_assets'] ?? [])): ?>
                        <tr><td colspan="3" class="text-center text-muted py-3">No trading data available.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table></div>
            </div>
        </div>
    </div>
</div>
