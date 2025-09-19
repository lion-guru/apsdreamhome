/**
 * Saved Searches Functionality for APS Dream Home
 * Handles saving, loading, and managing saved property searches
 */

// Auto-refresh interval in milliseconds (5 minutes)
const AUTO_REFRESH_INTERVAL = 5 * 60 * 1000;
let autoRefreshTimer = null;
let draftTimer = null;
let currentDraft = null;

// Initialize saved searches when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    // Initialize save search button if it exists
    const saveSearchBtn = document.getElementById('saveSearchBtn');
    if (saveSearchBtn) {
        saveSearchBtn.addEventListener('click', showSaveSearchModal);
    }
    
    // Initialize save search form submission
    const saveSearchForm = document.getElementById('saveSearchForm');
    if (saveSearchForm) {
        // Auto-save draft every 3 seconds when typing
        const searchNameInput = document.getElementById('searchName');
        if (searchNameInput) {
            searchNameInput.addEventListener('input', () => {
                clearTimeout(draftTimer);
                draftTimer = setTimeout(() => {
                    saveDraft();
                }, 3000);
            });
            
            // Load draft if exists
            loadDraft();
        }
        
        saveSearchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const searchName = document.getElementById('searchName')?.value.trim();
            if (searchName) {
                saveSearch(searchName);
            }
        });
    }
    
    // Load saved searches if the section exists
    if (document.getElementById('savedSearchesSection')) {
        loadSavedSearches();
        // Set up auto-refresh
        setupAutoRefresh();
    }
    
    // Add event delegation for dynamically loaded elements
    document.addEventListener('click', (e) => {
        // Handle load search button clicks
        if (e.target.closest('.load-search')) {
            e.preventDefault();
            loadSavedSearch(e);
        }
        
        // Handle delete search button clicks
        if (e.target.closest('.delete-search')) {
            e.preventDefault();
            deleteSavedSearch(e);
        }
        
        // Handle select all checkbox
        if (e.target.matches('.select-all-searches') || e.target.closest('#selectAllSearches') || e.target.closest('#deselectAllSearches')) {
            const checked = e.target.closest('#deselectAllSearches') ? false : true;
            toggleSelectAllSearches(checked);
        }
        
        // Handle bulk actions
        if (e.target.matches('.bulk-action-btn') || e.target.closest('.bulk-action-btn')) {
            const btn = e.target.closest('.bulk-action-btn') || e.target;
            handleBulkAction(btn.dataset.action);
        }
        
        // Handle bulk actions close button
        if (e.target.closest('.bulk-actions .btn-close')) {
            toggleSelectAllSearches(false);
        }
        
        // Handle individual checkbox clicks
        if (e.target.matches('.search-checkbox') || e.target.closest('.search-checkbox')) {
            const checkbox = e.target.matches('.search-checkbox') ? e.target : e.target.closest('.search-checkbox');
            const item = checkbox.closest('.saved-search-item');
            if (item) {
                item.classList.toggle('selected', checkbox.checked);
            }
            updateBulkActionsUI();
        }
    });
    
    // Handle bulk action bar close button
    document.addEventListener('click', (e) => {
        if (e.target.closest('.bulk-actions .btn-close')) {
            toggleSelectAllSearches(false);
        }
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', handleKeyboardShortcuts);
});

// Set up auto-refresh for saved searches
function setupAutoRefresh() {
    // Clear any existing timer
    if (autoRefreshTimer) {
        clearInterval(autoRefreshTimer);
    }
    
    // Set up new timer
    autoRefreshTimer = setInterval(() => {
        if (document.visibilityState === 'visible') {
            loadSavedSearches();
        }
    }, AUTO_REFRESH_INTERVAL);
    
    // Also refresh when tab becomes visible
    document.addEventListener('visibilitychange', () => {
        if (document.visibilityState === 'visible') {
            loadSavedSearches();
        }
    });
}

// Save search draft
function saveDraft() {
    const searchName = document.getElementById('searchName')?.value.trim();
    if (searchName) {
        currentDraft = {
            name: searchName,
            timestamp: new Date().toISOString()
        };
        localStorage.setItem('savedSearchDraft', JSON.stringify(currentDraft));
    }
}

// Load search draft
function loadDraft() {
    const draft = localStorage.getItem('savedSearchDraft');
    if (draft) {
        try {
            const { name, timestamp } = JSON.parse(draft);
            const draftAge = (new Date() - new Date(timestamp)) / (1000 * 60); // in minutes
            
            // Only load draft if it's less than 1 hour old
            if (draftAge < 60) {
                const searchNameInput = document.getElementById('searchName');
                if (searchNameInput) {
                    searchNameInput.value = name;
                    showToast('Draft restored', 'info');
                }
            } else {
                // Clear old draft
                localStorage.removeItem('savedSearchDraft');
            }
        } catch (e) {
            console.error('Error loading draft:', e);
        }
    }
}

