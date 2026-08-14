<?php
$page_title = $page_title ?? 'System Health';
$content = $content ?? '';
ob_start();
$report = $report ?? [];
$php = $report['php'] ?? [];
$db = $report['database'] ?? [];
$disk = $report['disk'] ?? [];
$mem = $report['memory'] ?? [];
$cache = $report['cache'] ?? [];
$tables = $report['tables'] ?? [];
$services = $report['services'] ?? [];

function statusColor($status) {
  return $status === 'ok' ? 'success' : ($status === 'warning' ? 'warning' : 'danger');
}
?>
<div class="container-fluid py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0"><i class="fas fa-heartbeat me-2"></i>System Health</h1>
    <small class="text-muted">Generated in <?= $report['execution_time_ms'] ?? 0 ?>ms at <?= $report['timestamp'] ?? '' ?></small>
  </div>

  <div class="row mb-3">
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-<?= statusColor($db['status'] ?? 'ok') ?> text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Database</small><h5 class="mb-0"><?= ucfirst($db['status'] ?? 'unknown') ?></h5><small><?= $db['tables'] ?? 0 ?> tables Â· <?= $db['size_mb'] ?? 0 ?> MB</small></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-<?= statusColor($disk['status'] ?? 'ok') ?> text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Disk</small><h5 class="mb-0"><?= $disk['used_pct'] ?? 0 ?>% used</h5><small><?= $disk['free_gb'] ?? 0 ?> GB free of <?= $disk['total_gb'] ?? 0 ?> GB</small></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-<?= statusColor($mem['status'] ?? 'ok') ?> text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Memory</small><h5 class="mb-0"><?= $mem['used_mb'] ?? 0 ?> MB</h5><small>Peak: <?= $mem['peak_mb'] ?? 0 ?> MB / <?= $mem['limit'] ?? '?' ?></small></div>
    </div></div>
    <div class="col-md-3"><div class="card border-0 shadow-sm bg-<?= statusColor($tables['status'] ?? 'ok') ?> text-white">
      <div class="card-body aps-cp-card-body"><small class="opacity-75">Core Tables</small><h5 class="mb-0"><?= $tables['ok'] ?? 0 ?>/<?= $tables['checked'] ?? 0 ?></h5><small><?= count($tables['missing'] ?? []) ?> missing</small></div>
    </div></div>
  </div>

  <div class="row">
    <div class="col-lg-6 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="fab fa-php me-2"></i>PHP Environment</h5></div>
        <div class="card-body aps-cp-card-body">
          <div class="table-responsive"><table class="table table-sm mb-0">
            <tr><th>Version</th><td><code><?= htmlspecialchars($php['version'] ?? '') ?></code></td></tr>
            <tr><th>OS</th><td><?= htmlspecialchars($php['os'] ?? '') ?></td></tr>
            <tr><th>SAPI</th><td><code><?= htmlspecialchars($php['sapi'] ?? '') ?></code></td></tr>
            <tr><th>Max Execution Time</th><td><?= htmlspecialchars($php['max_execution_time'] ?? '') ?>s</td></tr>
            <tr><th>Memory Limit</th><td><?= htmlspecialchars($php['memory_limit'] ?? '') ?></td></tr>
            <tr><th>Upload Max</th><td><?= htmlspecialchars($php['upload_max_filesize'] ?? '') ?></td></tr>
            <tr><th>POST Max</th><td><?= htmlspecialchars($php['post_max_size'] ?? '') ?></td></tr>
          </table></div>
          <h6 class="mt-3">Loaded Extensions</h6>
          <div>
            <?php foreach (($php['extensions'] ?? []) as $ext => $loaded): ?>
              <span class="badge bg-<?= $loaded ? 'success' : 'secondary' ?> me-1 mb-1"><?= $ext ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-database me-2"></i>Database</h5></div>
        <div class="card-body aps-cp-card-body">
          <div class="table-responsive"><table class="table table-sm mb-0">
            <tr><th>Version</th><td><code><?= htmlspecialchars($db['version'] ?? 'unknown') ?></code></td></tr>
            <tr><th>Total Tables</th><td><strong><?= $db['tables'] ?? 0 ?></strong></td></tr>
            <tr><th>Database Size</th><td><?= $db['size_mb'] ?? 0 ?> MB</td></tr>
            <tr><th>Throughput</th><td><?= $db['queries_per_sec'] ?? 0 ?> queries/sec</td></tr>
            <tr><th>Uptime</th><td><?= $db['uptime_days'] ?? 0 ?> days</td></tr>
          </table></div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-hdd me-2"></i>Storage</h5></div>
        <div class="card-body aps-cp-card-body">
          <div class="mb-3">
            <div class="d-flex justify-content-between"><strong>Disk Usage</strong><span><?= $disk['used_gb'] ?? 0 ?> / <?= $disk['total_gb'] ?? 0 ?> GB</span></div>
            <div class="progress mt-1" class="style-51309">
              <div class="progress-bar bg-<?= ($disk['used_pct'] ?? 0) > 90 ? 'danger' : (($disk['used_pct'] ?? 0) > 70 ? 'warning' : 'success') ?>" class="style-28798"><?= $disk['used_pct'] ?? 0 ?>%</div>
            </div>
          </div>
          <h6>Cache</h6>
          <div class="table-responsive"><table class="table table-sm mb-0">
            <tr><th>Files</th><td><?= $cache['files'] ?? 0 ?></td></tr>
            <tr><th>Size</th><td><?= $cache['size_mb'] ?? 0 ?> MB</td></tr>
            <tr><th>Writable</th><td><span class="badge bg-<?= ($cache['writable'] ?? false) ? 'success' : 'danger' ?>"><?= ($cache['writable'] ?? false) ? 'Yes' : 'No' ?></span></td></tr>
            <tr><th>Path</th><td><code class="small"><?= htmlspecialchars($cache['path'] ?? '') ?></code></td></tr>
          </table></div>
        </div>
      </div>
    </div>

    <div class="col-lg-6 mb-3">
      <div class="card shadow-sm h-100">
        <div class="card-header bg-white"><h5 class="mb-0"><i class="fas fa-cogs me-2"></i>Services Loaded</h5></div>
        <div class="card-body p-0">
          <div class="table-responsive"><table class="table table-sm mb-0">
            <thead class="table-light">
              <tr><th>Service</th><th>Status</th><th>Size</th><th>Path</th></tr>
            </thead>
            <tbody>
              <?php foreach ($services as $name => $info): ?>
                <tr>
                  <td><strong><?= htmlspecialchars($name) ?></strong></td>
                  <td><span class="badge bg-<?= ($info['loaded'] ?? false) ? 'success' : 'danger' ?>"><?= ($info['loaded'] ?? false) ? 'Loaded' : 'Missing' ?></span></td>
                  <td><small><?= $info['size_kb'] ?? 0 ?> KB</small></td>
                  <td><code class="small"><?= htmlspecialchars($info['path'] ?? '') ?></code></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table></div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
$content = ob_get_clean();
require_once APP_PATH . '/views/layouts/admin.php';
