<?php

/**
 * Admin Sidebar for APS Dream Homes
 * Contains sidebar navigation elements shared across all admin pages
 */

// Get current user role for role-based menu visibility
$adminRole = getAuthSubRole();
?>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            <ul>
                <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/') === false) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin"><i class="fe fe-home"></i> <span>Dashboard</span></a>
                </li>

                <li class="submenu">
                    <a href="#"><i class="fe fe-users"></i> <span> Leads</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/leads') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/leads/create') === false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/leads">Leads List</a></li>
                        <li><a class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/leads/create') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/leads/create">Add Lead</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="#"><i class="fe fe-user"></i> <span> Users</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/users') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/users/create') === false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/users">Users List</a></li>
                        <li><a class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/users/create') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/users/create">Add User</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="#"><i class="fe fe-location"></i> <span> Property</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/properties') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/properties/create') === false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/properties">Properties List</a></li>
                        <li><a class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/properties/create') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/properties/create">Add Property</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="#"><i class="fe fe-map"></i> <span> Projects</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/projects') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/projects/create') === false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/projects">Projects List</a></li>
                        <li><a class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/projects/create') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/projects/create">Add Project</a></li>
                    </ul>
                </li>

                <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/about') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/about"><i class="fe fe-info"></i> <span>About</span></a>
                </li>

                <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/contact') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/contact"><i class="fe fe-phone"></i> <span>Contact</span></a>
                </li>

                <li class="submenu">
                    <a href="#"><i class="fe fe-users"></i> <span> CRM</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/crm/dashboard') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/crm/dashboard">CRM Dashboard</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/customers') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/customers/create') === false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/customers">Customers List</a></li>
                        <li><a class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/customers/create') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/customers/create">Add Customer</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/bookings') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/bookings/create') === false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/bookings">Bookings List</a></li>
                        <li><a class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/bookings/create') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/bookings/create">Add Booking</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/employees') !== false && strpos($_SERVER['REQUEST_URI'], '/admin/employees/create') === false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/employees">Employees List</a></li>
                        <li><a class="<?php echo strpos($_SERVER['REQUEST_URI'], '/admin/employees/create') !== false ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/employees/create">Add Employee</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/tickets') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/tickets">Support Tickets</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/tasks') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/tasks">Tasks</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/reports') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/reports">Reports</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="#"><i class="fe fe-cpu"></i> <span> AI Hub</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/ai/hub') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/ai/hub">AI Control Center</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/ai/agent') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/ai/agent">Agent Performance</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/ai/lead-scoring') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/ai/lead-scoring">Lead Scoring</a></li>
                    </ul>
                </li>

                <?php if (isSuperAdmin()): ?>
                    <li class="submenu">
                        <a href="#"><i class="fe fe-settings"></i> <span> System Settings</span> <span class="menu-arrow"></span></a>
                        <ul style="display: none;">
                            <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/superadmin') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/superadmin">Superadmin Dashboard</a></li>
                            <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/settings/whatsapp') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/settings/whatsapp">WhatsApp Automation</a></li>
                            <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/settings/site') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/settings/site">Site Config</a></li>
                            <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/settings/api') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/settings/api">API Management</a></li>
                            <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/settings/backup') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/settings/backup">Backup Manager</a></li>
                            <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/settings/logs') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/settings/logs">Audit Logs</a></li>
                        </ul>
                    </li>
                <?php endif; ?>

                <li class="submenu">
                    <a href="#"><i class="fe fe-activity"></i> <span> Kissan Management</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/kisaan/list') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/kisaan/list">Land Records</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/kisaan/add') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/kisaan/add">Add Land Details</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/gata/list') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/gata/list">Gata Master</a></li>
                    </ul>
                </li>

                <li class="submenu">
                    <a href="#"><i class="fe fe-clock"></i> <span> EMI & Visits</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/emi') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/emi">EMI List</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/visits') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/visits">Visits List</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/visits/create') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/visits/create">Add Visit</a></li>
                    </ul>
                </li>

                <li class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/associates') !== false) ? 'active' : ''; ?>">
                    <a href="<?php echo BASE_URL; ?>/admin/associates"><i class="fe fe-user-plus"></i> <span>Associates</span></a>
                </li>

                <li class="submenu">
                    <a href="#"><i class="fe fe-users"></i> <span> MLM Management</span> <span class="menu-arrow"></span></a>
                    <ul style="display: none;">
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/mlm/reports') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm/reports">MLM Reports</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/mlm/settings') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm/settings">MLM Settings</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/mlm/payouts') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm/payouts">Payouts</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/mlm/commissions') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm/commissions">Commission Reports</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/mlm-analytics') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm-analytics">MLM Analytics</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/mlm-network') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm-network">Network Management</a></li>
                        <li><a class="<?php echo (strpos($_SERVER['REQUEST_URI'], '/admin/mlm-engagement') !== false) ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>/admin/mlm-engagement">Engagement Hub</a></li>
                    </ul>
                </li>

                <li>
                    <a href="<?php echo BASE_URL; ?>/logout"><i class="fe fe-power"></i> <span>Logout</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->