// Toggle select all searches
function toggleSelectAllSearches(checked) {
    const checkboxes = document.querySelectorAll('.search-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = checked;
        // Toggle selected class on parent item
        const item = checkbox.closest('.saved-search-item');
        if (item) {
            item.classList.toggle('selected', checked);
        }
    });
    updateBulkActionsUI();
}

// Update bulk actions UI based on selection
function updateBulkActionsUI() {
    const selectedCount = document.querySelectorAll('.search-checkbox:checked').length;
    const bulkActions = document.querySelector('.bulk-actions');
    const selectedCountEl = document.getElementById('selectedCount');
    const selectAllCheckbox = document.getElementById('selectAllSearchesCheckbox');
    
    if (bulkActions && selectedCountEl) {
        if (selectedCount > 0) {
            bulkActions.classList.remove('d-none');
            selectedCountEl.textContent = selectedCount;
            
            // Update select all checkbox state
            if (selectAllCheckbox) {
                const totalCheckboxes = document.querySelectorAll('.search-checkbox').length;
                selectAllCheckbox.checked = selectedCount === totalCheckboxes;
                selectAllCheckbox.indeterminate = selectedCount > 0 && selectedCount < totalCheckboxes;
            }
        } else {
            bulkActions.classList.add('d-none');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
        }
    }
    
    // Update individual item selected state
    document.querySelectorAll('.search-checkbox').forEach(checkbox => {
        const item = checkbox.closest('.saved-search-item');
        if (item) {
            item.classList.toggle('selected', checkbox.checked);
        }
    });
}

// Handle bulk actions
function handleBulkAction(action) {
    const selectedIds = [];
    document.querySelectorAll('.search-checkbox:checked').forEach(checkbox => {
        selectedIds.push(checkbox.value);
    });
    
    if (selectedIds.length === 0) {
        showToast('Please select at least one search', 'warning');
        return;
    }
    
    switch (action) {
        case 'delete':
            if (confirm(`Are you sure you want to delete ${selectedIds.length} selected searches?`)) {
                deleteMultipleSearches(selectedIds);
            }
            break;
        case 'export':
            exportSearches(selectedIds);
            break;
    }
}

// Delete multiple searches
async function deleteMultipleSearches(ids) {
    try {
        const response = await fetch('/api/delete_multiple_searches.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ search_ids: ids })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showToast(`Successfully deleted ${data.deleted_count} searches`, 'success');
            loadSavedSearches();
        } else {
            throw new Error(data.message || 'Failed to delete searches');
        }
    } catch (error) {
        console.error('Error deleting searches:', error);
        showToast(error.message || 'Failed to delete searches', 'error');
    }
}

// Export searches
function exportSearches(ids) {
    // Implement export functionality
    // This could be a CSV, JSON, or other format
    console.log('Exporting searches:', ids);
    showToast('Export functionality coming soon!', 'info');
}

