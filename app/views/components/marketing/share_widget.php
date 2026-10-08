<?php
/**
 * Quick Share Widget — one-tap share buttons for any property/listing.
 * Usage:
 *   $shareText = "Property details...";
 *   $sharePhone = "+91...";
 *   include __DIR__ . '/../../components/marketing/share_widget.php';
 *
 * Expects: $shareText (string), $sharePhone (optional), $shareImageUrl (optional)
 */
$shareText = $shareText ?? '';
$sharePhone = $sharePhone ?? '';
$shareImageUrl = $shareImageUrl ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';

$waUrl = 'https://wa.me/?text=' . urlencode($shareText);
$smsUrl = 'sms:?body=' . urlencode($shareText);
$emailUrl = 'mailto:?subject=' . urlencode('APS Dream Home - Property') . '&body=' . urlencode($shareText);
?>
<div class="d-flex gap-1 flex-wrap share-widget">
    <?php if ($shareImageUrl): ?>
    <a href="<?= htmlspecialchars($shareImageUrl) ?>" download class="btn btn-sm btn-outline-primary" title="Download image">
        <i class="fas fa-download"></i>
    </a>
    <?php endif; ?>
    <a href="<?= htmlspecialchars($waUrl) ?>" target="_blank" class="btn btn-sm btn-success" title="Share on WhatsApp">
        <i class="fab fa-whatsapp"></i> <span class="d-none d-md-inline">WhatsApp</span>
    </a>
    <a href="<?= htmlspecialchars($smsUrl) ?>" class="btn btn-sm btn-outline-success" title="Share via SMS (your phone)">
        <i class="fas fa-sms"></i> <span class="d-none d-md-inline">SMS</span>
    </a>
    <a href="<?= htmlspecialchars($emailUrl) ?>" class="btn btn-sm btn-outline-secondary" title="Share via Email">
        <i class="fas fa-envelope"></i> <span class="d-none d-md-inline">Email</span>
    </a>
    <a href="https://www.instagram.com/" target="_blank" class="btn btn-sm btn-outline-dark" title="Post on Instagram (download image first)">
        <i class="fab fa-instagram"></i> <span class="d-none d-md-inline">Insta</span>
    </a>
    <a href="https://www.facebook.com/" target="_blank" class="btn btn-sm btn-outline-primary" title="Post on Facebook">
        <i class="fab fa-facebook"></i> <span class="d-none d-md-inline">FB</span>
    </a>
    <button class="btn btn-sm btn-outline-secondary" onclick="navigator.clipboard.writeText(<?= json_encode($shareText) ?>).then(()=>{this.innerHTML='<i class=\'fas fa-check\'></i>';setTimeout(()=>this.innerHTML='<i class=\'fas fa-copy\'></i>',1500)})" title="Copy text">
        <i class="fas fa-copy"></i>
    </button>
</div>
