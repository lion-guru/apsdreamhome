<?php
/**
 * View Table Data - APS Dream Homes
 * Display detailed data from any database table
 */

require_once dirname(__DIR__, 2) . '/includes/config.php';

$config = AppConfig::getInstance();
$conn = $config->getDatabaseConnection();

$table = $_GET['table'] ?? '';
if (empty($table)) {
    die("Table name required");
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>View Table: " . h($table) . " - APS Dream Homes</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        body { background: #f8f9fa; }
        .table-container { max-width: 1400px; margin: 20px auto; background: white; border-radius: 15px; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .table-responsive { max-height: 600px; overflow-y: auto; }
        .header-info { background: #e7f3ff; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        .record-count { background: #d4edda; padding: 10px; border-radius: 5px; }
        .sensitive-data { color: #999; font-style: italic; }
    </style>
</head>
<body>
    <div class='table-container'>
        <div class='d-flex justify-content-between align-items-center mb-4'>
            <h2><i class='fas fa-table me-2'></i>Table: " . h($table) . "</h2>
            <button class='btn btn-secondary' onclick='window.close()'>
                <i class='fas fa-times me-2'></i>Close
            </button>
        </div>";

try {
    // Get table info
    $info_result = $conn->query("SHOW TABLE STATUS LIKE '$table'");
    $table_info = $info_result->fetch_assoc();

    echo "<div class='header-info'>
        <div class='row'>
            <div class='col-md-3'>
                <strong>Records:</strong> " . number_format($table_info['Rows']) . "
            </div>
            <div class='col-md-3'>
                <strong>Size:</strong> " . number_format($table_info['Data_length'] / 1024, 2) . " KB
            </div>
            <div class='col-md-3'>
                <strong>Engine:</strong> " . h($table_info['Engine']) . "
            </div>
            <div class='col-md-3'>
                <strong>Created:</strong> " . $table_info['Create_time'] . "
            </div>
        </div>
    </div>";

    // Get table structure
    $structure_result = $conn->query("DESCRIBE $table");
    $columns = [];
    while ($column = $structure_result->fetch_assoc()) {
        $columns[] = $column;
    }

    // Get table data
    $data_result = $conn->query("SELECT * FROM $table LIMIT 100");
    $records = [];
    while ($row = $data_result->fetch_assoc()) {
        $records[] = $row;
    }

    echo "<div class='record-count mb-3'>
        <i class='fas fa-database me-2'></i>
        Showing " . count($records) . " records (limited to first 100)
    </div>";

    if (!empty($records)) {
        echo "<div class='table-responsive'>
            <table class='table table-striped table-hover table-sm'>
                <thead class='table-dark sticky-top'>
                    <tr>";

        foreach ($columns as $column) {
            echo "<th>" . h($column['Field']) . "<br><small class='text-muted'>" . h($column['Type']) . "</small></th>";
        }

        echo "</tr></thead><tbody>";

        foreach ($records as $record) {
            echo "<tr>";
            foreach ($columns as $column) {
                $field_name = $column['Field'];
                $value = $record[$field_name] ?? '';

                // Hide sensitive data
                if (strpos($field_name, 'password') !== false || strpos($field_name, 'pass') !== false) {
                    echo "<td><span class='sensitive-data'>[HIDDEN]</span></td>";
                } else {
                    // Truncate long text
                    if (strlen($value) > 100) {
                        $value = substr($value, 0, 100) . '...';
                    }
                    echo "<td>" . h($value) . "</td>";
                }
            }
            echo "</tr>";
        }

        echo "</tbody></table></div>";
    } else {
        echo "<div class='alert alert-info'>
            <i class='fas fa-info-circle me-2'></i>
            No records found in this table.
        </div>";
    }

    // Show SQL query
    echo "<div class='mt-4'>
        <h5>SQL Query:</h5>
        <div class='bg-light p-3 rounded'>
            <code>SELECT * FROM " . h($table) . " LIMIT 100</code>
        </div>
    </div>";

} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
        <i class='fas fa-exclamation-triangle me-2'></i>
        Error: " . h($e->getMessage()) . "
    </div>";
}

echo "</div>
</body>
</html>";
?>
