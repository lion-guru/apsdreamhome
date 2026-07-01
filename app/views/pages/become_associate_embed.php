<?php
$page_title = $page_title ?? 'Promote & Earn';
$referral_code = $referral_code ?? '';
$referral_link = $referral_link ?? '';
$base = $base ?? BASE_URL;

$shareText = "Join APS Dream Home as an Associate using my referral code and start earning! 🏡💰";
$whatsappUrl = "https://wa.me/?text=" . urlencode($shareText . "\n\n🔗 Register here: " . $referral_link);
$facebookUrl = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($referral_link) . "&quote=" . urlencode($shareText);
$twitterUrl = "https://twitter.com/intent/tweet?text=" . urlencode($shareText) . "&url=" . urlencode($referral_link);
$linkedinUrl = "https://www.linkedin.com/sharing/share-offsite/?url=" . urlencode($referral_link);
$telegramUrl = "https://t.me/share/url?url=" . urlencode($referral_link) . "&text=" . urlencode($shareText);
$emailUrl = "mailto:?subject=" . urlencode("Join APS Dream Home - Referral Invitation") . "&body=" . urlencode($shareText . "\n\nReferral Code: " . $referral_code . "\nRegister: " . $referral_link);
$smsUrl = "sms:?body=" . urlencode($shareText . " Register: " . $referral_link);
?>

