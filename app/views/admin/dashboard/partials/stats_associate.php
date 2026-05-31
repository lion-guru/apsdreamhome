<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon p"><i class="fas fa-sitemap"></i></div>
            <div><div class="stat-label">My Team</div><div class="stat-value"><?php echo number_format($stats['team_size'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon s"><i class="fas fa-rupee-sign"></i></div>
            <div><div class="stat-label">Total Commission</div><div class="stat-value">&#8377;<?php echo number_format($stats['total_commission'] ?? 0, 2); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon w"><i class="fas fa-gift"></i></div>
            <div><div class="stat-label">Referrals</div><div class="stat-value"><?php echo number_format($stats['referrals'] ?? 0); ?></div></div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card stat-card">
            <div class="stat-icon i"><i class="fas fa-trophy"></i></div>
            <div><div class="stat-label">My Rank</div><div class="stat-value"><?php echo htmlspecialchars($stats['rank'] ?? 'Bronze'); ?></div></div>
        </div>
    </div>
</div>
