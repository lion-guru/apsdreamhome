<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>"><i class="fas fa-home"></i></a></li>
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>sustainability">Sustainability</a></li>
                    <li class="breadcrumb-item active">Green Finance</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-coins me-3 text-success"></i><?= ($page_title ?? 'Green Finance') ?></h1>
        </div>
    </div>

    <?php $fd = $finance_data ?? []; $bonds = $fd['green_bonds'] ?? []; $investments = $fd['sustainable_investments'] ?? []; $credits = $fd['carbon_credits'] ?? []; $impact = $fd['impact_investing'] ?? []; ?>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-hand-holding-usd me-2 text-success"></i>Green Bonds</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Issued Bonds</span><strong>₹<?= ($bonds['issued_bonds'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Projects Funded</span><strong><?= ($bonds['green_projects_funded'] ?? 0) ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Interest Rate</span><strong><?= ($bonds['interest_rate'] ?? '0%') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Maturity</span><strong><?= ($bonds['maturity_period'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Oversubscribed</span><strong class="text-success">₹<?= ($bonds['investor_interest'] ?? '0') ?></strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-chart-pie me-2 text-primary"></i>Sustainable Investments</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Total AUM</span><strong>₹<?= ($investments['total_aum'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Sustainable Properties</span><strong>₹<?= ($investments['sustainable_properties'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Green Tech</span><strong>₹<?= ($investments['green_technologies'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Renewable Energy</span><strong>₹<?= ($investments['renewable_energy'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Annual Returns</span><strong class="text-success"><?= ($investments['annual_returns'] ?? '0%') ?></strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-cloud me-2 text-info"></i>Carbon Credits</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Credits Generated</span><strong><?= ($credits['credits_generated'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Credits Sold</span><strong><?= ($credits['credits_sold'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Market Value</span><strong>₹<?= ($credits['market_value'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Platform</span><strong><?= ($credits['trading_platform'] ?? 'N/A') ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Standard</span><strong><?= ($credits['verification_standard'] ?? 'N/A') ?></strong></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-heart me-2 text-danger"></i>Impact Investing</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between mb-2"><span>Impact Funds</span><strong>₹<?= ($impact['impact_funds'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Social Impact</span><strong><?php $si = $impact['social_impact'] ?? 0; echo is_numeric($si) ? number_format($si) : htmlspecialchars($si); ?> beneficiaries</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Environmental Impact</span><strong><?= ($impact['environmental_impact'] ?? '0') ?></strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>Returns</span><strong class="text-success"><?= ($impact['financial_returns'] ?? '0%') ?></strong></div>
                    <div class="d-flex justify-content-between"><span>Framework</span><strong><?= ($impact['measuring_framework'] ?? 'UN SDG') ?></strong></div>
                </div>
            </div>
        </div>
    </div>
</div>
