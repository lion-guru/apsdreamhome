<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon p"><i class="fas fa-users"></i></div>
            <div><div class="stat-label">Total Users</div><div class="stat-value"><?php echo number_format($stats['total_users'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon s"><i class="fas fa-building"></i></div>
            <div><div class="stat-label">Properties</div><div class="stat-value"><?php echo number_format($stats['total_properties'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon w"><i class="fas fa-bullseye"></i></div>
            <div><div class="stat-label">Total Leads</div><div class="stat-value"><?php echo number_format($stats['total_leads'] ?? 0); ?></div>
            <div class="stat-change"><i class="fas fa-arrow-up"></i> <?php echo $stats['new_leads_today'] ?? 0; ?> today</div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon i"><i class="fas fa-handshake"></i></div>
            <div><div class="stat-label">users</div><div class="stat-value"><?php echo number_format($stats['total_associates'] ?? 0); ?></div></div>
        </div>
    </div>
</div>
<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon u"><i class="fas fa-rupee-sign"></i></div>
            <div><div class="stat-label">Revenue (30 Days)</div><div class="stat-value">&#8377;<?php echo number_format($stats['revenue_month'] ?? 0, 2); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon p"><i class="fas fa-user-tie"></i></div>
            <div><div class="stat-label">users</div><div class="stat-value"><?php echo number_format($stats['total_employees'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon w"><i class="fas fa-file-contract"></i></div>
            <div><div class="stat-label">Pending Bookings</div><div class="stat-value"><?php echo number_format($stats['pending_bookings'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon s"><i class="fas fa-check-circle"></i></div>
            <div><div class="stat-label">System Status</div><div class="stat-value text-success">Online</div></div>
        </div>
    </div>
</div>
