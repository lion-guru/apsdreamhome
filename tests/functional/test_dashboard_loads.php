<?php
// Simple dashboard loader test for APS Dream Homes
// Checks if each dashboard page loads without fatal errors (HTTP 200)
// Usage: Run from browser or CLI (php test_dashboard_loads.php)

$pages = [
    'index.php',
    'user_dashboard.php',
    'associate_dashboard.php',
    'customer_dashboard.php',
    'agent_dashboard.php',
    'builder_dashboard.php',
    'dash.php',
    'admin/dashboard.php',
    'admin/super-admin-dashboard.php',
    'admin/analytics_dashboard.php',
    'admin/ai_dashboard.php',
];

$base = (php_sapi_name() === 'cli') ? 'http://localhost/march2025apssite/' : '';
$results = [];

foreach ($pages as $page) {
    $url = $base . $page;
    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: APS-HealthCheck\r\n"
        ]
    ];
    $context = stream_context_create($opts);
    $content = @file_get_contents($url, false, $context);
    $success = ($content !== false && strpos($http_response_header[0], '200') !== false);
    $results[$page] = $success ? 'OK' : 'ERROR';
}

// Output results
if (php_sapi_name() === 'cli') {
    foreach ($results as $page => $status) {
        echo "$page\t$status\n";
    }
} else {
    echo '<h2>Dashboard Load Test Results</h2><table border="1" cellpadding="8"><tr><th>Page</th><th>Status</th></tr>';
    foreach ($results as $page => $status) {
        $color = ($status === 'OK') ? 'green' : 'red';
        echo "<tr><td>$page</td><td style='color:$color'>$status</td></tr>";
    }
    echo '</table>';
}
?>
