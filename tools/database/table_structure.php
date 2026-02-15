<?php
/**
 * Table Structure Viewer - APS Dream Homes
 * View and analyze database table structure
 */

require_once dirname(__DIR__, 2) . '/includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

// Get table name from URL parameter
$table_name = $_GET['table'] ?? '';

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Table Structure - APS Dream Homes</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .structure-container { max-width: 1200px; margin: 20px auto; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-radius: 20px; padding: 30px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); }
        .table-section { background: white; border-radius: 15px; padding: 25px; margin: 20px 0; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .column-type { font-family: monospace; background: #f8f9fa; padding: 2px 6px; border-radius: 4px; }
        .sql-preview { background: #2d3748; color: #e2e8f0; padding: 20px; border-radius: 8px; font-family: monospace; overflow-x: auto; }
        .table-list { max-height: 300px; overflow-y: auto; }
    </style>
</head>
<body>
    <div class='structure-container'>
        <div class='text-center mb-4'>
            <h1><i class='fas fa-table me-2'></i>Database Table Structure</h1>
            <p class='lead'>APS Dream Homes - Table Analysis Tool</p>
        </div>";

// If no table specified, show available tables
if (empty($table_name)) {
    echo "<div class='table-section'>
        <h3><i class='fas fa-list me-2'></i>Available Tables</h3>
        <p>Select a table to view its structure:</p>";

    try {
        $result = $conn->query("SHOW TABLES");
        if ($result->num_rows > 0) {
            echo "<div class='table-list'>";
            echo "<div class='list-group'>";

            while ($row = $result->fetch_assoc()) {
                $table = array_values($row)[0];
                echo "<a href='?table=" . urlencode($table) . "' class='list-group-item list-group-item-action'>
                    <i class='fas fa-table me-2'></i>" . h($table) . "
                    <span class='float-end'><i class='fas fa-arrow-right'></i></span>
                </a>";
            }

            echo "</div>";
            echo "</div>";
        } else {
            echo "<div class='alert alert-warning'>
                <i class='fas fa-exclamation-triangle me-2'></i>
                No tables found in database
            </div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>
            <i class='fas fa-times-circle me-2'></i>
            Error fetching tables: " . $e->getMessage() . "
        </div>";
    }

    echo "</div>";
} else {
    // Show specific table structure
    echo "<div class='table-section'>
        <h3><i class='fas fa-table me-2'></i>Table Structure: " . h($table_name) . "</h3>

        <div class='mb-3'>
            <a href='?' class='btn btn-secondary'>
                <i class='fas fa-arrow-left me-2'></i>Back to Table List
            </a>
            <a href='database_explorer.php' class='btn btn-primary ms-2'>
                <i class='fas fa-database me-2'></i>Database Explorer
            </a>
        </div>";

    try {
        // Check if table exists
        $result = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($table_name) . "'");
        if ($result->num_rows == 0) {
            echo "<div class='alert alert-danger'>
                <i class='fas fa-times-circle me-2'></i>
                Table '" . h($table_name) . "' does not exist
            </div>";
        } else {
            // Get table structure
            $result = $conn->query("DESCRIBE " . $conn->real_escape_string($table_name));

            if ($result->num_rows > 0) {
                echo "<div class='table-responsive'>
                    <table class='table table-striped table-bordered'>
                        <thead class='table-dark'>
                            <tr>
                                <th>Column</th>
                                <th>Type</th>
                                <th>Null</th>
                                <th>Key</th>
                                <th>Default</th>
                                <th>Extra</th>
                            </tr>
                        </thead>
                        <tbody>";

                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><strong>" . h($row['Field']) . "</strong></td>";
                    echo "<td><span class='column-type'>" . h($row['Type']) . "</span></td>";
                    echo "<td>" . h($row['Null']) . "</td>";
                    echo "<td>" . h($row['Key']) . "</td>";
                    echo "<td>" . h($row['Default'] ?? 'NULL') . "</td>";
                    echo "<td>" . h($row['Extra']) . "</td>";
                    echo "</tr>";
                }

                echo "</tbody>
                    </table>
                </div>";

                // Get table statistics
                $result = $conn->query("SELECT COUNT(*) as record_count FROM " . $conn->real_escape_string($table_name));
                $stats = $result->fetch_assoc();

                echo "<div class='row mt-4'>
                    <div class='col-md-4'>
                        <div class='card'>
                            <div class='card-body text-center'>
                                <h5 class='card-title text-primary'>" . number_format($stats['record_count']) . "</h5>
                                <p class='card-text'>Total Records</p>
                            </div>
                        </div>
                    </div>";

                // Get table size (approximate)
                try {
                    $result = $conn->query("
                        SELECT
                            ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
                            table_rows as rows
                        FROM information_schema.TABLES
                        WHERE table_schema = DATABASE()
                        AND table_name = '" . $conn->real_escape_string($table_name) . "'
                    ");
                    $size_info = $result->fetch_assoc();
                } catch (Exception $e) {
                    // Fallback for MariaDB/MySQL compatibility issues
                    $size_info = ['size_mb' => 'N/A', 'rows' => 'N/A'];
                }

                echo "<div class='col-md-4'>
                    <div class='card'>
                        <div class='card-body text-center'>
                            <h5 class='card-title text-success'>" . ($size_info['size_mb'] ?? '0') . " MB</h5>
                            <p class='card-text'>Table Size</p>
                        </div>
                    </div>
                </div>";

                // Get column count
                $result = $conn->query("DESCRIBE " . $conn->real_escape_string($table_name));
                $column_count = $result->num_rows;

                echo "<div class='col-md-4'>
                    <div class='card'>
                        <div class='card-body text-center'>
                            <h5 class='card-title text-warning'>$column_count</h5>
                            <p class='card-text'>Columns</p>
                        </div>
                    </div>
                </div>
                </div>";

                // Show CREATE TABLE statement
                $result = $conn->query("SHOW CREATE TABLE " . $conn->real_escape_string($table_name));
                $create_info = $result->fetch_assoc();

                echo "<div class='mt-4'>
                    <h5><i class='fas fa-code me-2'></i>CREATE TABLE Statement</h5>
                    <div class='sql-preview'>
                        <pre style='background: #f4f4f4; padding: 15px; border-radius: 5px; border: 1px solid #ddd; overflow-x: auto;'>" . h($create_info['Create Table']) . "</pre>
                    </div>
                </div>";

                // Show sample data (first 5 records)
                $result = $conn->query("SELECT * FROM " . $conn->real_escape_string($table_name) . " LIMIT 5");
                if ($result->num_rows > 0) {
                    echo "<div class='mt-4'>
                        <h5><i class='fas fa-database me-2'></i>Sample Data (First 5 Records)</h5>
                        <div class='table-responsive'>
                            <table class='table table-striped table-sm'>
                                <thead class='table-dark'>
                                    <tr>";

                    // Get column names
                    $columns = [];
                    $result->data_seek(0);
                    $first_row = $result->fetch_assoc();
                    foreach ($first_row as $column => $value) {
                        $columns[] = $column;
                        echo "<th>" . h($column) . "</th>";
                    }

                    echo "</tr>
                                </thead>
                                <tbody>";

                    // Reset and show data
                    $result->data_seek(0);
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        foreach ($columns as $column) {
                            $value = $row[$column];
                            if (is_null($value)) {
                                echo "<td><em>NULL</em></td>";
                            } elseif (strlen($value) > 100) {
                                echo "<td>" . h(substr($value, 0, 100)) . "...</td>";
                            } else {
                                echo "<td>" . h($value) . "</td>";
                            }
                        }
                        echo "</tr>";
                    }

                    echo "</tbody>
                            </table>
                        </div>
                    </div>";
                }

            } else {
                echo "<div class='alert alert-warning'>
                    <i class='fas fa-exclamation-triangle me-2'></i>
                    No structure information available for table '" . h($table_name) . "'
                </div>";
            }
        }

    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>
            <i class='fas fa-times-circle me-2'></i>
            Error analyzing table: " . h($e->getMessage()) . "
        </div>";
    }

    echo "</div>";
}

echo "<div class='text-center mt-4'>
    <hr>
    <p class='text-muted'>
        <i class='fas fa-table me-1'></i>
        Table Structure Viewer - APS Dream Homes<br>
        <small>Database table analysis and structure inspection</small>
    </p>
</div>

</div>
</body>
</html>";
?>
