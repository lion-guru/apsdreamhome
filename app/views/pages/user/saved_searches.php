<?php
$page_title = $page_title ?? 'Saved Searches - APS Dream Home';
$current_page = 'saved-searches';

$user = $user ?? [];
$searches = $searches ?? [];
$alertLog = $alertLog ?? [];
$stats = $stats ?? ['my_searches' => 0, 'alerts_enabled' => 0];
$flash_success = $_SESSION['flash_success'] ?? '';
$flash_error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><i class="fas fa-bookmark text-primary me-2"></i><?= __('saved_page_title', null, 'Saved Searches') ?></h3>
            <p class="text-muted mb-0"><?= __('saved_page_subtitle', null, 'Save property searches to revisit them anytime and get email alerts on new matches.') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/user/saved-searches/manage-alerts" class="btn btn-outline-primary rounded-pill px-3">
                <i class="fas fa-bell me-2"></i><?= __('saved_btn_manage', null, 'Manage Alerts') ?>
            </a>
            <a href="<?= BASE_URL ?>/properties" class="btn btn-primary rounded-pill px-3 shadow-sm">
                <i class="fas fa-search me-2"></i><?= __('saved_btn_new', null, 'New Search') ?>
            </a>
        </div>
    </div>

    <?php if ($flash_success): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($flash_success ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if ($flash_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($flash_error ?? '') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon blue me-3"><i class="fas fa-bookmark"></i></div>
                        <div>
                            <div class="stat-value fs-3"><?= (int)($stats['my_searches'] ?? 0) ?></div>
                            <div class="stat-label"><?= __('saved_stat_saved', null, 'Saved Searches') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon green me-3"><i class="fas fa-bell"></i></div>
                        <div>
                            <div class="stat-value fs-3"><?= (int)($stats['alerts_enabled'] ?? 0) ?></div>
                            <div class="stat-label"><?= __('saved_stat_alerts_active', null, 'Email Alerts Active') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body aps-cp-card-body">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon orange me-3"><i class="fas fa-paper-plane"></i></div>
                        <div>
                            <div class="stat-value fs-3"><?= count($alertLog) ?></div>
                            <div class="stat-label"><?= __('saved_stat_alerts_sent', null, 'Alerts Sent (recent)') ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Saved Searches List -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-list text-primary me-2"></i><?= __('saved_section_my', null, 'My Saved Searches') ?></h5>
        </div>
        <div class="card-body aps-cp-card-body">
            <?php if (empty($searches)): ?>
                <div class="text-center py-5">
                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center mx-auto mb-4 style-78270">
                        <i class="fas fa-search-location fa-3x text-muted opacity-25"></i>
                    </div>
                    <h5 class="fw-bold"><?= __('saved_empty_title', null, 'No saved searches yet') ?></h5>
                    <p class="text-muted mx-auto style-44213"><?= __('saved_empty_desc', null, 'Apply filters on the properties page and click "Save this search" to get notified when new properties match your criteria.') ?></p>
                    <a href="<?= BASE_URL ?>/properties" class="btn btn-primary rounded-pill px-4 mt-2"><?= __('saved_btn_start', null, 'Start Searching') ?></a>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($searches as $search):
                        $filters = is_array($search['filters'] ?? null) ? $search['filters'] : (json_decode($search['filters'] ?? '{}', true) ?: []);
                        $filterBadges = [];
                        if (!empty($filters['q'])) $filterBadges[] = ['q', $filters['q']];
                        if (!empty($filters['type'])) $filterBadges[] = ['Type', ucfirst($filters['type'])];
                        if (!empty($filters['listing'])) $filterBadges[] = ['Listing', ucfirst($filters['listing'])];
                        if (!empty($filters['location'])) $filterBadges[] = ['Location', $filters['location']];
                        if (!empty($filters['min_price'])) $filterBadges[] = ['Min ₹', number_format($filters['min_price'])];
                        if (!empty($filters['max_price'])) $filterBadges[] = ['Max ₹', number_format($filters['max_price'])];
                        if (!empty($filters['bedrooms'])) $filterBadges[] = ['Beds', $filters['bedrooms'] . '+'];
                        if (!empty($filters['bathrooms'])) $filterBadges[] = ['Baths', $filters['bathrooms'] . '+'];
                        if (!empty($filters['furnished'])) $filterBadges[] = ['Furnished', ucfirst($filters['furnished'])];
                        if (!empty($filters['year_built'])) $filterBadges[] = ['Year â‰¥', $filters['year_built']];
                        if (!empty($filters['area_min'])) $filterBadges[] = ['Area â‰¥', $filters['area_min'] . ' sqft'];
                        if (!empty($filters['area_max'])) $filterBadges[] = ['Area â‰¤', $filters['area_max'] . ' sqft'];

                        $alertsOn = (int)($search['email_alerts'] ?? 0) === 1;
                        $queryString = http_build_query($filters);
                    ?>
                    <div class="col-md-6">
                        <div class="card border rounded-4 p-3 transition-hover h-100 <?= $alertsOn ? 'border-success' : '' ?>" data-search-id="<?= (int)$search['id'] ?>">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h6 class="fw-bold mb-1 flex-grow-1"><?= htmlspecialchars($search['name'] ?? 'Untitled Search') ?></h6>
                                <div class="d-flex gap-1">
                                    <?php if ($alertsOn): ?>
                                        <span class="badge bg-success-subtle text-success" title="Email alerts enabled"><i class="fas fa-bell"></i></span>
                                    <?php endif; ?>
                                    <?php if (!empty($search['is_favorite'])): ?>
                                        <span class="badge bg-warning-subtle text-warning"><i class="fas fa-star"></i></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <?php if (!empty($filterBadges)): ?>
                                <div class="mb-2">
                                    <?php foreach ($filterBadges as $b): ?>
                                        <span class="badge bg-light text-dark border me-1 mb-1"><?= htmlspecialchars($b[0] ?? '') ?>: <strong><?= htmlspecialchars($b[1] ?? '') ?></strong></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <div class="small text-muted mb-2">
                                <i class="fas fa-calendar me-1"></i><?= __('saved_saved_on', null, 'Saved') ?> <?= date('d M Y', strtotime($search['created_at'] ?? 'now')) ?>
                                <?php if (!empty($search['last_run_at'])): ?>
                                    &middot; <i class="fas fa-clock me-1"></i><?= __('saved_last_run_short', null, 'Last run') ?> <?= date('d M, H:i', strtotime($search['last_run_at'])) ?>
                                <?php endif; ?>
                                <?php if (isset($search['result_count'])): ?>
                                    &middot; <i class="fas fa-list me-1"></i><?= (int)$search['result_count'] ?> <?= __('saved_matches', null, 'matches') ?>
                                <?php endif; ?>
                            </div>

                            <div class="d-flex gap-2 flex-wrap mt-2">
                                <a href="<?= BASE_URL ?>/user/saved-searches/<?= (int)$search['id'] ?>/execute?to=properties" class="btn btn-sm btn-primary rounded-pill px-3" title="Run this search and view matches">
                                    <i class="fas fa-play me-1"></i><?= __('saved_btn_run', null, 'Run') ?>
                                </a>
                                <a href="<?= BASE_URL ?>/properties?<?= htmlspecialchars($queryString ?? '') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3" title="Open in properties page">
                                    <i class="fas fa-external-link-alt me-1"></i><?= __('saved_btn_open', null, 'Open') ?>
                                </a>
                                <button type="button" class="btn btn-sm <?= $alertsOn ? 'btn-success' : 'btn-outline-success' ?> rounded-pill px-3 js-toggle-alerts" data-search-id="<?= (int)$search['id'] ?>" data-enabled="<?= $alertsOn ? '1' : '0' ?>">
                                    <i class="fas fa-bell me-1"></i><?= $alertsOn ? __('saved_btn_alerts_on', null, 'Alerts On') : __('saved_btn_alerts_enable', null, 'Enable Alerts') ?>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 js-rename" data-search-id="<?= (int)$search['id'] ?>" data-current-name="<?= htmlspecialchars($search['name'] ?? '') ?>" title="Rename">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 js-delete" data-search-id="<?= (int)$search['id'] ?>" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Alert Log -->
    <?php if (!empty($alertLog)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0"><i class="fas fa-bell text-success me-2"></i><?= __('saved_recent_activity', null, 'Recent Alert Activity') ?></h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                            <tr>
                                <th><?= __('saved_th_date', null, 'Date') ?></th>
                                <th><?= __('saved_th_search', null, 'Search') ?></th>
                                <th><?= __('saved_th_property', null, 'Property') ?></th>
                                <th><?= __('saved_th_status', null, 'Status') ?></th>
                            </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($alertLog as $log): ?>
                            <tr>
                                <td><small><?= date('d M Y, H:i', strtotime($log['sent_at'] ?? 'now')) ?></small></td>
                                <td><?= htmlspecialchars($log['search_name'] ?? '—') ?></td>
                                <td>
                                    <?php if (!empty($log['property_id'])): ?>
                                        <a href="<?= BASE_URL ?>/listing/<?= (int)$log['property_id'] ?>" target="_blank">
                                            <?= htmlspecialchars($log['property_name'] ?? 'Property #' . $log['property_id']) ?>
                                        </a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = match($log['email_status'] ?? 'pending') {
                                        'sent' => 'success',
                                        'failed' => 'danger',
                                        default => 'warning'
                                    };
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>-subtle text-<?= $statusClass ?>"><?= htmlspecialchars(ucfirst($log['email_status'] ?? 'pending')) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.transition-hover { transition: all 0.3s ease; }
.transition-hover:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.05) !important; }
.stat-icon {
    width: 50px; height: 50px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; color: white;
}
.stat-icon.blue { background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%); }
.stat-icon.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
.stat-icon.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.stat-value { font-weight: 700; line-height: 1; }
.stat-label { font-size: 0.85rem; color: #6c757d; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const baseUrl = '<?= BASE_URL ?>';

    document.querySelectorAll('.js-toggle-alerts').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.searchId;
            const newEnabled = this.dataset.enabled !== '1';
            this.disabled = true;
            try {
                const res = await fetch(baseUrl + '/user/saved-searches/' + id + '/alerts', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ email_alerts: newEnabled ? 1 : 0 })
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed: ' + (data.error || 'unknown'));
                    this.disabled = false;
                }
            } catch (e) {
                alert('Network error');
                this.disabled = false;
            }
        });
    });

    document.querySelectorAll('.js-delete').forEach(btn => {
        btn.addEventListener('click', async function() {
            if (!confirm('Delete this saved search?')) return;
            const id = this.dataset.searchId;
            const card = this.closest('.col-md-6');
            card.style.opacity = '0.4';
            try {
                const res = await fetch(baseUrl + '/user/saved-searches/' + id, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();
                if (data.success) {
                    card.remove();
                } else {
                    alert('Failed: ' + (data.error || 'unknown'));
                    card.style.opacity = '1';
                }
            } catch (e) {
                alert('Network error');
                card.style.opacity = '1';
            }
        });
    });

    document.querySelectorAll('.js-rename').forEach(btn => {
        btn.addEventListener('click', async function() {
            const id = this.dataset.searchId;
            const currentName = this.dataset.currentName;
            const newName = prompt('Rename saved search:', currentName);
            if (!newName || newName === currentName) return;
            try {
                const res = await fetch(baseUrl + '/user/saved-searches/' + id + '/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: JSON.stringify({ name: newName })
                });
                const data = await res.json();
                if (data.success) {
                    location.reload();
                } else {
                    alert('Failed: ' + (data.error || 'unknown'));
                }
            } catch (e) {
                alert('Network error');
            }
        });
    });
});
</script>
