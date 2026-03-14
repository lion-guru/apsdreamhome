<?php
// Accounts Module - Users Management
$module_title = 'Users Management';
$module_description = 'Manage all registered users, their roles, and permissions';

// Sample data
$users = [
    [
        'id' => 'USR001',
        'name' => 'Ramesh Kumar',
        'email' => 'ramesh.kumar@email.com',
        'phone' => '+91 98765 43210',
        'role' => 'Client',
        'status' => 'active',
        'registered_date' => '2024-01-15',
        'last_login' => '2024-03-14 10:30 AM',
        'properties_viewed' => 45,
        'enquiries_sent' => 12,
        'location' => 'Gorakhpur, UP'
    ],
    [
        'id' => 'USR002',
        'name' => 'Priya Singh',
        'email' => 'priya.singh@email.com',
        'phone' => '+91 98765 43211',
        'role' => 'Client',
        'status' => 'active',
        'registered_date' => '2024-01-10',
        'last_login' => '2024-03-14 09:15 AM',
        'properties_viewed' => 23,
        'enquiries_sent' => 8,
        'location' => 'Lucknow, UP'
    ],
    [
        'id' => 'USR003',
        'name' => 'Amit Verma',
        'email' => 'amit.verma@email.com',
        'phone' => '+91 98765 43212',
        'role' => 'Associate',
        'status' => 'active',
        'registered_date' => '2024-01-05',
        'last_login' => '2024-03-13 04:45 PM',
        'properties_viewed' => 67,
        'enquiries_sent' => 0,
        'location' => 'Kanpur, UP'
    ],
    [
        'id' => 'USR004',
        'name' => 'Sunita Sharma',
        'email' => 'sunita.sharma@email.com',
        'phone' => '+91 98765 43213',
        'role' => 'Client',
        'status' => 'inactive',
        'registered_date' => '2023-12-20',
        'last_login' => '2024-02-28 02:30 PM',
        'properties_viewed' => 15,
        'enquiries_sent' => 3,
        'location' => 'Delhi, NCR'
    ]
];
?>

