<?php
$page_title = 'Marketing Toolkit - APS Dream Home';
$stats = $stats ?? [];
$base = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h3><i class="fas fa-bullhorn me-2 text-primary"></i>Marketing Toolkit</h3>
        <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Free for all team members</span>
    </div>

    <p class="text-muted mb-4">
        <i class="fas fa-info-circle me-1"></i>
        Photo par logo lagao, WhatsApp banner banao, AI se Hindi post likhwao — phir download karke WhatsApp / Instagram / Facebook par share karo.
        <strong>Koi paid API nahi, sab free.</strong>
    </p>

    <?php if (!empty($stats)): ?>
    <div class="row g-3 mb-4">
        <?php foreach ($stats as $s): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="text-muted small"><?= ucfirst(htmlspecialchars($s['tool_type'])) ?></div>
                    <div class="h4 mb-0"><?= (int)$s['count'] ?> <small class="text-muted fs-6">uses</small></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- 1. Watermark Tool -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-stamp me-2 text-primary"></i>1. Photo Watermark</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Property photo par company logo lagao (bottom-right corner).</p>
                    <form id="watermarkForm" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Photo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="photo" accept="image/*" required>
                            <small class="text-muted">JPG/PNG/GIF/WebP, max 10MB</small>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">Position</label>
                                <select class="form-select" name="position">
                                    <option value="bottom-right">Bottom Right</option>
                                    <option value="bottom-left">Bottom Left</option>
                                    <option value="top-right">Top Right</option>
                                    <option value="top-left">Top Left</option>
                                    <option value="center">Center</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Opacity</label>
                                <select class="form-select" name="opacity">
                                    <option value="80" selected>80%</option>
                                    <option value="60">60%</option>
                                    <option value="100">100%</option>
                                </select>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-magic me-2"></i>Add Logo
                        </button>
                    </form>
                    <div id="watermarkResult" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- 2. Banner Generator -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-image me-2 text-success"></i>2. WhatsApp Banner</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">1080×1920 banner — WhatsApp Status / Instagram Story size.</p>
                    <form id="bannerForm" enctype="multipart/form-data">
                        <div class="mb-2">
                            <label class="form-label">Photo <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" name="photo" accept="image/*" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="price" placeholder="Price (e.g. ₹999/sq.ft.)">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="location" placeholder="Location (e.g. Suryoday, Kalesar)">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="offer" placeholder="Offer (e.g. 40% Cashback)">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="property_type" placeholder="Type (e.g. Residential Plot)">
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-wand-magic-sparkles me-2"></i>Generate Banner
                        </button>
                    </form>
                    <div id="bannerResult" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- 3. AI Content Writer -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-robot me-2 text-warning"></i>3. AI Post Writer</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">Photo details do → AI Hindi marketing post likhega. <span class="badge bg-success">Free (local AI)</span></p>
                    <form id="aiForm">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <input type="text" class="form-control" name="property_type" placeholder="Type (प्लॉट)" value="प्लॉट">
                            </div>
                            <div class="col-6">
                                <input type="text" class="form-control" name="size_sqft" placeholder="Size (1000)">
                            </div>
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="price" placeholder="Price (₹999/sq.ft.)">
                        </div>
                        <div class="mb-2">
                            <input type="text" class="form-control" name="location" placeholder="Location (गोरखपुर)" value="गोरखपुर">
                        </div>
                        <div class="mb-3">
                            <input type="text" class="form-control" name="offer" placeholder="Offer (40% Cashback)">
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fas fa-pen-nib me-2"></i>Write Post
                        </button>
                    </form>
                    <div id="aiResult" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="alert alert-info mt-4">
        <strong><i class="fas fa-share-alt me-1"></i>WhatsApp / Instagram par kaise share karein?</strong>
        <ol class="mb-0 mt-2">
            <li><strong>WhatsApp:</strong> "WhatsApp Share" button dabao → chat select karo → bhejo. Koi API/login nahi chahiye.</li>
            <li><strong>Instagram/Facebook:</strong> Image download karo → app kholo → post/status me upload karo. Direct auto-post ke liye Meta approval + paid API chahiye (abhi mat karo).</li>
            <li><strong>Google Login:</strong> Website par login ke liye hai (pehle se bana hai). Posting se iska koi lena-dena nahi.</li>
        </ol>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    const base = '<?= $base ?>';

    function showResult(elId, data) {
        const el = document.getElementById(elId);
        if (!data.success) {
            el.innerHTML = '<div class="alert alert-danger">' + escapeHtml(data.message || 'Failed') + '</div>';
            return;
        }
        let html = '<div class="alert alert-success">Done! <a href="' + escapeHtml(data.url) + '" download class="btn btn-sm btn-success ms-2"><i class="fas fa-download me-1"></i>Download</a></div>';
        if (data.url) {
            html += '<img src="' + escapeHtml(data.url) + '" class="img-fluid rounded mb-2" style="max-height:300px">';
        }
        if (data.whatsapp_share) {
            html += '<a href="' + escapeHtml(data.whatsapp_share) + '" target="_blank" class="btn btn-success btn-sm w-100"><i class="fab fa-whatsapp me-1"></i>WhatsApp Share</a>';
        }
        el.innerHTML = html;
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    // Watermark form
    document.getElementById('watermarkForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const el = document.getElementById('watermarkResult');
        el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Processing...</div>';
        try {
            const resp = await fetch(base + '/marketing/watermark', { method: 'POST', body: new FormData(this) });
            showResult('watermarkResult', await resp.json());
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger">Network error</div>';
        }
    });

    // Banner form
    document.getElementById('bannerForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const el = document.getElementById('bannerResult');
        el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Generating...</div>';
        try {
            const resp = await fetch(base + '/marketing/banner', { method: 'POST', body: new FormData(this) });
            showResult('bannerResult', await resp.json());
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger">Network error</div>';
        }
    });

    // AI form
    document.getElementById('aiForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const el = document.getElementById('aiResult');
        el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> AI likh raha hai...</div>';
        try {
            const fd = new FormData(this);
            const data = {};
            fd.forEach((v, k) => data[k] = v);
            const resp = await fetch(base + '/marketing/ai-writer', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await resp.json();
            if (!result.success) {
                el.innerHTML = '<div class="alert alert-danger">' + escapeHtml(result.message || 'Failed') + '</div>';
                return;
            }
            let html = '<div class="card"><div class="card-body">';
            html += '<h6>' + escapeHtml(result.headline || '') + '</h6>';
            html += '<p class="small" style="white-space:pre-wrap">' + escapeHtml(result.body || '') + '</p>';
            html += '<p class="small text-primary">' + escapeHtml(result.hashtags || '') + '</p>';
            if (!result.ai_generated) html += '<span class="badge bg-secondary">Template (AI unavailable)</span>';
            else html += '<span class="badge bg-success">AI Generated</span>';
            html += '<button class="btn btn-sm btn-outline-primary mt-2" onclick="copyPost(this)">Copy Post</button> ';
            if (result.whatsapp_share) {
                html += '<a href="' + escapeHtml(result.whatsapp_share) + '" target="_blank" class="btn btn-success btn-sm mt-2"><i class="fab fa-whatsapp me-1"></i>WhatsApp</a>';
            }
            html += '<div style="display:none" class="post-text">' + escapeHtml((result.headline || '') + '\n\n' + (result.body || '') + '\n\n' + (result.hashtags || '')) + '</div>';
            html += '</div></div>';
            el.innerHTML = html;
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger">Network error</div>';
        }
    });

    window.copyPost = function(btn) {
        const text = btn.parentElement.querySelector('.post-text').textContent;
        navigator.clipboard.writeText(text).then(() => {
            btn.textContent = 'Copied!';
            setTimeout(() => btn.textContent = 'Copy Post', 2000);
        });
    };
})();
</script>