// Handle keyboard shortcuts
function handleKeyboardShortcuts(e) {
    // Only process if not in an input field or textarea
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
        // Allow Cmd/Ctrl + A for text selection
        if ((e.metaKey || e.ctrlKey) && e.key === 'a') {
            return; // Let the default action happen
        }
        
        // Allow Cmd/Ctrl + C/X/V for copy/cut/paste
        if ((e.metaKey || e.ctrlKey) && ['c', 'x', 'v'].includes(e.key)) {
            return; // Let the default action happen
        }
        
        // Allow arrow keys, home, end, etc. for navigation
        if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown'].includes(e.key)) {
            return; // Let the default action happen
        }
        
        // Allow tab for navigation
        if (e.key === 'Tab') {
            return; // Let the default action happen
        }
    }
    
    // Cmd/Ctrl + S to save
    if ((e.metaKey || e.ctrlKey) && e.key === 's') {
        e.preventDefault();
        const saveBtn = document.getElementById('saveSearchBtn');
        if (saveBtn) saveBtn.click();
        return;
    }
    
    // Cmd/Ctrl + F to focus search
    if ((e.metaKey || e.ctrlKey) && e.key === 'f') {
        e.preventDefault();
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
        return;
    }
    
    // Esc to close modals or clear selection
    if (e.key === 'Escape') {
        // First check if we have any selected items
        const selectedItems = document.querySelectorAll('.search-checkbox:checked');
        if (selectedItems.length > 0) {
            toggleSelectAllSearches(false);
            e.preventDefault();
            return;
        }
        
        // Otherwise, close any open modals
        const modals = document.querySelectorAll('.modal.show');
        if (modals.length > 0) {
            const modal = bootstrap.Modal.getInstance(modals[0]);
            if (modal) {
                modal.hide();
                e.preventDefault();
                e.stopPropagation();
            }
        }
        return;
    }
    
    // Ctrl+A to select all
    if ((e.metaKey || e.ctrlKey) && e.key === 'a') {
        e.preventDefault();
        toggleSelectAllSearches(true);
        return;
    }
    
    // Delete key to delete selected items
    if (e.key === 'Delete') {
        const selectedItems = document.querySelectorAll('.search-checkbox:checked');
        if (selectedItems.length > 0) {
            const selectedIds = Array.from(selectedItems).map(checkbox => checkbox.value);
            if (confirm(`Are you sure you want to delete ${selectedIds.length} selected searches?`)) {
                deleteMultipleSearches(selectedIds);
            }
            e.preventDefault();
        }
    }
    
    // Number keys 1-5 to select items
    if (/^[1-5]$/.test(e.key)) {
        const index = parseInt(e.key) - 1;
        const items = document.querySelectorAll('.saved-search-item');
        if (items[index]) {
            const checkbox = items[index].querySelector('.search-checkbox');
            if (checkbox) {
                checkbox.checked = !checkbox.checked;
                items[index].classList.toggle('selected', checkbox.checked);
                updateBulkActionsUI();
            }
        }
    }
}

/**
 * Show the save search modal
 */
function showSaveSearchModal() {
    // Check if user is logged in
    fetch('/api/check_auth.php')
        .then(response => response.json())
        .then(data => {
            if (data.authenticated) {
                // Show the modal
                const modal = new bootstrap.Modal(document.getElementById('saveSearchModal'));
                modal.show();
                
                // Focus on the input field
                const searchNameInput = document.getElementById('searchName');
                if (searchNameInput) {
                    searchNameInput.value = ''; // Clear any previous value
                    searchNameInput.focus();
                }
            } else {
                // Redirect to login page with redirect back to current page
                window.location.href = '/login.php?redirect=' + 
                    encodeURIComponent(window.location.pathname + window.location.search);
            }
        })
        .catch(error => {
            console.error('Error checking authentication:', error);
            showToast('An error occurred. Please try again.', 'error');
        });
}

/**
 * Save the current search
 * @param {string} name - Name for the saved search
 */
async function saveSearch(name) {
    const saveButton = document.getElementById('confirmSaveSearch');
    const originalButtonText = saveButton?.innerHTML;
    
    try {
        // Show loading state
        if (saveButton) {
            saveButton.disabled = true;
            saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
        }
        
        // Get current search parameters from the form
        const form = document.getElementById('propertySearchForm');
        if (!form) throw new Error('Search form not found');
        
        const formData = new FormData(form);
        const searchParams = {};
        
        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            if (value) {
                searchParams[key] = value;
            }
        }
        
        // Add current view state if available
        if (typeof currentView !== 'undefined') {
            searchParams.view = currentView;
        }
        
        // Send save request to server
        const response = await fetch('/api/save_search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                name: name,
                search_params: searchParams
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Close the modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('saveSearchModal'));
            if (modal) modal.hide();
            
            // Show success message
            showToast('Search saved successfully!', 'success');
            
            // Reload saved searches
            loadSavedSearches();
        } else {
            throw new Error(data.message || 'Failed to save search');
        }
    } catch (error) {
        console.error('Error saving search:', error);
        showToast(error.message || 'Failed to save search. Please try again.', 'error');
    } finally {
        // Reset button state
        if (saveButton) {
            saveButton.disabled = false;
            if (originalButtonText) saveButton.innerHTML = originalButtonText;
        }
    }
}

/**
 * Load the user's saved searches
 */
