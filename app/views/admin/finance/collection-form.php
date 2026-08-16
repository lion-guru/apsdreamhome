ï»¿<?php
$page_title = $page_title ?? 'Record Collection';
$page_heading = $page_heading ?? 'Record Cash Collection';
$collectors = $collectors ?? [];
$bookings = $bookings ?? [];
?>
<div class="aps-cp-container">
    <div class="aps-cp-page-header">
        <h1 class="aps-cp-page-title"><?= htmlspecialchars($page_heading) ?></h1>
        <a href="<?= BASE_URL ?>/admin/finance/collections" class="aps-cp-btn aps-cp-btn-outline">
            <i class="fas fa-arrow-left"></i> Back to Collections
        </a>
    </div>

    <div class="aps-cp-card" class="style-65536">
        <div class="aps-cp-card-header">
            <span><i class="fas fa-edit"></i> Collection Details</span>
        </div>
        <div class="aps-cp-card-body">
            <form method="POST" action="<?= BASE_URL ?>/admin/finance/collections/store" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                <div class="aps-cp-form-group">
                    <label class="aps-cp-form-label">Customer Name <span class="style-85206">*</span></label>
                    <input type="text" name="customer_name" class="aps-cp-form-input" required placeholder="e.g. Rajesh Kumar">
                </div>

                <div class="aps-cp-form-row" class="style-37292">
                    <div class="aps-cp-form-group">
                        <label class="aps-cp-form-label">Collector <span class="style-85206">*</span></label>
                        <select name="collector_id" class="aps-cp-form-select" required>
                            <option value="">-- Select --</option>
                            <?php foreach ($collectors as $c): ?>
                                <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="aps-cp-form-group">
                        <label class="aps-cp-form-label">Collection Date <span class="style-85206">*</span></label>
                        <input type="date" name="collection_date" class="aps-cp-form-input" required value="<?= date('Y-m-d') ?>">
                    </div>
                </div>

                <div class="aps-cp-form-row" class="style-37292">
                    <div class="aps-cp-form-group">
                        <label class="aps-cp-form-label">Amount (₹) <span class="style-85206">*</span></label>
                        <input type="number" name="amount" class="aps-cp-form-input" required min="1" step="0.01" placeholder="e.g. 5000">
                    </div>
                    <div class="aps-cp-form-group">
                        <label class="aps-cp-form-label">Payment Method</label>
                        <select name="payment_method" class="aps-cp-form-select">
                            <option value="cash">Cash</option>
                            <option value="cheque">Cheque</option>
                            <option value="upi">UPI</option>
                            <option value="bank_transfer">Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <div class="aps-cp-form-row" class="style-37292">
                    <div class="aps-cp-form-group">
                        <label class="aps-cp-form-label">Reference Number</label>
                        <input type="text" name="reference_number" class="aps-cp-form-input" placeholder="Cheque/UPI ref (optional)">
                    </div>
                    <div class="aps-cp-form-group">
                        <label class="aps-cp-form-label">Booking (Optional)</label>
                        <select name="booking_id" class="aps-cp-form-select">
                            <option value="">-- None --</option>
                            <?php foreach ($bookings as $b): ?>
                                <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['booking_number']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="aps-cp-form-group">
                    <label class="aps-cp-form-label">Receipt Photo</label>
                    <input type="file" name="receipt_photo" class="aps-cp-form-input" accept="image/*">
                    <div class="style-64409">Upload receipt photo (JPG/PNG/WebP, max 5MB)</div>
                </div>

                <div class="aps-cp-form-group">
                    <label class="aps-cp-form-label">Notes</label>
                    <textarea name="notes" class="aps-cp-form-textarea" rows="3" placeholder="Any additional notes..."></textarea>
                </div>

                <div class="style-83125">
                    <button type="submit" class="aps-cp-btn aps-cp-btn-primary"><i class="fas fa-save"></i> Save Collection</button>
                    <a href="<?= BASE_URL ?>/admin/finance/collections" class="aps-cp-btn aps-cp-btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
