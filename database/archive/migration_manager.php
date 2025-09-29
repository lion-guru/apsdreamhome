<?php
/**
 * APS Dream Home - Database Migration Manager
 * 
 * This tool manages database schema migrations and versioning to ensure
 * smooth updates and data integrity for the APS Dream Home system.
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$message = '';
$error = '';
$currentVersion = '1.0.0';
$latestVersion = '1.0.0';
$migrations = [];
$migrationPath = __DIR__ . '/migrations/';

// Create migrations directory if it doesn't exist
if (!file_exists($migrationPath)) {
    mkdir($migrationPath, 0755, true);
}

// Create migrations table if it doesn't exist using prepared statement
$create_migrations_table = "CREATE TABLE IF NOT EXISTS db_migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(50) NOT NULL,
    migration_name VARCHAR(255) NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('success', 'failed') DEFAULT 'success'
)";
$conn->query($create_migrations_table);

// Create db_version table if it doesn't exist using prepared statement
$create_version_table = "CREATE TABLE IF NOT EXISTS db_version (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(50) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($create_version_table);

// Get current database version using prepared statement
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $currentVersion = $row['version'];
    $stmt->close();
} else {
    // Initialize version if not set
    $conn->query("INSERT INTO db_version (version) VALUES ('1.0.0')");
    $stmt->close();
}
// Get all executed migrations
$executedMigrations = [];
$result = $conn->query("SELECT migration_name FROM db_migrations WHERE status = 'success'");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $executedMigrations[] = $row['migration_name'];
    }
}

// Scan migrations directory for available migrations
if (file_exists($migrationPath)) {
    $files = scandir($migrationPath);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        if (preg_match('/^V(\d+\.\d+\.\d+)__(.+)\.sql$/', $file, $matches)) {
            $version = $matches[1];
            $name = $matches[2];
            
            // Check if this is a newer version
            if (version_compare($version, $latestVersion, '>')) {
                $latestVersion = $version;
            }
            
            $migrations[] = [
                'file' => $file,
                'version' => $version,
                'name' => str_replace('_', ' ', $name),
                'executed' => in_array($file, $executedMigrations)
            ];
        }
    }
}

// Sort migrations by version
usort($migrations, function($a, $b) {
    return version_compare($a['version'], $b['version']);
});

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new migration
    if (isset($_POST['create_migration'])) {
        $version = $_POST['new_version'];
        $name = $_POST['migration_name'];
        
        // Validate version format
        if (!preg_match('/^\d+\.\d+\.\d+$/', $version)) {
            $error = "Invalid version format. Use semantic versioning (e.g., 1.0.1).";
        } 
        // Validate name format
        else if (!preg_match('/^[a-zA-Z0-9_\s]+$/', $name)) {
            $error = "Invalid migration name. Use only letters, numbers, spaces, and underscores.";
        } 
        else {
            $fileName = 'V' . $version . '__' . str_replace(' ', '_', $name) . '.sql';
            $filePath = $migrationPath . $fileName;
            
            // Check if file already exists
            if (file_exists($filePath)) {
                $error = "Migration file already exists: $fileName";
            } else {
                // Create migration file with template
                $template = "-- Migration: $name
-- Version: $version
-- Created: " . date('Y-m-d H:i:s') . "

-- Write your SQL statements below this line
-- Each statement must end with a semicolon

-- Example:
-- ALTER TABLE table_name ADD COLUMN new_column VARCHAR(255);
-- CREATE INDEX idx_column ON table_name(column_name);

-- Add your migration SQL here


-- Migration verification
-- Add SELECT queries to verify the migration was successful
-- Example: SELECT COUNT(*) FROM table_name WHERE new_column IS NOT NULL;

";
                
                if (file_put_contents($filePath, $template)) {
                    $message = "Migration file created successfully: $fileName";
                    
                    // Refresh the page to show the new migration
                    header("Location: migration_manager.php?message=" . urlencode($message));
                    exit;
                } else {
                    $error = "Failed to create migration file. Check directory permissions.";
                }
            }
        }
    }
    
    // Run migrations
    else if (isset($_POST['run_migrations'])) {
        $targetVersion = $_POST['target_version'];
        $migrationsToRun = [];
        
        // Find migrations to run
        foreach ($migrations as $migration) {
            if (!$migration['executed'] && version_compare($migration['version'], $currentVersion, '>') && version_compare($migration['version'], $targetVersion, '<=')) {
                $migrationsToRun[] = $migration;
            }
        }
        
        if (empty($migrationsToRun)) {
            $message = "No migrations to run.";
        } else {
            $successCount = 0;
            $failCount = 0;
            
            // Start transaction
            $conn->begin_transaction();
            
            try {
                foreach ($migrationsToRun as $migration) {
                    $sql = file_get_contents($migrationPath . $migration['file']);
                    
                    // Split SQL by semicolons (ignoring semicolons in comments)
                    $queries = preg_split('/;\s*\n(?=(?:[^-\'"]|\'[^\']*\'|"[^"]*")*$)/', $sql);
                    
                    $success = true;
                    
                    foreach ($queries as $query) {
                        $query = trim($query);
                        if (empty($query)) continue;
                        
                        if (!$conn->query($query)) {
                            $error .= "Error in migration {$migration['file']}: " . $conn->error . "<br>";
                            $success = false;
                            break;
                        }
                    }
                    
                    // Record migration execution
                    $migrationName = $migration['file'];
                    $migrationVersion = $migration['version'];
                    $status = $success ? 'success' : 'failed';
                    
                    $stmt = $conn->prepare("INSERT INTO db_migrations (version, migration_name, status) VALUES (?, ?)");
                    $stmt->bind_param("ss", $migrationVersion, $migrationName);
                    $stmt->execute();
                    $stmt->close();
                }
                
                if ($failCount === 0) {
                    // Update database version
                    $stmt = $conn->prepare("INSERT INTO db_version (version) VALUES (?)");
                    $stmt->bind_param("s", $targetVersion);
                    $stmt->execute();
                    $stmt->close();
                    $conn->commit();
                    $message = "Successfully executed $successCount migrations. Database version updated to $targetVersion.";
                } else {
                    // Rollback transaction
                    $conn->rollback();
                    $error = "Migration failed. Database rolled back to previous state. $successCount migrations succeeded, $failCount failed.";
                }
            } catch (Exception $e) {
                // Rollback transaction
                $conn->rollback();
                $error = "Exception during migration: " . $e->getMessage();
            }
        }
        
        // Refresh the page to show updated migration status
        header("Location: migration_manager.php?message=" . urlencode($message) . "&error=" . urlencode($error));
        exit;
    }
    
    // Rollback migration
    else if (isset($_POST['rollback'])) {
        $rollbackVersion = $_POST['rollback_version'];
        
        // Find the migration to rollback
        $rollbackMigration = null;
        foreach ($migrations as $migration) {
            if ($migration['version'] === $rollbackVersion && $migration['executed']) {
                $rollbackMigration = $migration;
                break;
            }
        }
        
        if (!$rollbackMigration) {
            $error = "Migration not found or not executed: V$rollbackVersion";
        } else {
            // Create rollback file if it doesn't exist
            $rollbackFile = 'R' . $rollbackMigration['version'] . '__' . str_replace(' ', '_', $rollbackMigration['name']) . '.sql';
            $rollbackPath = $migrationPath . $rollbackFile;
            
            if (!file_exists($rollbackPath)) {
                $error = "Rollback file not found: $rollbackFile. Create it first.";
            } else {
                // Start transaction
                $conn->begin_transaction();
                
                try {
                    $sql = file_get_contents($rollbackPath);
                    
                    // Split SQL by semicolons
                    $queries = preg_split('/;\s*\n(?=(?:[^-\'"]|\'[^\']*\'|"[^"]*")*$)/', $sql);
                    
                    $success = true;
                    
                    foreach ($queries as $query) {
                        $query = trim($query);
                        if (empty($query)) continue;
                        
                        if (!$conn->query($query)) {
                            $error .= "Error in rollback {$rollbackFile}: " . $conn->error . "<br>";
                            $success = false;
                            break;
                        }
                    }
                    
                    if ($success) {
                        // Mark migration as rolled back
                        $migrationName = $rollbackMigration['file'];
                        $conn->query("DELETE FROM db_migrations WHERE migration_name = '$migrationName'");
                        
                        // Find previous version
                        $prevVersion = '1.0.0';
                        foreach ($migrations as $migration) {
                            if ($migration['executed'] && $migration['version'] !== $rollbackMigration['version'] && version_compare($migration['version'], $prevVersion, '>')) {
                                $prevVersion = $migration['version'];
                            }
                        }
                        
                        // Update database version
                    $stmt = $conn->prepare("INSERT INTO db_version (version) VALUES (?)");
                    $stmt->bind_param("s", $prevVersion);
                    $stmt->execute();
                    $stmt->close();
                        $conn->commit();
                        $message = "Successfully rolled back migration to version $prevVersion.";
                    } else {
                        // Rollback transaction
                        $conn->rollback();
                        $error = "Rollback failed. Database remains unchanged.";
                    }
                } catch (Exception $e) {
                    // Rollback transaction
                    $conn->rollback();
                    $error = "Exception during rollback: " . $e->getMessage();
                }
            }
        }
        
        // Refresh the page to show updated migration status
        header("Location: migration_manager.php?message=" . urlencode($message) . "&error=" . urlencode($error));
        exit;
    }
    
    // Create rollback file
    else if (isset($_POST['create_rollback'])) {
        $rollbackVersion = $_POST['rollback_version'];
        
        // Find the migration to create rollback for
        $targetMigration = null;
        foreach ($migrations as $migration) {
            if ($migration['version'] === $rollbackVersion) {
                $targetMigration = $migration;
                break;
            }
        }
        
        if (!$targetMigration) {
            $error = "Migration not found: V$rollbackVersion";
        } else {
            $rollbackFile = 'R' . $targetMigration['version'] . '__' . str_replace(' ', '_', $targetMigration['name']) . '.sql';
            $rollbackPath = $migrationPath . $rollbackFile;
            
            // Check if file already exists
            if (file_exists($rollbackPath)) {
                $error = "Rollback file already exists: $rollbackFile";
            } else {
                // Create rollback file with template
                $template = "-- Rollback for Migration: {$targetMigration['name']}
-- Version: {$targetMigration['version']}
-- Created: " . date('Y-m-d H:i:s') . "

-- Write your rollback SQL statements below this line
-- Each statement must end with a semicolon

-- Example:
-- ALTER TABLE table_name DROP COLUMN new_column;
-- DROP INDEX idx_column ON table_name;

-- Add your rollback SQL here


-- Rollback verification
-- Add SELECT queries to verify the rollback was successful

";
                
                if (file_put_contents($rollbackPath, $template)) {
                    $message = "Rollback file created successfully: $rollbackFile";
                } else {
                    $error = "Failed to create rollback file. Check directory permissions.";
                }
            }
        }
    }
}

// Get URL parameters
if (isset($_GET['message'])) {
    $message = $_GET['message'];
}
if (isset($_GET['error'])) {
    $error = $_GET['error'];
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Database Migration Manager</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 20px 0;
            margin-bottom: 30px;
        }
        h1 {
            margin: 0;
            padding: 0 20px;
            font-size: 28px;
        }
        h2 {
            color: #3498db;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        .card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .info-item {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 15px;
            flex: 1;
            margin-right: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .info-item:last-child {
            margin-right: 0;
        }
        .info-item h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .info-item p {
            margin-bottom: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover {
            background-color: #2980b9;
        }
        .btn-secondary {
            background-color: #95a5a6;
        }
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        .btn-success {
            background-color: #2ecc71;
        }
        .btn-success:hover {
            background-color: #27ae60;
        }
        .btn-warning {
            background-color: #f39c12;
        }
        .btn-warning:hover {
            background-color: #e67e22;
        }
        .btn-danger {
            background-color: #e74c3c;
        }
        .btn-danger:hover {
            background-color: #c0392b;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"], select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
        }
        tr:hover {
            background-color: #f8f9fa;
        }
        .status-executed {
            color: #2ecc71;
        }
        .status-pending {
            color: #f39c12;
        }
        .actions {
            display: flex;
            gap: 5px;
        }
        footer {
            margin-top: 50px;
            text-align: center;
            color: #7f8c8d;
            font-size: 14px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <h1>APS Dream Home - Database Migration Manager</h1>
        </div>
    </header>
    
    <div class="container">
        <?php if (!empty($message)): ?>
            <div class="alert alert-success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="info-box">
            <div class="info-item">
                <h3>Current Version</h3>
                <p><?php echo $currentVersion; ?></p>
            </div>
            <div class="info-item">
                <h3>Latest Available Version</h3>
                <p><?php echo $latestVersion; ?></p>
            </div>
            <div class="info-item">
                <h3>Total Migrations</h3>
                <p><?php echo count($migrations); ?></p>
            </div>
            <div class="info-item">
                <h3>Pending Migrations</h3>
                <p><?php echo count(array_filter($migrations, function($m) { return !$m['executed']; })); ?></p>
            </div>
        </div>
        
        <div class="card">
            <h2>Available Migrations</h2>
            
            <?php if (empty($migrations)): ?>
                <p>No migrations found. Create your first migration below.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Version</th>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($migrations as $migration): ?>
                            <tr>
                                <td><?php echo $migration['version']; ?></td>
                                <td><?php echo $migration['name']; ?></td>
                                <td class="<?php echo $migration['executed'] ? 'status-executed' : 'status-pending'; ?>">
                                    <?php echo $migration['executed'] ? 'Executed' : 'Pending'; ?>
                                </td>
                                <td class="actions">
                                    <?php if ($migration['executed']): ?>
                                        <form method="post" onsubmit="return confirm('Are you sure you want to rollback this migration? This action cannot be undone.');">
                                            <input type="hidden" name="rollback_version" value="<?php echo $migration['version']; ?>">
                                            <button type="submit" name="rollback" class="btn btn-danger">Rollback</button>
                                        </form>
                                    <?php else: ?>
                                        <a href="<?php echo $migrationPath . $migration['file']; ?>" class="btn btn-secondary" target="_blank">View</a>
                                    <?php endif; ?>
                                    
                                    <?php if (!file_exists($migrationPath . 'R' . $migration['version'] . '__' . str_replace(' ', '_', $migration['name']) . '.sql')): ?>
                                        <form method="post">
                                            <input type="hidden" name="rollback_version" value="<?php echo $migration['version']; ?>">
                                            <button type="submit" name="create_rollback" class="btn btn-warning">Create Rollback</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (count(array_filter($migrations, function($m) { return !$m['executed']; })) > 0): ?>
                    <div style="margin-top: 20px;">
                        <form method="post" onsubmit="return confirm('Are you sure you want to run all pending migrations up to the selected version?');">
                            <div class="form-group">
                                <label for="target_version">Run Migrations Up To Version:</label>
                                <select name="target_version" id="target_version">
                                    <?php 
                                    foreach ($migrations as $migration) {
                                        if (!$migration['executed'] && version_compare($migration['version'], $currentVersion, '>')) {
                                            echo "<option value=\"{$migration['version']}\">{$migration['version']} - {$migration['name']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <button type="submit" name="run_migrations" class="btn btn-success">Run Migrations</button>
                        </form>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="card">
            <h2>Create New Migration</h2>
            <form method="post">
                <div class="form-group">
                    <label for="new_version">Version:</label>
                    <input type="text" name="new_version" id="new_version" placeholder="e.g., 1.0.1" required>
                </div>
                <div class="form-group">
                    <label for="migration_name">Migration Name:</label>
                    <input type="text" name="migration_name" id="migration_name" placeholder="e.g., Add_User_Preferences_Table" required>
                </div>
                <button type="submit" name="create_migration" class="btn btn-primary">Create Migration</button>
            </form>
        </div>
        
        <div style="text-align: center; margin-top: 20px;">
            <a href="index.php" class="btn btn-secondary">Return to Database Management Hub</a>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>APS Dream Home Database Migration Manager &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
</body>
</html>
