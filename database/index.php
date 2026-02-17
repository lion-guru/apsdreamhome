<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Management - APS Dream Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
        }

        .dashboard-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .header-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 2rem;
            margin-bottom: 2rem;
        }

        .action-card {
            border: 2px solid #e0e6ed;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            background: white;
        }

        .action-card:hover {
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
            transform: translateY(-2px);
        }

        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .btn-danger-custom {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-success-custom {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            border-radius: 25px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #667eea;
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <div class="container">
            <!-- Header -->
            <div class="header-card text-center">
                <h1><i class="fas fa-database me-3"></i>Database Management Panel</h1>
                <p class="mb-0">APS Dream Home - Enhanced Database Management</p>
                <small>Manage your database updates, backups, and new features</small>
            </div>

            <!-- Main Actions -->
            <div class="row">
                <!-- Update Database -->
                <div class="col-md-6">
                    <div class="action-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-sync-alt"></i>
                        </div>
                        <h3>Update Database</h3>
                        <p>Add new tables and ffor Land Management, Builder Management, MLM system, and enhanced Customer portal.</p>
                        <div class="mb-3">
                            <strong>New Features:</strong>
                            <ul class="text-start mt-2">
                                <li>🌾 Farmer/Kissan Management</li>
                                <li>🏗️ Builder Management</li>
                                <li>👥 Enhanced MLM System</li>
                                <li>👤 Customer Support System</li>
                                <li>📊 Enhanced Analytics</li>
                            </ul>
                        </div>
                        <a href="scripts/updates/update_database_for_new_features.php" class="btn btn-custom" target="_blank">
                            <i class="fas fa-play me-1"></i>Run Database Update
                        </a>
                    </div>
                </div>

                <!-- Backup Database -->
                <div class="col-md-6">
                    <div class="action-card text-center">
                        <div class="feature-icon">
                            <i class="fas fa-download"></i>
                        </div>
                        <h3>Backup Database</h3>
                        <p>Create a coebackup of atayaseour current database including all data and structure for safe keeping.</p>
                        <div class="mb-3">
                            <strong>Backup includes:</strong>
                            <ul class="text-start mt-2">
                                <li>🗃️ Complete database structure</li>
                                <li>📊 All existing data</li>
                                <li>🔧 Table relationships</li>
                                <li>📋 Indexes and constraints</li>
                                <li>📅 Timestamped filename</li>
                            </ul>
                        </div>
                        <a href="scripts/tools/backup_db.php" class="btn btn-success-custom" target="_blank">
                            <i class="fas fa-shield-alt me-1"></i>Create Backup
                        </a>
                    </div>
                </div>

            </div>

            <!-- Additional Tools -->
            <div class="dashboard-card p-4">/schema
                <h2 class="mb-4"><i class="fas fa-tools me-2"></i>Database Tools & Information</h2>

                <div class="row">
                    <!-- Database Structure -->
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-sitemap fa-2x text-primary mb-3"></i>
                                <h5>Database Structure</h5>
                                <p>View the complete enhanced database structure with all new tables.</p>
                                <a href="sql/schema/enhanced_database_structure.sql" class="btn btn-outline-primary" download>
                                    <i class="fas fa-file-code me-1"></i>Download Schema
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Admin Access -->
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-user-shield fa-2x text-success mb-3"></i>
                                <h5>Admin Panel</h5>
                                <p>Access the admin panel to test all new features and functionality.</p>
                                <a href="../admin/login.php" class="btn btn-outline-success">
                                    <i class="fas fa-sign-in-alt me-1"></i>Admin Login
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Portal -->
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x text-info mb-3"></i>
                                <h5>Customer Portal</h5>
                                <p>Test the enhanced customer public dashboard with new features.</p>
                                <a href="../customer_public_dashboard.php" class="btn btn-outline-info">
                                    <i class="fas fa-external-link-alt me-1"></i>Customer Portal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feature Dashboards -->
            <div class="dashboard-card p-4">
                <h2 class="mb-4"><i class="fas fa-tachometer-alt me-2"></i>New Feature Dashboards</h2>

                <div class="row">
                    <!-- Land Manager -->
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-seedling fa-2x text-success mb-3"></i>
                                <h6>Land Manager</h6>
                                <p>Complete farmer to plot sales management</p>
                                <a href="../admin/land_manager_dashboard.php" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-leaf me-1"></i>Access
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Builder Management -->
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-hard-hat fa-2x text-warning mb-3"></i>
                                <h6>Builder Management</h6>
                                <p>Construction project management</p>
                                <a href="../admin/builder_management_dashboard.php" class="btn btn-sm btn-outline-warning">
                                    <i class="fas fa-hammer me-1"></i>Access
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- MLM Dashboard -->
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-network-wired fa-2x text-primary mb-3"></i>
                                <h6>MLM System</h6>
                                <p>Agent network & commission management</p>
                                <a href="../admin/agent_mlm_dashboard.php" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-users me-1"></i>Access
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Company Owner -->
                    <div class="col-md-3 mb-3">
                        <div class="card h-100">
                            <div class="card-body text-center">
                                <i class="fas fa-crown fa-2x text-danger mb-3"></i>
                                <h6>Company Owner</h6>
                                <p>Ultimate system access</p>
                                <a href="../admin/company_owner_dashboard.php" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-crown me-1"></i>Access
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="dashboard-card p-4">
                <h2 class="mb-4"><i class="fas fa-info-circle me-2"></i>Setup Instructions</h2>

                <div class="alert alert-info">
                    <h5><i class="fas fa-lightbulb me-2"></i>Recommended Setup Order:</h5>
                    <ol>
                        <li><strong>Backup First:</strong> Create a backup of your current database</li>
                        <li><strong>Update Database:</strong> Run the database update to add new features</li>
                        <li><strong>Test Admin Panel:</strong> Login and test all new dashboards</li>
                        <li><strong>Test Customer Portal:</strong> Verify customer functionality</li>
                        <li><strong>Add Sample Data:</strong> Use the dashboards to add test data</li>
                    </ol>
                </div>

                <div class="alert alert-warning">
                    <h5><i class="fas fa-exclamation-triangle me-2"></i>Important Notes:</h5>
                    <ul>
                        <li>Always backup your database before making changes</li>
                        <li>Make sure XAMPP/Apache and MySQL are running</li>
                        <li>Default Company Owner login: abhay@apsdreamhome.com</li>
                        <li>All new tables use utf8mb4 character set for full Unicode support</li>
                        <li>Foreign key constraints ensure data integrity</li>
                    </ul>
                </div>

                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle me-2"></i>Features Ready:</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <ul>
                                <li>✅ Land Management System</li>
                                <li>✅ Farmer/Kissan Registration</li>
                                <li>✅ Land Purchase Recording</li>
                                <li>✅ Plot Development Tracking</li>
                                <li>✅ Builder Management</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul>
                                <li>✅ Construction Project Management</li>
                                <li>✅ Enhanced MLM System</li>
                                <li>✅ Customer Support Portal</li>
                                <li>✅ Document Management</li>
                                <li>✅ Role-based Access Control</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-white">
                <p>&copy; 2025 APS Dream Home - Enhanced Database Management System</p>
                <small>Developed for Abhay Singh - Company Owner</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>