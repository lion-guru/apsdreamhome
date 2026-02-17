<?php
/**
 * Database Explorer - APS Dream Homes
 * View and manage database tables and data
 */

require_once dirname(__DIR__, 2) . '/includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Explorer - APS Dream Homes</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .db-container { max-width: 1400px; margin: 20px auto; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .table-card { background: white; border-radius: 15px; padding: 20px; margin: 15px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .table-responsive { max-height: 400px; overflow-y: auto; }
        .status-badge { padding: 5px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
        .status-success { background: #d4edda; color: #155724; }
        .status-warning { background: #fff3cd; color: #856404; }
        .status-danger { background: #f8d7da; color: #721c24; }
        .column-info { font-size: 0.85rem; color: #666; }
        .action-btn { padding: 3px 8px; margin: 1px; border-radius: 4px; font-size: 0.75rem; }
    </style>
</head>
<body>
    <div class='db-container'>
        <div class='text-center mb-4'>
            <h1><i class='fas fa-database me-2'></i>Database Explorer</h1>
            <p class='lead'>APS Dream Homes - Database Management System</p>
        </div>";

// Database connection info
echo "<div class='table-card'>
    <h3><i class='fas fa-plug me-2'></i>Database Connection</h3>
    <div class='row'>
        <div class='col-md-6'>
            <strong>Host:</strong> " . h(DB_HOST) . "<br>
            <strong>Database:</strong> " . h(DB_NAME) . "<br>
            <strong>Status:</strong> <span class='status-badge status-success'>Connected</span>
        </div>
        <div class='col-md-6'>
            <strong>Connection Type:</strong> MySQL<br>
            <strong>Charset:</strong> utf8mb4<br>
            <strong>Time:</strong> " . date('Y-m-d H:i:s') . "
        </div>
    </div>
</div>";

// Get all tables
echo "<div class='table-card'>
    <h3><i class='fas fa-table me-2'></i>Database Tables</h3>";

try {
    $tables_result = $conn->query("SHOW TABLES");
    $tables = [];

    while ($row = $tables_result->fetch_array()) {
        $tables[] = $row[0];
    }

    echo "<div class='table-responsive'>
        <table class='table table-striped table-hover'>
            <thead class='table-dark'>
                <tr>
                    <th>Table Name</th>
                    <th>Records</th>
                    <th>Size</th>
                    <th>Engine</th>
                    <th>Collation</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>";

    foreach ($tables as $table) {
        // Get table status
        $status_result = $conn->query("SHOW TABLE STATUS LIKE '$table'");
        $status = $status_result->fetch_assoc();

        $record_count = $status['Rows'];
        $size = number_format($status['Data_length'] / 1024, 2) . ' KB';
        $engine = $status['Engine'];
        $collation = $status['Collation'];

        echo "<tr>
            <td><strong>" . h($table) . "</strong></td>
            <td>" . number_format($record_count) . "</td>
            <td>" . $size . "</td>
            <td>" . $engine . "</td>
            <td>" . $collation . "</td>
            <td>
                <button class='btn btn-sm btn-primary action-btn' onclick='viewTable(\"$table\")'>
                    <i class='fas fa-eye'></i> View
                </button>
                <button class='btn btn-sm btn-info action-btn' onclick='viewStructure(\"$table\")'>
                    <i class='fas fa-sitemap'></i> Structure
                </button>
            </td>
        </tr>";
    }

    echo "</tbody></table></div>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
        <i class='fas fa-exclamation-triangle me-2'></i>
        Error fetching tables: " . h($e->getMessage()) . "
    </div>";
}

echo "</div>";

// Key tables detail
$key_tables = ['admin', 'employees', 'employee_tasks', 'employee_activities', 'users', 'properties'];

echo "<div class='table-card'>
    <h3><i class='fas fa-star me-2'></i>Key Tables Detail</h3>";

foreach ($key_tables as $table) {
    try {
        // Check if table exists
        $check_result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($check_result->num_rows > 0) {
            echo "<div class='mb-4 p-3 border rounded'>
                <h5 class='text-primary'><i class='fas fa-table me-2'></i>" . h($table) . "</h5>";

            // Get table structure
            $structure_result = $conn->query("DESCRIBE $table");
            echo "<div class='row'>
                <div class='col-md-6'>
                    <h6>Columns:</h6>
                    <div class='table-responsive'>
                        <table class='table table-sm'>
                            <thead>
                                <tr>
                                    <th>Field</th>
                                    <th>Type</th>
                                    <th>Null</th>
                                    <th>Key</th>
                                </tr>
                            </thead>
                            <tbody>";

            while ($column = $structure_result->fetch_assoc()) {
                echo "<tr>
                    <td><strong>" . h($column['Field']) . "</strong></td>
                    <td>" . h($column['Type']) . "</td>
                    <td>" . h($column['Null']) . "</td>
                    <td>" . h($column['Key']) . "</td>
                </tr>";
            }

            echo "</tbody></table></div></div>";

            // Get sample data
            try {
                $data_result = $conn->query("SELECT * FROM $table LIMIT 5");
                if ($data_result->num_rows > 0) {
                    echo "<div class='col-md-6'>
                        <h6>Sample Data (First 5 records):</h6>
                        <div class='table-responsive'>
                            <table class='table table-sm'>";

                    // Header
                    $fields = $data_result->fetch_fields();
                    echo "<thead><tr>";
                    foreach ($fields as $field) {
                        echo "<th>" . h($field->name) . "</th>";
                    }
                    echo "</tr></thead><tbody>";

                    // Data
                    $data_result->data_seek(0);
                    while ($row = $data_result->fetch_assoc()) {
                        echo "<tr>";
                        foreach ($row as $key => $value) {
                            // Hide sensitive data
                            if (strpos($key, 'password') !== false || strpos($key, 'pass') !== false) {
                                echo "<td><span class='text-muted'>[HIDDEN]</span></td>";
                            } else {
                                echo "<td>" . h(substr($value, 0, 50)) . (strlen($value) > 50 ? '...' : '') . "</td>";
                            }
                        }
                        echo "</tr>";
                    }

                    echo "</tbody></table></div></div>";
                } else {
                    echo "<div class='col-md-6'>
                        <h6>Sample Data:</h6>
                        <p class='text-muted'>No records found in this table.</p>
                    </div>";
                }
            } catch (Exception $e) {
                echo "<div class='col-md-6'>
                    <h6>Sample Data:</h6>
                    <p class='text-danger'>Error: " . h($e->getMessage()) . "</p>
                </div>";
            }

            echo "</div></div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-warning'>
            <i class='fas fa-exclamation-triangle me-2'></i>
            Table '" . h($table) . "': " . h($e->getMessage()) . "
        </div>";
    }
}

echo "</div>";

// Database Statistics
echo "<div class='table-card'>
    <h3><i class='fas fa-chart-bar me-2'></i>Database Statistics</h3>";

try {
    $stats_result = $conn->query("
        SELECT
            COUNT(DISTINCT TABLE_NAME) as table_count,
            SUM(TABLE_ROWS) as total_rows,
            ROUND(SUM(DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as total_size_mb
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
    ");
    $stats = $stats_result->fetch_assoc();

    echo "<div class='row text-center'>
        <div class='col-md-4'>
            <h4 class='text-primary'>" . $stats['table_count'] . "</h4>
            <p class='text-muted'>Total Tables</p>
        </div>
        <div class='col-md-4'>
            <h4 class='text-success'>" . number_format($stats['total_rows']) . "</h4>
            <p class='text-muted'>Total Records</p>
        </div>
        <div class='col-md-4'>
            <h4 class='text-warning'>" . $stats['total_size_mb'] . " MB</h4>
            <p class='text-muted'>Database Size</p>
        </div>
    </div>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
        <i class='fas fa-exclamation-triangle me-2'></i>
        Error fetching statistics: " . h($e->getMessage()) . "
    </div>";
}

echo "</div>";

// Quick Actions
echo "<div class='table-card'>
    <h3><i class='fas fa-tools me-2'></i>Quick Actions</h3>
    <div class='row'>
        <div class='col-md-3'>
            <button class='btn btn-success w-100 mb-2' onclick='runSetup()'>
                <i class='fas fa-play me-2'></i>Run Employee Setup
            </button>
        </div>
        <div class='col-md-3'>
            <button class='btn btn-info w-100 mb-2' onclick='runTest()'>
                <i class='fas fa-vial me-2'></i>Run System Test
            </button>
        </div>
        <div class='col-md-3'>
            <button class='btn btn-warning w-100 mb-2' onclick='createAdmin()'>
                <i class='fas fa-user-plus me-2'></i>Create Admin
            </button>
        </div>
        <div class='col-md-3'>
            <button class='btn btn-secondary w-100 mb-2' onclick='refreshPage()'>
                <i class='fas fa-sync me-2'></i>Refresh
            </button>
        </div>
    </div>
</div>";

echo "<script>
function viewTable(tableName) {
    window.open('view_table.php?table=' + tableName, '_blank', 'width=1000,height=600');
}

function viewStructure(tableName) {
    window.open('table_structure.php?table=' + tableName, '_blank', 'width=800,height=600');
}

function runSetup() {
    if (confirm('Run employee system setup? This will create missing tables and sample data.')) {
        window.open('setup_employee_system.php', '_blank');
    }
}

function runTest() {
    window.open('test_employee_system.php', '_blank');
}

function createAdmin() {
    window.open('create_first_admin.php', '_blank');
}

function refreshPage() {
    location.reload();
}
</script>

<div class='text-center mt-4 mb-3'>
    <hr>
    <p class='text-muted'>
        <i class='fas fa-info-circle me-1'></i>
        Database Explorer - APS Dream Homes Management System<br>
        <small>Last updated: " . date('Y-m-d H:i:s') . "</small>
    </p>
</div>

</div>
</body>
</html>";
?>
