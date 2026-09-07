<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="<?= $base ?? BASE_URL ?>admin">Admin</a></li>
                    <li class="breadcrumb-item active">Metaverse Analytics</li>
                </ol>
            </nav>
            <h1 class="display-5 fw-bold"><i class="fas fa-chart-bar me-3 text-primary"></i><?= ($page_title ?? 'Metaverse Analytics') ?></h1>
        </div>
    </div>

    <?php $analytics = $analytics ?? []; $vr_engagement = $analytics['vr_engagement'] ?? []; $virtual_property_sales = $analytics['virtual_property_sales'] ?? []; $metaverse_events = $analytics['metaverse_events'] ?? []; $nft_marketplace = $analytics['nft_marketplace'] ?? []; ?>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-vr-cardboard me-2 text-primary"></i>VR Engagement</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Total Tours</small><strong><?= ($vr_engagement['total_vr_tours'] ?? 0) ?></strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Avg Session</small><strong><?= ($vr_engagement['avg_session_duration'] ?? 'N/A') ?></strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Completion Rate</small><strong><?= ($vr_engagement['tour_completion_rate'] ?? '0%') ?></strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Conversion Rate</small><strong><?= ($vr_engagement['conversion_rate'] ?? '0%') ?></strong></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-shopping-cart me-2 text-success"></i>Virtual Property Sales</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Total Sales</small><strong><?= ($virtual_property_sales['total_sales'] ?? 0) ?></strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Total Volume</small><strong><?= number_format($virtual_property_sales['total_volume'] ?? 0) ?> VRC</strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Avg Sale Price</small><strong><?= number_format($virtual_property_sales['avg_sale_price'] ?? 0) ?> VRC</strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Monthly Growth</small><strong class="text-success"><?= ($virtual_property_sales['monthly_growth'] ?? '0%') ?></strong></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-calendar-alt me-2 text-warning"></i>Metaverse Events</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Total Events</small><strong><?= ($metaverse_events['total_events'] ?? 0) ?></strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Total Attendees</small><strong><?= number_format($metaverse_events['total_attendees'] ?? 0) ?></strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Avg Attendance</small><strong><?= ($metaverse_events['avg_attendance'] ?? 0) ?></strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Satisfaction</small><strong><?= ($metaverse_events['event_satisfaction'] ?? 'N/A') ?></strong></div></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0"><h5 class="mb-0"><i class="fas fa-certificate me-2 text-info"></i>NFT Marketplace</h5></div>
                <div class="card-body aps-cp-card-body">
                    <div class="row g-3">
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Total NFTs</small><strong><?= number_format($nft_marketplace['total_nfts'] ?? 0) ?></strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Tokenized Properties</small><strong><?= number_format($nft_marketplace['properties_tokenized'] ?? 0) ?></strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Trading Volume</small><strong><?= number_format($nft_marketplace['trading_volume'] ?? 0) ?> VRC</strong></div></div>
                        <div class="col-6"><div class="border rounded p-3 text-center"><small class="text-muted d-block">Avg NFT Price</small><strong><?= number_format($nft_marketplace['avg_nft_price'] ?? 0) ?> VRC</strong></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
