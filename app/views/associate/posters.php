<?php
/** @var array $colonies */
/** @var array $themes */
/** @var array $user */
$colonies = $colonies ?? []; $themes = $themes ?? []; $user = $user ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$ref = $referral_code ?? '';
$firstColony = $colonies[0]['id'] ?? 0;
?>
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <h4 class="mb-1"><i class="fas fa-wand-magic-sparkles me-2"></i>WhatsApp Poster Studio</h4>
        <p class="text-muted mb-0 small">Pick a colony → branded poster with your name, phone &amp; referral QR → 1-click Status share</p>
    </div>
    <a href="<?= $base ?>/associate/dashboard" class="btn btn-sm btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Dashboard</a>
</div>

<div class="row">
    <div class="col-lg-4">
        <div class="aps-cp-card mb-3">
            <div class="aps-cp-card-header"><span><i class="fas fa-sliders me-2"></i>Design</span></div>
            <div class="aps-cp-card-body row g-3">
                <div class="col-12"><label class="form-label small">Colony</label>
                    <select id="posterColony" class="form-select">
                        <?php foreach ($colonies as $c): ?>
                            <option value="<?= (int)$c['id'] ?>">
                                <?= htmlspecialchars($c['name']) ?>
                                <?= ((float)($c['min_price_per_sqft'] ?: $c['starting_price'] ?: 0) > 0) ? '(Rs.' . number_format((float)($c['min_price_per_sqft'] ?: $c['starting_price'])) . '/sqft)' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-12"><label class="form-label small">Theme</label>
                    <select id="posterTheme" class="form-select">
                        <?php foreach ($themes as $k => $l): ?>
                            <option value="<?= $k ?>"><?= htmlspecialchars($l) ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="col-12 d-grid gap-2">
                    <button class="btn btn-outline-primary btn-sm" id="posterRefresh"><i class="fas fa-rotate me-1"></i>Regenerate</button>
                    <a class="btn btn-success btn-sm" id="posterDownload" href="#" download><i class="fas fa-download me-1"></i>Download PNG</a>
                    <a class="btn btn-sm text-white" id="posterShare" target="_blank" rel="noopener" style="background:#25D366">
                        <i class="fab fa-whatsapp me-1"></i>Share to WhatsApp Status
                    </a>
                </div>
                <div class="col-12"><small class="text-muted">Tip: download karke apne WhatsApp Status / Story par lagayein — leads seedhe aapke referral link se judenge (Ref: <strong><?= htmlspecialchars($ref) ?></strong>).</small></div>
            </div>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="aps-cp-card">
            <div class="aps-cp-card-header"><span><i class="fas fa-image me-2"></i>Live Preview (1080 × 1350)</span>
                <span class="badge bg-success" id="posterReady" style="display:none">Ready</span></div>
            <div class="aps-cp-card-body text-center">
                <div id="posterLoading" class="py-5 text-muted"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p>Rendering poster…</p></div>
                <img id="posterPreview" class="img-fluid rounded border" style="max-height:70vh;display:none" alt="Branded colony poster preview">
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var base = <?= json_encode($base) ?>;
    var ref = <?= json_encode($ref) ?>;
    var colonySel = document.getElementById('posterColony');
    var themeSel = document.getElementById('posterTheme');
    var img = document.getElementById('posterPreview');
    var loading = document.getElementById('posterLoading');
    var ready = document.getElementById('posterReady');

    function posterUrl(fresh) {
        var u = base + '/associate/marketing/posters/image?colony_id=' + encodeURIComponent(colonySel.value) +
                '&theme=' + encodeURIComponent(themeSel.value);
        if (fresh) u += '&fresh=1&t=' + Date.now();
        return u;
    }
    function shareText() {
        var cname = colonySel.options[colonySel.selectedIndex].text;
        return 'New premium plots at ' + cname + ' by APS Dream Home! Bank loan + easy EMI available. Explore here: ' +
               base + '/register?ref=' + encodeURIComponent(ref) + ' Or call me for a FREE Sunday site visit!';
    }
    function render(fresh) {
        loading.style.display = '';
        img.style.display = 'none';
        ready.style.display = 'none';
        var u = posterUrl(fresh);
        img.onload = function () {
            loading.style.display = 'none';
            img.style.display = '';
            ready.style.display = '';
            document.getElementById('posterDownload').href = u;
            document.getElementById('posterShare').href = 'https://wa.me/?text=' + encodeURIComponent(shareText() + ' Poster: ' + u);
        };
        img.onerror = function () {
            loading.innerHTML = '<p class="text-danger">Render failed. Please retry.</p>';
        };
        img.src = u;
    }
    colonySel.addEventListener('change', function () { render(false); });
    themeSel.addEventListener('change', function () { render(false); });
    document.getElementById('posterRefresh').addEventListener('click', function () { render(true); });
    render(false);
})();
</script>
