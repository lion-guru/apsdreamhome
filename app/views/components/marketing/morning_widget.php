<?php
/**
 * Morning Post Widget — shows on associate/agent dashboards.
 * "Aaj ka post taiyaar hai" — 1-click render + share.
 * Include: <?php include __DIR__ . '/../../components/marketing/morning_widget.php'; ?>
 */
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="card border-0 shadow-sm mb-4" style="border:2px solid #FACC15 !important;background:linear-gradient(135deg,#FFFBEB,#FEF3C7)">
    <div class="card-body d-flex align-items-center gap-3 flex-wrap">
        <div style="font-size:2.5rem">🌅</div>
        <div class="flex-grow-1">
            <h6 class="mb-0">Aaj Ka Post Taiyaar Hai!</h6>
            <small class="text-muted">Good Morning post tumhari branding ke saath — ek click me banao, status lagao.</small>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-warning" onclick="morningPostWidget(this)">
                <i class="fas fa-bolt me-1"></i>Aaj Ka Post Banao
            </button>
            <a href="<?= $base ?>/marketing/toolkit" class="btn btn-outline-primary">
                <i class="fas fa-tools me-1"></i>All Tools
            </a>
        </div>
    </div>
    <div id="morningWidgetResult" class="px-3 pb-3"></div>
</div>
<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
async function morningPostWidget(btn) {
    const el = document.getElementById('morningWidgetResult');
    const base = '<?= $base ?>';
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Bana rahe hain...';
    el.innerHTML = '';
    try {
        const resp = await fetch(base + '/marketing/morning-post', { method: 'POST' });
        const data = await resp.json();
        if (!data.success) {
            el.innerHTML = '<div class="alert alert-danger py-1 small">' + (data.message || 'Failed') + '</div>';
        } else {
            let html = '<div class="d-flex gap-2 align-items-center flex-wrap mt-2">';
            html += '<img src="' + data.url + '" style="max-height:150px" class="rounded">';
            html += '<div><a href="' + data.url + '" download class="btn btn-sm btn-success mb-1"><i class="fas fa-download me-1"></i>Download</a><br>';
            if (data.whatsapp_share) {
                html += '<a href="' + data.whatsapp_share + '" target="_blank" class="btn btn-success btn-sm"><i class="fab fa-whatsapp me-1"></i>Status Lagao</a>';
            }
            html += '</div></div>';
            el.innerHTML = html;
        }
    } catch (e) {
        el.innerHTML = '<div class="alert alert-danger py-1 small">Network error</div>';
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-bolt me-1"></i>Aaj Ka Post Banao';
}
</script>
