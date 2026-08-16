<?php
$page_title = $page_title ?? __('auction_detail_title', [], 'Auction');
$page_heading = $page_heading ?? __('auction_detail_heading', [], 'Auction');
$content = $content ?? '';
$auction = $auction ?? [];
$bids = $bids ?? [];
$is_watching = $is_watching ?? false;
$has_deposit = $has_deposit ?? null;
ob_start();
?>
<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/auctions"><?= __('auction_breadcrumb', [], 'Auctions') ?></a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($auction['title'] ?? '') ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <?php if ($auction['image_url']): ?>
                    <img alt="" loading="lazy" src="<?= htmlspecialchars($auction['image_url'] ?? '') ?>" class="card-img-top" alt="<?= htmlspecialchars($auction['title'] ?? '') ?>" class="style-44644">
                <?php endif; ?>
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-<?= ['live'=>'danger','scheduled'=>'info','ended'=>'secondary','sold'=>'success','cancelled'=>'dark'][$auction['status']] ?? 'secondary' ?>">
                                <?= strtoupper($auction['status']) ?>
                            </span>
                            <h2 class="mt-2"><?= htmlspecialchars($auction['title'] ?? '') ?></h2>
                        </div>
                        <div class="text-end">
                            <small class="text-muted d-block"><?= __('auction_time_left', [], 'Time Left') ?></small>
                            <h3 class="text-danger" data-countdown="<?= date('c', strtotime($auction['ends_at'])) ?>">
                                <i class="fas fa-clock"></i> <span class="cd-text">—</span>
                            </h3>
                        </div>
                    </div>

                    <p class="lead"><?= htmlspecialchars($auction['description'] ?? '') ?></p>

                    <?php if ($auction['property_title']): ?>
                        <div class="bg-light p-3 rounded mb-3">
                            <h6 class="mb-2"><?= __('auction_property_details', [], 'Property Details') ?></h6>
                            <p class="mb-1"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($auction['property_address'] ?? '') ?>, <?= htmlspecialchars($auction['property_city'] ?? '') ?></p>
                            <?php if ($auction['area_sqft']): ?>
                                <p class="mb-0"><i class="fas fa-ruler-combined me-1"></i> <?= number_format($auction['area_sqft']) ?> sq ft</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block"><?= __('auction_starting_price', [], 'Starting Price') ?></small>
                            <strong>₹<?= number_format($auction['start_price']) ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block"><?= __('auction_current_bid', [], 'Current Bid') ?></small>
                            <strong class="text-success">₹<?= number_format($auction['current_bid'] ?? $auction['start_price']) ?></strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block"><?= __('auction_bid_increment', [], 'Bid Increment') ?></small>
                            <strong>₹<?= number_format($auction['bid_increment']) ?></strong>
                        </div>
                        <?php if ($auction['buy_now_price']): ?>
                            <div class="col-md-4">
                                <small class="text-muted d-block"><?= __('auction_buy_now', [], 'Buy Now') ?></small>
                                <strong class="text-primary">₹<?= number_format($auction['buy_now_price']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if ($auction['reserve_price']): ?>
                            <div class="col-md-4">
                                <small class="text-muted d-block"><?= __('auction_reserve', [], 'Reserve') ?></small>
                                <strong>₹<?= number_format($auction['reserve_price']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <div class="col-md-4">
                            <small class="text-muted d-block"><?= __('auction_total_bids', [], 'Total Bids') ?></small>
                            <strong><?= $auction['bid_count'] ?></strong>
                        </div>
                    </div>

                    <?php if ($auction['status'] === 'live' && strtotime($auction['ends_at']) > time()): ?>
                        <div class="card bg-light border-0">
                            <div class="card-body aps-cp-card-body">
                                <?php if ($auction['deposit_amount'] && !$has_deposit): ?>
                                    <div class="alert alert-warning">
                                        <i class="fas fa-info-circle me-1"></i>
                                        A deposit of <strong>₹<?= number_format($auction['deposit_amount']) ?></strong> <?= __('auction_deposit_required', [], 'is required to bid.') ?>
                                         <button class="btn btn-sm btn-warning ms-2" id="depositBtn"><?= __('auction_pay_deposit', [], 'Pay Deposit') ?></button>
                                    </div>
                                <?php endif; ?>

                                <?php if (!$auction['deposit_amount'] || $has_deposit): ?>
                                    <h6 class="mb-2"><?= __('auction_place_bid', [], 'Place Your Bid') ?></h6>
                                    <form id="bidForm" class="d-flex gap-2">
    <?php echo CSRFProtection::csrfField(); ?>
                                        <input type="hidden" name="auction_id" value="<?= $auction['id'] ?>">
                                        <input type="number" name="amount" id="bidAmount" class="form-control" step="0.01" min="<?= ($auction['current_bid'] ?? $auction['start_price']) + $auction['bid_increment'] ?>" placeholder="Enter bid amount" required>
                                         <button type="submit" class="btn btn-primary"><i class="fas fa-gavel me-1"></i> <?= __('auction_bid_btn', [], 'Bid') ?></button>
                                    </form>
                                    <small class="text-muted"><?= __('auction_minimum_bid', [], 'Minimum bid:') ?> ₹<?= number_format(($auction['current_bid'] ?? $auction['start_price']) + $auction['bid_increment']) ?></small>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="fas fa-info-circle me-1"></i>
                            This auction is <?= strtolower($auction['status']) ?>. <?= __('auction_bidding_closed', [], 'Bidding is closed.') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><?= __('auction_bid_history', [], 'Bid History') ?></h6>
                </div>
                <div class="card-body aps-cp-card-body" class="style-97603" id="bidHistory">
                    <?php if (empty($bids)): ?>
                        <p class="text-muted text-center"><?= __('auction_no_bids', [], 'No bids yet. Be the first!') ?></p>
                    <?php else: ?>
                        <?php foreach ($bids as $b): ?>
                            <div class="d-flex justify-content-between border-bottom py-2">
                                <div>
                                    <strong><?= htmlspecialchars($b['bidder_name'] ?? '') ?></strong>
                                    <br><small class="text-muted"><?= date('M j, H:i', strtotime($b['placed_at'])) ?></small>
                                </div>
                                <div class="text-end">
                                    <strong>₹<?= number_format($b['bid_amount']) ?></strong>
                                    <?php if ($b['status'] === 'winning'): ?>
                                        <br><span class="badge bg-success"><?= __('auction_winning', [], 'Winning') ?></span>
                                    <?php elseif ($b['status'] === 'outbid'): ?>
                                        <br><span class="badge bg-secondary"><?= __('auction_outbid', [], 'Outbid') ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <button class="btn btn-<?= $is_watching ? 'secondary' : 'outline-primary' ?> w-100" id="watchBtn">
                        <i class="fas fa-eye me-1"></i>
                        <?= $is_watching ? __('auction_watching', [], 'Watching') : __('auction_watch', [], 'Watch Auction') ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const auctionId = <?= $auction['id'] ?>;
function updateCountdown() {
    const el = document.querySelector('[data-countdown] .cd-text');
    if (!el) return;
    const target = new Date(document.querySelector('[data-countdown]').dataset.countdown).getTime();
    const diff = target - Date.now();
    if (diff <= 0) { el.textContent = 'ENDED'; return; }
    const h = Math.floor(diff / 3600000);
    const m = Math.floor((diff % 3600000) / 60000);
    const s = Math.floor((diff % 60000) / 1000);
    el.textContent = (h > 0 ? h + 'h ' : '') + m + 'm ' + s + 's';
}
setInterval(updateCountdown, 1000);
updateCountdown();

document.getElementById('bidForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const r = await fetch('<?= BASE_URL ?>/auctions/bid', { method: 'POST', body: formData, credentials: 'same-origin' });
    const data = await r.json();
    if (data.error) {
        alert('Ã¢Â�Å’ ' + data.error);
    } else {
        alert('Ã¢Å“â€¦ Bid placed! ₹' + data.amount);
        location.reload();
    }
});

document.getElementById('depositBtn')?.addEventListener('click', async function() {
    const formData = new FormData();
    formData.append('auction_id', auctionId);
    const r = await fetch('<?= BASE_URL ?>/auctions/deposit', { method: 'POST', body: formData, credentials: 'same-origin' });
    const data = await r.json();
    if (data.error) {
        alert('Ã¢Â�Å’ ' + data.error);
    } else {
        alert('Ã¢Å“â€¦ Deposit paid! You can now bid.');
        location.reload();
    }
});

document.getElementById('watchBtn')?.addEventListener('click', async function() {
    const formData = new FormData();
    formData.append('auction_id', auctionId);
    const endpoint = this.classList.contains('btn-outline-primary') ? '<?= BASE_URL ?>/auctions/watch' : '<?= BASE_URL ?>/auctions/unwatch';
    const r = await fetch(endpoint, { method: 'POST', body: formData, credentials: 'same-origin' });
    location.reload();
});
</script>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/base.php';
