<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fas fa-ad me-2"></i>Ad Manager</h1>
        <div>
            <a href="<?= BASE_URL ?>/admin/ads/settings" class="btn btn-outline-info me-2"><i class="fab fa-google me-1"></i>AdSense Settings</a>
            <a href="<?= BASE_URL ?>/admin/ads/create" class="btn btn-primary"><i class="fas fa-plus me-1"></i>New Ad Slot</a>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>"><?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?><?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-3"><div class="card bg-primary text-white"><div class="card-body aps-cp-card-body"><h6>Total Slots</h6><h3><?= $summary['total'] ?? 0 ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-success text-white"><div class="card-body aps-cp-card-body"><h6>Active</h6><h3><?= $summary['active'] ?? 0 ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-info text-white"><div class="card-body aps-cp-card-body"><h6>Total Views</h6><h3><?= number_format($summary['total_views'] ?? 0) ?></h3></div></div></div>
        <div class="col-md-3"><div class="card bg-warning text-dark"><div class="card-body aps-cp-card-body"><h6>Total Clicks</h6><h3><?= number_format($summary['total_clicks'] ?? 0) ?></h3></div></div></div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="table-dark">
                        <tr><th>ID</th><th>Slot Key</th><th>Title</th><th>Type</th><th>Status</th><th>Views</th><th>Clicks</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($slots)): ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">No ad slots yet. <a href="<?= BASE_URL ?>/admin/ads/create">Create your first ad slot</a></td></tr>
                        <?php else: ?>
                            <?php foreach ($slots as $s): ?>
                                <tr>
                                    <td><?= $s['id'] ?></td>
                                    <td><code><?= htmlspecialchars($s['slot_key']) ?></code></td>
                                    <td><?= htmlspecialchars($s['title']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($s['slot_type']) ?></span></td>
                                    <td><span class="badge bg-<?= $s['status'] === 'active' ? 'success' : 'danger' ?>"><?= $s['status'] ?></span></td>
                                    <td><?= number_format($s['views'] ?? 0) ?></td>
                                    <td><?= number_format($s['clicks'] ?? 0) ?></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/ads/edit/<?= $s['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <a href="<?= BASE_URL ?>/admin/ads/delete/<?= $s['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this ad slot?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header aps-cp-card-header"><h5 class="mb-0">How to Use Ad Slots</h5></div>
        <div class="card-body aps-cp-card-body">
            <p>Use the following PHP code in any view file to display ads:</p>
            <pre class="bg-light p-3 rounded"><code>&lt;?php
$adManager = new \App\Services\AdManagerService();
echo $adManager->renderSlot('slot_key_name');
?&gt;</code></pre>
            <p>Or use in the header/footer layout:</p>
            <pre class="bg-light p-3 rounded"><code>&lt;?= (new \App\Services\AdManagerService())->renderSlot('header_banner') ?&gt;</code></pre>
            <p class="text-muted small mt-2"><i class="fas fa-info-circle"></i> To add Google AdSense, paste the ad code in the "HTML Code" field when creating an ad slot.</p>
        </div>
    </div>
</div>
