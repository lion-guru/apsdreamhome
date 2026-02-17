<?php
/**
 * Employee Management Documentation - APS Dream Homes
 * Complete usage guide for the employee management system
 */

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Employee Management System - APS Dream Homes</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        :root { --primary: #1e40af; --secondary: #3b82f6; --success: #10b981; --danger: #ef4444; --warning: #f59e0b; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .doc-container { max-width: 1200px; margin: 20px auto; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .section-card { background: white; border-radius: 15px; padding: 25px; margin: 20px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .step-number { background: var(--primary); color: white; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; margin-right: 15px; }
        .feature-icon { color: var(--primary); font-size: 2rem; margin-bottom: 15px; }
        .code-block { background: #f8f9fa; border-left: 4px solid var(--primary); padding: 15px; margin: 10px 0; border-radius: 5px; font-family: 'Courier New', monospace; }
        .url-link { color: var(--primary); text-decoration: none; font-weight: 600; }
        .url-link:hover { text-decoration: underline; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-complete { background: #d4edda; color: #155724; }
        .status-pending { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <div class='doc-container'>
        <div class='text-center mb-4'>
            <h1 class='mb-3'><i class='fas fa-users-cog me-2'></i>Employee Management System</h1>
            <p class='lead'>APS Dream Homes - Complete Administration Guide</p>
            <div class='badge bg-success fs-6 mt-2'><i class='fas fa-check-circle me-1'></i>Production Ready</div>
        </div>

        <!-- System Overview -->
        <div class='section-card'>
            <h3><i class='fas fa-info-circle me-2'></i>System Overview</h3>
            <p>The APS Dream Homes Employee Management System provides comprehensive tools for managing employees, departments, roles, and access control. Built with security and scalability in mind.</p>
            
            <div class='row mt-4'>
                <div class='col-md-3 text-center'>
                    <div class='feature-icon'><i class='fas fa-shield-alt'></i></div>
                    <h6>Secure Authentication</h6>
                    <p class='small'>Role-based login with session management</p>
                </div>
                <div class='col-md-3 text-center'>
                    <div class='feature-icon'><i class='fas fa-users'></i></div>
                    <h6>Employee Management</h6>
                    <p class='small'>Complete CRUD operations for employees</p>
                </div>
                <div class='col-md-3 text-center'>
                    <div class='feature-icon'><i class='fas fa-tasks'></i></div>
                    <h6>Task Management</h6>
                    <p class='small'>Assign and track employee tasks</p>
                </div>
                <div class='col-md-3 text-center'>
                    <div class='feature-icon'><i class='fas fa-chart-line'></i></div>
                    <h6>Activity Tracking</h6>
                    <p class='small'>Complete audit logs and monitoring</p>
                </div>
            </div>
        </div>

        <!-- Quick Start -->
        <div class='section-card'>
            <h3><i class='fas fa-rocket me-2'></i>Quick Start Guide</h3>
            
            <div class='mt-4'>
                <div class='d-flex align-items-start mb-3'>
                    <div class='step-number'>1</div>
                    <div>
                        <h6>Setup Database</h6>
                        <p class='mb-2'>Run the setup script to create all required tables:</p>
                        <div class='code-block'><a href='setup_employee_system.php' class='url-link'>setup_employee_system.php</a></div>
                    </div>
                </div>
                
                <div class='d-flex align-items-start mb-3'>
                    <div class='step-number'>2</div>
                    <div>
                        <h6>Create Admin Account</h6>
                        <p class='mb-2'>If no admin exists, create your super admin:</p>
                        <div class='code-block'><a href='create_first_admin.php' class='url-link'>create_first_admin.php</a></div>
                    </div>
                </div>
                
                <div class='d-flex align-items-start mb-3'>
                    <div class='step-number'>3</div>
                    <div>
                        <h6>Access Admin Panel</h6>
                        <p class='mb-2'>Login to admin dashboard:</p>
                        <div class='code-block'><a href='admin/' class='url-link'>admin/</a></div>
                    </div>
                </div>
                
                <div class='d-flex align-items-start mb-3'>
                    <div class='step-number'>4</div>
                    <div>
                        <h6>Manage Employees</h6>
                        <p class='mb-2'>Create and manage employee accounts:</p>
                        <div class='code-block'><a href='admin/manage_employees.php' class='url-link'>admin/manage_employees.php</a></div>
                    </div>
                </div>
                
                <div class='d-flex align-items-start mb-3'>
                    <div class='step-number'>5</div>
                    <div>
                        <h6>Employee Login</h6>
                        <p class='mb-2'>Employees can access their dashboard:</p>
                        <div class='code-block'><a href='employee_login.php' class='url-link'>employee_login.php</a></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Roles -->
        <div class='section-card'>
            <h3><i class='fas fa-user-tag me-2'></i>User Roles & Permissions</h3>
            
            <div class='row mt-4'>
                <div class='col-md-6'>
                    <h5 class='text-primary'><i class='fas fa-crown me-2'></i>Super Admin</h5>
                    <ul class='list-unstyled'>
                        <li><i class='fas fa-check text-success me-2'></i>Create/manage admin accounts</li>
                        <li><i class='fas fa-check text-success me-2'></i>Create/manage employees</li>
                        <li><i class='fas fa-check text-success me-2'></i>Full system access</li>
                        <li><i class='fas fa-check text-success me-2'></i>View all reports</li>
                        <li><i class='fas fa-check text-success me-2'></i>System configuration</li>
                    </ul>
                </div>
                
                <div class='col-md-6'>
                    <h5 class='text-info'><i class='fas fa-user-shield me-2'></i>Admin</h5>
                    <ul class='list-unstyled'>
                        <li><i class='fas fa-check text-success me-2'></i>Manage employees</li>
                        <li><i class='fas fa-check text-success me-2'></i>Reset passwords</li>
                        <li><i class='fas fa-check text-success me-2'></i>View assigned reports</li>
                        <li><i class='fas fa-check text-success me-2'></i>Create content</li>
                        <li><i class='fas fa-times text-danger me-2'></i>Limited system access</li>
                    </ul>
                </div>
            </div>
            
            <div class='row mt-3'>
                <div class='col-md-4'>
                    <h6 class='text-warning'><i class='fas fa-user-tie me-2'></i>Manager</h6>
                    <p class='small'>Department management, team oversight</p>
                </div>
                <div class='col-md-4'>
                    <h6 class='text-secondary'><i class='fas fa-user me-2'></i>Employee</h6>
                    <p class='small'>Basic access, task management</p>
                </div>
                <div class='col-md-4'>
                    <h6 class='text-dark'><i class='fas fa-briefcase me-2'></i>Executive</h6>
                    <p class='small'>High-level access, strategic view</p>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class='section-card'>
            <h3><i class='fas fa-star me-2'></i>Key Features</h3>
            
            <div class='row mt-4'>
                <div class='col-md-6 mb-3'>
                    <div class='d-flex'>
                        <i class='fas fa-lock text-primary me-3 mt-1'></i>
                        <div>
                            <h6>Secure Authentication</h6>
                            <p class='small text-muted'>Password hashing, CSRF protection, session management</p>
                        </div>
                    </div>
                </div>
                
                <div class='col-md-6 mb-3'>
                    <div class='d-flex'>
                        <i class='fas fa-database text-primary me-3 mt-1'></i>
                        <div>
                            <h6>Database Management</h6>
                            <p class='small text-muted'>MySQL with proper indexing and relationships</p>
                        </div>
                    </div>
                </div>
                
                <div class='col-md-6 mb-3'>
                    <div class='d-flex'>
                        <i class='fas fa-chart-bar text-primary me-3 mt-1'></i>
                        <div>
                            <h6>Activity Tracking</h6>
                            <p class='small text-muted'>Complete audit logs for compliance</p>
                        </div>
                    </div>
                </div>
                
                <div class='col-md-6 mb-3'>
                    <div class='d-flex'>
                        <i class='fas fa-mobile-alt text-primary me-3 mt-1'></i>
                        <div>
                            <h6>Responsive Design</h6>
                            <p class='small text-muted'>Works on all devices and screen sizes</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Access URLs -->
        <div class='section-card'>
            <h3><i class='fas fa-link me-2'></i>Access URLs</h3>
            
            <div class='row mt-4'>
                <div class='col-md-6'>
                    <h5 class='text-primary'>Admin Access</h5>
                    <ul class='list-group list-group-flush'>
                        <li class='list-group-item d-flex justify-content-between'>
                            <span><i class='fas fa-sign-in-alt me-2'></i>Admin Login</span>
                            <a href='admin/' class='url-link'>admin/</a>
                        </li>
                        <li class='list-group-item d-flex justify-content-between'>
                            <span><i class='fas fa-tachometer-alt me-2'></i>Admin Dashboard</span>
                            <a href='admin/enhanced_dashboard.php' class='url-link'>enhanced_dashboard.php</a>
                        </li>
                        <li class='list-group-item d-flex justify-content-between'>
                            <span><i class='fas fa-users me-2'></i>Manage Employees</span>
                            <a href='admin/manage_employees.php' class='url-link'>manage_employees.php</a>
                        </li>
                    </ul>
                </div>
                
                <div class='col-md-6'>
                    <h5 class='text-success'>Employee Access</h5>
                    <ul class='list-group list-group-flush'>
                        <li class='list-group-item d-flex justify-content-between'>
                            <span><i class='fas fa-sign-in-alt me-2'></i>Employee Login</span>
                            <a href='employee_login.php' class='url-link'>employee_login.php</a>
                        </li>
                        <li class='list-group-item d-flex justify-content-between'>
                            <span><i class='fas fa-tachometer-alt me-2'></i>Employee Dashboard</span>
                            <a href='employee_dashboard.php' class='url-link'>employee_dashboard.php</a>
                        </li>
                        <li class='list-group-item d-flex justify-content-between'>
                            <span><i class='fas fa-sign-out-alt me-2'></i>Employee Logout</span>
                            <a href='employee_logout.php' class='url-link'>employee_logout.php</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Sample Credentials -->
        <div class='section-card'>
            <h3><i class='fas fa-key me-2'></i>Sample Credentials</h3>
            
            <div class='alert alert-info mt-3'>
                <h6><i class='fas fa-info-circle me-2'></i>Default Test Accounts</h6>
                <div class='row mt-3'>
                    <div class='col-md-6'>
                        <strong>Admin Account:</strong><br>
                        Email: admin@apsdreamhome.com<br>
                        Password: (Set during admin creation)
                    </div>
                    <div class='col-md-6'>
                        <strong>Sample Employees:</strong><br>
                        Email: john@apsdreamhome.com<br>
                        Password: Employee123!
                    </div>
                </div>
            </div>
            
            <div class='alert alert-warning'>
                <h6><i class='fas fa-exclamation-triangle me-2'></i>Security Note</h6>
                <p class='mb-0'>Change default passwords immediately after first login in production environment!</p>
            </div>
        </div>

        <!-- Troubleshooting -->
        <div class='section-card'>
            <h3><i class='fas fa-tools me-2'></i>Troubleshooting</h3>
            
            <div class='accordion mt-3' id='troubleshootAccordion'>
                <div class='accordion-item'>
                    <h2 class='accordion-header'>
                        <button class='accordion-button' type='button' data-bs-toggle='collapse' data-bs-target='#collapseOne'>
                            <i class='fas fa-database me-2'></i>Database Issues
                        </button>
                    </h2>
                    <div id='collapseOne' class='accordion-collapse collapse show' data-bs-parent='#troubleshootAccordion'>
                        <div class='accordion-body'>
                            <p><strong>Problem:</strong> Tables not found or database errors</p>
                            <p><strong>Solution:</strong> Run <a href='setup_employee_system.php' class='url-link'>setup_employee_system.php</a> to create all required tables.</p>
                        </div>
                    </div>
                </div>
                
                <div class='accordion-item'>
                    <h2 class='accordion-header'>
                        <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapseTwo'>
                            <i class='fas fa-user me-2'></i>Login Issues
                        </button>
                    </h2>
                    <div id='collapseTwo' class='accordion-collapse collapse' data-bs-parent='#troubleshootAccordion'>
                        <div class='accordion-body'>
                            <p><strong>Problem:</strong> Cannot login to admin or employee panels</p>
                            <p><strong>Solution:</strong> Check if accounts exist. Create admin with <a href='create_first_admin.php' class='url-link'>create_first_admin.php</a> or create employees via admin panel.</p>
                        </div>
                    </div>
                </div>
                
                <div class='accordion-item'>
                    <h2 class='accordion-header'>
                        <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' data-bs-target='#collapseThree'>
                            <i class='fas fa-lock me-2'></i>Permission Issues
                        </button>
                    </h2>
                    <div id='collapseThree' class='accordion-collapse collapse' data-bs-parent='#troubleshootAccordion'>
                        <div class='accordion-body'>
                            <p><strong>Problem:</strong> Access denied or permission errors</p>
                            <p><strong>Solution:</strong> Ensure user has correct role and status. Check session management and login status.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status -->
        <div class='section-card'>
            <h3><i class='fas fa-check-circle me-2'></i>System Status</h3>
            
            <div class='row mt-4'>
                <div class='col-md-6'>
                    <h6>Core Components</h6>
                    <div class='d-flex justify-content-between mb-2'>
                        <span>Database Tables</span>
                        <span class='status-badge status-complete'>Complete</span>
                    </div>
                    <div class='d-flex justify-content-between mb-2'>
                        <span>Admin System</span>
                        <span class='status-badge status-complete'>Complete</span>
                    </div>
                    <div class='d-flex justify-content-between mb-2'>
                        <span>Employee System</span>
                        <span class='status-badge status-complete'>Complete</span>
                    </div>
                    <div class='d-flex justify-content-between mb-2'>
                        <span>Security Features</span>
                        <span class='status-badge status-complete'>Complete</span>
                    </div>
                </div>
                
                <div class='col-md-6'>
                    <h6>Testing Status</h6>
                    <div class='d-flex justify-content-between mb-2'>
                        <span>Unit Tests</span>
                        <span class='status-badge status-pending'>Pending</span>
                    </div>
                    <div class='d-flex justify-content-between mb-2'>
                        <span>Integration Tests</span>
                        <span class='status-badge status-complete'>Complete</span>
                    </div>
                    <div class='d-flex justify-content-between mb-2'>
                        <span>Security Tests</span>
                        <span class='status-badge status-complete'>Complete</span>
                    </div>
                    <div class='d-flex justify-content-between mb-2'>
                        <span>User Acceptance</span>
                        <span class='status-badge status-pending'>Pending</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Support -->
        <div class='section-card'>
            <h3><i class='fas fa-headset me-2'></i>Support & Documentation</h3>
            
            <div class='row mt-4'>
                <div class='col-md-4 text-center'>
                    <div class='feature-icon'><i class='fas fa-book'></i></div>
                    <h6>Documentation</h6>
                    <p class='small'>Complete user guides and API documentation</p>
                </div>
                <div class='col-md-4 text-center'>
                    <div class='feature-icon'><i class='fas fa-code'></i></div>
                    <h6>Developer Resources</h6>
                    <p class='small'>Code examples and integration guides</p>
                </div>
                <div class='col-md-4 text-center'>
                    <div class='feature-icon'><i class='fas fa-life-ring'></i></div>
                    <h6>Technical Support</h6>
                    <p class='small'>24/7 support for critical issues</p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class='text-center mt-5 mb-3'>
            <hr>
            <p class='text-muted mb-0'>
                <i class='fas fa-copyright me-1'></i>2024 APS Dream Homes - Employee Management System<br>
                <small>Version 1.0 | Production Ready | Secure & Scalable</small>
            </p>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";
?>
