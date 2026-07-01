<?php
/**
 * Save Search Form (Modal)
 *
 * Include in pages where users might want to save a search:
 *     <?php include __DIR__ . '/../../components/save_search_modal.php'; ?>
 *
 * Renders a Bootstrap 5 modal that POSTs the current page's filter state
 * to /user/saved-searches (SavedSearchController@store).
 *
 * Required globals (set by caller):
 *   $currentFilters - array of currently-applied filter key=>value pairs
 */
$currentFilters = $currentFilters ?? [];
?>

<!-- Save Search Modal -->
<div class="modal fade" id="saveSearchModal" tabindex="-1" aria-labelledby="saveSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-gradient-primary text-white" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
                <h5 class="modal-title" id="saveSearchModalLabel">
                    <i class="fas fa-bookmark me-2"></i>__('component_save_this_search', 'Save this search')
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="saveSearchForm">
                <div class="modal-body">
                    <p class="text-muted small">__('component_save_filters_description', 'Save your current filters to access this search later and (optionally) receive email alerts when new properties match.')</p>

                    <div class="mb-3">
                        <label for="saveSearchName" class="form-label fw-semibold">__('component_search_name', 'Search name') <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="saveSearchName" name="name" placeholder="htmlspecialchars(__('component_search_name_placeholder', 'e.g. Plots in Gorakhpur under 20L'))" maxlength="100" required>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="saveSearchAlerts" name="email_alerts" value="1">
                        <label class="form-check-label" for="saveSearchAlerts">
                            <i class="fas fa-bell text-success me-1"></i>__('component_send_email_alerts', 'Send me email alerts when new properties match')
                        </label>
                    </div>

                    <div class="mb-3">
                        <label for="saveSearchDescription" class="form-label fw-semibold">__('component_description_optional', 'Description (optional)')</label>
                        <textarea class="form-control" id="saveSearchDescription" name="description" rows="2" placeholder="htmlspecialchars(__('component_add_notes_placeholder', 'Add notes about this search...'))" maxlength="500"></textarea>
                    </div>

                    <div class="alert alert-light border small">
                        <strong>__('component_filters_being_saved', 'Filters being saved:')</strong>
                        <ul id="saveSearchFiltersPreview" class="mb-0 mt-1"></ul>
                    </div>

                    <div id="saveSearchError" class="alert alert-danger d-none small" role="alert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">__('component_cancel', 'Cancel')</button>
                    <button type="submit" class="btn btn-primary" id="saveSearchSubmitBtn">
                        <i class="fas fa-bookmark me-1"></i>__('component_save_search', 'Save Search')
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    const form = document.getElementById('saveSearchForm');
    if (!form) return;

    const baseUrl = '<?= BASE_URL ?>';
    const modalEl = document.getElementById('saveSearchModal');
    const filtersPreview = document.getElementById('saveSearchFiltersPreview');
    const errorBox = document.getElementById('saveSearchError');
    const submitBtn = document.getElementById('saveSearchSubmitBtn');

    // Build a global window helper so other components can pre-fill the modal
    window.openSaveSearchModal = function(filters) {
        // Update the preview
        filtersPreview.innerHTML = '';
        let hasAny = false;
        Object.keys(filters || {}).forEach(k => {
            const v = filters[k];
            if (v !== '' && v !== null && v !== undefined && v !== 0) {
                hasAny = true;
                const li = document.createElement('li');
                li.textContent = k.replace(/_/g, ' ') + ': ' + v;
                filtersPreview.appendChild(li);
            }
        });
        if (!hasAny) {
            const li = document.createElement('li');
            li.className = 'text-muted';
            li.textContent = 'No filters applied. Open the advanced filters and try again.';
            filtersPreview.appendChild(li);
            submitBtn.disabled = true;
        } else {
            submitBtn.disabled = false;
        }

        // Auto-name suggestion based on filters
        const nameInput = document.getElementById('saveSearchName');
        if (!nameInput.value) {
            const parts = [];
            if (filters.type) parts.push(ucfirst(filters.type));
            if (filters.listing) parts.push(ucfirst(filters.listing));
            if (filters.location) parts.push('in ' + filters.location);
            if (filters.bedrooms) parts.push(filters.bedrooms + 'BHK');
            if (filters.min_price || filters.max_price) {
                const fmt = (n) => '₹' + (n / 100000).toFixed(n % 100000 ? 1 : 0) + 'L';
                if (filters.min_price && filters.max_price) parts.push(fmt(filters.min_price) + ' - ' + fmt(filters.max_price));
                else if (filters.min_price) parts.push('from ' + fmt(filters.min_price));
                else parts.push('up to ' + fmt(filters.max_price));
            }
            nameInput.placeholder = parts.length ? parts.join(' ') : 'My search';
        }

        errorBox.classList.add('d-none');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    function ucfirst(s) { return s.charAt(0).toUpperCase() + s.slice(1); }

    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        if (submitBtn.disabled) return;

        errorBox.classList.add('d-none');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Saving...';

        const name = document.getElementById('saveSearchName').value.trim();
        const emailAlerts = document.getElementById('saveSearchAlerts').checked;
        const description = document.getElementById('saveSearchDescription').value.trim();

        if (!name) {
            errorBox.textContent = 'Please enter a name for this search.';
            errorBox.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-bookmark me-1"></i>__('component_save_search', 'Save Search')';
            return;
        }

        // Collect current filter state from URL params
        const url = new URL(window.location.href);
        const filters = {};
        url.searchParams.forEach((v, k) => { filters[k] = v; });

        try {
            const res = await fetch(baseUrl + '/user/saved-searches', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ name, email_alerts: emailAlerts ? 1 : 0, description, filters })
            });
            const data = await res.json();
            if (data.success) {
                submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Saved!';
                submitBtn.classList.remove('btn-primary');
                submitBtn.classList.add('btn-success');
                setTimeout(() => {
                    const modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) modal.hide();
                    // Show success toast / banner and update the save-search button to "Saved"
                    if (window.onSearchSaved) window.onSearchSaved(data);
                    document.getElementById('saveSearchBtnGlobal')?.classList.add('d-none');
                }, 800);
            } else {
                errorBox.textContent = data.error || 'Failed to save search.';
                errorBox.classList.remove('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-bookmark me-1"></i>__('component_save_search', 'Save Search')';
            }
        } catch (err) {
            errorBox.textContent = 'Network error. Please try again.';
            errorBox.classList.remove('d-none');
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-bookmark me-1"></i>__('component_save_search', 'Save Search')';
        }
    });

    // Reset form when modal closes
    modalEl.addEventListener('hidden.bs.modal', function() {
        submitBtn.disabled = false;
        submitBtn.classList.remove('btn-success');
        submitBtn.classList.add('btn-primary');
        submitBtn.innerHTML = '<i class="fas fa-bookmark me-1"></i>__('component_save_search', 'Save Search')';
        form.reset();
    });
})();
</script>
