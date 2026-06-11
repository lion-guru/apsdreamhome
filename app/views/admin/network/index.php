<?php $pageTitle = 'Network Overview'; ?>
<div class="container-fluid">
    <div class="page-header mb-4">
        <div class="row align-items-center">
            <div class="col">
                <h3 class="page-title"><i class="fas fa-network-wired me-2"></i>Network Overview</h3>
                <ul class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Network</li>
                </ul>
            </div>
            <div class="col-auto">
                <a href="/admin/network/genealogy" class="btn btn-primary btn-sm"><i class="fas fa-sitemap me-1"></i>Genealogy</a>
                <a href="/admin/network/tree" class="btn btn-info btn-sm"><i class="fas fa-tree me-1"></i>Tree View</a>
            </div>
        </div>
    </div>
    <div class="row g-4 mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Members</h6><h3 class="mb-0"><?= number_format($totalMembers ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Active Members</h6><h3 class="mb-0"><?= number_format($activeMembers ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Total Teams</h6><h3 class="mb-0"><?= number_format($totalTeams ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-white border-0 shadow-sm"><div class="card-body aps-cp-card-body"><h6>Network Growth</h6><h3 class="mb-0">+<?= number_format($newMembers ?? 0) ?></h3><small>This month</small></div></div></div>
    </div>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3"><h5 class="mb-0"><i class="fas fa-list me-2"></i>Network Members</h5></div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light"><tr><th class="ps-4">#</th><th>Name</th><th>Sponsor</th><th>Level</th><th>Team Size</th><th>Status</th><th class="text-end pe-4">Joined</th></tr></thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted"><i class="fas fa-network-wired fa-3x d-block mb-3"></i>No network data available</td></tr>
                        <?php else: ?>
                            <?php foreach ($members as $i => $m): ?>
                            <tr><td class="ps-4"><?= $i+1 ?></td><td><strong><?= $m['name'] ?></strong></td><td><?= $m['sponsor_name'] ?? '—' ?></td><td><span class="badge bg-info-subtle text-info rounded-pill px-3">Level <?= $m['level'] ?? 1 ?></span></td><td><?= $m['team_size'] ?? 0 ?></td><td><span class="badge bg-<?= ($m['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?>-subtle text-<?= ($m['status'] ?? 'active') === 'active' ? 'success' : 'secondary' ?> rounded-pill px-3"><?= ucfirst($m['status'] ?? 'Active') ?></span></td><td class="text-end pe-4 small"><?= date('d M Y', strtotime($m['created_at'] ?? 'now')) ?></td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
