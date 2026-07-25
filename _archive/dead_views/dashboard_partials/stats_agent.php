<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon p"><i class="fas fa-bullseye"></i></div>
            <div><div class="stat-label">My Leads</div><div class="stat-value"><?php echo number_format($stats['my_leads'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon s"><i class="fas fa-check-circle"></i></div>
            <div><div class="stat-label">Conversions</div><div class="stat-value"><?php echo number_format($stats['conversions'] ?? 0); ?></div>
            <div class="stat-change"><?php echo $stats['conversion_rate'] ?? 0; ?>% rate</div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon w"><i class="fas fa-building"></i></div>
            <div><div class="stat-label">Properties Sold</div><div class="stat-value"><?php echo number_format($stats['properties_sold'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon i"><i class="fas fa-rupee-sign"></i></div>
            <div><div class="stat-label">My Earnings</div><div class="stat-value">&#8377;<?php echo number_format($stats['earnings'] ?? 0, 2); ?></div></div>
        </div>
    </div>
</div>