async function loadSavedSearches() {
    const savedSearchesList = document.getElementById('savedSearchesList');
    if (!savedSearchesList) return;
    
    try {
        // Show loading state
        savedSearchesList.innerHTML = `
            <div class="text-center py-5 my-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-3 mb-0 text-muted">Loading your saved searches...</p>
                <small class="text-muted">This may take a moment</small>
            </div>`;
            
        // Add loading class to refresh button if it exists
        const refreshBtn = document.getElementById('refreshSavedSearches');
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Refreshing...';
        }
        
        const response = await fetch('/api/get_saved_searches.php', {
            headers: {
                'Cache-Control': 'no-cache',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.searches && data.searches.length > 0) {
            // Sort by most recent first
            const sortedSearches = data.searches.sort((a, b) => 
                new Date(b.created_at) - new Date(a.created_at)
            );
            
            // Limit to 5 most recent searches
            const recentSearches = sortedSearches.slice(0, 5);
            
            // Check if there are no searches
            if (recentSearches.length === 0) {
                savedSearchesList.innerHTML = `
                    <div class="text-center py-5 my-3">
                        <div class="mb-3">
                            <i class="fas fa-search fa-3x text-muted opacity-25"></i>
                        </div>
                        <h5 class="text-muted mb-2">No saved searches yet</h5>
                        <p class="text-muted mb-3">Save your property searches to access them later</p>
                        <button class="btn btn-primary" id="saveCurrentSearchBtn">
                            <i class="fas fa-save me-2"></i>Save Current Search
                        </button>
                    </div>`;
                
                // Add event listener to the save current search button
                const saveCurrentBtn = document.getElementById('saveCurrentSearchBtn');
                if (saveCurrentBtn) {
                    saveCurrentBtn.addEventListener('click', showSaveSearchModal);
                }
                
                return;
            }
            
            savedSearchesList.innerHTML = recentSearches.map(search => `
                <div class="saved-search-item list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                    <div class="form-check">
                        <input class="form-check-input search-checkbox" type="checkbox" value="${search.id}" id="search-${search.id}">
                    </div>
                    <div class="d-flex align-items-center w-100">
                        <div class="search-icon me-3">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 text-truncate">${escapeHtml(search.name)}</h6>
                            <div class="d-flex align-items-center">
                                <small class="text-muted me-2">${formatDate(search.created_at)}</small>
                                ${search.search_params?.property_type ? 
                                    `<span class="badge bg-light text-dark border me-1">${search.search_params.property_type}</span>` : ''}
                                ${search.search_params?.min_price || search.search_params?.max_price ? 
                                    `<span class="badge bg-light text-dark border">
                                        ${search.search_params.min_price ? `$${parseInt(search.search_params.min_price).toLocaleString()}` : 'Any'}
                                        ${search.search_params.max_price ? ` - $${parseInt(search.search_params.max_price).toLocaleString()}` : '+'}
                                    </span>` : ''}
                            </div>
                        </div>
                        <div class="search-actions ms-auto">
                            <button class="btn btn-sm btn-outline-primary load-search me-1" data-id="${search.id}" title="Load Search" data-bs-toggle="tooltip">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-search" data-id="${search.id}" title="Delete Search" data-bs-toggle="tooltip">
                                <i class="fas fa-trash"></i>
                            </button>
                                <li>
                                    <a class="dropdown-item text-danger delete-search" href="#" data-id="${search.id}">
                                        <i class="fas fa-trash me-2"></i>Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            `).join('');
            
            // Add view all button if there are more than 5 searches
            if (data.searches.length > 5) {
                const viewAllItem = document.createElement('div');
                viewAllItem.className = 'list-group-item text-center';
                viewAllItem.innerHTML = `
                    <a href="saved-searches.php" class="text-primary">
                        View all saved searches <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                `;
                savedSearchesList.appendChild(viewAllItem);
            }
        } else {
            savedSearchesList.innerHTML = `
                <div class="text-center py-4">
                    <i class="far fa-bookmark fa-3x text-muted mb-3"></i>
                    <h6 class="mb-2">No saved searches yet</h6>
                    <p class="text-muted small mb-0">Save your searches to quickly access them later</p>
                </div>`;
        }
    } catch (error) {
        console.error('Error loading saved searches:', error);
        savedSearchesList.innerHTML = `
            <div class="alert alert-danger m-3">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>
                        <h6 class="mb-1">Error Loading Saved Searches</h6>
                        <p class="small mb-0">Please try refreshing the page or contact support if the issue persists.</p>
                    </div>
                </div>
                <div class="mt-2 text-end">
                    <button class="btn btn-sm btn-outline-secondary" id="retryLoadSearches">
                        <i class="fas fa-sync-alt me-1"></i> Retry
                    </button>
                </div>
            </div>`;
        
        // Add retry button event
        const retryBtn = savedSearchesList.querySelector('#retryLoadSearches');
        if (retryBtn) {
            retryBtn.addEventListener('click', loadSavedSearches);
        }
    } finally {
        // Add event listener for refresh button if it exists
        const refreshBtn = document.getElementById('refreshSavedSearches');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', loadSavedSearches);
        }
    }
}

/**
 * Load a saved search
 * @param {Event} event - The click event
 */
