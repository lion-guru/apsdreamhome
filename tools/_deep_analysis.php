<?php
// Deep Project & Database Analysis Script (Robust version)
// Analyzes database tables, models, controllers, and views to identify gaps, anomalies, and pending work.

$dbHost = '127.0.0.1';
$dbPort = '3307';
$dbUser = 'root';
$dbPass = '';
$dbName = 'apsdreamhome';

try {
    $db = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Database connection failed: " . $e->getMessage() . "\n";
    exit;
}

echo "============================================================\n";
echo "APS DREAM HOME - DEEP SYSTEM ANALYSIS\n";
echo "============================================================\n";

// 1. Analyze Database Tables & Views
$tables = [];
$emptyTables = [];
$activeTables = [];
$invalidViews = [];

$res = $db->query("SHOW TABLES");
foreach ($res as $row) {
    $table = $row[0];
    $tables[] = $table;
    try {
        $count = $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        if ($count == 0) {
            $emptyTables[] = $table;
        } else {
            $activeTables[$table] = $count;
        }
    } catch (PDOException $e) {
        $invalidViews[$table] = $e->getMessage();
    }
}

echo "1. DATABASE STATISTICS:\n";
echo "   Total Tables/Views: " . count($tables) . "\n";
echo "   Active Tables (with data): " . count($activeTables) . "\n";
echo "   Empty Tables: " . count($emptyTables) . "\n";
echo "   Invalid Views/Tables (CORRUPTED): " . count($invalidViews) . "\n";

if (count($invalidViews) > 0) {
    echo "\n   CRITICAL: Invalid/Corrupted Views found:\n";
    foreach ($invalidViews as $v => $err) {
        echo "     - $v: " . substr($err, 0, 100) . "...\n";
    }
}

arsort($activeTables);
echo "\n   Top 15 Largest Tables:\n";
foreach (array_slice($activeTables, 0, 15) as $t => $c) {
    echo "     - $t: $c rows\n";
}

// 2. Identify Core Business Modules with Empty Tables
$coreEmpty = [];
$coreKeywords = ['payroll', 'invoice', 'salary', 'commission', 'payout', 'attendance', 'leave', 'document', 'workflow', 'vapi', 'twilio', 'call', 'campaign', 'lead_score'];
foreach ($emptyTables as $t) {
    foreach ($coreKeywords as $kw) {
        if (strpos($t, $kw) !== false) {
            $coreEmpty[] = $t;
            break;
        }
    }
}
echo "\n2. EMPTY CORE BUSINESS TABLES (Need seeding or integration):\n";
foreach (array_slice($coreEmpty, 0, 20) as $t) {
    echo "     - $t\n";
}
if (count($coreEmpty) > 20) {
    echo "     ... and " . (count($coreEmpty) - 20) . " more empty core tables\n";
}

// 3. Scan for Missing View Files for routed controller methods
echo "\n3. ANALYSIS OF CONTROLLERS AND ROUTED VIEWS:\n";
$routesFile = 'C:\xampp\htdocs\apsdreamhome\routes\web.php';
$missingViews = [];
if (file_exists($routesFile)) {
    $content = file_get_contents($routesFile);
    preg_match_all('/\$router->get\(\'([^\']+)\'\s*,\s*\'([^\'\s]+)\'\)/', $content, $matches);
    $viewsChecked = 0;
    foreach ($matches[2] as $target) {
        if (strpos($target, '@') !== false) {
            list($controller, $method) = explode('@', $target);
            // Translate namespace
            $controllerFile = str_replace('\\', '/', $controller) . '.php';
            // Clean up App/Http/Controllers prefix if it exists in namespace
            if (strpos($controllerFile, 'App/Http/Controllers/') === 0) {
                $controllerFile = substr($controllerFile, 21);
            }
            // Check paths
            $paths = [
                "C:/xampp/htdocs/apsdreamhome/app/Http/Controllers/$controllerFile",
                "C:/xampp/htdocs/apsdreamhome/app/Http/Controllers/Admin/$controllerFile",
            ];
            $foundController = false;
            foreach ($paths as $path) {
                if (file_exists($path)) {
                    $foundController = $path;
                    break;
                }
            }
            if ($foundController) {
                // Read controller to see which view it renders
                $cContent = file_get_contents($foundController);
                if (preg_match('/function\s+' . $method . '\s*\(.*?\)\s*\{(.*?)\}/s', $cContent, $m)) {
                    $methodBody = $m[1];
                    if (preg_match_all('/render\(\'([^\'\$]+)\'/', $methodBody, $vMatches)) {
                        foreach ($vMatches[1] as $viewName) {
                            $viewFile = "C:/xampp/htdocs/apsdreamhome/app/views/$viewName.php";
                            $viewsChecked++;
                            if (!file_exists($viewFile)) {
                                $missingViews[$viewName] = [
                                    'controller' => $controller,
                                    'method' => $method,
                                    'view' => $viewName,
                                    'path' => $viewFile
                                ];
                            }
                        }
                    }
                }
            }
        }
    }
    echo "   Routed Views Checked: $viewsChecked\n";
    echo "   Missing Views Found: " . count($missingViews) . "\n";
    foreach (array_slice($missingViews, 0, 15) as $v => $info) {
        echo "     - View '{$info['view']}' missing (called by {$info['controller']}@{$info['method']})\n";
    }
}

// 4. Scan for broken/unimplemented Voice AI Call flow
echo "\n4. VOICE AI ASSISTANT SYSTEM INTEGRATION:\n";
$aiManagerFile = 'C:/xampp/htdocs/apsdreamhome/app/Services/AI/AIManager.php';
if (file_exists($aiManagerFile)) {
    $aiContent = file_get_contents($aiManagerFile);
    $hasTwilio = (strpos($aiContent, 'Twilio') !== false || strpos($aiContent, 'twilio') !== false) ? 'YES' : 'NO';
    $hasVapi = (strpos($aiContent, 'Vapi') !== false || strpos($aiContent, 'vapi') !== false) ? 'YES' : 'NO';
    echo "   AI Calling Telephony Integration:\n";
    echo "     - Twilio support: $hasTwilio\n";
    echo "     - Vapi support: $hasVapi\n";
    if ($hasTwilio === 'NO' && $hasVapi === 'NO') {
        echo "     - STATUS: Telephony integration is STUBBED (requires actual Vapi/Twilio API client implementation)\n";
    }
} else {
    echo "   Voice AI system files not found.\n";
}

// 5. Foreign Key and Data Integrity Gaps
echo "\n5. DATA INTEGRITY & RELATIONSHIP GAPS:\n";
$fks = $db->query("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                   FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                   WHERE REFERENCED_TABLE_SCHEMA = '$dbName'")->fetchAll(PDO::FETCH_ASSOC);
echo "   Total Active Foreign Keys: " . count($fks) . "\n";
if (count($fks) < 50) {
    echo "   - STATUS: Database is running mostly without Foreign Key constraints (requires relationship restoration to prevent orphaned records)\n";
}

echo "\n=== ANALYSIS COMPLETE ===\n";
