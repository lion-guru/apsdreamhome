<?php
$page_title = $page_title ?? 'Property Auctions';
$page_heading = $page_heading ?? 'Property Auctions';
$content = $content ?? '';
$live = $live ?? [];
$upcoming = $upcoming ?? [];
$closed = $closed ?? [];
ob_start();
?>
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">Property Auctions</h1>
            <p class="text-muted mb-0">Bid on exclusive properties in real-time</p>
        </div>
    </div>

    <h3 class="mb-3"><i class="fas fa-bolt text-warning me-2"></i>Live Now</h3>
    <?php if (empty($live)): ?>
        <div class="alert alert-info">No live auctions right now. Check back soon!</div>
    <?php else: ?>
        <div class="row g-3 mb-5">
            <?php foreach ($live as $a):
                $remaining = strtotime($a['ends_at']) - time();
                $isEnding = $remaining < 3600;
            ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <?php if ($a['image_url']): ?>
                            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="card-img-top" alt="<?= htmlspecialchars($a['title']) ?>" style="height: 180px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center" style="height: 180px;">
                                <i class="fas fa-gavel fa-3x"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <span class="badge bg-danger mb-2">LIVE</span>
                            <h5 class="card-title"><?= htmlspecialchars($a['title']) ?></h5>
                            <?php if ($a['property_title']): ?>
                                <p class="text-muted small mb-1"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($a['property_address'] ?? $a['property_city']) ?></p>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between my-3">
                                <div>
                                    <small class="text-muted d-block">Current Bid</small>
                                    <strong class="text-success">₹<?= number_format($a['current_bid'] ?? $a['start_price']) ?></strong>
                                </div>
                                <div>
                                    <small class="text-muted d-block">Bids</small>
                                    <strong><?= $a['bid_count'] ?></strong>
                                </div>
                            </div>
                            <div class="text-center mb-2 <?= $isEnding ? 'text-danger' : 'text-muted' ?>">
                                <i class="fas fa-clock"></i>
                                <small data-countdown="<?= date('c', strtotime($a['ends_at'])) ?>">
                                    Ends: <?= date('M j, H:i', strtotime($a['ends_at'])) ?>
                                </small>
                            </div>
                            <a href="<?= BASE_URL ?>/auctions/<?= $a['id'] ?>" class="btn btn-primary w-100">View & Bid</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3 class="mb-3"><i class="fas fa-calendar text-info me-2"></i>Upcoming Auctions</h3>
    <?php if (empty($upcoming)): ?>
        <p class="text-muted">No upcoming auctions scheduled.</p>
    <?php else: ?>
        <div class="row g-3 mb-5">
            <?php foreach ($upcoming as $a): ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <span class="badge bg-info mb-2">SCHEDULED</span>
                            <h6><?= htmlspecialchars($a['title']) ?></h6>
                            <p class="text-muted small">Starts: <?= date('M j, Y H:i', strtotime($a['starts_at'])) ?></p>
                            <p class="mb-2"><strong>Start: ₹<?= number_format($a['start_price']) ?></strong></p>
                            <a href="<?= BASE_URL ?>/auctions/<?= $a['id'] ?>" class="btn btn-outline-primary btn-sm w-100">View Details</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3 class="mb-3"><i class="fas fa-trophy text-warning me-2"></i>Recent Winners</h3>
    <?php if (empty($closed)): ?>
        <p class="text-muted">No closed auctions yet.</p>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($closed as $a): ?>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <span class="badge bg-success mb-2">SOLD</span>
                            <h6><?= htmlspecialchars($a['title']) ?></h6>
                            <p class="text-success mb-0"><strong>Final: ₹<?= number_format($a['winning_bid'] ?? 0) ?></strong></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
function updateCountdowns() {
    document.querySelectorAll('[data-countdown]').forEach(el => {
        const target = new Date(el.dataset.countdown).getTime();
        const now = Date.now();
        const diff = target - now;
        if (diff <= 0) {
            el.innerHTML = '<strong>ENDED</strong>';
            el.classList.add('text-danger');
            return;
        }
        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        el.innerHTML = h > 24 ? Math.floor(h/24) + 'd ' + (h%24) + 'h' : (h > 0 ? h + 'h ' : '') + m + 'm ' + s + 's';
        if (diff < 3600000) el.classList.add('text-danger');
    });
}
setInterval(updateCountdowns, 1000);
updateCountdowns();
</script>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/base.php';
