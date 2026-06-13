<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><i class="fab fa-google me-2"></i>Google AdSense Settings</h1>
        <a href="<?= BASE_URL ?>/admin/ads" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Back to Ad Manager</a>
    </div>

    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="alert alert-<?= $_SESSION['flash_type'] ?? 'info' ?>"><?= htmlspecialchars($_SESSION['flash_message'] ?? '') ?><?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?></div>
    <?php endif; ?>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Enter your Google AdSense publisher ID to enable AdSense ads across the site. The ad slots will automatically switch from placeholder images to AdSense ads.
    </div>

    <div class="card shadow-sm">
        <div class="card-body aps-cp-card-body">
            <form method="post" action="<?= BASE_URL ?>/admin/ads/save-settings">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>">
                <div class="mb-3">
                    <label for="adsense_publisher_id" class="form-label"><i class="fab fa-google me-1"></i>AdSense Publisher ID</label>
                    <input type="text" class="form-control" id="adsense_publisher_id" name="adsense_publisher_id"
                           placeholder="ca-pub-xxxxxxxxxxxxxx"
                           value="<?= htmlspecialchars($adsense_publisher_id ?? '') ?>">
                    <div class="form-text">Example: <code>ca-pub-1234567890123456</code>. Find this in your AdSense account under Settings &gt; Account Info.</div>
                </div>

                <div class="mb-3">
                    <label for="auto_ad_code" class="form-label"><i class="fas fa-code me-1"></i>AdSense Auto Ads Code</label>
                    <textarea class="form-control" id="auto_ad_code" name="auto_ad_code" rows="6"
                              placeholder="&lt;script async src=&quot;https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-xxxxxxxxxxxxxx&quot; crossorigin=&quot;anonymous&quot;&gt;&lt;/script&gt;"><?= htmlspecialchars($auto_ad_code ?? '') ?></textarea>
                    <div class="form-text">Paste the Auto Ads script tag from your AdSense account. This will be injected into the site header.</div>
                </div>

                <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Save Settings</button>
            </form>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header aps-cp-card-header"><h5 class="mb-0"><i class="fas fa-question-circle me-1"></i>How to Get Your Publisher ID</h5></div>
        <div class="card-body aps-cp-card-body">
            <ol class="mb-0">
                <li>Go to <a href="https://adsense.google.com" target="_blank">Google AdSense</a> and sign in</li>
                <li>Click on <strong>Settings</strong> in the left sidebar</li>
                <li>Under <strong>Account Info</strong>, find your Publisher ID (starts with <code>ca-pub-</code>)</li>
                <li>Copy and paste it into the field above</li>
                <li>For Auto Ads, go to <strong>Ads &gt; Auto ads</strong> and copy the code snippet</li>
            </ol>
        </div>
    </div>
</div>
