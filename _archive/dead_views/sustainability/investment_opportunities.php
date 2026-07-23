<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Investment Opportunities</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-chart-line me-3 text-success"></i><?= ($page_title ?? 'Investment Opportunities') ?></h1>
        </div>
    </div>

    <?php $id = $investment_data ?? []; $gb = $id['green_bonds'] ?? []; $sp = $id['sustainable_properties'] ?? []; $gt = $id['green_technologies'] ?? []; ?>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2 text-success"></i>Green Bonds</h5></div>
                <div class="card-body aps-cp-card-body">
                    <h3 class="text-primary mb-3">₹<?= ($gb['total_issued'] ?? '0') ?></h3>
                    <div class="d-flex justify-content-between mb-2"><span>Returns</span><strong class="text-success"><?= ($gb['investor_returns'] ?? '0%') ?></strong></div>
                    <div class="mb-2"><small class="text-muted d-block">Maturities:</small><?php foreach (($gb['maturity_periods'] ?? []) as $m): ?><span class="badge bg-secondary me-1"><?= htmlspecialchars($m, ENT_QUOTES, 'UTF-8') ?></span><?php endforeach; ?></div>
                    <small class="text-muted">Use: <?= implode(', ', $gb['use_of_proceeds'] ?? []) ?></small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-building me-2 text-info"></i>Sustainable Properties</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Available</span><strong><?= ($sp['properties_available'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Avg Premium</span><strong>₹<?php $ap = $sp['avg_premium'] ?? 0; echo is_numeric($ap) ? number_format($ap) : htmlspecialchars($ap); ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Rental Yield</span><strong class="text-success"><?= ($sp['rental_yield'] ?? '0%') ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Appreciation</span><strong class="text-success"><?= ($sp['appreciation_rate'] ?? '0%') ?></strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-microchip me-2 text-warning"></i>Green Technologies</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Investment Required</span><strong>₹<?= ($gt['investment_required'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Expected Returns</span><strong class="text-success"><?= ($gt['expected_returns'] ?? '0%') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Risk Level</span><strong><?= ($gt['risk_level'] ?? 'N/A') ?></strong></div>
                    <hr>
                    <small class="text-muted">Technologies: <?= implode(', ', $gt['technologies'] ?? []) ?></small>
                </div>
            </div>
        </div>
    </div>
</div>
