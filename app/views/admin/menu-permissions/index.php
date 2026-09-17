<?php
$page_title = 'Menu Permissions Management';
$active_page = 'menu-permissions';
?>
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2"><i class="fas fa-shield-alt me-2"></i>Menu Permissions Matrix</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button type="button" class="btn btn-outline-primary me-2" id="btnExpandAll">
            <i class="fas fa-expand-alt"></i> Expand All
        </button>
        <button type="button" class="btn btn-outline-secondary me-2" id="btnCollapseAll">
            <i class="fas fa-compress-alt"></i> Collapse All
        </button>
        <button type="button" class="btn btn-success" id="btnSaveAll">
            <i class="fas fa-save"></i> Save All Changes
        </button>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info mb-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Role-Based Menu Permissions Matrix</strong> — Configure which roles can access which menu items.
    <ul class="mb-0 mt-2">
        <li><strong>Can View</strong> — Menu item appears in sidebar</li>
        <li><strong>Can Create</strong> — Allows CREATE actions on that module</li>
        <li><strong>Can Edit</strong> — Allows EDIT actions on that module</li>
        <li><strong>Can Delete</strong> — Allows DELETE actions on that module</li>
        <li>Super Admin always has full access (cannot be modified)</li>
        <li>Changes are saved instantly via AJAX with toast notifications</li>
    </ul>
</div>

<!-- Role Category Tabs -->
<ul class="nav nav-tabs mb-4" id="roleCategoryTabs" role="tablist">
    <?php foreach ($categoryOrder as $catIndex => $category): ?>
        <?php if (!empty($rolesByCategory[$category])): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?= $catIndex === 0 ? 'active' : '' ?>" 
                        data-bs-toggle="tab" 
                        data-bs-target="#cat-<?= strtolower(str_replace(' ', '-', $category)) ?>" 
                        role="tab" 
                        aria-selected="<?= $catIndex === 0 ? 'true' : 'false' ?>">
                    <i class="fas fa-<?= $categoryIcons[$category] ?? 'users' ?> me-1"></i>
                    <?= $category ?>
                    <span class="badge bg-light text-dark ms-1"><?= count($rolesByCategory[$category]) ?></span>
                </button>
            </li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>

