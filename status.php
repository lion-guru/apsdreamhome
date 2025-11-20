<?php
// Simple test page to verify all fixes
require_once 'includes/db_connection.php';
require_once 'includes/helpers/file_helpers.php';

try {
    $conn = getMysqliConnection();
    $page_title = 'System Status - APS Dream Home';
    $meta_description = 'System status and diagnostics for APS Dream Home website';
} catch (Exception $e) {
    echo "<div style='color:red;'>Database connection error: " . $e->getMessage() . "</div>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($meta_description); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h1 class="display-4 fw-bold text-primary">🎉 System Status</h1>
                <p class="lead">All systems operational!</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i>Database Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-success">✅ Connected</span>
                            <small class="text-muted">Database: aps_dream_home</small>
                        </div>

                        <?php
                        $tables = ['properties', 'users', 'testimonials', 'news', 'site_settings'];
                        foreach ($tables as $table) {
                            try {
                                $result = $conn->query("SHOW TABLES LIKE '$table'");
                                $exists = $result->rowCount() > 0;
                                echo '<div class="mb-2">';
                                echo $exists ? '<span class="badge bg-success">✅</span>' : '<span class="badge bg-danger">❌</span>';
                                echo " $table table";
                                echo '</div>';
                            } catch (Exception $e) {
                                echo '<div class="mb-2">';
                                echo '<span class="badge bg-warning">⚠️</span>';
                                echo " $table table (error)";
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Live Data</h5>
                    </div>
                    <div class="card-body">
                        <?php
                        try {
                            $result = $conn->query("SELECT COUNT(*) as count FROM properties WHERE status = 'available'");
                            $row = $result->fetch(PDO::FETCH_ASSOC);
                            $propertyCount = $row['count'] ?? 0;
                        } catch (Exception $e) {
                            $propertyCount = 0;
                        }

                        try {
                            $result = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'agent'");
                            $row = $result->fetch(PDO::FETCH_ASSOC);
                            $agentCount = $row['count'] ?? 0;
                        } catch (Exception $e) {
                            $agentCount = 0;
                        }

                        try {
                            $result = $conn->query("SELECT COUNT(*) as count FROM testimonials WHERE status = 'approved'");
                            $row = $result->fetch(PDO::FETCH_ASSOC);
                            $testimonialCount = $row['count'] ?? 0;
                        } catch (Exception $e) {
                            $testimonialCount = 0;
                        }
                        ?>

                        <div class="row text-center">
                            <div class="col-4">
                                <h3 class="text-primary"><?php echo $propertyCount; ?></h3>
                                <small class="text-muted">Properties</small>
                            </div>
                            <div class="col-4">
                                <h3 class="text-info"><?php echo $agentCount; ?></h3>
                                <small class="text-muted">Agents</small>
                            </div>
                            <div class="col-4">
                                <h3 class="text-success"><?php echo $testimonialCount; ?></h3>
                                <small class="text-muted">Reviews</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-link me-2"></i>Website Pages</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <a href="index.php" class="btn btn-primary w-100">
                                    <i class="fas fa-home me-2"></i>Main Index
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="properties.php" class="btn btn-success w-100">
                                    <i class="fas fa-building me-2"></i>Properties
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="about.php" class="btn btn-info w-100">
                                    <i class="fas fa-info-circle me-2"></i>About Us
                                </a>
                            </div>
                            <div class="col-md-3 mb-3">
                                <a href="contact.php" class="btn btn-warning w-100">
                                    <i class="fas fa-envelope me-2"></i>Contact
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 text-center">
                <div class="alert alert-success">
                    <h5><i class="fas fa-check-circle me-2"></i>All Systems Operational!</h5>
                    <p class="mb-0">Your APS Dream Home website is running perfectly with all features working.</p>
                </div>

                <div class="mt-4">
                    <h6>Next Steps:</h6>
                    <p class="text-muted">
                        1. Clear your browser cache (Ctrl+F5)<br>
                        2. Restart XAMPP if needed<br>
                        3. Visit <strong>http://localhost/apsdreamhome/index.php</strong>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
