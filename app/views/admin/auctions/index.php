<?php
$page_title = $page_title ?? 'Auctions';
$page_heading = $page_heading ?? 'Property Auctions';
$content = $content ?? '';
$auctions = $auctions ?? [];
$stats = $stats ?? [];
$current_status = $current_status ?? null;
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Property Auctions</h2>
            <p class="text-muted mb-0">Manage live and scheduled property auctions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/admin/auctions/process-ending" class="btn btn-outline-info" onclick="return confirm('End all expired auctions now?')">
                <i class="fas fa-cogs me-1"></i> Process Ending
            </a>
            <a href="<?= BASE_URL ?>/admin/auctions/create" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Create Auction
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Live</p>
                    <h3 class="text-danger"><?= $stats['live'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Scheduled</p>
                    <h3 class="text-info"><?= $stats['scheduled'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Sold</p>
                    <h3 class="text-success"><?= $stats['sold'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Total Bids</p>
                    <h3><?= number_format($stats['total_bids'] ?? 0) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Bidders</p>
                    <h3><?= $stats['unique_bidders'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted small mb-1">Sold Value</p>
                    <h3 class="text-success">₹<?= number_format(($stats['total_value'] ?? 0) / 100000, 1) ?>L</h3>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-pills mb-3">
        <li class="nav-item"><a class="nav-link <?= !$current_status ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/auctions">All</a></li>
        <?php foreach (['live','scheduled','sold','ended','cancelled'] as $st): ?>
            <li class="nav-item"><a class="nav-link <?= $current_status === $st ? 'active' : '' ?>" href="<?= BASE_URL ?>/admin/auctions?status=<?= $st ?>"><?= ucfirst($st) ?></a></li>
        <?php endforeach; ?>
    </ul>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Current Bid</th>
                            <th>Bids</th>
                            <th>Watchers</th>
                            <th>Ends</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($auctions)): ?>
                            <tr><td colspan="9" class="text-center text-muted py-4"><i class="fas fa-inbox fa-2x d-block mb-2 text-muted" aria-hidden="true"></i>No auctions yet</td></tr>
                        <?php else: ?>
                            <?php foreach ($auctions as $a): ?>
                                <tr>
                                    <td>#<?= $a['id'] ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($a['title']) ?></strong>
                                        <?php if ($a['property_title']): ?>
                                            <br><small class="text-muted"><?= htmlspecialchars($a['property_title']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-light text-dark"><?= ucfirst($a['auction_type']) ?></span></td>
                                    <td>
                                        <span class="badge bg-<?= ['live'=>'danger','scheduled'=>'info','ended'=>'secondary','sold'=>'success','cancelled'=>'dark','draft'=>'secondary','paused'=>'warning'][$a['status']] ?? 'secondary' ?>">
                                            <?= ucfirst($a['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <strong class="text-success">₹<?= number_format($a['current_bid'] ?? $a['start_price']) ?></strong>
                                        <?php if ($a['winning_bid']): ?>
                                            <br><small>Won: ₹<?= number_format($a['winning_bid']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $a['bid_count'] ?></td>
                                    <td><?= $a['watcher_count'] ?></td>
                                    <td><small><?= date('M j, H:i', strtotime($a['ends_at'])) ?></small></td>
                                    <td>
                                        <a href="<?= BASE_URL ?>/admin/auctions/show/<?= $a['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                        <?php if ($a['status'] === 'scheduled' || $a['status'] === 'draft'): ?>
                                            <a href="<?= BASE_URL ?>/admin/auctions/start/<?= $a['id'] ?>" class="btn btn-sm btn-outline-success" onclick="return confirm('Start this auction?')"><i class="fas fa-play"></i></a>
                                        <?php endif; ?>
                                        <?php if ($a['status'] === 'live'): ?>
                                            <a href="<?= BASE_URL ?>/admin/auctions/end/<?= $a['id'] ?>" class="btn btn-sm btn-outline-warning" onclick="return confirm('End this auction now?')"><i class="fas fa-stop"></i></a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/admin/auctions/delete/<?= $a['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this auction and all bids?')"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/admin.php';