<!-- Tab Panes -->
<div class="tab-content" id="roleCategoryTabsContent">
    <?php foreach ($categoryOrder as $catIndex => $category): ?>
        <?php if (!empty($rolesByCategory[$category])): ?>
            <div class="tab-pane fade <?= $catIndex === 0 ? 'show active' : '' ?>" 
                 id="cat-<?= strtolower(str_replace(' ', '-', $category)) ?>" 
                 role="tabpanel">
                
                <div class="card aps-cp-card mb-4">
                    <div class="card-header aps-cp-card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-<?= $categoryIcons[$category] ?? 'users' ?> me-2"></i>
                            <?= $category ?> Roles
                        </h5>
                    </div>
                    <div class="card-body aps-cp-card-body">
                        <?php foreach ($rolesByCategory[$category] as $roleKey => $roleInfo): ?>
                            <div class="role-matrix-section mb-4">
                                <h6 class="d-flex align-items-center mb-3">
                                    <span class="badge bg-<?= $roleBadgeColors[$category] ?? 'primary' ?> me-2">
                                        <?= $roleInfo['level'] ?? '?' ?>
                                    </span>
                                    <span class="fw-bold"><?= $roleInfo['name'] ?></span>
                                    <span class="text-muted ms-2">(<code><?= $roleKey ?></code>)</span>
                                    <?php if ($roleKey === 'super_admin'): ?>
                                        <span class="badge bg-danger ms-2">Full Access</span>
                                    <?php endif; ?>
                                </h6>
                                
                                <?php if ($roleKey === 'super_admin'): ?>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-lock me-2"></i> Super Admin has unrestricted access to all menu items. Permissions cannot be modified.
                                    </div>
                                <?php else: ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover permission-matrix" data-role="<?= $roleKey ?>">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th style="width: 35%;">Menu Item</th>
                                                    <th style="width: 20%;">URL</th>
                                                    <th class="text-center" style="width: 11%;">
                                                        <i class="fas fa-eye" title="Can View - Menu visibility"></i>
                                                    </th>
                                                    <th class="text-center" style="width: 11%;">
                                                        <i class="fas fa-plus" title="Can Create"></i>
                                                    </th>
                                                    <th class="text-center" style="width: 11%;">
                                                        <i class="fas fa-edit" title="Can Edit"></i>
                                                    </th>
                                                    <th class="text-center" style="width: 12%;">
                                                        <i class="fas fa-trash" title="Can Delete"></i>
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php 
                                                // Flatten menu items for this role
                                                $flatItems = flattenMenuItems($menuItems);
                                                foreach ($flatItems as $item): 
                                                    $perms = $item['role_permissions'][$roleKey] ?? ['can_view'=>0,'can_create'=>0,'can_edit'=>0,'can_delete'=>0];
                                                    $isParent = !empty($item['children']);
                                                ?>
                                                    <tr data-menu-id="<?= $item['id'] ?>" class="<?= $isParent ? 'table-primary fw-bold parent-row' : '' ?>" <?= $isParent ? 'data-has-children="true"' : '' ?>>
                                                        <td>
                                                            <i class="fas <?= htmlspecialchars($item['icon'] ?? 'fa-circle') ?> me-2 text-muted"></i>
                                                            <?= str_repeat('&nbsp;&nbsp;&nbsp;', $item['depth'] ?? 0) ?>
                                                            <?= htmlspecialchars($item['name'] ?? '') ?>
                                                            <?php if ($isParent): ?>
                                                                <i class="fas fa-chevron-down toggle-children ms-2" style="cursor:pointer;"></i>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td><code class="small"><?= htmlspecialchars($item['url'] ?? '') ?></code></td>
                                                        <td class="text-center">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input class="form-check-input perm-check" type="checkbox" role="switch"
                                                                       name="can_view" data-perm="can_view"
                                                                       data-role="<?= $roleKey ?>" data-menu-id="<?= $item['id'] ?>"
                                                                       <?= $perms['can_view'] ? 'checked' : '' ?>
                                                                       <?= $roleKey === 'super_admin' ? 'disabled' : '' ?>>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input class="form-check-input perm-check" type="checkbox" role="switch"
                                                                       name="can_create" data-perm="can_create"
                                                                       data-role="<?= $roleKey ?>" data-menu-id="<?= $item['id'] ?>"
                                                                       <?= $perms['can_create'] ? 'checked' : '' ?>
                                                                       <?= !$perms['can_view'] || $roleKey === 'super_admin' ? 'disabled' : '' ?>>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input class="form-check-input perm-check" type="checkbox" role="switch"
                                                                       name="can_edit" data-perm="can_edit"
                                                                       data-role="<?= $roleKey ?>" data-menu-id="<?= $item['id'] ?>"
                                                                       <?= $perms['can_edit'] ? 'checked' : '' ?>
                                                                       <?= !$perms['can_view'] || $roleKey === 'super_admin' ? 'disabled' : '' ?>>
                                                            </div>
                                                        </td>
                                                        <td class="text-center">
                                                            <div class="form-check form-switch d-inline-block">
                                                                <input class="form-check-input perm-check" type="checkbox" role="switch"
                                                                       name="can_delete" data-perm="can_delete"
                                                                       data-role="<?= $roleKey ?>" data-menu-id="<?= $item['id'] ?>"
                                                                       <?= $perms['can_delete'] ? 'checked' : '' ?>
                                                                       <?= !$perms['can_view'] || $roleKey === 'super_admin' ? 'disabled' : '' ?>>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- User Custom Permissions Section -->
<div class="card aps-cp-card mt-5">
    <div class="card-header aps-cp-card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>Custom User Permissions</h5>
        <span class="badge bg-info">Overrides role permissions</span>
    </div>
    <div class="card-body aps-cp-card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <label class="form-label fw-bold">Select User</label>
                <select class="form-select select2" id="userSelect" style="width: 100%;">
                    <option value="">-- Select a user --</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary" id="btnLoadUserPerms" disabled>
                    <i class="fas fa-folder-open me-1"></i> Load Permissions
                </button>
            </div>
        </div>
        
        <div id="userPermissionsContent" style="display: none;">
            <div class="card aps-cp-card">
                <div class="card-header aps-cp-card-header">
                    <strong>Custom Permissions for: <span id="selectedUserName" class="text-primary"></span></strong>
                    <small class="text-muted ms-3">These override role-based permissions</small>
                </div>
                <div class="card-body aps-cp-card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="userPermissionsTable">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 40%;">Menu Item</th>
                                    <th class="text-center" style="width: 15%;">View</th>
                                    <th class="text-center" style="width: 15%;">Create</th>
                                    <th class="text-center" style="width: 15%;">Edit</th>
                                    <th class="text-center" style="width: 15%;">Delete</th>
                                </tr>
                            </thead>
                            <tbody id="userPermissionsBody">
                                <!-- Dynamic content -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Helper function to flatten menu items with depth
