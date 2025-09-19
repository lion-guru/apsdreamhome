<?php
/**
 * APS Dream Home - System Health Check
 * 
 * This script performs a comprehensive health check of the entire APS Dream Home system,
 * including database, file permissions, PHP configuration, and more.
 */

// Set header for browser output
header('Content-Type: text/html; charset=utf-8');

// Start time tracking
$start_time = microtime(true);

// Initialize results array
$results = [
    'database' => [],
    'files' => [],
    'php' => [],
    'security' => [],
    'performance' => [],
    'widgets' => []
];

// Connect to database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "apsdreamhomefinal";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    $results['database'][] = [
        'test' => 'Database Connection',
        'status' => 'error',
        'message' => "Connection failed: " . $conn->connect_error,
        'recommendation' => 'Check database credentials and ensure MySQL server is running.'
    ];
} else {
    $results['database'][] = [
        'test' => 'Database Connection',
        'status' => 'success',
        'message' => "Connected successfully to database",
        'recommendation' => ''
    ];
    
    // Check core tables
    $core_tables = [
        'properties', 'customers', 'leads', 'bookings', 
        'transactions', 'property_visits', 'notifications', 
        'users', 'mlm_commission_ledger', 'associates'
    ];
    
    foreach ($core_tables as $table) {
        $result = $conn->query("SHOW TABLES LIKE '$table'");
        if ($result && $result->num_rows > 0) {
            // Table exists, check record count
            $count_result = $conn->query("SELECT COUNT(*) as count FROM $table");
            if ($count_result) {
                $row = $count_result->fetch_assoc();
                $count = $row['count'];
                
                if ($count > 0) {
                    $results['database'][] = [
                        'test' => "Table: $table",
                        'status' => 'success',
                        'message' => "Table exists with $count records",
                        'recommendation' => ''
                    ];
                } else {
                    $results['database'][] = [
                        'test' => "Table: $table",
                        'status' => 'warning',
                        'message' => "Table exists but has no records",
                        'recommendation' => "Run the Dashboard Data Manager to populate the $table table."
                    ];
                }
            }
        } else {
            $results['database'][] = [
                'test' => "Table: $table",
                'status' => 'error',
                'message' => "Table does not exist",
                'recommendation' => "Run the Final Dashboard Check to create the $table table."
            ];
        }
    }
    
    // Check database size
    $size_result = $conn->query("SELECT 
        SUM(data_length + index_length) / 1024 / 1024 AS size_mb 
        FROM information_schema.TABLES 
        WHERE table_schema = '$dbname'");
    
    if ($size_result) {
        $row = $size_result->fetch_assoc();
        $size_mb = round($row['size_mb'], 2);
        
        if ($size_mb > 100) {
            $results['database'][] = [
                'test' => 'Database Size',
                'status' => 'warning',
                'message' => "Database size is $size_mb MB",
                'recommendation' => 'Consider optimizing the database or removing unnecessary data.'
            ];
        } else {
            $results['database'][] = [
                'test' => 'Database Size',
                'status' => 'success',
                'message' => "Database size is $size_mb MB",
                'recommendation' => ''
            ];
        }
    }
}

// Check file permissions
$directories = [
    '../uploads' => 'Uploads Directory',
    '../backups' => 'Backups Directory',
    '../logs' => 'Logs Directory',
    '../temp' => 'Temp Directory'
];

foreach ($directories as $dir => $label) {
    if (file_exists($dir)) {
        if (is_writable($dir)) {
            $results['files'][] = [
                'test' => $label,
                'status' => 'success',
                'message' => "Directory exists and is writable",
                'recommendation' => ''
            ];
        } else {
            $results['files'][] = [
                'test' => $label,
                'status' => 'error',
                'message' => "Directory exists but is not writable",
                'recommendation' => "Change permissions on $dir to allow writing."
            ];
        }
    } else {
        $results['files'][] = [
            'test' => $label,
            'status' => 'warning',
            'message' => "Directory does not exist",
            'recommendation' => "Create the $dir directory with write permissions."
        ];
    }
}

// Check PHP configuration
$php_checks = [
    'version' => [
        'value' => phpversion(),
        'min' => '7.4.0',
        'recommendation' => 'Upgrade PHP to at least 7.4.0'
    ],
    'memory_limit' => [
        'value' => ini_get('memory_limit'),
        'min' => '128M',
        'recommendation' => 'Increase memory_limit to at least 128M'
    ],
    'max_execution_time' => [
        'value' => ini_get('max_execution_time'),
        'min' => 30,
        'recommendation' => 'Increase max_execution_time to at least 30 seconds'
    ],
    'file_uploads' => [
        'value' => ini_get('file_uploads'),
        'min' => 1,
        'recommendation' => 'Enable file_uploads in php.ini'
    ],
    'post_max_size' => [
        'value' => ini_get('post_max_size'),
        'min' => '8M',
        'recommendation' => 'Increase post_max_size to at least 8M'
    ],
    'upload_max_filesize' => [
        'value' => ini_get('upload_max_filesize'),
        'min' => '8M',
        'recommendation' => 'Increase upload_max_filesize to at least 8M'
    ]
];

foreach ($php_checks as $check => $config) {
    $value = $config['value'];
    $min = $config['min'];
    
    // Convert memory values to bytes for comparison
    if (in_array($check, ['memory_limit', 'post_max_size', 'upload_max_filesize'])) {
        $value_bytes = convertToBytes($value);
        $min_bytes = convertToBytes($min);
        
        if ($value_bytes >= $min_bytes) {
            $results['php'][] = [
                'test' => "PHP $check",
                'status' => 'success',
                'message' => "Current: $value (Minimum: $min)",
                'recommendation' => ''
            ];
        } else {
            $results['php'][] = [
                'test' => "PHP $check",
                'status' => 'warning',
                'message' => "Current: $value (Minimum: $min)",
                'recommendation' => $config['recommendation']
            ];
        }
    } 
    // Version comparison
    elseif ($check === 'version') {
        if (version_compare($value, $min, '>=')) {
            $results['php'][] = [
                'test' => "PHP $check",
                'status' => 'success',
                'message' => "Current: $value (Minimum: $min)",
                'recommendation' => ''
            ];
        } else {
            $results['php'][] = [
                'test' => "PHP $check",
                'status' => 'warning',
                'message' => "Current: $value (Minimum: $min)",
                'recommendation' => $config['recommendation']
            ];
        }
    }
    // Numeric comparison
    else {
        if ($value >= $min) {
            $results['php'][] = [
                'test' => "PHP $check",
                'status' => 'success',
                'message' => "Current: $value (Minimum: $min)",
                'recommendation' => ''
            ];
        } else {
            $results['php'][] = [
                'test' => "PHP $check",
                'status' => 'warning',
                'message' => "Current: $value (Minimum: $min)",
                'recommendation' => $config['recommendation']
            ];
        }
    }
}

// Check security
$security_checks = [
    'admin_directory' => [
        'test' => 'Admin Directory Protection',
        'path' => '../admin/.htaccess',
        'recommendation' => 'Create an .htaccess file in the admin directory to restrict access.'
    ],
    'config_file_permissions' => [
        'test' => 'Config File Permissions',
        'path' => '../includes/config/config.php',
        'recommendation' => 'Set config file permissions to 644 (readable by owner and group, not writable by others).'
    ],
    'error_reporting' => [
        'test' => 'Error Reporting',
        'value' => ini_get('display_errors'),
        'recommendation' => 'Disable display_errors in production environment.'
    ],
    'session_security' => [
        'test' => 'Session Security',
        'path' => '../includes/functions/session_manager.php',
        'recommendation' => 'Implement session security measures (regenerate ID, timeout, etc.).'
    ]
];

// Check admin directory protection
if (file_exists($security_checks['admin_directory']['path'])) {
    $results['security'][] = [
        'test' => $security_checks['admin_directory']['test'],
        'status' => 'success',
        'message' => 'Admin directory has .htaccess protection',
        'recommendation' => ''
    ];
} else {
    $results['security'][] = [
        'test' => $security_checks['admin_directory']['test'],
        'status' => 'warning',
        'message' => 'Admin directory does not have .htaccess protection',
        'recommendation' => $security_checks['admin_directory']['recommendation']
    ];
}

// Check config file permissions
if (file_exists($security_checks['config_file_permissions']['path'])) {
    $perms = substr(sprintf('%o', fileperms($security_checks['config_file_permissions']['path'])), -4);
    if ($perms == '0644' || $perms == '0640') {
        $results['security'][] = [
            'test' => $security_checks['config_file_permissions']['test'],
            'status' => 'success',
            'message' => 'Config file has secure permissions',
            'recommendation' => ''
        ];
    } else {
        $results['security'][] = [
            'test' => $security_checks['config_file_permissions']['test'],
            'status' => 'warning',
            'message' => "Config file has permissions: $perms",
            'recommendation' => $security_checks['config_file_permissions']['recommendation']
        ];
    }
} else {
    $results['security'][] = [
        'test' => $security_checks['config_file_permissions']['test'],
        'status' => 'error',
        'message' => 'Config file not found',
        'recommendation' => 'Create a secure configuration file.'
    ];
}

// Check error reporting
if ($security_checks['error_reporting']['value'] == '1') {
    $results['security'][] = [
        'test' => $security_checks['error_reporting']['test'],
        'status' => 'warning',
        'message' => 'Error display is enabled',
        'recommendation' => $security_checks['error_reporting']['recommendation']
    ];
} else {
    $results['security'][] = [
        'test' => $security_checks['error_reporting']['test'],
        'status' => 'success',
        'message' => 'Error display is disabled',
        'recommendation' => ''
    ];
}

// Check session security
if (file_exists($security_checks['session_security']['path'])) {
    $results['security'][] = [
        'test' => $security_checks['session_security']['test'],
        'status' => 'success',
        'message' => 'Session manager file exists',
        'recommendation' => ''
    ];
} else {
    $results['security'][] = [
        'test' => $security_checks['session_security']['test'],
        'status' => 'warning',
        'message' => 'Session manager file not found',
        'recommendation' => $security_checks['session_security']['recommendation']
    ];
}

// Check performance
$performance_checks = [
    'database_queries' => [
        'test' => 'Database Query Performance',
        'query' => "SELECT COUNT(*) as count FROM properties",
        'threshold' => 0.01 // 10ms
    ],
    'page_load' => [
        'test' => 'Page Load Time',
        'url' => '../index.php',
        'threshold' => 0.5 // 500ms
    ]
];

// Check database query performance
$query_start = microtime(true);
$conn->query($performance_checks['database_queries']['query']);
$query_time = microtime(true) - $query_start;

if ($query_time <= $performance_checks['database_queries']['threshold']) {
    $results['performance'][] = [
        'test' => $performance_checks['database_queries']['test'],
        'status' => 'success',
        'message' => sprintf("Query time: %.4f seconds", $query_time),
        'recommendation' => ''
    ];
} else {
    $results['performance'][] = [
        'test' => $performance_checks['database_queries']['test'],
        'status' => 'warning',
        'message' => sprintf("Query time: %.4f seconds", $query_time),
        'recommendation' => 'Consider optimizing database indexes and queries.'
    ];
}

// Check dashboard widgets
if ($conn->connect_error === null) {
    $widgets = [
        'Properties Widget' => "SELECT COUNT(*) as count FROM properties",
        'Customers Widget' => "SELECT COUNT(*) as count FROM customers",
        'Bookings Widget' => "SELECT COUNT(*) as count FROM bookings",
        'Leads Widget' => "SELECT COUNT(*) as count FROM leads",
        'Transactions Widget' => "SELECT COUNT(*) as count FROM transactions",
        'Visits Widget' => "SELECT COUNT(*) as count FROM property_visits",
        'Notifications Widget' => "SELECT COUNT(*) as count FROM notifications",
        'MLM Commission Widget' => "SELECT COUNT(*) as count FROM mlm_commission_ledger"
    ];
    
    foreach ($widgets as $widget => $query) {
        $result = $conn->query($query);
        if ($result) {
            $row = $result->fetch_assoc();
            $count = $row['count'];
            
            if ($count > 0) {
                $results['widgets'][] = [
                    'test' => $widget,
                    'status' => 'success',
                    'message' => "Widget has data ($count records)",
                    'recommendation' => ''
                ];
            } else {
                $results['widgets'][] = [
                    'test' => $widget,
                    'status' => 'warning',
                    'message' => "Widget has no data",
                    'recommendation' => "Run the Final Dashboard Check to populate data for this widget."
                ];
            }
        } else {
            $results['widgets'][] = [
                'test' => $widget,
                'status' => 'error',
                'message' => "Could not query widget data: " . $conn->error,
                'recommendation' => "Check the database structure for this widget."
            ];
        }
    }
}

// Close database connection
if ($conn->connect_error === null) {
    $conn->close();
}

// Calculate overall system health
$total_checks = 0;
$passed_checks = 0;
$warning_checks = 0;
$error_checks = 0;

foreach ($results as $category => $checks) {
    foreach ($checks as $check) {
        $total_checks++;
        if ($check['status'] === 'success') {
            $passed_checks++;
        } elseif ($check['status'] === 'warning') {
            $warning_checks++;
        } elseif ($check['status'] === 'error') {
            $error_checks++;
        }
    }
}

$health_percentage = ($total_checks > 0) ? round(($passed_checks / $total_checks) * 100) : 0;

// Determine overall health status
if ($health_percentage >= 90) {
    $health_status = 'excellent';
} elseif ($health_percentage >= 75) {
    $health_status = 'good';
} elseif ($health_percentage >= 50) {
    $health_status = 'fair';
} else {
    $health_status = 'poor';
}

// End time tracking
$end_time = microtime(true);
$execution_time = $end_time - $start_time;

// Helper function to convert memory values to bytes
function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int)$value;
    
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}

