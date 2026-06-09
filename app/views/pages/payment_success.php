<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay) ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay) ?>'); ?>
<?php
$paymentId = $payment_id ?? '';
$orderId   = $order_id   ?? '';
$bookingId = (int)($booking_id ?? 0);
$baseUrl   = defined('BASE_URL') ? BASE_URL : '';
$receiptUrl = $bookingId > 0 ? $baseUrl . '/pdf/download/receipt/' . $bookingId : '';
?>
<style>
.success-shell { max-width: 640px; margin: 4rem auto; padding: 0 1rem; text-align: center; font-family: 'Segoe UI', system-ui, sans-serif; }
.success-icon { width: 96px; height: 96px; border-radius: 50%; background: #d1fae5; color: #059669; display: flex; align-items: center; justify-content: center; font-size: 3rem; margin: 0 auto 1.5rem; }
.success-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08); padding: 2.5rem 2rem; border: 1px solid #e5e7eb; }
.success-card h1 { margin: 0 0 0.5rem; color: #0f172a; font-size: 1.75rem; }
.success-card p { color: #64748b; margin: 0 0 2rem; }
.detail-row { display: flex; justify-content: space-between; align-items: center; padding: 0.85rem 1.25rem; background: #f8fafc; border-radius: 8px; margin-bottom: 0.75rem; text-align: left; }
.detail-label { color: #64748b; font-size: 0.85rem; font-weight: 500; }
.detail-value { color: #0f172a; font-weight: 600; font-family: 'SFMono-Regular', Consolas, monospace; font-size: 0.9rem; word-break: break-all; max-width: 60%; text-align: right; }
.action-row { display: flex; gap: 0.75rem; margin-top: 2rem; flex-wrap: wrap; justify-content: center; }
.btn-primary { background: #2563eb; color: #fff; border: 0; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
.btn-primary:hover { background: #1d4ed8; color: #fff; text-decoration: none; }
.btn-secondary { background: #fff; color: #2563eb; border: 1px solid #2563eb; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
.btn-secondary:hover { background: #eff6ff; color: #1d4ed8; text-decoration: none; }
</style>

<div class="success-shell">
    <div class="success-icon"><i class="fas fa-check"></i></div>
    <div class="success-card">
        <h1>Payment Successful!</h1>
        <p>Thank you for your payment. Your booking is now confirmed.</p>

        <?php if ($paymentId): ?>
        <div class="detail-row">
            <span class="detail-label">Payment ID</span>
            <span class="detail-value"><?= htmlspecialchars($paymentId, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <?php endif; ?>

        <?php if ($orderId): ?>
        <div class="detail-row">
            <span class="detail-label">Order ID</span>
            <span class="detail-value"><?= htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <?php endif; ?>

        <div class="action-row">
            <a href="/user/bookings" class="btn-primary">
                <i class="fas fa-list"></i> View My Bookings
            </a>
            <?php if ($receiptUrl): ?>
            <a href="<?= htmlspecialchars($receiptUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn-secondary" target="_blank" rel="noopener">
                <i class="fas fa-file-pdf"></i> Download Receipt
            </a>
            <?php endif; ?>
            <a href="/" class="btn-secondary">
                <i class="fas fa-home"></i> Continue Browsing
            </a>
        </div>
    </div>
</div>
