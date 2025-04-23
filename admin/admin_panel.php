<?php
require_once(__DIR__ . '/includes/functions/role_helper.php');
enforceRole(['admin','superadmin']);
$menu_config = include(__DIR__ . '/includes/config/menu_config.php');
$current_role = getCurrentUserRole();
$menu_items = $menu_config[$current_role] ?? [];
include(__DIR__ . '/includes/templates/header.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel - APS Dream Homes</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/font-awesome.min.css">
    <style>
        body {background: #f8f9fa;}
        .sidebar {background: #fff; min-height: 100vh; box-shadow: 2px 0 8px rgba(0,0,0,0.03);}
        .sidebar .nav-link {color: #333; font-weight: 500;}
        .sidebar .nav-link.active, .sidebar .nav-link:hover {background: #007bff; color: #fff;}
        .dashboard-section {padding: 32px 24px;}
        .section-title {font-size: 1.5rem; font-weight: 600; margin-bottom: 1.5rem;}
        .card {margin-bottom: 24px;}
    </style>
</head>
<body>
<div class="container my-3">
    <div class="d-flex justify-content-end mb-2 gap-2">
        <a href="admin/export_ai_interactions.php" class="btn btn-outline-info btn-sm" target="_blank">
            <i class="fa fa-download me-1"></i>Export AI Feedback (CSV)
        </a>
        <a href="admin/ai_feedback_analytics.php" class="btn btn-outline-primary btn-sm" target="_blank">
            <i class="fa fa-chart-bar me-1"></i>AI Feedback Analytics
        </a>
    </div>
    <div class="card border-success mb-3">
        <div class="card-header bg-success text-white"><i class="fa fa-magic me-2"></i>AI Insights, Trends & Forecast</div>
        <div class="card-body">
            <div id="aiAdminInsightsPanel">
                <div class="text-center text-muted">Loading admin insights...</div>
            </div>
            <div id="adminTrendsPanel" class="mt-3" style="display:none;">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <canvas id="trendRegistrations" height="60"></canvas>
                        <div class="small">Registrations (14d)</div>
                    </div>
                    <div class="col-md-3 text-center">
                        <canvas id="trendBookings" height="60"></canvas>
                        <div class="small">Bookings (14d)</div>
                    </div>
                    <div class="col-md-3 text-center">
                        <canvas id="trendTickets" height="60"></canvas>
                        <div class="small">Tickets (14d)</div>
                    </div>
                    <div class="col-md-3 text-center">
                        <canvas id="trendPayments" height="60"></canvas>
                        <div class="small">Payments (14d)</div>
                    </div>
                </div>
                <div id="aiForecastPanel" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-2 d-none d-md-block sidebar py-4">
            <ul class="nav flex-column">
                <li class="nav-item"><a class="nav-link active" href="#users"><i class="fa fa-users me-2"></i>Users/Roles</a></li>
                <li class="nav-item"><a class="nav-link" href="#employees"><i class="fa fa-id-badge me-2"></i>Employees</a></li>
                <li class="nav-item"><a class="nav-link" href="#permissions"><i class="fa fa-key me-2"></i>Permissions</a></li>
                <li class="nav-item"><a class="nav-link" href="#settings"><i class="fa fa-cog me-2"></i>Settings</a></li>
                <li class="nav-item"><a class="nav-link" href="#analytics"><i class="fa fa-bar-chart me-2"></i>Analytics</a></li>
                <li class="nav-item"><a class="nav-link" href="#ai"><i class="fa fa-magic me-2"></i>AI Tools</a></li>
            </ul>
        </nav>
        <main class="col-md-10 ms-sm-auto dashboard-section">
            <div id="users">
                <div class="section-title"><i class="fa fa-users"></i> User & Role Management</div>
                <div class="mb-3">
                    <label for="roleFilter" class="form-label">Filter by Role:</label>
                    <select id="roleFilter" class="form-select" style="width:auto;display:inline-block">
                        <option value="">All</option>
                        <option value="admin">Admin</option>
                        <option value="superadmin">Superadmin</option>
                        <option value="associate">Associate</option>
                        <option value="user">User</option>
                        <option value="builder">Builder</option>
                        <option value="agent">Agent</option>
                        <option value="employee">Employee</option>
                        <option value="customer">Customer</option>
                    </select>
                    <button class="btn btn-primary ms-2" id="addUserBtn"><i class="fa fa-plus"></i> Add User</button>
                </div>
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="usersTable">
                            <thead>
                                <tr>
                                    <th>UID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <!-- Add/Edit User Modal -->
                <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form id="userForm">
                        <div class="modal-header">
                          <h5 class="modal-title" id="userModalLabel">Add User</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id" id="userId">
                          <div class="mb-3">
                            <label for="userName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="userName" name="name" required>
                          </div>
                          <div class="mb-3">
                            <label for="userEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="userEmail" name="email" required>
                          </div>
                          <div class="mb-3">
                            <label for="userPhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="userPhone" name="phone" required>
                          </div>
                          <div class="mb-3">
                            <label for="userRole" class="form-label">Role</label>
                            <select class="form-select" id="userRole" name="role" required>
                              <option value="user">User</option>
                              <option value="admin">Admin</option>
                              <option value="superadmin">Superadmin</option>
                              <option value="associate">Associate</option>
                              <option value="builder">Builder</option>
                              <option value="agent">Agent</option>
                              <option value="employee">Employee</option>
                              <option value="customer">Customer</option>
                            </select>
                          </div>
                          <div class="mb-3">
                            <label for="userStatus" class="form-label">Status</label>
                            <select class="form-select" id="userStatus" name="status" required>
                              <option value="active">Active</option>
                              <option value="inactive">Inactive</option>
                            </select>
                          </div>
                          <div class="mb-3 password-field">
                            <label for="userPassword" class="form-label">Password</label>
                            <input type="password" class="form-control" id="userPassword" name="password" minlength="6">
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-primary" id="userModalSubmit">Save</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            </div>
            <div id="employees">
                <div class="section-title"><i class="fa fa-id-badge"></i> Employee Management</div>
                <div class="mb-3">
                    <button class="btn btn-primary" id="addEmployeeBtn"><i class="fa fa-plus"></i> Add Employee</button>
                </div>
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="employeesTable">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <!-- Add/Edit Employee Modal -->
                <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form id="employeeForm">
                        <div class="modal-header">
                          <h5 class="modal-title" id="employeeModalLabel">Add Employee</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id" id="employeeId">
                          <div class="mb-3">
                            <label for="employeeName" class="form-label">Name</label>
                            <input type="text" class="form-control" id="employeeName" name="name" required>
                          </div>
                          <div class="mb-3">
                            <label for="employeeEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="employeeEmail" name="email" required>
                          </div>
                          <div class="mb-3">
                            <label for="employeePhone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="employeePhone" name="phone" required>
                          </div>
                          <div class="mb-3">
                            <label for="employeeRole" class="form-label">Role</label>
                            <select class="form-select" id="employeeRole" name="role" required>
                              <option value="employee">Employee</option>
                              <option value="admin">Admin</option>
                              <option value="associate">Associate</option>
                              <option value="builder">Builder</option>
                              <option value="agent">Agent</option>
                            </select>
                          </div>
                          <div class="mb-3">
                            <label for="employeeStatus" class="form-label">Status</label>
                            <select class="form-select" id="employeeStatus" name="status" required>
                              <option value="active">Active</option>
                              <option value="inactive">Inactive</option>
                            </select>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-primary" id="employeeModalSubmit">Save</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
            </div>
            <div id="permissions">
                <div class="section-title"><i class="fa fa-key"></i> Permissions Matrix</div>
                <div class="card p-4">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="permissionsTable">
                            <thead>
                                <tr>
                                    <th>Role</th>
                                    <th>Dashboard</th>
                                    <th>Add Property</th>
                                    <th>View Analytics</th>
                                    <th>Manage Users</th>
                                    <th>Manage Employees</th>
                                    <th>Settings</th>
                                    <th>AI Tools</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <button class="btn btn-success mt-3" id="savePermissionsBtn"><i class="fa fa-save"></i> Save Changes</button>
                </div>
            </div>
            <div id="settings">
                <div class="section-title"><i class="fa fa-cog"></i> System Settings</div>
                <div class="card p-4">
                    <form id="settingsForm">
                        <div class="mb-3">
                            <label for="siteTitle" class="form-label">Site Title</label>
                            <input type="text" class="form-control" id="siteTitle" name="site_title" required>
                        </div>
                        <div class="mb-3">
                            <label for="notificationEmail" class="form-label">Notification Email (From Address)</label>
                            <input type="email" class="form-control" id="notificationEmail" name="notification_email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Booking Notifications</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="bookingNotificationEmail" name="booking_notification_email" value="1">
                                <label class="form-check-label" for="bookingNotificationEmail">Send Email on Booking</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="bookingNotificationWhatsapp" name="booking_notification_whatsapp" value="1">
                                <label class="form-check-label" for="bookingNotificationWhatsapp">Send WhatsApp on Booking</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="defaultUserRole" class="form-label">Default User Role</label>
                            <select class="form-select" id="defaultUserRole" name="default_user_role" required>
                                <option value="user">User</option>
                                <option value="customer">Customer</option>
                                <option value="associate">Associate</option>
                                <option value="builder">Builder</option>
                                <option value="agent">Agent</option>
                                <option value="employee">Employee</option>
                            </select>
                        </div>
                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="maintenanceMode" name="maintenance_mode" value="1">
                            <label class="form-check-label" for="maintenanceMode">Maintenance Mode</label>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save Settings</button>
                    </form>
                </div>
            </div>
            <div id="analytics">
                <div class="section-title"><i class="fa fa-bar-chart"></i> Analytics & Reports</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <div class="fw-bold fs-4" id="totalUsers">0</div>
                            <div>Total Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <div class="fw-bold fs-4" id="activeUsers">0</div>
                            <div>Active Users</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <div class="fw-bold fs-4" id="totalProperties">0</div>
                            <div>Total Properties</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center p-3">
                            <div class="fw-bold fs-4" id="totalBookings">0</div>
                            <div>Total Bookings</div>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card p-3">
                            <div class="fw-bold mb-2">Users by Role</div>
                            <canvas id="usersByRoleChart" height="180"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card p-3">
                            <div class="fw-bold mb-2">Bookings Over Time</div>
                            <canvas id="bookingsOverTimeChart" height="180"></canvas>
                        </div>
                    </div>
                </div>
                <div class="card p-3">
                    <div class="fw-bold mb-2">Recent Logins</div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0">
                            <thead>
                                <tr><th>Name</th><th>Email</th><th>Role</th><th>Last Login</th></tr>
                            </thead>
                            <tbody id="recentLogins"></tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div id="ai">
                <div class="section-title"><i class="fa fa-magic"></i> AI Tools & Automation</div>
                <div class="card p-4">
                    <form id="aiSettingsForm">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="aiChatbot" name="ai_chatbot" value="1">
                            <label class="form-check-label" for="aiChatbot">Enable AI Chatbot</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="autoReminders" name="auto_reminders" value="1">
                            <label class="form-check-label" for="autoReminders">Enable Auto-Reminders</label>
                        </div>
                        <div class="mb-3 ms-4">
                            <label for="reminderFrequency" class="form-label">Reminder Frequency</label>
                            <select class="form-select" id="reminderFrequency" name="reminder_frequency" style="max-width:160px">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="smartTicketRouting" name="smart_ticket_routing" value="1">
                            <label class="form-check-label" for="smartTicketRouting">Enable Smart Ticket Routing</label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" id="autoReports" name="auto_reports" value="1">
                            <label class="form-check-label" for="autoReports">Enable Automated Reports</label>
                        </div>
                        <div class="mb-3 ms-4">
                            <label for="reportSchedule" class="form-label">Report Schedule</label>
                            <select class="form-select" id="reportSchedule" name="report_schedule" style="max-width:160px">
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="aiSuggestions" name="ai_suggestions" value="1">
                            <label class="form-check-label" for="aiSuggestions">Enable AI Suggestions/Feedback</label>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fa fa-save"></i> Save AI/Automation Settings</button>
                    </form>
                </div>
                <!-- Automation Log Viewer -->
                <div class="card mt-4">
                    <div class="card-header bg-light">
                        <a class="text-decoration-none" data-bs-toggle="collapse" href="#automationLogCollapse" role="button" aria-expanded="false" aria-controls="automationLogCollapse">
                            <i class="fa fa-file-alt me-2"></i>Automation Log <small class="text-muted">(last 100 lines)</small>
                        </a>
                    </div>
                    <div class="collapse" id="automationLogCollapse">
                        <div class="card-body" style="background:#222;color:#eee;font-family:monospace;font-size:13px;max-height:300px;overflow:auto">
                            <pre id="automationLogContent">Loading...</pre>
                            <button class="btn btn-sm btn-outline-secondary mt-2" id="refreshAutomationLog"><i class="fa fa-sync"></i> Refresh Log</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
<?php include(__DIR__ . '/includes/templates/footer.php'); ?>
<script src="assets/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Fetch and render users
function loadUsers(role = '') {
    $.get('admin/fetch_users.php', role ? {role} : {}, function(users) {
        var rows = '';
        users.forEach(function(user) {
            rows += `<tr>
                <td>${user.id}</td>
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.phone}</td>
                <td>${user.role}</td>
                <td>${user.status}</td>
                <td>
                    <button class='btn btn-sm btn-info me-1 edit-user-btn' data-user='${JSON.stringify(user)}'>Edit</button>
                    <button class='btn btn-sm btn-danger delete-user-btn' data-id='${user.id}'>Delete</button>
                </td>
            </tr>`;
        });
        $('#usersTable tbody').html(rows);
    });
}
$(function() {
    loadUsers();
    $('#roleFilter').change(function() {
        loadUsers($(this).val());
    });
    // Add User
    $('#addUserBtn').click(function() {
        $('#userModalLabel').text('Add User');
        $('#userForm')[0].reset();
        $('#userId').val('');
        $('.password-field').show();
        $('#userModal').modal('show');
    });
    // Edit User
    $(document).on('click', '.edit-user-btn', function() {
        var user = $(this).data('user');
        $('#userModalLabel').text('Edit User');
        $('#userId').val(user.id);
        $('#userName').val(user.name);
        $('#userEmail').val(user.email);
        $('#userPhone').val(user.phone);
        $('#userRole').val(user.role);
        $('#userStatus').val(user.status);
        $('.password-field').hide();
        $('#userModal').modal('show');
    });
    // Delete User
    $(document).on('click', '.delete-user-btn', function() {
        if(confirm('Are you sure you want to delete this user?')) {
            var id = $(this).data('id');
            $.post('admin/user_actions.php', {action:'delete', id}, function(resp) {
                alert(resp.message);
                if(resp.success) loadUsers($('#roleFilter').val());
            },'json');
        }
    });
    // Submit Add/Edit User
    $('#userForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var data = {};
        formData.forEach(function(item){ data[item.name]=item.value; });
        if($('#userId').val()){
            data.action = 'edit';
        }else{
            data.action = 'add';
        }
        $.post('admin/user_actions.php', data, function(resp) {
            alert(resp.message);
            if(resp.success) {
                $('#userModal').modal('hide');
                loadUsers($('#roleFilter').val());
            }
        },'json');
    });
    // Fetch and render employees
    function loadEmployees() {
        $.get('admin/fetch_employees.php', function(employees) {
            var rows = '';
            employees.forEach(function(emp) {
                rows += `<tr>
                    <td>${emp.id}</td>
                    <td>${emp.name}</td>
                    <td>${emp.email}</td>
                    <td>${emp.phone}</td>
                    <td>${emp.role}</td>
                    <td>${emp.status}</td>
                    <td>
                        <button class='btn btn-sm btn-info me-1 edit-employee-btn' data-employee='${JSON.stringify(emp)}'>Edit</button>
                        <button class='btn btn-sm btn-danger delete-employee-btn' data-id='${emp.id}'>Delete</button>
                    </td>
                </tr>`;
            });
            $('#employeesTable tbody').html(rows);
        });
    }
    loadEmployees();
    // Add Employee
    $('#addEmployeeBtn').click(function() {
        $('#employeeModalLabel').text('Add Employee');
        $('#employeeForm')[0].reset();
        $('#employeeId').val('');
        $('#employeeModal').modal('show');
    });
    // Edit Employee
    $(document).on('click', '.edit-employee-btn', function() {
        var emp = $(this).data('employee');
        $('#employeeModalLabel').text('Edit Employee');
        $('#employeeId').val(emp.id);
        $('#employeeName').val(emp.name);
        $('#employeeEmail').val(emp.email);
        $('#employeePhone').val(emp.phone);
        $('#employeeRole').val(emp.role);
        $('#employeeStatus').val(emp.status);
        $('#employeeModal').modal('show');
    });
    // Delete Employee
    $(document).on('click', '.delete-employee-btn', function() {
        if(confirm('Are you sure you want to delete this employee?')) {
            var id = $(this).data('id');
            $.post('admin/employee_actions.php', {action:'delete', id}, function(resp) {
                alert(resp.message);
                if(resp.success) loadEmployees();
            },'json');
        }
    });
    // Submit Add/Edit Employee
    $('#employeeForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serializeArray();
        var data = {};
        formData.forEach(function(item){ data[item.name]=item.value; });
        if($('#employeeId').val()){
            data.action = 'edit';
        }else{
            data.action = 'add';
        }
        $.post('admin/employee_actions.php', data, function(resp) {
            alert(resp.message);
            if(resp.success) {
                $('#employeeModal').modal('hide');
                loadEmployees();
            }
        },'json');
    });
    // Permissions Matrix
    function renderPermissionsTable(permissions) {
        var features = ['dashboard','add_property','view_analytics','manage_users','manage_employees','settings','ai_tools'];
        var featureLabels = ['Dashboard','Add Property','View Analytics','Manage Users','Manage Employees','Settings','AI Tools'];
        var roles = Object.keys(permissions);
        var rows = '';
        roles.forEach(function(role) {
            rows += `<tr data-role='${role}'>
                <td><strong>${role.charAt(0).toUpperCase() + role.slice(1)}</strong></td>`;
            features.forEach(function(feat) {
                var checked = permissions[role][feat] ? 'checked' : '';
                rows += `<td><input type='checkbox' class='perm-switch' data-role='${role}' data-feature='${feat}' ${checked}></td>`;
            });
            rows += '</tr>';
        });
        $('#permissionsTable tbody').html(rows);
    }
    var permissionsData = {};
    function loadPermissions() {
        $.get('admin/fetch_permissions.php', function(perms) {
            permissionsData = perms;
            renderPermissionsTable(permissionsData);
        });
    }
    loadPermissions();
    $(document).on('change', '.perm-switch', function() {
        var role = $(this).data('role');
        var feature = $(this).data('feature');
        permissionsData[role][feature] = $(this).is(':checked') ? 1 : 0;
    });
    $('#savePermissionsBtn').click(function() {
        $.post('admin/save_permissions.php', {permissions: permissionsData}, function(resp) {
            alert(resp.message);
            if(resp.success) loadPermissions();
        },'json');
    });
    // System Settings
    function loadSettings() {
        $.get('admin/fetch_settings.php', function(settings) {
            $('#siteTitle').val(settings.site_title);
            $('#notificationEmail').val(settings.notification_email);
            $('#bookingNotificationEmail').prop('checked', settings.booking_notification_email == 1);
            $('#bookingNotificationWhatsapp').prop('checked', settings.booking_notification_whatsapp == 1);
            $('#defaultUserRole').val(settings.default_user_role);
            $('#maintenanceMode').prop('checked', settings.maintenance_mode == 1);
        });
    }
    loadSettings();
    $('#settingsForm').submit(function(e) {
        e.preventDefault();
        var data = {
            site_title: $('#siteTitle').val(),
            notification_email: $('#notificationEmail').val(),
            booking_notification_email: $('#bookingNotificationEmail').is(':checked') ? 1 : 0,
            booking_notification_whatsapp: $('#bookingNotificationWhatsapp').is(':checked') ? 1 : 0,
            default_user_role: $('#defaultUserRole').val(),
            maintenance_mode: $('#maintenanceMode').is(':checked') ? 1 : 0
        };
        $.post('admin/save_settings.php', {settings: data}, function(resp) {
            alert(resp.message);
            if(resp.success) loadSettings();
        },'json');
    });
    // Analytics Section
    function loadAnalytics() {
        $.get('admin/fetch_analytics.php', function(data) {
            $('#totalUsers').text(data.total_users);
            $('#activeUsers').text(data.active_users);
            $('#totalProperties').text(data.total_properties);
            $('#totalBookings').text(data.total_bookings);
            // Users by role chart
            var ctx1 = document.getElementById('usersByRoleChart').getContext('2d');
            if(window.usersByRoleChart) window.usersByRoleChart.destroy();
            window.usersByRoleChart = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: Object.keys(data.users_by_role).map(function(role){return role.charAt(0).toUpperCase()+role.slice(1);}),
                    datasets: [{
                        label: 'Users',
                        data: Object.values(data.users_by_role),
                        backgroundColor: '#007bff'
                    }]
                },
                options: {plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
            });
            // Bookings over time chart
            var ctx2 = document.getElementById('bookingsOverTimeChart').getContext('2d');
            if(window.bookingsOverTimeChart) window.bookingsOverTimeChart.destroy();
            window.bookingsOverTimeChart = new Chart(ctx2, {
                type: 'line',
                data: {
                    labels: data.bookings_over_time.map(function(row){return row.month;}),
                    datasets: [{
                        label: 'Bookings',
                        data: data.bookings_over_time.map(function(row){return row.cnt;}),
                        fill: true,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40,167,69,0.1)'
                    }]
                },
                options: {plugins:{legend:{display:false}},scales:{y:{beginAtZero:true}}}
            });
            // Recent logins table
            var rows = '';
            data.recent_logins.forEach(function(log){
                rows += `<tr><td>${log.name}</td><td>${log.email}</td><td>${log.utype}</td><td>${log.last_login||''}</td></tr>`;
            });
            $('#recentLogins').html(rows);
        });
    }
    loadAnalytics();
    // AI/Automation Settings
    function loadAISettings() {
        $.get('admin/fetch_ai_settings.php', function(settings) {
            $('#aiChatbot').prop('checked', settings.ai_chatbot == 1);
            $('#autoReminders').prop('checked', settings.auto_reminders == 1);
            $('#reminderFrequency').val(settings.reminder_frequency);
            $('#smartTicketRouting').prop('checked', settings.smart_ticket_routing == 1);
            $('#autoReports').prop('checked', settings.auto_reports == 1);
            $('#reportSchedule').val(settings.report_schedule);
            $('#aiSuggestions').prop('checked', settings.ai_suggestions == 1);
        });
    }
    loadAISettings();
    $('#aiSettingsForm').submit(function(e) {
        e.preventDefault();
        var data = {
            ai_chatbot: $('#aiChatbot').is(':checked') ? 1 : 0,
            auto_reminders: $('#autoReminders').is(':checked') ? 1 : 0,
            reminder_frequency: $('#reminderFrequency').val(),
            smart_ticket_routing: $('#smartTicketRouting').is(':checked') ? 1 : 0,
            auto_reports: $('#autoReports').is(':checked') ? 1 : 0,
            report_schedule: $('#reportSchedule').val(),
            ai_suggestions: $('#aiSuggestions').is(':checked') ? 1 : 0
        };
        $.post('admin/save_ai_settings.php', {settings: data}, function(resp) {
            alert(resp.message);
            if(resp.success) loadAISettings();
        },'json');
    });
    // Automation Log Viewer
    function loadAutomationLog() {
        $.get('admin/fetch_automation_log.php', function(resp) {
            if(resp.success) {
                $('#automationLogContent').text(resp.log);
            } else {
                $('#automationLogContent').text('Failed to load log.');
            }
        });
    }
    $('#refreshAutomationLog').click(function(){loadAutomationLog();});
    $('#automationLogCollapse').on('show.bs.collapse', function(){loadAutomationLog();});
    function logAIInteraction(action, suggestion, feedback, notes) {
        $.post('admin/log_ai_interaction.php', {
            action: action,
            suggestion: suggestion,
            feedback: feedback||'',
            notes: notes||''
        });
    }
    $.get('admin/ai_admin_insights.php', function(resp) {
        if(resp.success) {
            let html = '';
            if(resp.status && resp.status.length) {
                html += '<div class="mb-2"><b>Urgent Issues:</b><ul>';
                resp.status.forEach(function(rem) { html += '<li>'+rem+' <span class="badge bg-light text-dark pointer ms-1" onclick="logAIInteraction(\'feedback\', `'+rem.replace(/'/g,"&#39;")+'`,\'like\')">👍</span> <span class="badge bg-light text-dark pointer" onclick="logAIInteraction(\'feedback\', `'+rem.replace(/'/g,"&#39;")+'`,\'dislike\')">👎</span></li>'; });
                html += '</ul></div>';
            }
            if(resp.insights && resp.insights.length) {
                html += '<div><b>AI Insights:</b><ul>';
                resp.insights.forEach(function(ins) { html += '<li>'+ins+' <span class="badge bg-light text-dark pointer ms-1" onclick="logAIInteraction(\'feedback\', `'+ins.replace(/'/g,"&#39;")+'`,\'like\')">👍</span> <span class="badge bg-light text-dark pointer" onclick="logAIInteraction(\'feedback\', `'+ins.replace(/'/g,"&#39;")+'`,\'dislike\')">👎</span></li>'; });
                html += '</ul></div>';
            }
            if(!html) html = '<div class="text-success">No urgent admin actions required.</div>';
            $('#aiAdminInsightsPanel').html(html);
            // Log panel view for learning
            if(resp.insights) resp.insights.forEach(function(ins){ logAIInteraction('view', ins); });
            if(resp.status) resp.status.forEach(function(rem){ logAIInteraction('view', rem); });
            // Show trends if present
            if(resp.trends && resp.trends.registrations) {
                $('#adminTrendsPanel').show();
                renderTrendChart('trendRegistrations', resp.trends.registrations, 'rgba(40,167,69,0.7)');
                renderTrendChart('trendBookings', resp.trends.bookings, 'rgba(23,162,184,0.7)');
                renderTrendChart('trendTickets', resp.trends.tickets, 'rgba(255,193,7,0.7)');
                renderTrendChart('trendPayments', resp.trends.payments, 'rgba(220,53,69,0.7)');
                if(resp.forecast) {
                    $('#aiForecastPanel').html('<div class="alert alert-info"><b>AI Forecast:</b> '+resp.forecast+' <span class="badge bg-light text-dark pointer ms-1" onclick="logAIInteraction(\'feedback\', `'+resp.forecast.replace(/'/g,"&#39;")+'`,\'like\')">👍</span> <span class="badge bg-light text-dark pointer" onclick="logAIInteraction(\'feedback\', `'+resp.forecast.replace(/'/g,"&#39;")+'`,\'dislike\')">👎</span></div>');
                    logAIInteraction('view', resp.forecast);
                }
            }
        } else {
            $('#aiAdminInsightsPanel').html('<div class="text-danger">Could not load admin insights.</div>');
        }
    },'json');
});
function renderTrendChart(canvasId, data, color) {
    new Chart(document.getElementById(canvasId).getContext('2d'), {
        type: 'line',
        data: {
            labels: Array.from({length: data.length}, (_,i)=>i-13+13),
            datasets: [{
                data: data,
                backgroundColor: color,
                borderColor: color,
                fill: true,
                tension: 0.4,
                pointRadius: 0
            }]
        },
        options: {
            plugins: {legend: {display: false}},
            scales: {x: {display: false}, y: {display: false}},
            elements: {line: {borderWidth:2}}
        }
    });
}
</script>
</body>
</html>
