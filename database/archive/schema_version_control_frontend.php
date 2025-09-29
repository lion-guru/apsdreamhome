<?php
session_start();
require_once 'includes/src/Database/Database.php';
require_once 'db_schema_version_control.php';

// Authentication check
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Initialize schema version control
try {
    $schemaVersionControl = new DatabaseSchemaVersionControl($conn);
} catch (Exception $e) {
    $error = "Failed to initialize schema version control: " . $e->getMessage();
}

// Handle form submissions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['create_migration'])) {
            $migrationName = $_POST['migration_name'] ?? '';
            $migrationType = $_POST['migration_type'] ?? 'sql';
            
            if (empty($migrationName)) {
                throw new Exception("Migration name is required");
            }

            $migrationPath = $schemaVersionControl->createMigration($migrationName, $migrationType);
            $message = "Migration created successfully: " . basename($migrationPath);
        }

        if (isset($_POST['apply_migrations'])) {
            $appliedMigrations = $schemaVersionControl->applyMigrations();
            $message = "Applied " . count($appliedMigrations) . " migrations successfully";
        }

        if (isset($_POST['generate_diff_report'])) {
            $reportPath = $schemaVersionControl->generateSchemaDiffReport();
            $message = "Schema diff report generated: " . basename($reportPath);
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get pending migrations
try {
    $pendingMigrations = $schemaVersionControl->getPendingMigrations();
} catch (Exception $e) {
    $error = "Failed to retrieve pending migrations: " . $e->getMessage();
    $pendingMigrations = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Schema Version Control</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/custom-admin.css">
    <style>
        .migration-card {
            margin-bottom: 15px;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .pending-migrations {
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
</head>
<body>
    <?php include 'includes/components/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/components/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Database Schema Version Control</h1>
                </div>

                <?php if (!empty($message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($message); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Create Migration</div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="mb-3">
                                        <label class="form-label">Migration Name</label>
                                        <input type="text" name="migration_name" class="form-control" 
                                               placeholder="e.g., Add User Roles" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Migration Type</label>
                                        <select name="migration_type" class="form-select">
                                            <option value="sql">SQL Migration</option>
                                            <option value="php">PHP Migration</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="create_migration" class="btn btn-primary">
                                        Create Migration
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Apply Migrations</div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="pending-migrations mb-3">
                                        <h6>Pending Migrations</h6>
                                        <?php if (empty($pendingMigrations)): ?>
                                            <p class="text-muted">No pending migrations</p>
                                        <?php else: ?>
                                            <ul class="list-group">
                                                <?php foreach ($pendingMigrations as $migration): ?>
                                                    <li class="list-group-item">
                                                        <?php echo htmlspecialchars($migration); ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                    <button type="submit" name="apply_migrations" 
                                            class="btn btn-success" 
                                            <?php echo empty($pendingMigrations) ? 'disabled' : ''; ?>>
                                        Apply Migrations
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">Schema Reports</div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="mb-3">
                                        <button type="submit" name="generate_diff_report" 
                                                class="btn btn-info w-100">
                                            Generate Schema Diff Report
                                        </button>
                                    </div>
                                </form>
                                <div class="recent-reports">
                                    <h6>Recent Reports</h6>
                                    <?php
                                    $reportDir = __DIR__ . '/reports/schema_diff';
                                    $reports = glob($reportDir . '/*.txt');
                                    rsort($reports);
                                    $recentReports = array_slice($reports, 0, 5);
                                    ?>
                                    <ul class="list-group">
                                        <?php foreach ($recentReports as $report): ?>
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <?php echo htmlspecialchars(basename($report)); ?>
                                                <a href="view_report.php?file=<?php echo urlencode(basename($report)); ?>" 
                                                   class="btn btn-sm btn-outline-primary">View</a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">Migration History</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped table-sm">
                                        <thead>
                                            <tr>
                                                <th>Migration Name</th>
                                                <th>Applied At</th>
                                                <th>Status</th>
                                                <th>Execution Time</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Fetch migration history (you'll need to implement this method)
                                            $migrationHistory = $schemaVersionControl->getMigrationHistory();
                                            foreach ($migrationHistory as $migration):
                                            ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($migration['name']); ?></td>
                                                <td><?php echo htmlspecialchars($migration['applied_at']); ?></td>
                                                <td>
                                                    <span class="badge 
                                                        <?php echo $migration['status'] === 'success' ? 'bg-success' : 'bg-danger'; ?>">
                                                        <?php echo htmlspecialchars($migration['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo number_format($migration['execution_time'], 4); ?> sec</td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Optional: Add client-side validation
            const migrationForm = document.querySelector('form[name="create_migration"]');
            migrationForm.addEventListener('submit', function(e) {
                const migrationName = document.querySelector('input[name="migration_name"]');
                if (!migrationName.value.trim()) {
                    e.preventDefault();
                    alert('Please enter a migration name');
                }
            });
        });
    </script>
</body>
</html>
