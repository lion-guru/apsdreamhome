<?php if (!isset($sc)) { $sc = function($k, $d='') { return $GLOBALS['_site_settings_cache'][$k] ?? $d; }; } $phoneRaw = preg_replace('/[^0-9]/', '', $sc('contact_whatsapp', '919277121112')); $phoneDisplay = $sc('contact_phone', '<?= htmlspecialchars($phoneDisplay) ?>'); $emailDisplay = $sc('contact_email', '<?= htmlspecialchars($emailDisplay) ?>'); ?>
<?php
$msg = $error_message ?? __('payment_failed_default_message', [], 'Something went wrong while processing your payment. Please try again or contact support.');
$orderId = $order_id ?? '';
?>
<style>
.fail-shell { max-width: 640px; margin: 4rem auto; padding: 0 1rem; text-align: center; font-family: 'Segoe UI', system-ui, sans-serif; }
.fail-icon { width: 96px; height: 96px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: 3rem; margin: 0 auto 1.5rem; }
.fail-card { background: #fff; border-radius: 16px; box-shadow: 0 10px 40px rgba(15, 23, 42, 0.08); padding: 2.5rem 2rem; border: 1px solid #e5e7eb; }
.fail-card h1 { margin: 0 0 0.5rem; color: #0f172a; font-size: 1.75rem; }
.fail-card p.error-msg { color: #64748b; margin: 0 0 1.5rem; line-height: 1.6; }
.support-box { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 1rem 1.25rem; margin: 1.5rem 0; text-align: left; }
.support-box h3 { margin: 0 0 0.5rem; color: #1e40af; font-size: 0.95rem; }
.support-box p { margin: 0.25rem 0; color: #1e3a8a; font-size: 0.88rem; }
.support-box i { margin-right: 0.4rem; color: #2563eb; }
.action-row { display: flex; gap: 0.75rem; margin-top: 1.5rem; flex-wrap: wrap; justify-content: center; }
.btn-primary { background: #2563eb; color: #fff; border: 0; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
.btn-primary:hover { background: #1d4ed8; color: #fff; text-decoration: none; }
.btn-secondary { background: #fff; color: #64748b; border: 1px solid #cbd5e1; padding: 0.75rem 1.5rem; border-radius: 8px; font-size: 0.95rem; font-weight: 500; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; }
.btn-secondary:hover { background: #f1f5f9; color: #0f172a; text-decoration: none; }
.order-ref { font-size: 0.85rem; color: #94a3b8; margin-top: 1.5rem; }
</style>

<div class="fail-shell">
    <div class="fail-icon"><i class="fas fa-times"></i></div>
    <div class="fail-card">
        <h1><?php echo __('payment_failed_title', [], 'Payment Failed'); ?></h1>
        <p class="error-msg"><?= htmlspecialchars($msg, ENT_QUOTES, 'UTF-8') ?></p>

        <?php if ($orderId): ?>
            <p class="order-ref"><?php echo __('payment_failed_reference', [], 'Reference:'); ?> <code><?= htmlspecialchars($orderId, ENT_QUOTES, 'UTF-8') ?></code></p>
        <?php endif; ?>

        <div class="support-box">
            <h3><i class="fas fa-headset"></i> <?php echo __('payment_failed_need_help', [], 'Need Help?'); ?></h3>
            <p><i class="fas fa-phone"></i> <?= htmlspecialchars($phoneDisplay) ?></p>
            <p><i class="fas fa-envelope"></i> support@apsdreamhome.com</p>
            <p><i class="fas fa-clock"></i> <?php echo __('payment_failed_hours', [], 'Mon-Sat, 9:00 AM - 7:00 PM IST'); ?></p>
        </div>

        <div class="action-row">
            <a href="/user/bookings" class="btn-primary">
                <i class="fas fa-redo"></i> <?php echo __('payment_failed_try_again', [], 'Try Again'); ?>
            </a>
            <a href="/" class="btn-secondary">
                <i class="fas fa-home"></i> <?php echo __('payment_failed_back_home', [], 'Back to Home'); ?>
            </a>
        </div>
    </div>
</div>
