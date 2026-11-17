<?php
/** @var array $colonies */
/** @var array $themes */
/** @var array $user */
$colonies = $colonies ?? [];
$themes = $themes ?? [];
$user = $user ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
$ref = $referral_code ?? ($user['referral_code'] ?? '');
$firstColony = $colonies[0]['id'] ?? 0;
?>

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h4 class="mb-1 text-dark fw-bold"><i class="fas fa-wand-magic-sparkles text-teal me-2" style="color: #0d9488;"></i>WhatsApp Poster Studio</h4>
        <small class="text-muted">Generate high-converting, branded marketing posters (1080×1350) with your direct referral QR</small>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= $base ?>/associate/dashboard" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Controls Column -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3 px-4">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-sliders-h text-primary me-2"></i>Poster Customizer</h6>
            </div>
            <div class="card-body px-4 pt-1 pb-4">
                <!-- Colony Selection -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Target Colony</label>
                    <select aria-label="Poster colony" id="posterColony" class="form-select form-select-sm">
                        <?php foreach ($colonies as $c): 
                            $rate = (float)($c['min_price_per_sqft'] ?: $c['starting_price'] ?: 0);
                            $rateText = $rate >= 100 ? '(Rs. ' . number_format($rate) . '/sqft)' : '(Prime Location)';
                        ?>
                            <option value="<?= (int)$c['id'] ?>">
                                <?= htmlspecialchars($c['name']) ?> <?= $rateText ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Theme Selection -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Design Theme</label>
                    <select aria-label="Poster theme" id="posterTheme" class="form-select form-select-sm">
                        <?php foreach ($themes as $k => $l): ?>
                            <option value="<?= $k ?>"><?= htmlspecialchars($l) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Campaign Pitch -->
                <div class="mb-4">
                    <label class="form-label small fw-bold text-dark">Campaign Hook / Offer</label>
                    <select id="posterHook" class="form-select form-select-sm">
                        <option value="visit">🚗 Free Sunday Site Visit (Home Pick & Drop)</option>
                        <option value="registry">📜 Immediate Registry & Direct Possession</option>
                        <option value="loan">🏦 100% Bank Loan Approved & Easy EMI</option>
                        <option value="roi">📈 High Appreciation Township Plots</option>
                    </select>
                </div>

                <!-- Actions Grid -->
                <div class="d-grid gap-2">
                    <button class="btn btn-primary btn-sm fw-bold rounded-pill text-white shadow-sm" id="posterRefresh" style="background-color: #0d9488; border-color: #0d9488;">
                        <i class="fas fa-sync-alt me-1"></i> Regenerate Poster
                    </button>
                    <a class="btn btn-success btn-sm fw-bold rounded-pill" id="posterDownload" href="#" download>
                        <i class="fas fa-download me-1"></i> Download HD Poster (PNG)
                    </a>
                    <a class="btn btn-sm text-white fw-bold rounded-pill" id="posterShare" target="_blank" rel="noopener" style="background:#25D366">
                        <i class="fab fa-whatsapp me-1"></i> Share to WhatsApp Status
                    </a>
                </div>

                <!-- Quick Copy Tools -->
                <div class="pt-3 border-top mt-3">
                    <label class="form-label small fw-bold text-muted mb-2">1-Click Quick Copy Tools</label>
                    <div class="d-flex gap-2 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill rounded-pill" id="btnCopyText">
                            <i class="fas fa-copy me-1"></i> Copy Message
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill rounded-pill" id="btnCopyLink">
                            <i class="fas fa-link me-1"></i> Copy Link
                        </button>
                    </div>
                    <div id="copyFeedback" class="text-success small fw-bold text-center" style="display:none;"></div>
                </div>

                <div class="mt-3 p-3 bg-light rounded-3">
                    <small class="text-muted d-block">
                        <i class="fas fa-lightbulb text-warning me-1"></i>
                        <strong>Associate Pro-Tip:</strong> Is poster ko apne WhatsApp Status aur Story par post karein. Customer QR code scan karega toh lead seedhe aapke referral code (<strong><?= htmlspecialchars($ref) ?></strong>) ke sath register ho jayegi!
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Column -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark"><i class="fas fa-image text-primary me-2"></i>Live Preview (1080 × 1350)</span>
                <span class="badge bg-success rounded-pill px-3 py-1" id="posterReady" style="display:none">Ready to Post</span>
            </div>
            <div class="card-body text-center p-4">
                <div id="posterLoading" class="py-5 text-muted">
                    <div class="spinner-border text-teal mb-2" role="status" style="color: #0d9488;"></div>
                    <p class="mb-0">Rendering branded poster with your referral QR...</p>
                </div>
                <img id="posterPreview" class="img-fluid rounded-3 border shadow-sm" style="max-height: 72vh; display: none;" alt="Branded colony poster preview">
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
    var hookSel = document.getElementById('posterHook');
    var img = document.getElementById('posterPreview');
    var loading = document.getElementById('posterLoading');
    var ready = document.getElementById('posterReady');
    var feedback = document.getElementById('copyFeedback');

    function posterUrl(fresh) {
        var u = base + '/associate/marketing/posters/image?colony_id=' + encodeURIComponent(colonySel.value) +
                '&theme=' + encodeURIComponent(themeSel.value);
        if (fresh) u += '&fresh=1&t=' + Date.now();
        return u;
    }

    function shareText() {
        var cname = colonySel.options[colonySel.selectedIndex].text;
        var hookText = hookSel.options[hookSel.selectedIndex].text;
        return '🌟 *APS DREAM HOME — ' + cname + '* 🏡\n' +
               '✨ ' + hookText + '\n\n' +
               '📍 Prime Location in Gorakhpur with wide roads, electricity & gated security.\n' +
               '💳 Bank loan approval & easy monthly installments available!\n\n' +
               '👉 Explore plots & calculate EMI here:\n' +
               base + '/register?ref=' + encodeURIComponent(ref) + '\n\n' +
               '📞 Call/WhatsApp me directly for Sunday Free Site Visit with family!';
    }

    function render(fresh) {
        loading.style.display = '';
        img.style.display = 'none';
        ready.style.display = 'none';
        var u = posterUrl(fresh);
        img.onload = function () {
            loading.style.display = 'none';
            img.style.display = 'inline-block';
            ready.style.display = '';
            document.getElementById('posterDownload').href = u;
            document.getElementById('posterShare').href = 'https://wa.me/?text=' + encodeURIComponent(shareText());
        };
        img.onerror = function () {
            loading.innerHTML = '<p class="text-danger py-4"><i class="fas fa-exclamation-circle fa-2x d-block mb-2"></i>Render failed. Please retry.</p>';
        };
        img.src = u;
    }

    function showFeedback(msg) {
        feedback.textContent = msg;
        feedback.style.display = 'block';
        setTimeout(function() {
            feedback.style.display = 'none';
        }, 2500);
    }

    colonySel.addEventListener('change', function () { render(false); });
    themeSel.addEventListener('change', function () { render(false); });
    hookSel.addEventListener('change', function () {
        document.getElementById('posterShare').href = 'https://wa.me/?text=' + encodeURIComponent(shareText());
    });
    document.getElementById('posterRefresh').addEventListener('click', function () { render(true); });

    document.getElementById('btnCopyText').addEventListener('click', function() {
        navigator.clipboard.writeText(shareText()).then(function() {
            showFeedback('✓ Marketing message copied to clipboard!');
        });
    });

    document.getElementById('btnCopyLink').addEventListener('click', function() {
        var link = base + '/register?ref=' + encodeURIComponent(ref);
        navigator.clipboard.writeText(link).then(function() {
            showFeedback('✓ Referral link copied to clipboard!');
        });
    });

    render(false);
})();
</script>
