<?php
require_once __DIR__ . '/../app/core/App.php';
/**
 * APS Dream Home - Simple Admin Panel
 * Manage properties, users, and system settings
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database connection
require_once 'includes/db_connection.php';

try {
    global $con;
    $db = \App\Core\App::database();

    // Check if user is logged in as admin
    if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
        // Simple login check - in production, use proper authentication
        if (isset($_POST['admin_login'])) {
            if ($_POST['username'] === 'admin' && $_POST['password'] === 'admin123') {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = 'admin';
            } else {
                $error = "Invalid credentials";
            }
        }

        if (!isset($_SESSION['admin_logged_in'])) {
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
                                        <div class="alert alert-danger"><?php echo $error; ?></div>
                                    <?php endif; ?>
                                    <form method="POST">
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
                                'properties' => $conn->query("SELECT COUNT(*) as count FROM properties WHERE status = 'available'")->fetch(PDO::FETCH_ASSOC)['count'],
                                'users' => $conn->query("SELECT COUNT(*) as count FROM users")->fetch(PDO::FETCH_ASSOC)['count'],
                                'agents' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'agent'")->fetch(PDO::FETCH_ASSOC)['count'],
                                'customers' => $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'customer'")->fetch(PDO::FETCH_ASSOC)['count']
                            ];
                            ?>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-home fa-2x mb-3"></i>
                                    <h3><?php echo $stats['properties']; ?></h3>
                                    <p>Total Properties</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-users fa-2x mb-3"></i>
                                    <h3><?php echo $stats['users']; ?></h3>
                                    <p>Total Users</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-user-tie fa-2x mb-3"></i>
                                    <h3><?php echo $stats['agents']; ?></h3>
                                    <p>Agents</p>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="stat-card text-center">
                                    <i class="fas fa-user fa-2x mb-3"></i>
                                    <h3><?php echo $stats['customers']; ?></h3>
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
                            $title = $_POST['title'];
                            $description = $_POST['description'];
                            $price = $_POST['price'];
                            $bedrooms = $_POST['bedrooms'];
                            $bathrooms = $_POST['bathrooms'];
                            $area = $_POST['area'];
                            $address = $_POST['address'];
                            $property_type = $_POST['property_type'];

                            $stmt = $conn->prepare("INSERT INTO properties (title, description, price, bedrooms, bathrooms, area, address, status, property_type_id, agent_id, is_featured) VALUES (?, ?, ?, ?, ?, ?, ?, 'available', ?, 1, 0)");
                            $stmt->execute([$title, $description, $price, $bedrooms, $bathrooms, $area, $address, $property_type]);

                            echo '<div class="alert alert-success">Property added successfully!</div>';
                            ?>
                        <?php endif; ?>

                        <!-- Add Property Form -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5><i class="fas fa-plus me-2"></i>Add New Property</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST">
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
                                $result = $conn->query("SELECT p.*, pt.name as property_type FROM properties p LEFT JOIN property_types pt ON p.property_type_id = pt.id ORDER BY p.created_at DESC LIMIT 5");
                                if ($result && $result->rowCount() > 0):
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
                                                <?php while ($property = $result->fetch(PDO::FETCH_ASSOC)): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($property['title']); ?></td>
                                                        <td><?php echo htmlspecialchars($property['property_type']); ?></td>
                                                        <td>₹<?php echo number_format($property['price']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $property['status'] === 'available' ? 'success' : 'secondary'; ?>">
                                                                <?php echo ucfirst($property['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <a href="property-details.php?id=<?php echo $property['id']; ?>" class="btn btn-sm btn-info" target="_blank">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                <?php endwhile; ?>
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
                                $result = $conn->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 10");
                                if ($result && $result->rowCount() > 0):
                                ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Role</th>
                                                    <th>Status</th>
                                                    <th>Created</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($user = $result->fetch(PDO::FETCH_ASSOC)): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php
                                                            echo $user['role'] === 'admin' ? 'danger' :
                                                                 ($user['role'] === 'agent' ? 'primary' : 'secondary'); ?>">
                                                                <?php echo ucfirst($user['role']); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-<?php echo $user['status'] === 'active' ? 'success' : 'warning'; ?>">
                                                                <?php echo ucfirst($user['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
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
                                        <p><strong>PHP Version:</strong> <?php echo PHP_VERSION; ?></p>
                                        <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
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
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>