async function loadSavedSearch(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const button = event.target.closest('.load-search');
    if (!button) return;
    
    const searchId = button.dataset.id;
    if (!searchId) return;
    
    try {
        // Show loading state on the button
        const originalContent = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Loading...';
        
        const response = await fetch(`/api/get_saved_search.php?id=${searchId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.search) {
            const searchParams = data.search.search_params;
            
            // Update form fields with saved search parameters
            const form = document.getElementById('propertySearchForm');
            if (!form) throw new Error('Search form not found');
            
            // Reset form
            form.reset();
            
            // Set form values from saved search
            for (const [key, value] of Object.entries(searchParams)) {
                const input = form.querySelector(`[name="${key}"]`);
                if (input) {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        input.checked = true;
                    } else {
                        input.value = value;
                    }
                }
            }
            
            // Update price range slider if it exists
            if (typeof priceRangeSlider !== 'undefined' && 
                (searchParams.min_price || searchParams.max_price)) {
                priceRangeSlider.update({
                    from: parseInt(searchParams.min_price) || 0,
                    to: parseInt(searchParams.max_price) || 10000000
                });
            }
            
            // Submit the form to perform the search
            const submitEvent = new Event('submit', { cancelable: true });
            const formSubmitted = form.dispatchEvent(submitEvent);
            
            if (formSubmitted) {
                form.submit();
            }
            
            // Switch to saved view if different
            if (searchParams.view && typeof toggleView === 'function' && 
                searchParams.view !== currentView) {
                toggleView(searchParams.view);
            }
            
            showToast('Search loaded successfully!', 'success');
        } else {
            throw new Error(data.message || 'Failed to load search');
        }
    } catch (error) {
        console.error('Error loading search:', error);
        showToast('Failed to load search. Please try again.', 'error');
    } finally {
        // Reset button state
        if (button) {
            button.disabled = false;
            button.innerHTML = originalContent || 'Load Search';
        }
    }
}

/**
 * Delete a saved search
 * @param {Event} event - The click event
 */
async function deleteSavedSearch(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const button = event.target.closest('.delete-search');
    if (!button) return;
    
    const searchId = button.dataset.id;
    if (!searchId) return;
    
    // Confirm deletion
    if (!confirm('Are you sure you want to delete this saved search?')) {
        return;
    }
    
    try {
        // Show loading state on the button
        const originalContent = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>';
        
        const response = await fetch('/api/delete_search.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ id: searchId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Remove the search item from the UI
            const searchItem = button.closest('.list-group-item');
            if (searchItem) {
                searchItem.style.opacity = '0.5';
                setTimeout(() => {
                    searchItem.remove();
                    // If no searches left, show empty state
                    const savedSearchesList = document.getElementById('savedSearchesList');
                    if (savedSearchesList && savedSearchesList.children.length === 0) {
                        loadSavedSearches();
                    }
                }, 300);
            }
            
            showToast('Search deleted successfully', 'success');
        } else {
            throw new Error(data.message || 'Failed to delete search');
        }
    } catch (error) {
        console.error('Error deleting search:', error);
        showToast('Failed to delete search. Please try again.', 'error');
    } finally {
        // Reset button state
        if (button) {
            button.disabled = false;
            button.innerHTML = originalContent || '<i class="fas fa-trash"></i>';
        }
    }
}

/**
 * Show a toast notification
 * @param {string} message - The message to display
 * @param {string} type - The type of toast (success, error, warning, info)
 */
function showToast(message, type = 'info') {
    const toastContainer = document.getElementById('toast-container');
    if (!toastContainer) return;
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0 show mb-2`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    
    // Create toast content
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="${getToastIcon(type)} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    // Add to container
    toastContainer.appendChild(toast);
    
    // Initialize Bootstrap toast
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 5000
    });
    
    // Show the toast
    bsToast.show();
    
    // Remove from DOM after hiding
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

/**
 * Get the appropriate icon for the toast type
 * @param {string} type - The type of toast
 * @returns {string} The icon class
 */
function getToastIcon(type) {
    const icons = {
        'success': 'fas fa-check-circle',
        'error': 'fas fa-exclamation-circle',
        'warning': 'fas fa-exclamation-triangle',
        'info': 'fas fa-info-circle'
    };
    
    return icons[type] || 'fas fa-info-circle';
}

/**
 * Format a date string
 * @param {string} dateString - The date string to format
 * @returns {string} Formatted date string
 */
function formatDate(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

/**
 * Escape HTML special characters to prevent XSS
 * @param {string} unsafe - The unsafe string
 * @returns {string} The escaped string
 */
function escapeHtml(unsafe) {
    if (!unsafe) return '';
    return unsafe.toString()
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
