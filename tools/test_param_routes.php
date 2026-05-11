<?php
// Find parameterized GET routes and test with real IDs from DB
$routes = file_get_contents('routes/web.php');

// Extract all parameterized GET routes
preg_match_all("/->get\(['\"]([^'\"]+\{[^}]+\}[^'\"]*)['\"]/", $routes, $m);
$paramRoutes = $m[1];

echo "=== Parameterized GET Routes Found: " . count($paramRoutes) . " ===" . PHP_EOL;

// Connect to DB for real IDs
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
} catch (Exception $e) {
    echo "DB Error: " . $e->getMessage() . PHP_EOL;
    exit;
}

// Get real IDs from common tables
$sampleIds = [];

// properties table
try { $sampleIds['property'] = $pdo->query('SELECT id FROM properties LIMIT 1')->fetchColumn() ?: 42; } catch (Exception $e) { $sampleIds['property'] = 1; }
// projects table
try { $sampleIds['project'] = $pdo->query('SELECT id FROM projects LIMIT 1')->fetchColumn() ?: 1; } catch (Exception $e) { $sampleIds['project'] = 1; }
// plots table
try { $sampleIds['plot'] = $pdo->query('SELECT id FROM plots LIMIT 1')->fetchColumn() ?: 1; } catch (Exception $e) { $sampleIds['plot'] = 1; }
// leads table
try { $sampleIds['lead'] = $pdo->query('SELECT id FROM leads LIMIT 1')->fetchColumn() ?: 1; } catch (Exception $e) { $sampleIds['lead'] = 1; }
// user_properties table
try { $sampleIds['user_property'] = $pdo->query('SELECT id FROM user_properties LIMIT 1')->fetchColumn() ?: 1; } catch (Exception $e) { $sampleIds['user_property'] = 1; }
// colonies table
try { $sampleIds['colony'] = $pdo->query('SELECT id FROM colonies LIMIT 1')->fetchColumn() ?: 1; } catch (Exception $e) { $sampleIds['colony'] = 1; }
// bookings table
try { $sampleIds['booking'] = $pdo->query('SELECT id FROM bookings LIMIT 1')->fetchColumn() ?: 1; } catch (Exception $e) { $sampleIds['booking'] = 1; }
// pages table
try { $sampleIds['page'] = $pdo->query('SELECT id FROM pages LIMIT 1')->fetchColumn() ?: 1; } catch (Exception $e) { $sampleIds['page'] = 1; }

echo "Sample IDs: " . json_encode($sampleIds) . PHP_EOL . PHP_EOL;

// Test each route
$ok = 0; $fail = 0; $failPaths = [];
foreach ($paramRoutes as $pattern) {
    // Replace {id}, {slug}, {property_id}, etc with appropriate real values
    $url = $pattern;
    $url = preg_replace_callback('/\{([^}]+)\}/', function($m) use ($sampleIds) {
        $key = $m[1];
        // Try to match key to sample IDs
        foreach ($sampleIds as $table => $id) {
            if (strpos($key, 'id') !== false || strpos($key, 'Id') !== false || strpos($key, 'ID') !== false) {
                return $id;
            }
        }
        return '1';
    }, $url);

    $fullUrl = 'http://localhost/apsdreamhome' . $url;
    $ctx = stream_context_create(['http' => ['timeout' => 4, 'follow_location' => false]]);
    $content = @file_get_contents($fullUrl, false, $ctx);
    $code = $http_response_header ? explode(' ', $http_response_header[0])[1] : '000';
    $size = $content !== false ? strlen($content) . 'b' : 'ERR';
    
    $status = ($code[0] === '2' || $code === '302' || $code === '301') ? 'OK' : 'FAIL';
    echo str_pad($status, 5) . " $code " . str_pad($size, 8) . " $url" . PHP_EOL;
    
    if ($status === 'OK') $ok++; else { $fail++; $failPaths[] = "$url ($code)"; }
}

echo PHP_EOL . "=== Results: OK=$ok FAIL=$fail ===" . PHP_EOL;
if ($failPaths) {
    echo "Failed:" . PHP_EOL;
    foreach ($failPaths as $p) echo "  $p" . PHP_EOL;
}
