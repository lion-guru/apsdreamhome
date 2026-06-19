<?php
$page_title = $page_title ?? __('saved_res_page_title', null, 'Saved Search Results');
$current_page = 'saved-searches';
$user = $user ?? [];
$search = $search ?? [];
$matches = $matches ?? [];
$count = $count ?? 0;

$filters = is_array($search['filters'] ?? null) ? $search['filters'] : (json_decode($search['filters'] ?? '{}', true) ?: []);
$queryString = http_build_query($filters);
$alertsOn = (int)($search['email_alerts'] ?? 0) === 1;
?>

<div class="container py-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/user/saved-searches"><?= __('saved_res_breadcrumb', null, 'Saved Searches') ?></a></li>
            <li class="breadcrumb-item active"><?= htmlspecialchars($search['name'] ?? 'Search') ?></li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h3 class="fw-bold mb-1"><?= htmlspecialchars($search['name'] ?? 'Saved Search') ?></h3>
            <p class="text-muted mb-0">
                <strong><?= number_format($count) ?></strong> <?= __('saved_res_matching', null, 'matching properties') ?>
                <?php if (!empty($search['last_run_at'])): ?>
                    &middot; <?= __('saved_res_last_run', null, 'Last run') ?> <?= date('d M Y, H:i', strtotime($search['last_run_at'])) ?>
                <?php endif; ?>
            </p>
        </div>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= BASE_URL ?>/properties?<?= htmlspecialchars($queryString) ?>" class="btn btn-outline-primary rounded-pill">
                <i class="fas fa-external-link-alt me-1"></i><?= __('saved_res_open_props', null, 'Open in Properties Page') ?>
            </a>
            <button type="button" class="btn <?= $alertsOn ? 'btn-success' : 'btn-outline-success' ?> rounded-pill js-toggle-alert"
                    data-search-id="<?= (int)($search['id'] ?? 0) ?>" data-enabled="<?= $alertsOn ? '1' : '0' ?>">
                <i class="fas fa-bell me-1"></i><?= $alertsOn ? __('saved_res_alerts_on', null, 'Alerts On') : __('saved_res_alerts_enable', null, 'Enable Alerts') ?>
            </button>
        </div>
    </div>

    <?php if (empty($matches)): ?>
        <div class="card aps-cp-card">
            <div class="card-body text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h5 class="text-muted"><?= __('saved_res_no_match', null, 'No properties match this search right now') ?></h5>
                <p class="text-muted"><?= __('saved_res_alert_prompt', null, 'Enable email alerts to be notified when new properties matching your criteria are listed.') ?></p>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($matches as $p): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="card property-card h-100">
                        <div class="position-relative">
                            <?php
                            $imgSrc = !empty($p['image']) ? BASE_URL . '/assets/images/properties/' . htmlspecialchars($p['image']) : BASE_URL . '/assets/images/placeholder/property.svg';
                            ?>
                            <img src="<?= BASE_URL ?>/assets/images/placeholder/property.svg" class="card-img-top"
                                 style="height: 200px; object-fit: cover;"
                                 onerror="this.src='<?= BASE_URL ?>/assets/images/placeholder/property.svg'">
                            <span class="badge bg-<?= ($p['listing_type'] ?? 'sell') === 'rent' ? 'info' : 'success' ?> position-absolute top-0 end-0 m-2">
                                <?= ucfirst($p['listing_type'] ?? 'Sell') ?>
                            </span>
                        </div>
                        <div class="card-body aps-cp-card-body">
                            <h5 class="card-title"><?= htmlspecialchars($p['name'] ?? '') ?></h5>
                            <p class="text-muted small"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($p['address'] ?? $p['location'] ?? '') ?></p>
                            <div class="row small text-center border-top border-bottom py-2 mb-3 g-1">
                                <div class="col"><strong><?= number_format((float)($p['area_sqft'] ?? 0)) ?></strong> sqft</div>
                                <div class="col"><strong><?= ucfirst($p['property_type'] ?? 'Plot') ?></strong></div>
                                <?php if (!empty($p['bedrooms'])): ?>
                                    <div class="col"><strong><?= (int)$p['bedrooms'] ?></strong> BHK</div>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-success fw-bold fs-5">₹<?= number_format((float)($p['price'] ?? 0)) ?></span>
                                <a href="<?= BASE_URL ?>/listing/<?= (int)($p['id'] ?? 0) ?>" class="btn btn-sm btn-primary"><?= __('saved_res_view_details', null, 'View Details') ?></a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.querySelector('.js-toggle-alert');
    if (!btn) return;
    btn.addEventListener('click', async function() {
        const id = this.dataset.searchId;
        const newEnabled = this.dataset.enabled !== '1';
        this.disabled = true;
        try {
            const res = await fetch('<?= BASE_URL ?>/user/saved-searches/' + id + '/alerts', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ email_alerts: newEnabled ? 1 : 0 })
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
        this.disabled = false;
    });
});
</script>
