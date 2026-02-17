<?php
/**
 * APS Dream Home - Simple Admin Panel
 * Manage properties, users, and system settings
 */

require_once __DIR__ . '/core/init.php';

try {
    $db = \App\Core\App::database();

    // Check if user is logged in as admin
    if (!isAuthenticated() || getAuthRole() !== 'admin') {
        // Simple login check - in production, use proper authentication
        if (isset($_POST['admin_login'])) {
            if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
                    $adminData = [
                        'id' => 1,
                        'auser' => 'admin',
                        'role' => 'admin'
                    ];
                    setAuthSession($adminData, 'admin', 'admin');
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['admin_username'] = 'admin';
                } else {
                    $error = "Invalid credentials";
                }
            } else {
                $error = "Security validation failed. Please try again.";
            }
        }

        if (!isAuthenticated()) {
            ?>
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Admin Login - APS Dream Home</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            </head>
            <body>
                <div class="container mt-5">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <div class="card shadow">
                                <div class="card-header bg-primary text-white">
                                    <h3 class="mb-0"><i class="fas fa-lock me-2"></i>Admin Login</h3>
                                </div>
                                <div class="card-body">
                                    <?php if (isset($error)): ?>
                                        <div class="alert alert-danger"><?php echo h($error); ?></div>
                                    <?php endif; ?>
                                    <form method="POST">
                                        <?php echo getCsrfField(); ?>
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input type="text" name="username" class="form-control" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                        <button type="submit" name="admin_login" class="btn btn-primary w-100">
                                            <i class="fas fa-sign-in-alt me-2"></i>Login
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </body>
            </html>
            <?php
            exit;
        }
    }

    // Admin is logged in - show dashboard
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Dashboard - APS Dream Home</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
        <style>
            .sidebar {
                background: #f8f9fa;
                min-height: 100vh;
                padding: 20px;
            }
            .main-content {
                padding: 20px;
            }
            .stat-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 10px;
                padding: 20px;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <div class="col-md-3 sidebar">
                    <h4><i class="fas fa-tachometer-alt me-2"></i>Admin Panel</h4>
                    <hr>
                    <div class="list-group">
                        <a href="#dashboard" class="list-group-item list-group-item-action active">
                            <i class="fas fa-home me-2"></i>Dashboard
                        </a>
                        <a href="#properties" class="list-group-item list-group-item-action">
                            <i class="fas fa-building me-2"></i>Properties
                        </a>
                        <a href="#users" class="list-group-item list-group-item-action">
                            <i class="fas fa-users me-2"></i>Users
                        </a>
                        <a href="#settings" class="list-group-item list-group-item-action">
                            <i class="fas fa-cog me-2"></i>Settings
                        </a>
                        <a href="logout.php" class="list-group-item list-group-item-action text-danger">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-md-9 main-content">
                    <!-- Dashboard Overview -->
                    <div id="dashboard-content">
                        <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard Overview</h2>

                        <!-- Statistics Cards -->
                        <div class="row">
                            <?php
                            // Get statistics
                            $stats = [
                                'properties' => $db->fetchOne("SELECT COUNT(*) as count FROM properties WHERE status = 'available'")['count'] ?? 0,
                                'users' => $db->fetchOne("SELECT COUNT(*) as count FROM user")['count'] ?? 0,
                                'agents' => $db->fetchOne("SELECT COUNT(*) as count FROM user WHERE utype = '2'")['count'] ?? 0,
                                'customers' => $db->fetchOne("SELECT COUNT(*) as count FROM user WHERE utype = '3'")['count'] ?? 0
                            ];
                            ?>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-home fa-2x mb-3"></i>
                                    <h3><?php echo h($stats['properties']); ?></h3>
                                    <p>Total Properties</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-users fa-2x mb-3"></i>
                                    <h3><?php echo h($stats['users']); ?></h3>
                                    <p>Total Users</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-user-tie fa-2x mb-3"></i>
                                    <h3><?php echo h($stats['agents']); ?></h3>
                                    <p>Agents</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-user fa-2x mb-3"></i>
                                    <h3><?php echo h($stats['customers']); ?></h3>
                                    <p>Customers</p>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2 d-md-flex">
                                            <a href="#properties" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Add New Property
                                            </a>
                                            <a href="setup_demo_data_fixed.php" class="btn btn-success">
                                                <i class="fas fa-database me-2"></i>Refresh Demo Data
                                            </a>
                                            <a href="system_verification.php" class="btn btn-info">
                                                <i class="fas fa-check-circle me-2"></i>System Check
                                            </a>
                                            <a href="index.php" class="btn btn-secondary" target="_blank">
                                                <i class="fas fa-external-link-alt me-2"></i>View Website
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Properties Management -->
                    <div id="properties-content" style="display: none;">
                        <h2><i class="fas fa-building me-2"></i>Properties Management</h2>

                        <?php if (isset($_POST['add_property'])): ?>
                            <?php
                            // Handle property addition
                            if (verifyCSRFToken($_POST['csrf_token'] ?? '')) {
                                $title = $_POST['title'];
                                $description = $_POST['description'];
                                $price = $_POST['price'];
                                $bedrooms = $_POST['bedrooms'];
                                $bathrooms = $_POST['bathrooms'];
                                $area = $_POST['area'];
                                $address = $_POST['address'];
                                $property_type = $_POST['property_type'];

                                $db->execute("INSERT INTO properties (title, description, price, bedrooms, bathrooms, area, address, status, property_type_id, agent_id, is_featured) VALUES (:title, :description, :price, :bedrooms, :bathrooms, :area, :address, 'available', :property_type, 1, 0)", [
                                    'title' => $title,
                                    'description' => $description,
                                    'price' => $price,
                                    'bedrooms' => $bedrooms,
                                    'bathrooms' => $bathrooms,
                                    'area' => $area,
                                    'address' => $address,
                                    'property_type' => $property_type
                                ]);

                                echo '<div class="alert alert-success">Property added successfully!</div>';
                            } else {
                                echo '<div class="alert alert-danger">Security validation failed. Please try again.</div>';
                            }
                            ?>
                        <?php endif; ?>

                        <!-- Add Property Form -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-plus me-2"></i>Add New Property</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <?php echo getCsrfField(); ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Title</label>
                                                <input type="text" name="title" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Price (₹)</label>
                                                <input type="number" name="price" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Bedrooms</label>
                                                <input type="number" name="bedrooms" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Bathrooms</label>
                                                <input type="number" name="bathrooms" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Area (sq.ft)</label>
                                                <input type="number" name="area" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Property Type</label>
                                                <select name="property_type" class="form-select" required>
                                                    <option value="1">Apartment</option>
                                                    <option value="2">Villa</option>
                                                    <option value="3">House</option>
                                                    <option value="4">Plot</option>
                                                    <option value="5">Commercial</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Address</label>
                                                <input type="text" name="address" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label class="form-label">Description</label>
                                                <textarea name="description" class="form-control" rows="3" required></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="submit" name="add_property" class="btn btn-primary">
                                        <i class="fas fa-plus me-2"></i>Add Property
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Properties List -->
                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-list me-2"></i>Recent Properties</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $recent_properties = $db->fetchAll("SELECT p.*, pt.name as property_type FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id ORDER BY p.created_at DESC LIMIT 5");
                                if (!empty($recent_properties)):
                                ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Title</th>
                                                    <th>Type</th>
                                                    <th>Price</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_properties as $property): ?>
                                                    <tr>
                                                        <td><?php echo h($property['title']); ?></td>
                                                        <td><?php echo h($property['property_type']); ?></td>
                                                        <td>₹<?php echo h(number_format($property['price'])); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $property['status'] === 'available' ? 'success' : 'secondary'; ?>">
                                                                <?php echo h(ucfirst($property['status'])); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="property-details.php?id=<?php echo h($property['id']); ?>" class="btn btn-sm btn-info" target="_blank">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No properties found. <a href="#properties">Add your first property!</a></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Users Management -->
                    <div id="users-content" style="display: none;">
                        <h2><i class="fas fa-users me-2"></i>Users Management</h2>

                        <div class="card">
                            <div class="card-header">
                                <h5><i class="fas fa-list me-2"></i>Recent Users</h5>
                            </div>
                            <div class="card-body">
                                <?php
                                $recent_users = $db->fetchAll("SELECT u.uname, u.uemail, u.utype, u.join_date, COALESCE(a.status, 'active') as status
                                                      FROM user u
                                                      LEFT JOIN associates a ON u.uid = a.user_id
                                                      ORDER BY u.join_date DESC LIMIT 10");
                                if (!empty($recent_users)):
                                ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>Status</th>
                                                    <th>Joined</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_users as $user): ?>
                                                    <tr>
                                                        <td><?php echo h($user['uname']); ?></td>
                                                        <td><?php echo h($user['uemail']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php
                                                            echo $user['utype'] == '1' ? 'danger' :
                                                                 ($user['utype'] == '2' ? 'primary' : 'secondary'); ?>">
                                                                <?php
                                                                if ($user['utype'] == '1') echo h('Admin');
                                                                elseif ($user['utype'] == '2') echo h('Agent');
                                                                elseif ($user['utype'] == '3') echo h('Customer');
                                                                else echo h('User');
                                                                ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                                <?php echo h(ucfirst($user['status'])); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo h(date('M d, Y', strtotime($user['join_date']))); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No users found.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div id="settings-content" style="display: none;">
                        <h2><i class="fas fa-cog me-2"></i>System Settings</h2>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-info-circle me-2"></i>System Information</h5>
                                    </div>
                                    <div class="card-body">
                                        <p><strong>PHP Version:</strong> <?php echo h(PHP_VERSION); ?></p>
                                        <p><strong>Server:</strong> <?php echo h($_SERVER['SERVER_SOFTWARE']); ?></p>
                                        <p><strong>Database:</strong> Connected</p>
                                        <p><strong>Demo Data:</strong> Installed</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5><i class="fas fa-tools me-2"></i>Maintenance</h5>
                                    </div>
                                    <div class="card-body">
                                        <a href="comprehensive_test.php" class="btn btn-info mb-2" target="_blank">
                                            <i class="fas fa-check-circle me-2"></i>Run System Tests
                                        </a>
                                        <a href="system_verification.php" class="btn btn-success mb-2" target="_blank">
                                            <i class="fas fa-chart-line me-2"></i>System Verification
                                        </a>
                                        <a href="setup_demo_data_fixed.php" class="btn btn-warning">
                                            <i class="fas fa-database me-2"></i>Refresh Demo Data
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Simple tab switching
            document.querySelectorAll('.list-group-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Remove active class from all items
                    document.querySelectorAll('.list-group-item').forEach(i => i.classList.remove('active'));

                    // Add active class to clicked item
                    this.classList.add('active');

                    // Hide all content
                    document.querySelectorAll('#dashboard-content, #properties-content, #users-content, #settings-content').forEach(content => {
                        content.style.display = 'none';
                    });

                    // Show corresponding content
                    const target = this.getAttribute('href').substring(1) + '-content';
                    const targetElement = document.getElementById(target);
                    if (targetElement) {
                        targetElement.style.display = 'block';
                    }
                });
            });
        </script>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . h($e->getMessage()) . "</div>";
}
?>