<!-- Users Management Module -->
<div class="users-module">
    <!-- Module Header -->
    <div class="module-header-actions">
        <div class="module-header">
            <h3><i class="fas fa-users"></i> Users Management</h3>
            <p class="text-muted">Manage registered users, their roles, permissions and activity</p>
        </div>
        
        <div class="module-actions">
            <button class="btn btn-primary" onclick="openAddUserModal()">
                <i class="fas fa-user-plus"></i> Add User
            </button>
            <button class="btn btn-outline-secondary" onclick="exportUsers()">
                <i class="fas fa-download"></i> Export
            </button>
            <button class="btn btn-outline-info" onclick="sendBulkNotification()">
                <i class="fas fa-envelope"></i> Send Notification
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fas fa-filter"></i> Quick Filter
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="filterUsers('active')">Active Users</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterUsers('inactive')">Inactive Users</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterUsers('client')">Clients Only</a></li>
                    <li><a class="dropdown-item" href="#" onclick="filterUsers('associate')">Associates Only</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="filterUsers('recent')">Recent Login (7 days)</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="quick-stats-cards">
        <div class="stat-card">
            <div class="stat-icon bg-primary">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo count($users); ?></h4>
                <p>Total Users</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-success">
                <i class="fas fa-user-check"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo count(array_filter($users, fn($u) => $u['status'] === 'active')); ?></h4>
                <p>Active Users</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-warning">
                <i class="fas fa-user-tie"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo count(array_filter($users, fn($u) => $u['role'] === 'Associate')); ?></h4>
                <p>Associates</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon bg-info">
                <i class="fas fa-user-clock"></i>
            </div>
            <div class="stat-content">
                <h4><?php echo count(array_filter($users, fn($u) => strtotime($u['last_login']) > strtotime('-7 days'))); ?></h4>
                <p>Recent Active</p>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="users-table-container">
        <div class="table-header">
            <h4>Users Directory</h4>
            <div class="table-controls">
                <input type="text" class="form-control" placeholder="Search users..." id="userSearch" onkeyup="searchUsers()">
                <select class="form-select" id="roleFilter" onchange="filterByRole()">
                    <option value="">All Roles</option>
                    <option value="Client">Client</option>
                    <option value="Associate">Associate</option>
                    <option value="Admin">Admin</option>
                </select>
                <select class="form-select" id="statusFilter" onchange="filterByStatus()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover" id="usersTable">
                <thead class="table-dark">
                    <tr>
                        <th>
                            <input type="checkbox" id="selectAllUsers" onchange="toggleAllUsers()">
                        </th>
                        <th>User ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Activity</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr class="user-row" data-status="<?php echo $user['status']; ?>" data-role="<?php echo $user['role']; ?>">
                            <td>
                                <input type="checkbox" class="user-checkbox" value="<?php echo $user['id']; ?>">
                            </td>
                            <td>
                                <span class="user-id"><?php echo $user['id']; ?></span>
                            </td>
                            <td>
                                <div class="user-info">
                                    <div class="user-name"><?php echo $user['name']; ?></div>
                                    <small class="text-muted"><?php echo $user['email']; ?></small>
                                </div>
                            </td>
                            <td><?php echo $user['email']; ?></td>
                            <td><?php echo $user['phone']; ?></td>
                            <td>
                                <?php if ($user['role'] === 'Client'): ?>
                                    <span class="badge bg-primary">Client</span>
                                <?php elseif ($user['role'] === 'Associate'): ?>
                                    <span class="badge bg-success">Associate</span>
                                <?php elseif ($user['role'] === 'Admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo $user['role']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user['status'] === 'active'): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php elseif ($user['status'] === 'inactive'): ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Suspended</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $user['location']; ?></td>
                            <td>
                                <div class="activity-info">
                                    <div><i class="fas fa-eye"></i> <?php echo $user['properties_viewed']; ?> views</div>
                                    <div><i class="fas fa-envelope"></i> <?php echo $user['enquiries_sent']; ?> enquiries</div>
                                    <small class="text-muted">Last: <?php echo $user['last_login']; ?></small>
                                </div>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn btn-sm btn-info" onclick="viewUser('<?php echo $user['id']; ?>')" title="View">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="editUser('<?php echo $user['id']; ?>')" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-secondary" onclick="resetPassword('<?php echo $user['id']; ?>')" title="Reset Password">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <?php if ($user['status'] === 'active'): ?>
                                        <button class="btn btn-sm btn-danger" onclick="suspendUser('<?php echo $user['id']; ?>')" title="Suspend">
                                            <i class="fas fa-ban"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-sm btn-success" onclick="activateUser('<?php echo $user['id']; ?>')" title="Activate">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Module Styles -->
<style>
    .users-module {
        max-width: 100%;
    }
    
    .module-header-actions {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .module-header h3 {
        margin: 0 0 0.5rem 0;
        color: #2c3e50;
    }
    
    .module-header p {
        margin: 0;
        color: #6c757d;
    }
    
    .module-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .quick-stats-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .quick-stats-cards .stat-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        display: flex;
        align-items: center;
        gap: 1rem;
        transition: all 0.3s ease;
    }
    
    .quick-stats-cards .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }
    
    .stat-icon.bg-primary { background: #007bff; }
    .stat-icon.bg-success { background: #28a745; }
    .stat-icon.bg-warning { background: #ffc107; color: #000; }
    .stat-icon.bg-info { background: #17a2b8; }
    
    .stat-content h4 {
        margin: 0;
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
    }
    
    .stat-content p {
        margin: 0;
        color: #6c757d;
        font-size: 0.9rem;
    }
    
    .users-table-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .table-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.5rem;
        border-bottom: 1px solid #dee2e6;
        flex-wrap: wrap;
        gap: 1rem;
    }
    
    .table-header h4 {
        margin: 0;
        color: #2c3e50;
    }
    
    .table-controls {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .table-controls input,
    .table-controls select {
        min-width: 180px;
    }
    
    .user-id {
        background: #6c757d;
        color: white;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .user-info .user-name {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .activity-info {
        font-size: 0.85rem;
    }
    
    .activity-info div {
        margin-bottom: 0.25rem;
    }
    
    .activity-info i {
        width: 15px;
        text-align: center;
        margin-right: 0.5rem;
        color: #6c757d;
    }
    
    .action-buttons {
        display: flex;
        gap: 0.25rem;
        flex-wrap: wrap;
    }
    
    .action-buttons .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.8rem;
    }
    
    @media (max-width: 768px) {
        .module-header-actions {
            flex-direction: column;
            align-items: stretch;
        }
        
        .quick-stats-cards {
            grid-template-columns: 1fr;
        }
        
        .table-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .table-controls {
            flex-direction: column;
        }
        
        .table-controls input,
        .table-controls select {
            min-width: auto;
        }
        
        .action-buttons {
            flex-direction: column;
        }
    }
</style>

<!-- Module JavaScript -->
<script>
    let allUsers = <?php echo json_encode($users); ?>;
    let filteredUsers = [...allUsers];
    
    function openAddUserModal() {
        showNotification('Opening add user modal...', 'info');
        // Here you would open a modal with user registration form
    }
    
    function exportUsers() {
        showNotification('Exporting users data...', 'info');
        setTimeout(() => {
            showNotification('Users exported successfully', 'success');
        }, 1500);
    }
    
    function sendBulkNotification() {
        const message = prompt('Enter notification message:');
        if (message) {
            showNotification('Sending bulk notification...', 'info');
            setTimeout(() => {
                showNotification(`Notification sent to ${filteredUsers.length} users`, 'success');
            }, 1500);
        }
    }
    
    function filterUsers(filter) {
        switch(filter) {
            case 'recent':
                filteredUsers = allUsers.filter(u => new Date(u.last_login) > new Date(Date.now() - 7 * 24 * 60 * 60 * 1000));
                break;
            case 'client':
                filteredUsers = allUsers.filter(u => u.role === 'Client');
                break;
            case 'associate':
                filteredUsers = allUsers.filter(u => u.role === 'Associate');
                break;
            default:
                filteredUsers = allUsers.filter(u => u.status === filter);
        }
        renderUsersTable();
        showNotification(`Filtered by ${filter}`, 'info');
    }
    
    function filterByRole() {
        const role = document.getElementById('roleFilter').value;
        if (role === '') {
            filteredUsers = [...allUsers];
        } else {
            filteredUsers = allUsers.filter(u => u.role === role);
        }
        renderUsersTable();
    }
    
    function filterByStatus() {
        const status = document.getElementById('statusFilter').value;
        if (status === '') {
            filteredUsers = [...allUsers];
        } else {
            filteredUsers = allUsers.filter(u => u.status === status);
        }
        renderUsersTable();
    }
    
    function searchUsers() {
        const searchTerm = document.getElementById('userSearch').value.toLowerCase();
        filteredUsers = allUsers.filter(u => 
            u.name.toLowerCase().includes(searchTerm) ||
            u.email.toLowerCase().includes(searchTerm) ||
            u.phone.includes(searchTerm) ||
            u.location.toLowerCase().includes(searchTerm) ||
            u.id.toLowerCase().includes(searchTerm)
        );
        renderUsersTable();
    }
    
    function toggleAllUsers() {
        const selectAll = document.getElementById('selectAllUsers');
        const checkboxes = document.querySelectorAll('.user-checkbox');
        
        checkboxes.forEach(checkbox => {
            checkbox.checked = selectAll.checked;
        });
    }
    
    function viewUser(userId) {
        const user = allUsers.find(u => u.id === userId);
        showNotification(`Viewing user: ${user.name}`, 'info');
        // Here you would open user details modal
    }
    
    function editUser(userId) {
        const user = allUsers.find(u => u.id === userId);
        showNotification(`Editing user: ${user.name}`, 'info');
        // Here you would open edit user modal
    }
    
    function resetPassword(userId) {
        const user = allUsers.find(u => u.id === userId);
        if (confirm(`Reset password for ${user.name}?`)) {
            showNotification(`Password reset link sent to ${user.email}`, 'success');
            // Here you would make API call to reset password
        }
    }
    
    function suspendUser(userId) {
        const user = allUsers.find(u => u.id === userId);
        if (confirm(`Suspend user: ${user.name}?`)) {
            showNotification(`User ${userId} suspended`, 'warning');
            // Here you would make API call to suspend user
        }
    }
    
    function activateUser(userId) {
        const user = allUsers.find(u => u.id === userId);
        if (confirm(`Activate user: ${user.name}?`)) {
            showNotification(`User ${userId} activated`, 'success');
            // Here you would make API call to activate user
        }
    }
    
    function renderUsersTable() {
        const tbody = document.querySelector('#usersTable tbody');
        tbody.innerHTML = '';
        
        filteredUsers.forEach(user => {
            const row = createUserRow(user);
            tbody.appendChild(row);
        });
    }
    
    function createUserRow(user) {
        const tr = document.createElement('tr');
        tr.className = 'user-row';
        tr.setAttribute('data-status', user.status);
        tr.setAttribute('data-role', user.role);
        
        tr.innerHTML = `
            <td>
                <input type="checkbox" class="user-checkbox" value="${user.id}">
            </td>
            <td>
                <span class="user-id">${user.id}</span>
            </td>
            <td>
                <div class="user-info">
                    <div class="user-name">${user.name}</div>
                    <small class="text-muted">${user.email}</small>
                </div>
            </td>
            <td>${user.email}</td>
            <td>${user.phone}</td>
            <td>
                ${getRoleBadge(user.role)}
            </td>
            <td>
                ${getStatusBadge(user.status)}
            </td>
            <td>${user.location}</td>
            <td>
                <div class="activity-info">
                    <div><i class="fas fa-eye"></i> ${user.properties_viewed} views</div>
                    <div><i class="fas fa-envelope"></i> ${user.enquiries_sent} enquiries</div>
                    <small class="text-muted">Last: ${user.last_login}</small>
                </div>
            </td>
            <td>
                <div class="action-buttons">
                    <button class="btn btn-sm btn-info" onclick="viewUser('${user.id}')" title="View">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-warning" onclick="editUser('${user.id}')" title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="resetPassword('${user.id}')" title="Reset Password">
                        <i class="fas fa-key"></i>
                    </button>
                    ${getActionButton(user)}
                </div>
            </td>
        `;
        
        return tr;
    }
    
    function getRoleBadge(role) {
        const badges = {
            'Client': '<span class="badge bg-primary">Client</span>',
            'Associate': '<span class="badge bg-success">Associate</span>',
            'Admin': '<span class="badge bg-danger">Admin</span>'
        };
        return badges[role] || `<span class="badge bg-secondary">${role}</span>`;
    }
    
    function getStatusBadge(status) {
        const badges = {
            'active': '<span class="badge bg-success">Active</span>',
            'inactive': '<span class="badge bg-secondary">Inactive</span>',
            'suspended': '<span class="badge bg-warning">Suspended</span>'
        };
        return badges[status] || badges['inactive'];
    }
    
    function getActionButton(user) {
        if (user.status === 'active') {
            return `<button class="btn btn-sm btn-danger" onclick="suspendUser('${user.id}')" title="Suspend">
                        <i class="fas fa-ban"></i>
                    </button>`;
        } else {
            return `<button class="btn btn-sm btn-success" onclick="activateUser('${user.id}')" title="Activate">
                        <i class="fas fa-check"></i>
                    </button>`;
        }
    }
    
    function showNotification(message, type = 'info') {
        // This would use the global notification system
        console.log(`${type.toUpperCase()}: ${message}`);
    }
</script>
