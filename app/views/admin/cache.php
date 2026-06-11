<?php
/**
 * @var array $stats     Cache statistics from CacheService::getStats()
 * @var array $test      Connection test result from CacheService::testConnection()
 * @var string $driver   'redis' or 'file (fallback)'
 * @var array $hotpath   HotPathCacheService::getStats() payload
 * @var string $page_title
 * @var string $page_heading
 */
$stats = $stats ?? [];
$test  = $test  ?? [];
$driver = $driver ?? 'unknown';
$hotpath = $hotpath ?? ['paths' => [], 'total' => ['hits' => 0, 'misses' => 0, 'calls' => 0, 'hit_rate' => 0.0]];

$redisInfo  = $stats['redis']  ?? [];
$fileInfo   = $stats['file']   ?? [];
$session    = $stats['session'] ?? [];
$hitRate    = $stats['hit_rate']?? 0.0;
$available  = (bool)($stats['available'] ?? false);
$host       = $stats['host'] ?? '127.0.0.1';
$port       = $stats['port'] ?? 6379;
$prefix     = $stats['prefix']?? 'apsdream_';

// Hot path metadata
$hotPathsMeta = [
    'property_list'        => ['ttl' => 300,  'label' => 'Property Listings',     'icon' => 'fa-list'],
    'header_projects'      => ['ttl' => 600,  'label' => 'Header Projects',       'icon' => 'fa-sitemap'],
    'admin_dash_kpis'      => ['ttl' => 120,  'label' => 'Admin Dashboard KPIs',  'icon' => 'fa-tachometer-alt'],
    'home_featured'        => ['ttl' => 900,  'label' => 'Home Featured',         'icon' => 'fa-home'],
    'saved_searches_count' => ['ttl' => 30,   'label' => 'Saved Searches Count',  'icon' => 'fa-bookmark'],
];

// Flash messages from controller (BaseController::setFlash uses bare keys)
$flashSuccess = $_SESSION['success'] ?? $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['error']   ?? $_SESSION['flash_error']   ?? null;
$flashWarning = $_SESSION['warning'] ?? $_SESSION['flash_warning'] ?? null;
unset($_SESSION['success'], $_SESSION['flash_success'],
      $_SESSION['error'],   $_SESSION['flash_error'],
      $_SESSION['warning'], $_SESSION['flash_warning']);
