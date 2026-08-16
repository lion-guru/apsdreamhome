<?php
$page_title = $page_title ?? 'Backup Recovery Codes';
$content = $content ?? '';
ob_start();
?>
<div class="container py-5">
  <?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <strong><?= htmlspecialchars($_SESSION['flash_success'] ?? '') ?></strong>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
  <?php endif; ?>
  <?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show"><?= htmlspecialchars($_SESSION['flash_error'] ?? '') ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    <?php unset($_SESSION['flash_error']); ?>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-lg-8">
      <div class="card shadow border-warning">
        <div class="card-header bg-warning text-dark">
          <h4 class="mb-0"><i class="fas fa-key me-2"></i>Backup Recovery Codes</h4>
        </div>
        <div class="card-body aps-cp-card-body">
          <div class="alert alert-warning mb-4">
            <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-1"></i> Save these codes now!</h6>
            <p class="mb-0 small">
              Each code can be used <strong>once</strong> if you lose access to your authenticator app.
              They will not be shown again in full — keep them in a safe place.
            </p>
          </div>

          <div class="bg-light p-4 rounded mb-4" id="codes-box">
            <div class="row g-2" id="codes-grid">
              <?php foreach (($codes ?? []) as $i => $c): ?>
                <div class="col-md-6">
                  <div class="border rounded p-2 d-flex justify-content-between align-items-center bg-white">
                    <code class="fs-6 user-select-all fw-bold text-primary"><?= htmlspecialchars($c ?? '') ?></code>
                    <button type="button" class="btn btn-sm btn-link p-0 ms-2 copy-code" data-code="<?= htmlspecialchars($c ?? '') ?>" title="Copy">
                      <i class="fas fa-copy"></i>
                    </button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 mb-4">
            <button type="button" class="btn btn-primary" id="downloadBtn">
              <i class="fas fa-download me-1"></i> Download as .txt
            </button>
            <button type="button" class="btn btn-outline-primary" id="printBtn">
              <i class="fas fa-print me-1"></i> Print
            </button>
            <button type="button" class="btn btn-outline-secondary" id="copyAllBtn">
              <i class="fas fa-copy me-1"></i> Copy All
            </button>
            <a href="<?= BASE_URL ?>/user/two-factor" class="btn btn-outline-success ms-auto">
              <i class="fas fa-check me-1"></i> I've saved my codes
            </a>
          </div>

          <hr class="my-4">

          <h6><i class="fas fa-info-circle me-1"></i> How to use backup codes</h6>
          <ul class="small text-muted">
            <li>Go to the login page and enter your email + password as usual</li>
            <li>When prompted for a 2FA code, click <a href="<?= BASE_URL ?>/user/two-factor/recovery">"Use backup code instead"</a></li>
            <li>Enter one of the 8-character codes above</li>
            <li>Each code works <strong>only once</strong> — the system will mark it as used</li>
            <li>If you run out of codes, re-enable 2FA to get a fresh set of 8</li>
          </ul>
        </div>
        <div class="card-footer bg-light small text-muted">
          <i class="fas fa-shield-alt me-1"></i> Account: <strong><?= htmlspecialchars($user_email ?? '') ?></strong>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
(function() {
  const codes = <?= json_encode($codes ?? []) ?>;
  const filename = '2fa-backup-codes-<?= htmlspecialchars($user_email ?? 'user') ?>.txt';

  document.querySelectorAll('.copy-code').forEach(btn => {
    btn.addEventListener('click', () => {
      const code = btn.getAttribute('data-code');
      navigator.clipboard.writeText(code).then(() => {
        const orig = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-success"></i>';
        setTimeout(() => { btn.innerHTML = orig; }, 1500);
      });
    });
  });

  const downloadBtn = document.getElementById('downloadBtn');
  if (downloadBtn) {
    downloadBtn.addEventListener('click', () => {
      const content = 'APS Dream Home — Two-Factor Backup Recovery Codes\n' +
        'Account: <?= htmlspecialchars($user_email ?? '') ?>\n' +
        'Generated: ' + new Date().toISOString() + '\n' +
        '--------------------------------------------------------\n\n' +
        codes.map((c, i) => '  ' + (i + 1) + '. ' + c).join('\n') +
        '\n\n--------------------------------------------------------\n' +
        'Each code works ONCE. Keep this file in a safe place.\n';
      const blob = new Blob([content], { type: 'text/plain' });
      const url = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = url;
      a.download = filename;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    });
  }

  const printBtn = document.getElementById('printBtn');
  if (printBtn) {
    printBtn.addEventListener('click', () => {
      const w = window.open('', '_blank');
      const html = '<!DOCTYPE html><html><head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><title>2FA Backup Codes</title>' +
        '<style>body{font-family:system-ui;padding:30px;max-width:600px;margin:0 auto}' +
        'h1{color:#b45309}code{display:inline-block;padding:8px 12px;background:#f3f4f6;border-radius:6px;margin:4px;font-weight:bold}' +
        '.grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin:20px 0}' +
        '.footer{margin-top:30px;padding-top:15px;border-top:1px solid #ccc;font-size:12px;color:#666}' +
        '</style></head><body>' +
        '<h1>APS Dream Home — 2FA Backup Codes</h1>' +
        '<p>Account: <strong><?= htmlspecialchars($user_email ?? '') ?></strong></p>' +
        '<p>Generated: ' + new Date().toLocaleString() + '</p>' +
        '<div class="grid">' +
        codes.map(c => '<code>' + c + '</code>').join('') +
        '</div>' +
        '<p><strong>Important:</strong> Each code works only once. Keep this page in a safe place.</p>' +
        '<div class="footer">APS Dream Home &copy; ' + new Date().getFullYear() + '</div>' +
        '</body></html>';
      w.document.write(html);
      w.document.close();
      w.focus();
      setTimeout(() => w.print(), 250);
    });
  }

  const copyAllBtn = document.getElementById('copyAllBtn');
  if (copyAllBtn) {
    copyAllBtn.addEventListener('click', () => {
      const text = codes.join('\n');
      navigator.clipboard.writeText(text).then(() => {
        const orig = copyAllBtn.innerHTML;
        copyAllBtn.innerHTML = '<i class="fas fa-check me-1"></i> Copied!';
        setTimeout(() => { copyAllBtn.innerHTML = orig; }, 2000);
      });
    });
  }
})();
</script>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/base.php';
