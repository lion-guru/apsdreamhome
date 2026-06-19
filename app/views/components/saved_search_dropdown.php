<?php
/**
 * Saved Searches Dropdown
 *
 * Include in the properties page so logged-in users can quickly
 * apply one of their saved searches with a single click.
 *
 *     <?php include __DIR__ . '/../../components/saved_search_dropdown.php'; ?>
 *
 * Required globals:
 *   $savedSearches - array of saved search rows (with `filters` JSON)
 *   $isLoggedIn    - bool, whether to render at all
 */
$savedSearches = $savedSearches ?? [];
$isLoggedIn = $isLoggedIn ?? false;
?>

<?php if ($isLoggedIn): ?>
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2">
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="text-muted small fw-semibold">
                <i class="fas fa-bookmark text-primary me-1"></i>__('component_my_saved', 'My Saved:')
            </span>
            <?php if (empty($savedSearches)): ?>
                <span class="text-muted small">__('component_no_saved_searches', 'No saved searches. Apply some filters and click "Save Search".')</span>
            <?php else: ?>
                <div class="dropdown d-inline-block">
                    <button class="btn btn-sm btn-outline-primary dropdown-toggle rounded-pill" type="button" id="savedSearchesDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        __('component_choose_saved_search', 'Choose saved search')
                    </button>
                    <ul class="dropdown-menu shadow" aria-labelledby="savedSearchesDropdown" style="max-height: 350px; overflow-y: auto;">
                        <?php foreach ($savedSearches as $s):
                            $filters = is_array($s['filters'] ?? null) ? $s['filters'] : (json_decode($s['filters'] ?? '{}', true) ?: []);
                            $queryString = http_build_query($filters);
                            $alertsOn = (int)($s['email_alerts'] ?? 0) === 1;
                        ?>
                            <li>
                                <a class="dropdown-item d-flex align-items-center gap-2" href="<?= BASE_URL ?>/properties?<?= htmlspecialchars($queryString) ?>">
                                    <i class="fas fa-search text-muted"></i>
                                    <span class="flex-grow-1"><?= htmlspecialchars($s['name'] ?? 'Untitled') ?></span>
                                    <?php if ($alertsOn): ?>
                                        <i class="fas fa-bell text-success" title="htmlspecialchars(__('component_email_alerts_enabled', 'Email alerts enabled'))"></i>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-primary" href="<?= BASE_URL ?>/user/saved-searches">
                                <i class="fas fa-cog me-1"></i>__('component_manage_all_saved_searches', 'Manage all saved searches')
                            </a>
                        </li>
                    </ul>
                </div>

                <a href="<?= BASE_URL ?>/user/saved-searches" class="btn btn-sm btn-link text-decoration-none">
                    <i class="fas fa-list me-1"></i>__('component_view_all', 'View all')
                </a>
            <?php endif; ?>

            <!-- Save current search button (only shows when filters are applied) -->
            <button type="button" class="btn btn-sm btn-success rounded-pill ms-auto" id="saveSearchBtnGlobal" onclick="triggerSaveSearch()" style="display: none;">
                <i class="fas fa-bookmark me-1"></i>__('component_save_this_search_btn', 'Save this search')
            </button>
        </div>
    </div>
</div>

<script>
function triggerSaveSearch() {
    const url = new URL(window.location.href);
    const filters = {};
    url.searchParams.forEach((v, k) => { filters[k] = v; });
    if (window.openSaveSearchModal) {
        window.openSaveSearchModal(filters);
    } else {
        // Fallback: redirect to inline save page
        const name = prompt('Enter a name for this search:');
        if (name) {
            const fd = new FormData();
            fd.append('name', name);
            fd.append('params', JSON.stringify(filters));
            fetch('<?= BASE_URL ?>/user/saved-searches', { method: 'POST', body: fd })
                .then(r => r.json()).then(d => {
                    if (d.success) location.reload();
                    else alert(d.error || 'Failed');
                });
        }
    }
}

// Show "Save this search" button when any filter is applied
document.addEventListener('DOMContentLoaded', function() {
    const url = new URL(window.location.href);
    const hasFilters = Array.from(url.searchParams.keys()).some(k =>
        !['page', 'format', 'lang'].includes(k) && url.searchParams.get(k) !== ''
    );
    const btn = document.getElementById('saveSearchBtnGlobal');
    if (btn && hasFilters) {
        btn.style.display = 'inline-block';
    }
});
</script>
<?php endif; ?>
