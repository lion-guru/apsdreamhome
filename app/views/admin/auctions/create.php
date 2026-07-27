<?php
$page_title = $page_title ?? 'Create Auction';
$page_heading = $page_heading ?? 'Create New Auction';
$content = $content ?? '';
$properties = $properties ?? [];
ob_start();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Create New Auction</h2>
        <a href="<?= BASE_URL ?>/admin/auctions" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
    <form method="POST" action="<?= BASE_URL ?>/admin/auctions/store">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <div class="card border-0 shadow-sm">
            <div class="card-body aps-cp-card-body">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Auction Type</label>
                        <select name="auction_type" class="form-select">
                            <option value="english">English (ascending)</option>
                            <option value="sealed">Sealed Bid</option>
                            <option value="dutch">Dutch (descending)</option>
                            <option value="reserve">Reserve</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Property (optional)</label>
                        <select name="property_id" class="form-select">
                            <option value="">-- Not linked --</option>
                            <?php foreach ($properties as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['title']) ?> - <?= htmlspecialchars($p['address']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Image URL</label>
                        <input type="url" name="image_url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Start Price (₹) <span class="text-danger">*</span></label>
                        <input type="number" name="start_price" class="form-control" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Reserve Price (₹)</label>
                        <input type="number" name="reserve_price" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bid Increment (₹)</label>
                        <input type="number" name="bid_increment" class="form-control" step="0.01" value="1000" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Buy Now Price (₹)</label>
                        <input type="number" name="buy_now_price" class="form-control" step="0.01" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Deposit Required (₹)</label>
                        <input type="number" name="deposit_amount" class="form-control" step="0.01" min="0" placeholder="0 for none">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Auto-Extend Threshold (sec)</label>
                        <input type="number" name="extension_seconds" class="form-control" value="60" min="0">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Starts At</label>
                        <input type="datetime-local" name="starts_at" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Ends At</label>
                        <input type="datetime-local" name="ends_at" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="draft">Draft</option>
                            <option value="scheduled" selected>Scheduled</option>
                            <option value="live">Live (start now)</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Terms & Conditions</label>
                        <textarea name="terms" class="form-control" rows="3"></textarea>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-white text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Create Auction</button>
            </div>
        </div>
    </form>
</div>
<?php
$content = ob_get_clean();
include APP_PATH . '/views/layouts/admin.php';
