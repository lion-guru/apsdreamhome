<?php
$page_title = 'Template Gallery - APS Dream Home';
$templates = $templates ?? [];
$category = $category ?? '';
$base = defined('BASE_URL') ? BASE_URL : '';
$categories = ['festival' => 'Festival', 'offer' => 'Offer', 'launch' => 'Launch', 'status' => 'Status', 'greeting' => 'Greeting', 'info' => 'Info'];
?>
<div class="content-area p-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h3><i class="fas fa-layer-group me-2 text-primary"></i>Template Gallery</h3>
        <a href="<?= $base ?>/marketing/toolkit" class="btn btn-outline-secondary btn-sm"><i class="fas fa-tools me-1"></i>Toolkit</a>
    </div>
    <p class="text-muted small mb-3">Ready-made design chuno → photo + price dalo → ek click me branded post taiyaar. Sab me tumhari branding + referral QR auto.</p>

    <div class="mb-3 d-flex gap-2 flex-wrap">
        <a href="<?= $base ?>/marketing/templates" class="btn btn-sm <?= $category === '' ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
        <?php foreach ($categories as $key => $label): ?>
        <a href="<?= $base ?>/marketing/templates?category=<?= $key ?>" class="btn btn-sm <?= $category === $key ? 'btn-primary' : 'btn-outline-primary' ?>"><?= $label ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($templates)): ?>
    <div class="alert alert-info">Koi template nahi mila. Admin se template banane ko kaho.</div>
    <?php else: ?>
    <div class="row g-3">
        <?php foreach ($templates as $t): ?>
        <div class="col-md-4 col-lg-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center" style="background:<?= htmlspecialchars($t['bg_color']) ?>;border-radius:12px 12px 0 0;min-height:120px;display:flex;flex-direction:column;justify-content:center">
                    <div style="color:<?= htmlspecialchars($t['accent_color']) ?>;font-weight:800"><?= htmlspecialchars($t['title_text'] ?: $t['name']) ?></div>
                    <?php if ($t['subtitle_text']): ?><div class="text-white small"><?= htmlspecialchars($t['subtitle_text']) ?></div><?php endif; ?>
                </div>
                <div class="card-body">
                    <h6 class="mb-0"><?= htmlspecialchars($t['name']) ?></h6>
                    <small class="text-muted"><?= ucfirst($t['category']) ?> • <?= (int)$t['canvas_w'] ?>×<?= (int)$t['canvas_h'] ?> • used <?= (int)$t['use_count'] ?>x</small>
                    <button class="btn btn-primary btn-sm w-100 mt-2" onclick="openCustomize('<?= htmlspecialchars($t['slug']) ?>','<?= htmlspecialchars($t['name']) ?>')">
                        <i class="fas fa-pen me-1"></i>Customize
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- Customize Modal -->
<div class="modal fade" id="customizeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customizeTitle">Customize Template</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="customizeForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="slug" id="customizeSlug">
                    <div class="mb-2"><label class="form-label">Photo (optional)</label><input type="file" class="form-control" name="photo" accept="image/*"></div>
                    <div class="mb-2"><input type="text" class="form-control" name="price" placeholder="Price (e.g. ₹999/sq.ft.)"></div>
                    <div class="mb-2"><input type="text" class="form-control" name="location" placeholder="Location"></div>
                    <div class="mb-2"><input type="text" class="form-control" name="offer" placeholder="Offer"></div>
                    <div class="mb-2"><input type="text" class="form-control" name="property_type" placeholder="Type"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-magic me-1"></i>Render</button>
                </div>
            </form>
            <div id="customizeResult" class="p-3"></div>
        </div>
    </div>
</div>

<script nonce="<?= $GLOBALS['csp_nonce'] ?? '' ?>">
(function() {
    const base = '<?= $base ?>';
    let modal = null;

    window.openCustomize = function(slug, name) {
        document.getElementById('customizeSlug').value = slug;
        document.getElementById('customizeTitle').textContent = 'Customize: ' + name;
        document.getElementById('customizeResult').innerHTML = '';
        if (!modal) modal = new bootstrap.Modal(document.getElementById('customizeModal'));
        modal.show();
    };

    document.getElementById('customizeForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const el = document.getElementById('customizeResult');
        el.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Rendering...</div>';
        try {
            const resp = await fetch(base + '/marketing/template/render', { method: 'POST', body: new FormData(this) });
            const data = await resp.json();
            if (!data.success) {
                el.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                return;
            }
            let html = '<div class="alert alert-success">Done! <a href="' + data.url + '" download class="btn btn-sm btn-success ms-2">Download</a></div>';
            html += '<img src="' + data.url + '" class="img-fluid rounded mb-2">';
            if (data.whatsapp_share) {
                html += '<a href="' + data.whatsapp_share + '" target="_blank" class="btn btn-success btn-sm w-100">WhatsApp Share</a>';
            }
            el.innerHTML = html;
        } catch (err) {
            el.innerHTML = '<div class="alert alert-danger">Network error</div>';
        }
    });
})();
</script>