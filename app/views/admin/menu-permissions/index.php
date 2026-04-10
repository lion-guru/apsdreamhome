<?php
$page_title = 'Menu Permissions Management';
$active_page = 'menu-permissions';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Menu Permissions Management</h1>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs" id="permissionsTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="role-permissions-tab" data-bs-toggle="tab" href="#role-permissions" role="tab">Role Permissions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="user-permissions-tab" data-bs-toggle="tab" href="#user-permissions" role="tab">User Permissions</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="permissionsTabsContent">
                    <!-- Role Permissions Tab -->
                    <div class="tab-pane fade show active" id="role-permissions" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Manage which roles can access which menu items. Super Admin and Admin automatically have full access.
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover" id="rolePermissionsTable">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Menu Item</th>
                                        <th>URL</th>
                                        <th>Super Admin</th>
                                        <th>Admin</th>
                                        <th>Manager</th>
                                        <th>Associate</th>
                                        <th>Agent</th>
                                        <th>User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menuItems as $item): ?>
                                        <?php if (empty($item['children'])): ?>
                                            <tr data-menu-id="<?php echo $item['id']; ?>">
                                                <td>
                                                    <i class="fas <?php echo htmlspecialchars($item['icon']); ?> me-2"></i>
                                                    <?php echo htmlspecialchars($item['name']); ?>
                                                </td>
                                                <td><code><?php echo htmlspecialchars($item['url']); ?></code></td>
                                                <?php
                                                $roles = ['super_admin', 'admin', 'manager', 'associate', 'agent', 'user'];
                                                foreach ($roles as $role): ?>
                                                    <td>
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input role-permission-check" 
                                                                   type="checkbox" 
                                                                   role="switch"
                                                                   data-role="<?php echo $role; ?>"
                                                                   data-menu-id="<?php echo $item['id']; ?>"
                                                                   <?php echo isset($item['role_permissions'][$role]) && $item['role_permissions'][$role]['can_view'] ? 'checked' : ''; ?>>
                                                        </div>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php else: ?>
                                            <!-- Parent menu item with children -->
                                            <tr class="table-primary font-weight-bold" data-menu-id="<?php echo $item['id']; ?>">
                                                <td colspan="8">
                                                    <i class="fas <?php echo htmlspecialchars($item['icon']); ?> me-2"></i>
                                                    <?php echo htmlspecialchars($item['name']); ?> (Parent)
                                                </td>
                                            </tr>
                                            <?php foreach ($item['children'] as $child): ?>
                                                <tr class="ms-4" data-menu-id="<?php echo $child['id']; ?>">
                                                    <td>
                                                        <i class="fas <?php echo htmlspecialchars($child['icon']); ?> me-2"></i>
                                                        <?php echo htmlspecialchars($child['name']); ?>
                                                    </td>
                                                    <td><code><?php echo htmlspecialchars($child['url']); ?></code></td>
                                                    <?php
                                                    foreach ($roles as $role): ?>
                                                        <td>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input role-permission-check" 
                                                                       type="checkbox" 
                                                                       role="switch"
                                                                       data-role="<?php echo $role; ?>"
                                                                       data-menu-id="<?php echo $child['id']; ?>"
                                                                       <?php echo isset($child['role_permissions'][$role]) && $child['role_permissions'][$role]['can_view'] ? 'checked' : ''; ?>>
                                                            </div>
                                                        </td>
                                                    <?php endforeach; ?>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- User Permissions Tab -->
                    <div class="tab-pane fade" id="user-permissions" role="tabpanel">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Grant custom menu permissions to specific users. These permissions override role-based permissions.
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Select User</label>
                                <select class="form-select" id="userSelect">
                                    <option value="">-- Select a user --</option>
                                </select>
                            </div>
                        </div>

                        <div id="userPermissionsContent" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <strong>Custom Permissions for: <span id="selectedUserName"></span></strong>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover" id="userPermissionsTable">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>Menu Item</th>
                                                    <th>View</th>
                                                    <th>Create</th>
                                                    <th>Edit</th>
                                                    <th>Delete</th>
                                                    <th>Action</th>
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
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle role permission toggle
    document.querySelectorAll('.role-permission-check').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const role = this.dataset.role;
            const menuId = this.dataset.menuId;
            const canView = this.checked ? 1 : 0;

            fetch('<?php echo BASE_URL; ?>/admin/menu-permissions/update-role', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `role=${role}&menu_item_id=${menuId}&can_view=${canView}&can_create=${canView}&can_edit=${canView}&can_delete=${canView}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    const alert = document.createElement('div');
                    alert.className = 'alert alert-success alert-dismissible fade show';
                    alert.innerHTML = `
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        Permission updated successfully!
                    `;
                    document.querySelector('.card-body').prepend(alert);
                    setTimeout(() => alert.remove(), 3000);
                } else {
                    alert('Failed to update permission');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating permission');
            });
        });
    });

    // Load users for user permissions tab
    document.getElementById('user-permissions-tab').addEventListener('click', function() {
        fetch('<?php echo BASE_URL; ?>/admin/menu-permissions/get-users')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const select = document.getElementById('userSelect');
                    select.innerHTML = '<option value="">-- Select a user --</option>';
                    data.users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.id;
                        option.textContent = `${user.name} (${user.role})`;
                        select.appendChild(option);
                    });
                }
            });
    });

    // Handle user selection
    document.getElementById('userSelect').addEventListener('change', function() {
        const userId = this.value;
        if (!userId) {
            document.getElementById('userPermissionsContent').style.display = 'none';
            return;
        }

        document.getElementById('selectedUserName').textContent = this.options[this.selectedIndex].text;
        document.getElementById('userPermissionsContent').style.display = 'block';

        // Load user's custom permissions
        fetch('<?php echo BASE_URL; ?>/admin/menu-permissions/get-user-permissions?user_id=' + userId)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const tbody = document.getElementById('userPermissionsBody');
                    tbody.innerHTML = '';

                    // Get all menu items
                    const menuItems = <?php echo json_encode($menuItems); ?>;
                    
                    function renderMenuItems(items, level = 0) {
                        items.forEach(item => {
                            if (item.children && item.children.length > 0) {
                                // Render parent
                                const row = document.createElement('tr');
                                row.className = 'table-primary';
                                row.innerHTML = `
                                    <td colspan="6">
                                        <i class="fas ${item.icon} me-2"></i>
                                        ${item.name} (Parent)
                                    </td>
                                `;
                                tbody.appendChild(row);
                                
                                // Render children
                                renderMenuItems(item.children, level + 1);
                            } else {
                                // Render item
                                const permission = data.permissions.find(p => p.menu_item_id == item.id) || {};
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td style="padding-left: ${level * 20}px">
                                        <i class="fas ${item.icon} me-2"></i>
                                        ${item.name}
                                    </td>
                                    <td>
                                        <input type="checkbox" class="user-perm-check" data-perm="can_view" 
                                               data-user-id="${userId}" data-menu-id="${item.id}"
                                               ${permission.can_view ? 'checked' : ''}>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="user-perm-check" data-perm="can_create" 
                                               data-user-id="${userId}" data-menu-id="${item.id}"
                                               ${permission.can_create ? 'checked' : ''}>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="user-perm-check" data-perm="can_edit" 
                                               data-user-id="${userId}" data-menu-id="${item.id}"
                                               ${permission.can_edit ? 'checked' : ''}>
                                    </td>
                                    <td>
                                        <input type="checkbox" class="user-perm-check" data-perm="can_delete" 
                                               data-user-id="${userId}" data-menu-id="${item.id}"
                                               ${permission.can_delete ? 'checked' : ''}>
                                    </td>
                                    <td>
                                        ${Object.keys(permission).length > 0 ? 
                                            `<button class="btn btn-sm btn-danger revoke-perm-btn" 
                                                    data-user-id="${userId}" data-menu-id="${item.id}">Revoke</button>` : 
                                            '<span class="text-muted">No custom permission</span>'}
                                    </td>
                                `;
                                tbody.appendChild(row);
                            }
                        });
                    }
                    
                    renderMenuItems(menuItems);

                    // Add event listeners for user permission checkboxes
                    document.querySelectorAll('.user-perm-check').forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const userId = this.dataset.userId;
                            const menuId = this.dataset.menuId;
                            const perm = this.dataset.perm;
                            const value = this.checked ? 1 : 0;

                            // Get all permission values for this menu item
                            const row = this.closest('tr');
                            const canView = row.querySelector('[data-perm="can_view"]').checked ? 1 : 0;
                            const canCreate = row.querySelector('[data-perm="can_create"]').checked ? 1 : 0;
                            const canEdit = row.querySelector('[data-perm="can_edit"]').checked ? 1 : 0;
                            const canDelete = row.querySelector('[data-perm="can_delete"]').checked ? 1 : 0;

                            fetch('<?php echo BASE_URL; ?>/admin/menu-permissions/update-user', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `user_id=${userId}&menu_item_id=${menuId}&can_view=${canView}&can_create=${canCreate}&can_edit=${canEdit}&can_delete=${canDelete}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    // Update revoke button
                                    const actionCell = row.querySelector('td:last-child');
                                    if (canView || canCreate || canEdit || canDelete) {
                                        actionCell.innerHTML = `<button class="btn btn-sm btn-danger revoke-perm-btn" 
                                            data-user-id="${userId}" data-menu-id="${menuId}">Revoke</button>`;
                                        // Add event listener to new button
                                        actionCell.querySelector('.revoke-perm-btn').addEventListener('click', handleRevoke);
                                    } else {
                                        actionCell.innerHTML = '<span class="text-muted">No custom permission</span>';
                                    }
                                }
                            });
                        });
                    });

                    // Add event listeners for revoke buttons
                    document.querySelectorAll('.revoke-perm-btn').forEach(btn => {
                        btn.addEventListener('click', handleRevoke);
                    });
                }
            });
    });

    function handleRevoke(e) {
        const userId = e.target.dataset.userId;
        const menuId = e.target.dataset.menuId;

        if (!confirm('Are you sure you want to revoke this custom permission?')) {
            return;
        }

        fetch('<?php echo BASE_URL; ?>/admin/menu-permissions/revoke-user', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `user_id=${userId}&menu_item_id=${menuId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Refresh the user permissions
                document.getElementById('userSelect').dispatchEvent(new Event('change'));
            }
        });
    }
});
</script>
