<?php
$page_title = $page_title ?? 'Auction';
$page_heading = $page_heading ?? 'Auction Details';
$content = $content ?? '';
$auction = $auction ?? [];
$bids = $bids ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1"><?= htmlspecialchars($auction['title']) ?></h2>
            <p class="text-muted mb-0">
                <span class="badge bg-<?= ['live'=>'danger','scheduled'=>'info','ended'=>'secondary','sold'=>'success','cancelled'=>'dark'][$auction['status']] ?? 'secondary' ?>">
                    <?= strtoupper($auction['status']) ?>
                </span>
                · Type: <?= ucfirst($auction['auction_type']) ?>
                · <?= $auction['bid_count'] ?> bids
            </p>
        </div>
        <a href="<?= BASE_URL ?>/admin/auctions" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>

    <div class="row g-3">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <h6 class="mb-3">Auction Details</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <small class="text-muted d-block">Start Price</small>
                            <strong>₹<?= number_format($auction['start_price']) ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Current Bid</small>
                            <strong class="text-success">₹<?= number_format($auction['current_bid'] ?? $auction['start_price']) ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Bid Increment</small>
                            <strong>₹<?= number_format($auction['bid_increment']) ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Buy Now Price</small>
                            <strong><?= $auction['buy_now_price'] ? '₹' . number_format($auction['buy_now_price']) : '—' ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Deposit Required</small>
                            <strong><?= $auction['deposit_amount'] ? '₹' . number_format($auction['deposit_amount']) : 'None' ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Reserve Price</small>
                            <strong><?= $auction['reserve_price'] ? '₹' . number_format($auction['reserve_price']) : '—' ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Starts</small>
                            <strong><?= date('M j, Y H:i', strtotime($auction['starts_at'])) ?></strong>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted d-block">Ends</small>
                            <strong><?= date('M j, Y H:i', strtotime($auction['ends_at'])) ?></strong>
                        </div>
                    </div>

                    <?php if ($auction['description']): ?>
                        <hr>
                        <h6>Description</h6>
                        <p class="text-muted"><?= nl2br(htmlspecialchars($auction['description'])) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Bid History (<?= count($bids) ?>)</h6>
                </div>
                <div class="card-body aps-cp-card-body" style="max-height: 500px; overflow-y: auto;">
                    <?php if (empty($bids)): ?>
                        <p class="text-muted text-center small">No bids yet</p>
                    <?php else: ?>
                        <?php foreach ($bids as $b): ?>
                            <div class="border-bottom py-2">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong><?= htmlspecialchars($b['bidder_name']) ?></strong>
                                        <br><small class="text-muted"><?= date('M j, H:i', strtotime($b['placed_at'])) ?></small>
                                    </div>
                                    <div class="text-end">
                                        <strong>₹<?= number_format($b['bid_amount']) ?></strong>
                                        <br><span class="badge bg-<?= ['winning'=>'success','outbid'=>'secondary','won'=>'success','lost'=>'dark'][$b['status']] ?? 'secondary' ?>" style="font-size: 0.65rem;"><?= ucfirst($b['status']) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/admin/layouts/admin.php';