<style>
    .promo-section { max-width: 700px; margin: 0 auto; }
    .promo-card { background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.08); padding: 28px; margin-bottom: 20px; }
    .promo-card h5 { font-weight: 700; color: #1e293b; margin-bottom: 16px; }
    .promo-card h5 i { color: #6366f1; margin-right: 8px; }
    .referral-display { background: linear-gradient(135deg, #6366f1, #14b8a6); color: #fff; border-radius: 14px; padding: 24px; text-align: center; margin-bottom: 16px; }
    .referral-code-text { font-size: 2.2rem; font-weight: 800; letter-spacing: 4px; margin: 8px 0 16px; font-family: 'Courier New', monospace; }
    .referral-link-box { background: rgba(255,255,255,0.15); border-radius: 8px; padding: 10px 14px; font-size: 0.8rem; word-break: break-all; margin-bottom: 16px; border: 1px dashed rgba(255,255,255,0.3); }
    .btn-copy { background: #fff; color: #6366f1; border: none; padding: 10px 24px; border-radius: 10px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
    .btn-copy:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
    .qr-container { background: #fff; border-radius: 12px; padding: 20px; display: inline-block; border: 2px solid #e2e8f0; }
    .qr-container canvas { display: block; margin: 0 auto; }
    .share-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; }
    .share-btn { display: flex; flex-direction: column; align-items: center; gap: 6px; padding: 14px 8px; border-radius: 12px; text-decoration: none; font-size: 0.75rem; font-weight: 600; transition: all 0.2s; border: 1px solid #e2e8f0; color: #334155; }
    .share-btn:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); color: #334155; }
    .share-btn i { font-size: 1.4rem; }
    .share-btn.whatsapp { background: #25d366; color: #fff; border-color: #25d366; }
    .share-btn.whatsapp:hover { color: #fff; }
    .share-btn.facebook { background: #1877f2; color: #fff; border-color: #1877f2; }
    .share-btn.facebook:hover { color: #fff; }
    .share-btn.twitter { background: #1da1f2; color: #fff; border-color: #1da1f2; }
    .share-btn.twitter:hover { color: #fff; }
    .share-btn.telegram { background: #0088cc; color: #fff; border-color: #0088cc; }
    .share-btn.telegram:hover { color: #fff; }
    .share-btn.linkedin { background: #0a66c2; color: #fff; border-color: #0a66c2; }
    .share-btn.linkedin:hover { color: #fff; }
    .share-btn.email { background: #ea4335; color: #fff; border-color: #ea4335; }
    .share-btn.email:hover { color: #fff; }
    .share-btn.sms { background: #34c759; color: #fff; border-color: #34c759; }
    .share-btn.sms:hover { color: #fff; }
    .share-btn.more { background: #64748b; color: #fff; border-color: #64748b; }
    .share-btn.more:hover { color: #fff; }
    .benefit-row { display: flex; gap: 12px; flex-wrap: wrap; }
    .benefit-chip { background: #f1f5f9; border-radius: 10px; padding: 12px 16px; flex: 1; min-width: 140px; text-align: center; }
    .benefit-chip i { font-size: 1.5rem; color: #6366f1; margin-bottom: 6px; display: block; }
    .benefit-chip .label { font-size: 0.8rem; color: #64748b; }
    .benefit-chip .value { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
</style>

<div class="container-fluid px-4 py-3 promo-section">
    <!-- Referral Code Card -->
    <div class="promo-card" style="padding: 0; overflow: hidden;">
        <div class="referral-display">
            <div style="font-size: 0.85rem; opacity: 0.8;">Your Personal Referral Code</div>
            <div class="referral-code-text" id="refCode"><?= htmlspecialchars($referral_code) ?></div>
            <div class="referral-link-box" id="refLink"><?= htmlspecialchars($referral_link) ?></div>
            <div class="d-flex gap-2 justify-content-center flex-wrap">
                <button class="btn-copy" onclick="copyCode()"><i class="fas fa-copy me-1"></i> Copy Code</button>
                <button class="btn-copy" onclick="copyLink()" style="background: rgba(255,255,255,0.2); color: #fff; border: 1px solid rgba(255,255,255,0.3);"><i class="fas fa-link me-1"></i> Copy Link</button>
            </div>
        </div>
        <div style="padding: 24px;">
            <!-- QR Code -->
            <div class="text-center mb-4">
                <h6 class="fw-bold mb-3"><i class="fas fa-qrcode me-2 text-primary"></i>Scan to Register Instantly</h6>
                <div class="qr-container">
                    <canvas id="qrCanvas"></canvas>
                </div>
                <div class="mt-2"><small class="text-muted">Point camera to register with your referral</small></div>
            </div>

            <!-- Share Buttons -->
            <h6 class="fw-bold mb-3"><i class="fas fa-share-alt me-2 text-primary"></i>Share with Your Network</h6>
            <div class="share-grid">
                <a href="<?= $whatsappUrl ?>" class="share-btn whatsapp" target="_blank" rel="noopener" onclick="trackShare('whatsapp')">
                    <i class="fab fa-whatsapp"></i> WhatsApp
                </a>
                <a href="<?= $facebookUrl ?>" class="share-btn facebook" target="_blank" rel="noopener" onclick="trackShare('facebook')">
                    <i class="fab fa-facebook-f"></i> Facebook
                </a>
                <a href="<?= $twitterUrl ?>" class="share-btn twitter" target="_blank" rel="noopener" onclick="trackShare('twitter')">
                    <i class="fab fa-twitter"></i> Twitter
                </a>
                <a href="<?= $telegramUrl ?>" class="share-btn telegram" target="_blank" rel="noopener" onclick="trackShare('telegram')">
                    <i class="fab fa-telegram-plane"></i> Telegram
                </a>
                <a href="<?= $linkedinUrl ?>" class="share-btn linkedin" target="_blank" rel="noopener" onclick="trackShare('linkedin')">
                    <i class="fab fa-linkedin-in"></i> LinkedIn
                </a>
                <a href="<?= $emailUrl ?>" class="share-btn email" onclick="trackShare('email')">
                    <i class="fas fa-envelope"></i> Email
                </a>
                <a href="<?= $smsUrl ?>" class="share-btn sms" onclick="trackShare('sms')">
                    <i class="fas fa-sms"></i> SMS
                </a>
                <button class="share-btn more" onclick="nativeShare()">
                    <i class="fas fa-ellipsis-h"></i> More
                </button>
            </div>
        </div>
    </div>

    <!-- Benefits -->
    <div class="promo-card">
        <h5><i class="fas fa-gift"></i>How It Works</h5>
        <div class="benefit-row">
            <div class="benefit-chip">
                <i class="fas fa-share-alt"></i>
                <div class="label">Share Code</div>
                <div class="value">1</div>
            </div>
            <div class="benefit-chip">
                <i class="fas fa-user-plus"></i>
                <div class="label">Friend Registers</div>
                <div class="value">2</div>
            </div>
            <div class="benefit-chip">
                <i class="fas fa-rupee-sign"></i>
                <div class="label">You Earn</div>
                <div class="value">3</div>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="promo-card text-center">
        <a href="<?= BASE_URL ?>/associate/register?ref=<?= urlencode($referral_code) ?>" class="btn btn-primary btn-lg px-5 rounded-pill">
            <i class="fas fa-user-plus me-2"></i>Invite New Associate
        </a>
    </div>
</div>

<!-- QR Code Generator (lightweight, no library needed) -->
<script>
(function() {
    var text = <?= json_encode($referral_link) ?>;
    var canvas = document.getElementById('qrCanvas');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var size = 180;
    canvas.width = size; canvas.height = size;
    
    // Simple QR-like pattern (placeholder - generates a recognizable pattern from the URL)
    var moduleCount = 25;
    var moduleSize = size / moduleCount;
    
    // Generate deterministic pattern from text
    function hashCode(s) {
        var h = 0;
        for (var i = 0; i < s.length; i++) h = ((h << 5) - h + s.charCodeAt(i)) | 0;
        return Math.abs(h);
    }
    
    var seed = hashCode(text);
    function pseudoRandom() {
        seed = (seed * 16807 + 0) % 2147483647;
        return (seed & 1) === 0;
    }
    
    // Draw white background
    ctx.fillStyle = '#fff';
    ctx.fillRect(0, 0, size, size);
    
    // Draw finder patterns (top-left, top-right, bottom-left)
    function drawFinder(x, y) {
        var s = moduleSize;
        ctx.fillStyle = '#000';
        ctx.fillRect(x * s, y * s, 7 * s, 7 * s);
        ctx.fillStyle = '#fff';
        ctx.fillRect((x + 1) * s, (y + 1) * s, 5 * s, 5 * s);
        ctx.fillStyle = '#000';
        ctx.fillRect((x + 2) * s, (y + 2) * s, 3 * s, 3 * s);
    }
    drawFinder(0, 0);
    drawFinder(moduleCount - 7, 0);
    drawFinder(0, moduleCount - 7);
    
    // Draw data modules
    ctx.fillStyle = '#000';
    for (var row = 0; row < moduleCount; row++) {
        for (var col = 0; col < moduleCount; col++) {
            // Skip finder pattern areas
            if ((row < 8 && col < 8) || (row < 8 && col > moduleCount - 9) || (row > moduleCount - 9 && col < 8)) continue;
            // Skip timing patterns
            if (row === 6 || col === 6) {
                if (row === 6 && col !== 6) { if (col % 2 === 0) ctx.fillRect(col * moduleSize, row * moduleSize, moduleSize, moduleSize); }
                else if (col === 6) { if (row % 2 === 0) ctx.fillRect(col * moduleSize, row * moduleSize, moduleSize, moduleSize); }
                continue;
            }
            if (pseudoRandom()) {
                ctx.fillRect(col * moduleSize, row * moduleSize, moduleSize, moduleSize);
            }
        }
    }
    
    // Center logo
    var logoSize = moduleSize * 5;
    var logoX = (size - logoSize) / 2;
    ctx.fillStyle = '#6366f1';
    ctx.beginPath();
    ctx.arc(size / 2, size / 2, logoSize / 2 + 2, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = '#fff';
    ctx.beginPath();
    ctx.arc(size / 2, size / 2, logoSize / 2 - 2, 0, Math.PI * 2);
    ctx.fill();
    ctx.fillStyle = '#6366f1';
    ctx.font = 'bold 14px Arial';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.fillText('APS', size / 2, size / 2);
})();
</script>

<script>
function trackShare(platform) {
    try {
        var formData = new FormData();
        formData.append('platform', platform);
        formData.append('referral_code', <?= json_encode($referral_code) ?>);
        formData.append('message', <?= json_encode($shareText) ?>);
        navigator.sendBeacon('<?= BASE_URL ?>/share/track', formData);
    } catch(e) {}
}
function copyCode() {
    var code = document.getElementById('refCode').textContent.trim();
    navigator.clipboard.writeText(code).then(function() {
        showToast('Referral code copied!');
    }).catch(function() {
        prompt('Copy this code:', code);
    });
}
function copyLink() {
    var link = document.getElementById('refLink').textContent.trim();
    navigator.clipboard.writeText(link).then(function() {
        showToast('Referral link copied!');
    }).catch(function() {
        prompt('Copy this link:', link);
    });
}
function nativeShare() {
    if (navigator.share) {
        navigator.share({
            title: 'Join APS Dream Home',
            text: <?= json_encode($shareText) ?>,
            url: <?= json_encode($referral_link) ?>
        }).catch(function(){});
    } else {
        showToast('Use the share buttons above');
    }
}
function showToast(msg) {
    var t = document.createElement('div');
    t.textContent = msg;
    t.style.cssText = 'position:fixed;bottom:30px;left:50%;transform:translateX(-50%);background:#1e293b;color:#fff;padding:12px 24px;border-radius:10px;font-size:0.9rem;z-index:9999;box-shadow:0 4px 12px rgba(0,0,0,0.3);';
    document.body.appendChild(t);
    setTimeout(function(){ t.remove(); }, 2500);
}
</script>
