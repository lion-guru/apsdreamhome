<?php
$page_title = $page_title ?? 'Embed Code';
$form = $form ?? null;
$embed_code = $embed_code ?? '';
$script_code = $script_code ?? '';
$baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h2 class="mb-1 fw-bold"><i class="fas fa-code me-2 text-primary"></i>Embed Code</h2>
            <p class="text-muted mb-0">Copy and paste these codes to embed the form on your website</p>
        </div>
        <a href="<?= BASE_URL ?>/admin/crm/forms" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Back to Forms</a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i><?= $_SESSION['success']; unset($_SESSION['success']); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <!-- iframe Method -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-window-maximize me-2"></i>iframe Embed</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Copy this code and paste it wherever you want the form to appear:</p>
                    <div class="position-relative">
                        <textarea class="form-control font-monospace" rows="3" readonly id="iframeCode"><?= htmlspecialchars($embed_code) ?></textarea>
                        <button class="btn btn-sm btn-primary position-absolute" class="style-71993" onclick="copyToClipboard('iframeCode')"><i class="fas fa-copy"></i> Copy</button>
                    </div>
                    <div class="mt-3 p-3 bg-light rounded">
                        <h6 class="fw-bold text-muted mb-2"><i class="fas fa-info-circle me-1"></i> iframe Settings</h6>
                        <code class="d-block">width="100%" height="500" frameborder="0"</code>
                        <small class="text-muted d-block mt-1">Adjust height as needed. The form will be responsive by default.</small>
                    </div>
                </div>
            </div>

            <!-- JavaScript Embed -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fas fa-code me-2"></i>JavaScript Embed (Recommended)</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Add this code to your website's HTML. The form will load in a popup/modal:</p>
                    <div class="position-relative">
                        <textarea class="form-control font-monospace" rows="2" readonly id="scriptCode"><?= htmlspecialchars($script_code) ?></textarea>
                        <button class="btn btn-sm btn-primary position-absolute" class="style-71993" onclick="copyToClipboard('scriptCode')"><i class="fas fa-copy"></i> Copy</button>
                    </div>
                </div>
            </div>

            <!-- Direct URL -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-link me-2"></i>Direct URL</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Share this link directly via email, WhatsApp, or social media:</p>
                    <div class="position-relative">
                        <input type="text" class="form-control" readonly id="directUrl" value="<?= $baseUrl ?>/form/<?= $form['id'] ?? '' ?>">
                        <button class="btn btn-sm btn-success position-absolute" class="style-71993" onclick="copyToClipboard('directUrl')"><i class="fas fa-copy"></i> Copy</button>
                    </div>
                    <div class="mt-3 d-flex gap-2 flex-wrap">
                        <a href="<?= $baseUrl ?>/form/<?= $form['id'] ?? '' ?>" target="_blank" class="btn btn-outline-primary btn-sm"><i class="fas fa-external-link-alt me-1"></i> Open Form</a>
                        <a href="https://wa.me/?text=<?= urlencode('Check out our property inquiry form: ' . $baseUrl . '/form/' . ($form['id'] ?? '')) ?>" target="_blank" class="btn btn-success btn-sm"><i class="fab fa-whatsapp me-1"></i> Share on WhatsApp</a>
                        <a href="mailto:?subject=<?= urlencode($form['name'] ?? 'Lead Form') ?>&body=<?= urlencode('Please fill out this form: ' . $baseUrl . '/form/' . ($form['id'] ?? '')) ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-envelope me-1"></i> Share via Email</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar: Tips -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-warning">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-lightbulb me-2"></i>Embedding Tips</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold">For WordPress:</h6>
                    <p class="text-muted" class="style-87981">Paste the iframe code into a Custom HTML block in your page editor.</p>
                    <h6 class="fw-bold">For Shopify:</h6>
                    <p class="text-muted" class="style-87981">Add the iframe code to your theme's template files or use a Custom Liquid section.</p>
                    <h6 class="fw-bold">For Wix/Squarespace:</h6>
                    <p class="text-muted" class="style-87981">Use the "Embed HTML" or "Custom Code" widget to paste the iframe code.</p>
                    <h6 class="fw-bold">For Social Media:</h6>
                    <p class="text-muted" class="style-87981">Share the Direct URL via WhatsApp, Facebook, Instagram stories, or email campaigns.</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-bar me-2"></i>Form Stats</h6>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        $db = \App\Core\Database\Database::getInstance()->getConnection();
                        $submissions = (int)$db->query("SELECT COUNT(*) FROM leads WHERE form_id = " . ($form['id'] ?? 0))->fetchColumn();
                    } catch (\Throwable $e) { $submissions = 0; }
                    ?>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted">Total Submissions</span>
                        <strong class="text-primary fs-5"><?= number_format($submissions) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Created</span>
                        <span class="fw-semibold"><?= date('d M Y', strtotime($form['created_at'] ?? 'now')) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(id) {
    const el = document.getElementById(id);
    el.select();
    document.execCommand('copy');
    const btn = el.nextElementSibling || el.parentElement.querySelector('button');
    const orig = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    setTimeout(() => { btn.innerHTML = orig; }, 2000);
}
</script>