function flattenMenuItems($items, $depth = 0) {
    $result = [];
    foreach ($items as $item) {
        $item['depth'] = $depth;
        $result[] = $item;
        if (!empty($item['children'])) {
            $result = array_merge($result, flattenMenuItems($item['children'], $depth + 1));
        }
    }
    return $result;
}

// Category icons
$categoryIcons = [
    'Executive' => 'crown',
    'Management' => 'briefcase',
    'Departmental' => 'building',
    'Team Lead' => 'user-tie',
    'Senior Staff' => 'star',
    'Staff' => 'user',
    'Telecalling' => 'headset',
    'MLM' => 'sitemap',
    'Agent' => 'id-badge',
    'Franchise' => 'store',
    'Customer' => 'user-check',
    'Lead' => 'magnifying-glass',
    'Guest' => 'user-clock',
    'Legacy' => 'archive',
];

// Role badge colors by category
$roleBadgeColors = [
    'Executive' => 'danger',
    'Management' => 'warning',
    'Departmental' => 'info',
    'Team Lead' => 'success',
    'Senior Staff' => 'primary',
    'Staff' => 'secondary',
    'Telecalling' => 'purple',
    'MLM' => 'orange',
    'Agent' => 'teal',
    'Franchise' => 'pink',
    'Customer' => 'green',
    'Lead' => 'indigo',
    'Guest' => 'dark',
    'Legacy' => 'light',
];
?>

<script>
// Global variables
const BASE_URL = '<?= defined('BASE_URL') ? BASE_URL : '' ?>';
const CSRF_TOKEN = '<?= $_SESSION['csrf_token'] ?? '' ?>';

// Toast notification system
function showToast(message, type = 'success') {
    const toastContainer = document.getElementById('toastContainer') || createToastContainer();
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    toastContainer.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { delay: 3000 });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());
}

function createToastContainer() {
    const container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    container.style.zIndex = '9999';
    document.body.appendChild(container);
    return container;
}

// Show loader
function showLoader() {
    if (!document.getElementById('global-loader')) {
        const loader = document.createElement('div');
        loader.id = 'global-loader';
        loader.innerHTML = '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>';
        document.body.appendChild(loader);
        document.body.classList.add('loading-shim');
    }
}

function hideLoader() {
    const loader = document.getElementById('global-loader');
    if (loader) { loader.remove(); document.body.classList.remove('loading-shim'); }
}