?>
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-bolt me-2 text-warning"></i><?= __('admin_cache_title', null, 'Cache Management') ?></h4>
            <p class="text-muted small mb-0"><?= __('admin_cache_subtitle', null, 'Inspect and control the Redis + file cache layers powering hot queries.') ?></p>
        </div>
        <div>
            <span class="badge bg-<?= $available ? 'success' : 'secondary' ?> fs-6">
                <i class="fas fa-circle me-1"></i><?= htmlspecialchars(ucfirst($driver)) ?>
            </span>
        </div>
    </div>

    <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($flashSuccess) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flashWarning): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><?= htmlspecialchars($flashWarning) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-times-circle me-2"></i><?= htmlspecialchars($flashError) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Driver & Connection -->
        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase small mb-2"><?= __('admin_cache_driver', null, 'Cache Driver') ?></h6>
                            <h3 class="fw-bold mb-0"><?= htmlspecialchars(strtoupper($driver)) ?></h3>
                        </div>
                        <i class="fas fa-server fa-2x text-primary opacity-50"></i>
                    </div>
                    <p class="text-muted small mb-1 mt-3">
                        Host: <code><?= htmlspecialchars($host) ?>:<?= htmlspecialchars((string)$port) ?></code>
                    </p>
                    <p class="text-muted small mb-0">
                        Prefix: <code><?= htmlspecialchars($prefix) ?></code>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase small mb-2"><?= __('admin_cache_hit_rate', null, 'Hit Rate') ?></h6>
                            <h3 class="fw-bold mb-0"><?= number_format($hitRate, 1) ?>%</h3>
                        </div>
                        <i class="fas fa-chart-line fa-2x text-success opacity-50"></i>
                    </div>
                    <p class="text-muted small mb-0 mt-3">
                        Hits: <strong><?= (int)($redisInfo['hits'] ?? 0) + (int)($session['file_hits'] ?? 0) ?></strong>
                        &nbsp;|&nbsp; Misses: <strong><?= (int)($redisInfo['misses'] ?? 0) + (int)($session['file_misses'] ?? 0) ?></strong>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase small mb-2"><?= __('admin_cache_redis_keys', null, 'Redis Keys') ?></h6>
                            <h3 class="fw-bold mb-0"><?= number_format((int)($redisInfo['size'] ?? 0)) ?></h3>
                        </div>
                        <i class="fas fa-key fa-2x text-info opacity-50"></i>
                    </div>
                    <p class="text-muted small mb-0 mt-3">
                        Sets: <?= (int)($redisInfo['sets'] ?? 0) ?>
                        &nbsp;|&nbsp; Deletes: <?= (int)($redisInfo['deletes'] ?? 0) ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card shadow-sm h-100">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted text-uppercase small mb-2"><?= __('admin_cache_file_cache', null, 'File Cache') ?></h6>
                            <h3 class="fw-bold mb-0"><?= (int)($fileInfo['active_files'] ?? 0) ?></h3>
                        </div>
                        <i class="fas fa-folder-open fa-2x text-warning opacity-50"></i>
                    </div>
                    <p class="text-muted small mb-0 mt-3">
                        Size: <strong><?= htmlspecialchars($fileInfo['total_size'] ?? '0 bytes') ?></strong>
                        &nbsp;|&nbsp; Expired: <?= (int)($fileInfo['expired_files'] ?? 0) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Action cards -->
    <div class="row g-4 mt-1">
        <div class="col-md-4">
            <div class="card shadow-sm text-center h-100">
                <div class="card-body py-4">
                    <i class="fas fa-database fa-3x text-warning mb-3"></i>
                    <h5>Flush All Cache</h5>
                    <p class="text-muted small">Clear both Redis and file cache. Use when you need a clean slate.</p>
                    <form method="POST" action="<?= htmlspecialchars((defined('BASE_URL') ? BASE_URL : '') . '/admin/cache/flush') ?>" onsubmit="return confirm('Clear ALL cache layers?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-trash me-1"></i>Flush All
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center h-100">
                <div class="card-body py-4">
                    <i class="fas fa-bolt fa-3x text-danger mb-3"></i>
                    <h5>Flush Redis Only</h5>
                    <p class="text-muted small">Clear Redis but keep the file cache intact. Safe fallback still works.</p>
                    <form method="POST" action="<?= htmlspecialchars((defined('BASE_URL') ? BASE_URL : '') . '/admin/cache/redis/flush') ?>" onsubmit="return confirm('Clear Redis only?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-danger" <?= $available ? '' : 'disabled' ?>>
                            <i class="fas fa-bolt me-1"></i>Flush Redis
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm text-center h-100">
                <div class="card-body py-4">
                    <i class="fas fa-plug fa-3x text-info mb-3"></i>
                    <h5>Test Connection</h5>
                    <p class="text-muted small">
                        <?php if ($available): ?>
                            Connected in <strong><?= htmlspecialchars((string)($test['latency_ms'] ?? '0')) ?>ms</strong>.
                        <?php else: ?>
                            Not available. Will fall back to file cache.
                        <?php endif; ?>
                    </p>
                    <form method="POST" action="<?= htmlspecialchars((defined('BASE_URL') ? BASE_URL : '') . '/admin/cache/test') ?>">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-info text-white">
                            <i class="fas fa-sync me-1"></i>Test Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Hot-Path Cache Stats -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0"><i class="fas fa-fire text-danger me-2"></i>Hot-Path Cache (per-path hit/miss)</h6>
            <form method="POST" action="<?= htmlspecialchars((defined('BASE_URL') ? BASE_URL : '') . '/admin/cache/hotpath/flush') ?>" onsubmit="return confirm('Clear all hot-path cache keys?');" class="d-inline">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-trash me-1"></i>Clear Hot Path Cache
                </button>
            </form>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-3 mb-3">
                <?php foreach ($hotPathsMeta as $pathKey => $meta):
                    $s = $hotpath['paths'][$pathKey] ?? ['hits' => 0, 'misses' => 0, 'calls' => 0, 'hit_rate' => 0.0];
                    $total = (int)$s['calls'];
                    $hits  = (int)$s['hits'];
                    $miss  = (int)$s['misses'];
                    $rate  = (float)$s['hit_rate'];
                ?>
                <div class="col-md-6 col-xl-4">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1 small text-uppercase text-muted">
                                    <i class="fas <?= htmlspecialchars($meta['icon']) ?> me-1"></i>
                                    <?= htmlspecialchars($meta['label']) ?>
                                </h6>
                                <code class="small text-muted"><?= htmlspecialchars($pathKey) ?></code>
                            </div>
                            <span class="badge bg-info">TTL <?= (int)$meta['ttl'] ?>s</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-end">
                            <div>
                                <div class="h4 mb-0 fw-bold"><?= number_format($rate, 1) ?>%</div>
                                <div class="small text-muted">hit rate</div>
                            </div>
                            <div class="text-end small">
                                <div><span class="badge bg-success"><?= $hits ?> hits</span></div>
                                <div class="mt-1"><span class="badge bg-warning text-dark"><?= $miss ?> misses</span></div>
                                <div class="mt-1 text-muted"><?= $total ?> calls</div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="row g-3 small text-muted">
                <div class="col-6 col-md-3"><strong>Aggregate hits:</strong> <?= (int)($hotpath['total']['hits'] ?? 0) ?></div>
                <div class="col-6 col-md-3"><strong>Aggregate misses:</strong> <?= (int)($hotpath['total']['misses'] ?? 0) ?></div>
                <div class="col-6 col-md-3"><strong>Aggregate calls:</strong> <?= (int)($hotpath['total']['calls'] ?? 0) ?></div>
                <div class="col-6 col-md-3"><strong>Aggregate hit rate:</strong> <?= number_format((float)($hotpath['total']['hit_rate'] ?? 0), 1) ?>%</div>
            </div>
        </div>
    </div>

    <!-- Hot-cache key reference -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fas fa-fire text-danger me-2"></i>Hot Cache Keys</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Key Pattern</th>
                        <th>TTL</th>
                        <th>Purpose</th>
                        <th>Invalidation Hook</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>admin_menu_role_*</code></td>
                        <td><span class="badge bg-info">1h</span></td>
                        <td>Admin sidebar menu (per role)</td>
                        <td><code>CacheService::invalidateAdminMenu()</code></td>
                    </tr>
                    <tr>
                        <td><code>header_projects_all</code></td>
                        <td><span class="badge bg-info">5m</span></td>
                        <td>Header projects/locations dropdown</td>
                        <td><code>CacheService::invalidateHeaderProjects()</code></td>
                    </tr>
                    <tr>
                        <td><code>unread_count_user_*</code></td>
                        <td><span class="badge bg-warning">30s</span></td>
                        <td>User unread notification badge</td>
                        <td><code>CacheService::invalidateUnreadCount($uid)</code></td>
                    </tr>
                    <tr>
                        <td><code>admin_dash_stats</code></td>
                        <td><span class="badge bg-warning">2m</span></td>
                        <td>Admin dashboard KPI tiles</td>
                        <td><code>CacheService::invalidateAdminDashboard()</code></td>
                    </tr>
                    <tr>
                        <td><code>property_filters_all</code></td>
                        <td><span class="badge bg-info">1h</span></td>
                        <td>Property list filter options</td>
                        <td><code>CacheService::invalidatePropertyFilters()</code></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Session stats (in-process) -->
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white">
            <h6 class="mb-0"><i class="fas fa-clock text-secondary me-2"></i>This-Page Statistics</h6>
        </div>
        <div class="card-body aps-cp-card-body">
            <div class="row g-3 small">
                <div class="col-6 col-md-3"><strong>Redis hits:</strong> <?= (int)($session['redis_hits'] ?? 0) ?></div>
                <div class="col-6 col-md-3"><strong>Redis misses:</strong> <?= (int)($session['redis_misses'] ?? 0) ?></div>
                <div class="col-6 col-md-3"><strong>File hits:</strong> <?= (int)($session['file_hits'] ?? 0) ?></div>
                <div class="col-6 col-md-3"><strong>File misses:</strong> <?= (int)($session['file_misses'] ?? 0) ?></div>
                <div class="col-6 col-md-3"><strong>Redis errors:</strong> <?= (int)($redisInfo['errors'] ?? 0) ?></div>
                <div class="col-6 col-md-3"><strong>Evictions:</strong> <?= (int)($redisInfo['evictions'] ?? 0) ?></div>
                <div class="col-6 col-md-3"><strong>Invalidations:</strong> <?= (int)($session['invalidations'] ?? 0) ?></div>
                <div class="col-6 col-md-3"><strong>Updated:</strong> <?= date('H:i:s') ?></div>
            </div>
        </div>
    </div>
</div>