// Output HTML
?>
<!DOCTYPE html>
<html>
<head>
    <title>APS Dream Home - System Health Check</title>
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
        .health-summary {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }
        .health-gauge {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: conic-gradient(
                <?php 
                if ($health_status === 'excellent') {
                    echo '#2ecc71 0% ' . $health_percentage . '%, #ecf0f1 ' . $health_percentage . '% 100%';
                } elseif ($health_status === 'good') {
                    echo '#3498db 0% ' . $health_percentage . '%, #ecf0f1 ' . $health_percentage . '% 100%';
                } elseif ($health_status === 'fair') {
                    echo '#f39c12 0% ' . $health_percentage . '%, #ecf0f1 ' . $health_percentage . '% 100%';
                } else {
                    echo '#e74c3c 0% ' . $health_percentage . '%, #ecf0f1 ' . $health_percentage . '% 100%';
                }
                ?>
            );
            position: relative;
            margin-right: 30px;
        }
        .health-gauge::before {
            content: "<?php echo $health_percentage; ?>%";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }
        .health-gauge::after {
            content: "";
            position: absolute;
            top: 10px;
            left: 10px;
            right: 10px;
            bottom: 10px;
            border-radius: 50%;
            background: white;
        }
        .health-info {
            flex: 1;
        }
        .health-status {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .health-status.excellent { color: #2ecc71; }
        .health-status.good { color: #3498db; }
        .health-status.fair { color: #f39c12; }
        .health-status.poor { color: #e74c3c; }
        .health-stats {
            display: flex;
            gap: 20px;
            margin-top: 15px;
        }
        .stat {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-radius: 4px;
            text-align: center;
            flex: 1;
        }
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-label {
            font-size: 14px;
            color: #7f8c8d;
        }
        .category {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 30px;
        }
        .category h3 {
            margin-top: 0;
            color: #2c3e50;
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
        .success { color: #2ecc71; }
        .warning { color: #f39c12; }
        .error { color: #e74c3c; }
        .btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border-radius: 4px;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #2980b9;
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
            <h1>APS Dream Home - System Health Check</h1>
        </div>
    </header>
    
    <div class="container">
        <div class="health-summary">
            <div class="health-gauge"></div>
            <div class="health-info">
                <div class="health-status <?php echo $health_status; ?>">
                    System Health: <?php echo ucfirst($health_status); ?>
                </div>
                <p>
                    The overall health of your APS Dream Home system is <?php echo $health_status; ?>.
                    <?php
                    if ($health_status === 'excellent') {
                        echo 'Your system is running optimally with no significant issues.';
                    } elseif ($health_status === 'good') {
                        echo 'Your system is running well with only minor issues that should be addressed.';
                    } elseif ($health_status === 'fair') {
                        echo 'Your system has several issues that need attention to ensure optimal performance.';
                    } else {
                        echo 'Your system has critical issues that require immediate attention.';
                    }
                    ?>
                </p>
                <div class="health-stats">
                    <div class="stat">
                        <div class="stat-value"><?php echo $passed_checks; ?></div>
                        <div class="stat-label">Passed</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo $warning_checks; ?></div>
                        <div class="stat-label">Warnings</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo $error_checks; ?></div>
                        <div class="stat-label">Errors</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value"><?php echo sprintf("%.2f", $execution_time); ?>s</div>
                        <div class="stat-label">Execution Time</div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php foreach ($results as $category => $checks): ?>
            <div class="category">
                <h3><?php echo ucfirst($category); ?> Checks</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Test</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Recommendation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($checks as $check): ?>
                            <tr>
                                <td><?php echo $check['test']; ?></td>
                                <td class="<?php echo $check['status']; ?>">
                                    <?php 
                                    if ($check['status'] === 'success') {
                                        echo '✓ Pass';
                                    } elseif ($check['status'] === 'warning') {
                                        echo '⚠ Warning';
                                    } else {
                                        echo '✗ Error';
                                    }
                                    ?>
                                </td>
                                <td><?php echo $check['message']; ?></td>
                                <td><?php echo $check['recommendation']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
        
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn">Return to Database Management Hub</a>
        </div>
    </div>
    
    <footer>
        <div class="container">
            <p>APS Dream Home System Health Check &copy; <?php echo date('Y'); ?></p>
            <p>Generated on: <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </footer>
</body>
</html>