// Permission toggle handler
document.addEventListener('DOMContentLoaded', function() {
    // Expand/Collapse all
    document.getElementById('btnExpandAll')?.addEventListener('click', function() {
        document.querySelectorAll('.permission-matrix tbody tr[data-has-children="true"]').forEach(row => {
            const toggle = row.querySelector('.toggle-children');
            if (toggle && toggle.classList.contains('fa-chevron-down')) {
                toggle.click();
            }
        });
    });
    
    document.getElementById('btnCollapseAll')?.addEventListener('click', function() {
        document.querySelectorAll('.permission-matrix tbody tr[data-has-children="true"]').forEach(row => {
            const toggle = row.querySelector('.toggle-children');
            if (toggle && !toggle.classList.contains('fa-chevron-down')) {
                toggle.click();
            }
        });
    });

    // Toggle children rows
    document.querySelectorAll('.toggle-children').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const menuId = row.dataset.menuId;
            const isExpanded = this.classList.toggle('fa-chevron-up');
            this.classList.toggle('fa-chevron-down', !isExpanded);
            
            // Show/hide child rows
            document.querySelectorAll(`.permission-matrix tbody tr[data-parent-id="${menuId}"]`).forEach(childRow => {
                childRow.style.display = isExpanded ? '' : 'none';
            });
        });
    });

    // Permission checkbox changes
    document.querySelectorAll('.perm-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const role = this.dataset.role;
            const menuId = this.dataset.menuId;
            const perm = this.dataset.perm;
            const checked = this.checked;
            
            // If enabling a child permission, ensure View is also enabled
            if (checked && perm !== 'can_view') {
                const viewCheck = document.querySelector(`.perm-check[data-role="${role}"][data-menu-id="${menuId}"][data-perm="can_view"]`);
                if (viewCheck && !viewCheck.checked) {
                    viewCheck.checked = true;
                    viewCheck.dispatchEvent(new Event('change'));
                }
            }
            
            // If disabling View, disable all child permissions
            if (!checked && perm === 'can_view') {
                ['can_create', 'can_edit', 'can_delete'].forEach(p => {
                    const childCheck = document.querySelector(`.perm-check[data-role="${role}"][data-menu-id="${menuId}"][data-perm="${p}"]`);
                    if (childCheck && childCheck.checked) {
                        childCheck.checked = false;
                        childCheck.dispatchEvent(new Event('change'));
                    }
                    childCheck.disabled = true;
                });
            } else if (checked && perm === 'can_view') {
                ['can_create', 'can_edit', 'can_delete'].forEach(p => {
                    const childCheck = document.querySelector(`.perm-check[data-role="${role}"][data-menu-id="${menuId}"][data-perm="${p}"]`);
                    if (childCheck) {
                        childCheck.disabled = false;
                    }
                });
            }
            
            // Send AJAX request
            showLoader();
            fetch(BASE_URL + '/admin/menu-permissions/update-role', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `role=${encodeURIComponent(role)}&menu_item_id=${menuId}&can_view=${document.querySelector(`.perm-check[data-role="${role}"][data-menu-id="${menuId}"][data-perm="can_view"]`)?.checked ? 1 : 0}&can_create=${document.querySelector(`.perm-check[data-role="${role}"][data-menu-id="${menuId}"][data-perm="can_create"]`)?.checked ? 1 : 0}&can_edit=${document.querySelector(`.perm-check[data-role="${role}"][data-menu-id="${menuId}"][data-perm="can_edit"]`)?.checked ? 1 : 0}&can_delete=${document.querySelector(`.perm-check[data-role="${role}"][data-menu-id="${menuId}"][data-perm="can_delete"]`)?.checked ? 1 : 0}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast('Permission updated successfully', 'success');
                } else {
                    showToast('Failed to update permission: ' + (data.message || 'Unknown error'), 'danger');
                    // Revert checkbox
                    this.checked = !checked;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error updating permission', 'danger');
                this.checked = !checked;
            })
            .finally(() => hideLoader());
        });
    });

    // Initialize Select2 for user dropdown
    if (typeof $.fn.select2 !== 'undefined') {
        $('#userSelect').select2({
            placeholder: 'Search for a user...',
            allowClear: true,
            ajax: {
                url: BASE_URL + '/admin/menu-permissions/get-users',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return { q: params.term };
                },
                processResults: function(data) {
                    return {
                        results: data.users.map(u => ({ id: u.id, text: u.name + ' (' + u.role + ') - ' + u.email }))
                    };
                },
                cache: true
            }
        }).on('select2:select', function(e) {
            document.getElementById('btnLoadUserPerms').disabled = false;
        });
    } else {
        // Fallback without Select2
        document.getElementById('userSelect').addEventListener('change', function() {
            document.getElementById('btnLoadUserPerms').disabled = !this.value;
        });
    }

    // Load user permissions
    document.getElementById('btnLoadUserPerms')?.addEventListener('click', function() {
        const userId = document.getElementById('userSelect').value;
        if (!userId) return;
        
        showLoader();
        fetch(BASE_URL + '/admin/menu-permissions/get-user-permissions?user_id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('selectedUserName').textContent = document.getElementById('userSelect').options[document.getElementById('userSelect').selectedIndex].text;
                    document.getElementById('userPermissionsContent').style.display = 'block';
                    renderUserPermissions(data.permissions);
                } else {
                    showToast('Failed to load permissions', 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error loading permissions', 'danger');
            })
            .finally(() => hideLoader());
    });

    function renderUserPermissions(permissions) {
        const tbody = document.getElementById('userPermissionsBody');
        tbody.innerHTML = '';
        
        // Get all menu items from the page
        const menuItems = [];
        document.querySelectorAll('.permission-matrix').forEach(table => {
            table.querySelectorAll('tbody tr:not([data-has-children="true"])').forEach(row => {
                const menuId = row.dataset.menuId;
                const nameCell = row.querySelector('td:first-child');
                const urlCell = row.querySelector('td:nth-child(2)');
                const iconEl = row.querySelector('i.fa');
                menuItems.push({
                    id: menuId,
                    name: nameCell ? nameCell.textContent.trim() : '',
                    url: urlCell ? urlCell.textContent.trim() : '',
                    icon: iconEl ? iconEl.className.replace('fas ', '').replace('fa-', 'fa-') : 'fa-circle',
                    isParent: row.hasAttribute('data-has-children')
                });
            });
        });
        
        // Build permission map
        const permMap = {};
        permissions.forEach(p => {
            permMap[p.menu_item_id] = p;
        });
        
        menuItems.forEach(item => {
            const perm = permMap[item.id] || {};
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <i class="fas ${item.icon} me-2 text-muted"></i>
                    ${item.name}
                </td>
                <td class="text-center">
                    <input type="checkbox" class="user-perm-check" data-perm="can_view" 
                           data-user-id="${userId}" data-menu-id="${item.id}"
                           ${perm.can_view ? 'checked' : ''}>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="user-perm-check" data-perm="can_create" 
                           data-user-id="${userId}" data-menu-id="${item.id}"
                           ${perm.can_create ? 'checked' : ''} ${!perm.can_view ? 'disabled' : ''}>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="user-perm-check" data-perm="can_edit" 
                           data-user-id="${userId}" data-menu-id="${item.id}"
                           ${perm.can_edit ? 'checked' : ''} ${!perm.can_view ? 'disabled' : ''}>
                </td>
                <td class="text-center">
                    <input type="checkbox" class="user-perm-check" data-perm="can_delete" 
                           data-user-id="${userId}" data-menu-id="${item.id}"
                           ${perm.can_delete ? 'checked' : ''} ${!perm.can_view ? 'disabled' : ''}>
                </td>
            `;
            tbody.appendChild(row);
        });
        
        // Add event listeners for user permission checkboxes
        document.querySelectorAll('.user-perm-check').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const userId = this.dataset.userId;
                const menuId = this.dataset.menuId;
                
                const row = this.closest('tr');
                const canView = row.querySelector('[data-perm="can_view"]').checked ? 1 : 0;
                const canCreate = row.querySelector('[data-perm="can_create"]').checked ? 1 : 0;
                const canEdit = row.querySelector('[data-perm="can_edit"]').checked ? 1 : 0;
                const canDelete = row.querySelector('[data-perm="can_delete"]').checked ? 1 : 0;
                
                // If view is disabled, disable others
                if (this.dataset.perm === 'can_view' && !this.checked) {
                    row.querySelectorAll('[data-perm="can_create"], [data-perm="can_edit"], [data-perm="can_delete"]').forEach(cb => {
                        cb.checked = false;
                        cb.disabled = true;
                    });
                } else if (this.dataset.perm === 'can_view' && this.checked) {
                    row.querySelectorAll('[data-perm="can_create"], [data-perm="can_edit"], [data-perm="can_delete"]').forEach(cb => {
                        cb.disabled = false;
                    });
                }
                
                showLoader();
                fetch(BASE_URL + '/admin/menu-permissions/update-user', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `user_id=${userId}&menu_item_id=${menuId}&can_view=${canView}&can_create=${canCreate}&can_edit=${canEdit}&can_delete=${canDelete}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast('User permission updated', 'success');
                    } else {
                        showToast('Failed to update permission', 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('Error updating permission', 'danger');
                })
                .finally(() => hideLoader());
            });
        });
    }
});

// Save all changes button (optional - for batch saving if needed)
document.getElementById('btnSaveAll')?.addEventListener('click', function() {
    showToast('All changes are auto-saved instantly via AJAX', 'info');
});
</script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>