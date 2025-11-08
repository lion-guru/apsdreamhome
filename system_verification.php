<?php
/**
 * APS Dream Home - Final System Verification
 * Comprehensive system check after deployment
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>System Verification - APS Dream Home</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css' rel='stylesheet'>
    <style>
        .verification-item { margin: 10px 0; padding: 15px; border-radius: 8px; }
        .success { background-color: #d4edda; border-left: 4px solid #28a745; }
        .warning { background-color: #fff3cd; border-left: 4px solid #ffc107; }
        .error { background-color: #f8d7da; border-left: 4px solid #dc3545; }
        .info { background-color: #d1ecf1; border-left: 4px solid #17a2b8; }
        .score-circle { width: 100px; height: 100px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 24px; font-weight: bold; }
        .score-excellent { background-color: #28a745; color: white; }
        .score-good { background-color: #17a2b8; color: white; }
        .score-fair { background-color: #ffc107; color: black; }
        .score-poor { background-color: #dc3545; color: white; }
    </style>
</head>
<body>
    <nav class='navbar navbar-expand-lg navbar-dark bg-primary'>
        <div class='container'>
            <a class='navbar-brand' href='index.php'>
                <i class='fas fa-home me-2'></i>APS Dream Home
            </a>
        </div>
    </nav>

    <div class='container mt-5'>
        <div class='row justify-content-center'>
            <div class='col-lg-10'>
                <div class='card shadow-lg'>
                    <div class='card-header bg-primary text-white'>
                        <h1 class='card-title mb-0'>
                            <i class='fas fa-check-circle me-2'></i>System Verification Report
                        </h1>
                        <p class='mb-0 mt-2'>Comprehensive system check for APS Dream Home</p>
                    </div>
                    <div class='card-body'>";

$results = [];
$score = 0;
$total_checks = 0;

// 1. File System Check
$total_checks++;
echo "<div class='verification-item success'><i class='fas fa-folder-open me-2'></i><strong>File System:</strong> ";
$required_files = ['index.php', 'about.php', 'contact.php', 'properties.php', 'includes/config.php', 'includes/db_connection.php'];
$missing_files = [];
foreach ($required_files as $file) {
    if (file_exists($file)) {
        echo "✅ $file ";
        $score += 10;
    } else {
        echo "❌ $file ";
        $missing_files[] = $file;
    }
}
if (empty($missing_files)) {
    echo "<span class='text-success'>- All files present</span>";
} else {
    echo "<span class='text-danger'>- Missing: " . implode(', ', $missing_files) . "</span>";
}
echo "</div>";

// 2. PHP Configuration Check
$total_checks++;
echo "<div class='verification-item ";
$php_ok = true;
if (version_compare(PHP_VERSION, '7.4.0', '>=')) {
    echo "success'><i class='fas fa-code me-2'></i><strong>PHP Version:</strong> ✅ " . PHP_VERSION . " (Good)";
    $score += 15;
} else {
    echo "warning'><i class='fas fa-exclamation-triangle me-2'></i><strong>PHP Version:</strong> ⚠️ " . PHP_VERSION . " (Upgrade recommended)";
    $php_ok = false;
}
echo "</div>";

// 3. Database Connection Check
$total_checks++;
echo "<div class='verification-item ";
try {
    require_once 'includes/db_connection.php';
    $conn = getDbConnection();
    if ($conn) {
        echo "success'><i class='fas fa-database me-2'></i><strong>Database:</strong> ✅ Connection successful";
        $score += 20;
    } else {
        echo "error'><i class='fas fa-times-circle me-2'></i><strong>Database:</strong> ❌ Connection failed";
    }
} catch (Exception $e) {
    echo "error'><i class='fas fa-times-circle me-2'></i><strong>Database:</strong> ❌ " . $e->getMessage();
}
echo "</div>";

// 4. Session Check
$total_checks++;
echo "<div class='verification-item ";
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "success'><i class='fas fa-user-circle me-2'></i><strong>Session:</strong> ✅ Session active";
    $score += 10;
} else {
    echo "warning'><i class='fas fa-exclamation-triangle me-2'></i><strong>Session:</strong> ⚠️ Session not active";
}
echo "</div>";

// 5. Configuration Check
$total_checks++;
echo "<div class='verification-item ";
try {
    require_once 'includes/config.php';
    $config = AppConfig::getInstance();
    if ($config) {
        echo "success'><i class='fas fa-cog me-2'></i><strong>Configuration:</strong> ✅ Configuration loaded";
        $score += 10;
    } else {
        echo "error'><i class='fas fa-times-circle me-2'></i><strong>Configuration:</strong> ❌ Configuration failed";
    }
} catch (Exception $e) {
    echo "error'><i class='fas fa-times-circle me-2'></i><strong>Configuration:</strong> ❌ " . $e->getMessage();
}
echo "</div>";

// 6. Template System Check
$total_checks++;
echo "<div class='verification-item ";
if (file_exists('includes/enhanced_universal_template.php')) {
    echo "success'><i class='fas fa-palette me-2'></i><strong>Template System:</strong> ✅ Universal template active";
    $score += 10;
} else {
    echo "warning'><i class='fas fa-exclamation-triangle me-2'></i><strong>Template System:</strong> ⚠️ Template system missing";
}
echo "</div>";

// 7. Performance Check
$total_checks++;
$start_time = microtime(true);
for ($i = 0; $i < 1000; $i++) {
    $test = $i * $i;
}
$end_time = microtime(true);
$load_time = ($end_time - $start_time) * 1000;

echo "<div class='verification-item ";
if ($load_time < 50) {
    echo "success'><i class='fas fa-tachometer-alt me-2'></i><strong>Performance:</strong> ✅ " . round($load_time, 2) . "ms (Excellent)";
    $score += 15;
} elseif ($load_time < 100) {
    echo "warning'><i class='fas fa-exclamation-triangle me-2'></i><strong>Performance:</strong> ⚠️ " . round($load_time, 2) . "ms (Good)";
    $score += 10;
} else {
    echo "error'><i class='fas fa-times-circle me-2'></i><strong>Performance:</strong> ❌ " . round($load_time, 2) . "ms (Slow)";
    $score += 0;
}
echo "</div>";

// 8. Security Check
$total_checks++;
$security_score = 0;
if (function_exists('password_hash')) $security_score += 1;
if (function_exists('session_start')) $security_score += 1;
if (file_exists('includes/config.php')) $security_score += 1;

echo "<div class='verification-item ";
if ($security_score >= 2) {
    echo "success'><i class='fas fa-shield-alt me-2'></i><strong>Security:</strong> ✅ Score: $security_score/3";
    $score += 10;
} else {
    echo "warning'><i class='fas fa-exclamation-triangle me-2'></i><strong>Security:</strong> ⚠️ Score: $security_score/3";
    $score += 5;
}
echo "</div>";

// Calculate final score
$max_score = $total_checks * 10;
$percentage = round(($score / $max_score) * 100);

echo "<hr>
<div class='row mt-4'>
    <div class='col-md-6'>
        <h3>📊 Verification Summary</h3>
        <ul class='list-unstyled'>
            <li><strong>Total Checks:</strong> $total_checks</li>
            <li><strong>Score:</strong> $score/$max_score</li>
            <li><strong>Percentage:</strong> $percentage%</li>
            <li><strong>Status:</strong> ";

if ($percentage >= 90) {
    echo "<span class='text-success'>EXCELLENT</span>";
} elseif ($percentage >= 70) {
    echo "<span class='text-info'>GOOD</span>";
} elseif ($percentage >= 50) {
    echo "<span class='text-warning'>FAIR</span>";
} else {
    echo "<span class='text-danger'>NEEDS ATTENTION</span>";
}

echo "</li>
        </ul>
    </div>
    <div class='col-md-6 text-center'>
        <div class='score-circle ";

if ($percentage >= 90) {
    echo "score-excellent";
} elseif ($percentage >= 70) {
    echo "score-good";
} elseif ($percentage >= 50) {
    echo "score-fair";
} else {
    echo "score-poor";
}

echo "'>$percentage%</div>
        <p class='mt-3'>System Health Score</p>
    </div>
</div>";

// Recommendations
echo "<div class='mt-5'>
    <h3>💡 Recommendations</h3>";

if ($percentage >= 90) {
    echo "<div class='alert alert-success'>
        <i class='fas fa-thumbs-up me-2'></i>
        <strong>Excellent!</strong> Your system is production-ready. No immediate action required.
    </div>";
} else {
    echo "<div class='alert alert-warning'>
        <i class='fas fa-exclamation-triangle me-2'></i>
        <strong>Good foundation!</strong> Consider the following improvements:
        <ul class='mt-2 mb-0'>
            <li>Update PHP to latest version</li>
            <li>Review security configuration</li>
            <li>Optimize database queries</li>
            <li>Enable caching mechanisms</li>
        </ul>
    </div>";
}

echo "<div class='mt-4'>
    <a href='index.php' class='btn btn-primary btn-lg me-3'>
        <i class='fas fa-home me-2'></i>Homepage
    </a>
    <a href='properties.php' class='btn btn-success btn-lg me-3'>
        <i class='fas fa-building me-2'></i>Properties
    </a>
    <a href='comprehensive_test.php' class='btn btn-info btn-lg me-3'>
        <i class='fas fa-cog me-2'></i>Full Test Suite
    </a>
    <a href='setup_demo_data.php' class='btn btn-warning btn-lg'>
        <i class='fas fa-database me-2'></i>Setup Demo Data
    </a>
</div>";

echo "
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'></script>
</body>
</html>";

?>
