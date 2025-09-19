<?php
/**
 * APS Dream Home - Data Export & Reporting Tool
 * 
 * This script provides comprehensive data export and reporting capabilities
 * for the APS Dream Home system, allowing for business analytics and decision-making.
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

// Get available tables
$tables = [];
$result = $conn->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }
}

// Define report types
$reportTypes = [
    'property_sales' => [
        'title' => 'Property Sales Report',
        'description' => 'Analyze property sales data including transaction amounts, dates, and property details.',
        'tables' => ['properties', 'transactions'],
        'query' => "SELECT p.id as property_id, p.title as property_title, p.price as asking_price, 
                    t.amount as transaction_amount, t.date as transaction_date, 
                    (t.amount - p.price) as price_difference
                    FROM properties p
                    JOIN transactions t ON p.id = t.property_id
                    ORDER BY t.date DESC"
    ],
    'lead_conversion' => [
        'title' => 'Lead Conversion Report',
        'description' => 'Track lead conversion rates, sources, and associated revenue.',
        'tables' => ['leads'],
        'query' => "SELECT source, status, 
                    COUNT(*) as total_leads,
                    SUM(CASE WHEN status = 'closed_won' THEN 1 ELSE 0 END) as converted_leads,
                    ROUND((SUM(CASE WHEN status = 'closed_won' THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as conversion_rate,
                    SUM(converted_amount) as total_revenue
                    FROM leads
                    GROUP BY source, status
                    ORDER BY source, status"
    ],
    'agent_performance' => [
        'title' => 'Agent Performance Report',
        'description' => 'Evaluate agent performance based on sales, leads, and commissions.',
        'tables' => ['users', 'transactions', 'leads', 'mlm_commission_ledger'],
        'query' => "SELECT u.id as agent_id, u.name as agent_name,
                    COUNT(DISTINCT t.id) as total_sales,
                    SUM(t.amount) as total_sales_amount,
                    COUNT(DISTINCT l.id) as total_leads,
                    SUM(CASE WHEN l.status = 'closed_won' THEN 1 ELSE 0 END) as converted_leads,
                    SUM(m.commission_amount) as total_commission
                    FROM users u
                    LEFT JOIN transactions t ON u.id = t.agent_id
                    LEFT JOIN leads l ON u.id = l.assigned_to
                    LEFT JOIN mlm_commission_ledger m ON u.id = m.associate_id
                    WHERE u.role = 'agent' OR u.role = 'admin'
                    GROUP BY u.id, u.name
                    ORDER BY total_sales_amount DESC"
    ],
    'property_visits' => [
        'title' => 'Property Visit Analysis',
        'description' => 'Analyze property visit data, conversion rates, and customer feedback.',
        'tables' => ['property_visits', 'properties'],
        'query' => "SELECT p.id as property_id, p.title as property_title,
                    COUNT(v.id) as total_visits,
                    SUM(CASE WHEN v.status = 'completed' THEN 1 ELSE 0 END) as completed_visits,
                    SUM(CASE WHEN v.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_visits,
                    SUM(CASE WHEN v.status = 'no_show' THEN 1 ELSE 0 END) as no_show_visits,
                    AVG(v.rating) as average_rating
                    FROM properties p
                    LEFT JOIN property_visits v ON p.id = v.property_id
                    GROUP BY p.id, p.title
                    ORDER BY total_visits DESC"
    ],
    'revenue_trend' => [
        'title' => 'Revenue Trend Analysis',
        'description' => 'Track revenue trends over time with monthly and quarterly breakdowns.',
        'tables' => ['transactions'],
        'query' => "SELECT 
                    YEAR(date) as year,
                    MONTH(date) as month,
                    CONCAT(YEAR(date), '-', LPAD(MONTH(date), 2, '0')) as period,
                    COUNT(*) as transaction_count,
                    SUM(amount) as total_amount,
                    AVG(amount) as average_amount,
                    MIN(amount) as min_amount,
                    MAX(amount) as max_amount
                    FROM transactions
                    GROUP BY YEAR(date), MONTH(date)
                    ORDER BY YEAR(date), MONTH(date)"
    ],
    'customer_analysis' => [
        'title' => 'Customer Analysis Report',
        'description' => 'Analyze customer data, preferences, and purchase history.',
        'tables' => ['customers', 'transactions', 'property_visits'],
        'query' => "SELECT c.id as customer_id, c.name as customer_name,
                    COUNT(DISTINCT t.id) as total_purchases,
                    SUM(t.amount) as total_spent,
                    COUNT(DISTINCT v.id) as total_visits,
                    MAX(t.date) as last_purchase_date,
                    DATEDIFF(NOW(), MAX(t.date)) as days_since_last_purchase
                    FROM customers c
                    LEFT JOIN transactions t ON c.id = t.user_id
                    LEFT JOIN property_visits v ON c.id = v.customer_id
                    GROUP BY c.id, c.name
                    ORDER BY total_spent DESC"
    ]
];

// Handle form submission
$selectedReport = '';
$reportData = [];
$reportTitle = '';
$reportDescription = '';
$exportFormat = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['report_type']) && array_key_exists($_POST['report_type'], $reportTypes)) {
        $selectedReport = $_POST['report_type'];
        $reportTitle = $reportTypes[$selectedReport]['title'];
        $reportDescription = $reportTypes[$selectedReport]['description'];
        
        // Check if required tables exist
        $missingTables = [];
        foreach ($reportTypes[$selectedReport]['tables'] as $requiredTable) {
            if (!in_array($requiredTable, $tables)) {
                $missingTables[] = $requiredTable;
            }
        }
        
        if (empty($missingTables)) {
            // Run the report query
            $result = $conn->query($reportTypes[$selectedReport]['query']);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $reportData[] = $row;
                }
                
                // Handle export
                if (isset($_POST['export_format'])) {
                    $exportFormat = $_POST['export_format'];
                    
                    switch ($exportFormat) {
                        case 'csv':
                            exportCSV($reportData, $reportTitle);
                            break;
                        case 'excel':
                            exportExcel($reportData, $reportTitle);
                            break;
                        case 'pdf':
                            exportPDF($reportData, $reportTitle, $reportDescription);
                            break;
                        case 'json':
                            exportJSON($reportData, $reportTitle);
                            break;
                    }
                }
            } else {
                $error = "Error running report query: " . $conn->error;
            }
        } else {
            $error = "Missing required tables: " . implode(", ", $missingTables);
        }
    } else {
        $error = "Invalid report type selected";
    }
}

// Export functions
function exportCSV($data, $title) {
    if (empty($data)) return;
    
    // Clean the title for filename
    $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, array_keys($data[0]));
    
    // Add data rows
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

function exportExcel($data, $title) {
    if (empty($data)) return;
    
    // Clean the title for filename
    $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Y-m-d') . '.xls';
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo '<table border="1">';
    
    // Add headers
    echo '<tr>';
    foreach (array_keys($data[0]) as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    echo '</tr>';
    
    // Add data rows
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell) . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>';
    exit;
}

function exportPDF($data, $title, $description) {
    // For PDF export, we'd typically use a library like FPDF or TCPDF
    // Since we can't include external libraries here, we'll create a simple HTML version
    // that can be printed to PDF by the browser
    
    if (empty($data)) return;
    
    echo '<!DOCTYPE html>
    <html>
    <head>
        <title>' . htmlspecialchars($title) . '</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; }
            h1 { color: #3498db; }
            p { margin-bottom: 20px; }
            table { border-collapse: collapse; width: 100%; margin-bottom: 30px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; }
            @media print {
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="no-print" style="text-align: right; margin-bottom: 20px;">
            <button onclick="window.print()">Print PDF</button>
            <button onclick="window.close()">Close</button>
        </div>
        
        <h1>' . htmlspecialchars($title) . '</h1>
        <p>' . htmlspecialchars($description) . '</p>
        
        <table>
            <tr>';
    
    // Add headers
    foreach (array_keys($data[0]) as $header) {
        echo '<th>' . htmlspecialchars($header) . '</th>';
    }
    
    echo '</tr>';
    
    // Add data rows
    foreach ($data as $row) {
        echo '<tr>';
        foreach ($row as $cell) {
            echo '<td>' . htmlspecialchars($cell) . '</td>';
        }
        echo '</tr>';
    }
    
    echo '</table>
        
        <div class="footer">
            Generated on ' . date('Y-m-d H:i:s') . ' by APS Dream Home System
        </div>
    </body>
    </html>';
    exit;
}

function exportJSON($data, $title) {
    if (empty($data)) return;
    
    // Clean the title for filename
    $filename = strtolower(str_replace(' ', '_', $title)) . '_' . date('Y-m-d') . '.json';
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    echo json_encode([
        'title' => $title,
        'generated_at' => date('Y-m-d H:i:s'),
        'data' => $data
    ], JSON_PRETTY_PRINT);
    exit;
}

// Close connection if not exporting
if (empty($exportFormat)) {
    $conn->close();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - Data Export & Reporting Tool</title>
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
        .report-form {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        select, input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
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
        .report-options {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        .report-option {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            width: calc(33.333% - 15px);
            min-width: 250px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .report-option:hover {
            border-color: #3498db;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .report-option.selected {
            border-color: #3498db;
            background-color: #ebf5fb;
        }
        .report-option h3 {
            margin-top: 0;
            color: #2c3e50;
        }
        .report-option p {
            color: #7f8c8d;
            margin-bottom: 0;
        }
        .error {
            color: #e74c3c;
            background-color: #fadbd8;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: white;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            position: sticky;
            top: 0;
        }
        .table-container {
            max-height: 500px;
            overflow-y: auto;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .export-options {
            display: flex;
            gap: 10px;
            margin-top: 20px;
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
            <h1>APS Dream Home - Data Export & Reporting Tool</h1>
        </div>
    </header>
    
    <div class="container">
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="report-form">
            <h2>Select Report Type</h2>
            <form method="post" id="reportForm">
                <div class="report-options">
                    <?php foreach ($reportTypes as $key => $report): ?>
                        <div class="report-option <?php echo ($selectedReport === $key) ? 'selected' : ''; ?>" onclick="selectReport('<?php echo $key; ?>')">
                            <h3><?php echo $report['title']; ?></h3>
                            <p><?php echo $report['description']; ?></p>
                            <input type="radio" name="report_type" value="<?php echo $key; ?>" <?php echo ($selectedReport === $key) ? 'checked' : ''; ?> style="display: none;">
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="form-group" style="margin-top: 20px;">
                    <button type="submit" class="btn">Generate Report</button>
                    <a href="index.php" class="btn btn-secondary">Back to Database Management Hub</a>
                </div>
            </form>
        </div>
        
        <?php if (!empty($reportData)): ?>
            <h2><?php echo $reportTitle; ?></h2>
            <p><?php echo $reportDescription; ?></p>
            
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <?php foreach (array_keys($reportData[0]) as $header): ?>
                                <th><?php echo htmlspecialchars($header); ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <?php foreach ($row as $cell): ?>
                                    <td><?php echo htmlspecialchars($cell ?? ''); ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="export-options">
                <form method="post" action="">
                    <input type="hidden" name="report_type" value="<?php echo $selectedReport; ?>">
                    <button type="submit" name="export_format" value="csv" class="btn">Export as CSV</button>
                    <button type="submit" name="export_format" value="excel" class="btn">Export as Excel</button>
                    <button type="submit" name="export_format" value="pdf" class="btn">Export as PDF</button>
                    <button type="submit" name="export_format" value="json" class="btn">Export as JSON</button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <div class="container">
            <p>APS Dream Home Data Export & Reporting Tool &copy; <?php echo date('Y'); ?></p>
        </div>
    </footer>
    
    <script>
        function selectReport(key) {
            // Unselect all
            document.querySelectorAll('.report-option').forEach(function(el) {
                el.classList.remove('selected');
            });
            
            // Select the clicked one
            document.querySelector('.report-option input[value="' + key + '"]').checked = true;
            document.querySelector('.report-option input[value="' + key + '"]').parentNode.classList.add('selected');
        }
    </script>
</body>
</html>
