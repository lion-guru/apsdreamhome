<?php
/**
 * @var array $backups
 * @var array $health
 * @var array $stats
 */
$pageTitle = $page_title ?? 'System Backup';
$flashSuccess = $_SESSION['success'] ?? $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['error']   ?? $_SESSION['flash_error']   ?? null;
unset($_SESSION['success'], $_SESSION['flash_success'], $_SESSION['error'], $_SESSION['flash_error']);

$baseUrl = defined('BASE_URL') ? BASE_URL : '';
$hStatus  = $health['status'] ?? 'stale';
$hBadge   = $hStatus === 'ok' ? 'success' : 'danger';
$hIcon    = $hStatus === 'ok' ? 'check-circle' : 'exclamation-triangle';
$hText    = $hStatus === 'ok' ? 'Healthy' : 'Stale / No recent backup';
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0"><?= htmlspecialchars($pageTitle ?? '') ?></h1>
            <p class="text-muted mb-0">Create, restore, upload and monitor database backups.</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-secondary" id="btn-refresh-health" title="Refresh health status">
                <i class="fas fa-sync"></i>
            </button>
            <a href="<?= htmlspecialchars($baseUrl ?? '') ?>/admin/backup/health" target="_blank" class="btn btn-outline-info" title="Open JSON health endpoint">
                <i class="fas fa-code"></i> Health JSON
            </a>
        </div>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-1"></i> <?= htmlspecialchars($flashSuccess ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-1"></i> <?= htmlspecialchars($flashError ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-<?= $hBadge ?> bg-opacity-10 p-3">
                            <i class="fas fa-<?= $hIcon ?> fa-2x text-<?= $hBadge ?>"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Backup Health</h6>
                        <h4 class="mb-0 text-<?= $hBadge ?>"><?= htmlspecialchars($hText ?? '') ?></h4>
                        <small class="text-muted">
                            <?php if (!empty($health['last_backup_at'])): ?>
                                Last: <?= htmlspecialchars($health['last_backup_at'] ?? '') ?>
                                (<?= $health['age_hours'] !== null ? htmlspecialchars((string) $health['age_hours']) : '?' ?> h ago)
                            <?php else: ?>
                                No successful backup yet
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="fas fa-database fa-2x text-primary"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Backups</h6>
                        <h4 class="mb-0"><?= (int)($stats['total'] ?? 0) ?></h4>
                        <small class="text-muted">
                            <?= (int)($stats['completed'] ?? 0) ?> ok &middot;
                            <?= (int)($stats['failed'] ?? 0) ?> failed
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="fas fa-hdd fa-2x text-success"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Total Size</h6>
                        <h4 class="mb-0"><?= htmlspecialchars($stats['total_size'] ?? '0 B') ?></h4>
                        <small class="text-muted">on disk</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-shrink-0 me-3">
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="fas fa-clock fa-2x text-info"></i>
                        </div>
                    </div>
                    <div>
                        <h6 class="text-muted mb-1">Newest</h6>
                        <h6 class="mb-0 small"><?= htmlspecialchars($stats['newest'] ?? 'Never') ?></h6>
                        <small class="text-muted">Oldest: <?= htmlspecialchars($stats['oldest'] ?? '-') ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-bolt me-1 text-warning"></i> Quick Actions</h5>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <form method="POST" action="<?= htmlspecialchars($baseUrl ?? '') ?>/admin/backup/create" onsubmit="return confirm('Create a full database backup now? This may take a few minutes.');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->getCsrfToken()) ?>">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus-circle me-1"></i> Create Full Backup Now
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#upload-panel">
                        <i class="fas fa-upload me-1"></i> Upload Existing Backup
                    </button>
                    <div class="collapse mt-2" id="upload-panel">
                        <form method="POST" action="<?= htmlspecialchars($baseUrl ?? '') ?>/admin/backup/upload" enctype="multipart/form-data" class="border rounded p-3 bg-light">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->getCsrfToken()) ?>">
                            <div class="mb-2">
                                <label class="form-label small mb-1">Backup file (.sql or .sql.gz, max 500 MB)</label>
                                <input type="file" name="backup_file" class="form-control form-control-sm" accept=".sql,.gz" required>
                            </div>
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-cloud-upload-alt me-1"></i> Upload
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-1 text-info"></i> About This Page</h5>
                </div>
                <div class="card-body small text-muted">
                    <p class="mb-2">
                        <strong>Automated schedule:</strong> A daily 2:00 AM backup is set up via Windows Task Scheduler
                        (<code>APS_DailyBackup</code>). It runs
                        <code>scripts/backup_cron.php</code> and writes logs to
                        <code>storage/logs/backup_cron.log</code>.
                    </p>
                    <p class="mb-2">
                        <strong>Health check:</strong> An hourly task (<code>scripts/backup_health_check.php</code>)
                        verifies a fresh backup exists. It emails the admin if the gap exceeds 24 hours.
                    </p>
                    <p class="mb-0">
                        <strong>Retention:</strong> Backups older than 30 days are pruned automatically.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-list me-1"></i> Backup Files</h5>
            <span class="badge bg-secondary"><?= count($backups) ?> total</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>File</th>
                            <th>Type</th>
                            <th>Size</th>
                            <th>Status</th>
                            <th>Started</th>
                            <th>Completed</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($backups)): ?>
                            <?php foreach ($backups as $b):
                                $bid   = (int)($b['id'] ?? 0);
                                $fpath = $b['file_path'] ?? '';
                                $fname = $fpath ? basename($fpath) : '(no file)';
                                $fsize = $b['file_size'] ?? 0;
                                $fsize = is_numeric($fsize) ? $fsize : 0;
                                $bStatus = $b['status'] ?? 'unknown';
                                $bType   = $b['backup_type'] ?? 'full';
                                $exists  = $fpath && file_exists($fpath);
                                $badgeClass = $bStatus === 'completed' ? 'success'
                                            : ($bStatus === 'failed'    ? 'danger'
                                            : ($bStatus === 'running'   ? 'warning' : 'secondary'));
                            ?>
                                <tr>
                                    <td><?= $bid ?></td>
                                    <td>
                                        <code class="small"><?= htmlspecialchars($fname ?? '') ?></code>
                                        <?php if (!$exists && $bStatus === 'completed'): ?>
                                            <i class="fas fa-exclamation-triangle text-warning ms-1" title="File missing on disk"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge bg-info bg-opacity-25 text-dark"><?= htmlspecialchars($bType ?? '') ?></span></td>
                                    <td><?= htmlspecialchars(format_bytes($fsize)) ?></td>
                                    <td><span class="badge bg-<?= $badgeClass ?>"><?= htmlspecialchars($bStatus ?? '') ?></span></td>
                                    <td class="small text-nowrap"><?= htmlspecialchars($b['started_at'] ?? '-') ?></td>
                                    <td class="small text-nowrap"><?= htmlspecialchars($b['completed_at'] ?? '-') ?></td>
                                    <td class="text-end text-nowrap">
                                        <?php if ($exists): ?>
                                            <a href="<?= htmlspecialchars($baseUrl ?? '') ?>/admin/backup/download/<?= $bid ?>" class="btn btn-sm btn-outline-primary" title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($bStatus === 'completed' && $exists): ?>
                                            <form method="POST" action="<?= htmlspecialchars($baseUrl ?? '') ?>/admin/backup/restore/<?= $bid ?>" class="d-inline" onsubmit="return confirm('RESTORE backup #<?= $bid ?> ?\n\nThis will REPLACE current database content. Make sure you have a fresh backup first.');">
                                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($this->getCsrfToken()) ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Restore">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="8" class="text-center text-muted py-4">
                                <i class="fas fa-inbox fa-2x d-block mb-2 opacity-50"></i>
                                No backups yet. Click <strong>Create Full Backup Now</strong> above to make one.
                            </td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Helper local to view (renamed to avoid $this_ prefix which PHP treats as $this->method)
function format_bytes($bytes): string
{
    if (!is_numeric($bytes) || $bytes <= 0) return '0 B';
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $i = 0;
    while ($bytes > 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 2) . ' ' . $units[$i];
}
?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btn-refresh-health');
    if (!btn) return;
    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.querySelector('i').classList.add('fa-spin');
        fetch('<?= htmlspecialchars($baseUrl ?? '') ?>/admin/backup/health', { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : { status: 'unknown' }; })
            .then(function (j) {
                var msg = 'Status: ' + (j.status || '?') +
                          ' | Age: ' + (j.age_hours !== null ? j.age_hours + 'h' : '?') +
                          ' | Last: ' + (j.last_backup_at || 'never');
                showToast(msg, 'info');
            })
            .catch(function (e) { showToast('Health check failed: ' + e.message, 'danger'); })
            .finally(function () {
                btn.disabled = false;
                btn.querySelector('i').classList.remove('fa-spin');
            });
    });
});
</script>
