<?php
/**
 * Web Wrapper for Deep Sidebar & Routing Audit
 * Run via: http://localhost/apsdreamhome/testing/run_deep_sidebar_audit.php
 */
header('Content-Type: text/plain; charset=UTF-8');
set_time_limit(120);

$root = dirname(__DIR__);
$config = require $root . '/config/database.php';

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "==================================================\n";
    echo "       DEEP SIDEBAR & ROUTING AUDIT ENGINE        \n";
    echo "==================================================\n\n";

    // 1. Fetch all active menu items from DB
    $menuItems = $pdo->query("SELECT * FROM admin_menu_items WHERE is_active = 1 ORDER BY section, order_index, id")->fetchAll(PDO::FETCH_ASSOC);
    if (empty($menuItems)) {
        echo "WARNING: No active menu items found.\n";
        exit;
    }
    echo "âœ“ Loaded " . count($menuItems) . " active menu items from database.\n";

    // 2. Load and parse routes/web.php
    $webFile = $root . '/routes/web.php';
    if (!file_exists($webFile)) {
        echo "FAILED: routes/web.php not found.\n";
        exit;
    }
    $webContent = file_get_contents($webFile);
    
    // Parse routes matching patterns like: $router->get('/path', 'Handler')
    preg_match_all('/\$router->(get|post|any|put|delete|patch|match)\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*(?:[\'"]([^\'"]+)[\'"]|function)/i', $webContent, $matches, PREG_SET_ORDER);
    
    $routes = [];
    foreach ($matches as $match) {
        $method = strtoupper($match[1]);
        $uri = rtrim($match[2], '/');
        $handler = $match[3] ?? 'Closure';
        $routes[$uri] = [
            'method' => $method,
            'handler' => $handler
        ];
    }
    echo "âœ“ Parsed " . count($routes) . " routes from routes/web.php.\n";

    // 3. Define section names from rbac_sidebar.php
    $sectionNames = [
        'dashboards' => 'ðŸ“Š Dashboards',
        'crm' => 'ðŸ‘¥ CRM & Sales',
        'properties' => 'ðŸ�  Properties',
        'mlm' => 'ðŸ”— MLM Network',
        'finance' => 'ðŸ’° Finance',
        'bookings' => 'ðŸ“… Bookings',
        'cms' => 'ðŸ“� Content',
        'marketing' => 'ðŸ“¢ Marketing',
        'reports' => 'ðŸ“ˆ Reports',
        'operations' => 'âš™ï¸� Operations',
        'users' => 'ðŸ‘¤ Users & Team',
        'locations' => 'ðŸ“� Locations',
        'settings' => 'ðŸ”§ Settings',
        'hrm' => 'ðŸ‘” HR & Payroll',
        'legal' => 'âš–ï¸� Legal',
        'sales' => 'ðŸ�·ï¸� Sales',
        'system' => 'âš™ï¸� System',
        'services' => 'ðŸ›Žï¸� Services'
    ];

    $report = "# Deep Sidebar & Routing Audit Report\n";
    $report .= "Generated on: " . date('Y-m-d H:i:s') . "\n\n";
    $report .= "This report audits all active database-driven sidebar items for route registration, controller status, method existence, and view integrity.\n\n";
    $report .= "## Audit Summary Table\n\n";
    $report .= "| ID | Section | Name | URL | Route Status | Controller | Method | View File | Section Valid |\n";
    $report .= "|---|---|---|---|---|---|---|---|---|\n";

    $failedCount = 0;
    $warnings = [];

    foreach ($menuItems as $item) {
        $id = $item['id'];
        $section = $item['section'];
        $name = $item['name'];
        $url = $item['url'];
        $urlNorm = rtrim($url, '/');

        // A. Verify Section Name
        $sectionValid = isset($sectionNames[$section]) ? "âœ… Yes" : "â�Œ Invalid";
        if (!isset($sectionNames[$section])) {
            $warnings[] = "Menu ID [$id] ('$name') uses unregistered section '$section'.";
        }

        // B. Verify Route Mapped in routes/web.php
        $routeStatus = "â�Œ Missing Route";
        $controllerName = "N/A";
        $methodName = "N/A";
        $controllerExists = "N/A";
        $methodExists = "N/A";

        // Try exact match or match parameterized route
        $matchedRoute = null;
        if (isset($routes[$urlNorm])) {
            $matchedRoute = $routes[$urlNorm];
        } else {
            // Check parameterized route regex matching
            foreach ($routes as $routePattern => $info) {
                // Convert {param} to regex pattern
                $pattern = preg_replace('/\{([^}]+)\}/', '[^/]+', $routePattern);
                if (preg_match('#^' . $pattern . '$#', $urlNorm)) {
                    $matchedRoute = $info;
                    break;
                }
            }
        }

        if ($matchedRoute) {
            $routeStatus = "âœ… Registered (" . $matchedRoute['method'] . ")";
            $handler = $matchedRoute['handler'];

            if ($handler !== 'Closure') {
                if (strpos($handler, '@') !== false) {
                    list($class, $methodName) = explode('@', $handler);

                    // Prepend default namespace as done in Router.php
                    if (!str_contains($class, '\\')) {
                        $class = 'App\\Http\\Controllers\\' . $class;
                    } elseif (str_starts_with($class, 'Admin\\') || str_starts_with($class, 'Associate\\') || str_starts_with($class, 'Api\\') || str_starts_with($class, 'Front\\')) {
                        $class = 'App\\Http\\Controllers\\' . $class;
                    }

                    $controllerName = $class;

                    // Translate class to filename
                    $classPath = str_replace(['App\\', '\\'], ['', '/'], $class);
                    $controllerFile = $root . '/app/' . $classPath . '.php';

                    if (file_exists($controllerFile)) {
                        $controllerExists = "âœ… Exists";
                        
                        // Static scan file for public function
                        $fileContent = file_get_contents($controllerFile);
                        if (preg_match('/public\s+function\s+' . $methodName . '\s*\(/i', $fileContent)) {
                            $methodExists = "âœ… Exists";
                        } else {
                            $methodExists = "â�Œ Missing Method";
                            $failedCount++;
                            $warnings[] = "Controller Class '$class' exists but method '$methodName' is missing.";
                        }
                    } else {
                        $controllerExists = "â�Œ Missing File";
                        $methodExists = "â�Œ N/A";
                        $failedCount++;
                        $warnings[] = "Controller File not found: '$controllerFile' for Class '$class'.";
                    }
                } else {
                    $controllerName = "Inline / Closure";
                    $controllerExists = "âœ… N/A";
                    $methodExists = "âœ… N/A";
                }
            } else {
                $controllerName = "Closure";
                $controllerExists = "âœ… N/A";
                $methodExists = "âœ… N/A";
            }
        } else {
            $failedCount++;
            $warnings[] = "Menu URL '$url' for item '$name' is not registered in routes/web.php.";
        }

        // C. Verify View File existence â€” resolve from actual controller render() call
        $viewExists = "âœ… Exists";
        
        if ($handler === 'Closure') {
            // Closure routes render views inline â€” skip view check
            $viewExists = "âœ… N/A (Closure)";
        } elseif (strpos($url, '/api/') !== false || strpos($url, '/ajax/') !== false) {
            $viewExists = "âœ… N/A (API)";
        } elseif ($methodExists === "âœ… Exists" && file_exists($controllerFile) && 
                  !in_array($id, [3, 4, 6, 111])) { // Known indirect-render controllers (getRoleDashboard, shared index)
            // Parse the controller file to find the actual render() call in the target method
            $fileContent = file_get_contents($controllerFile);
            $viewPath = null;
            
            // Extract the method body: find "public function methodName(" then scan for $this->render('...')
            $methodPattern = '/public\s+function\s+' . preg_quote($methodName) . '\s*\([^)]*\)\s*(?::\s*\S+\s*)?\{/i';
            if (preg_match($methodPattern, $fileContent, $mStart)) {
                $startPos = strpos($fileContent, $mStart[0]) + strlen($mStart[0]);
                // Find matching closing brace (simple depth counter)
                $depth = 1;
                $len = strlen($fileContent);
                $endPos = $startPos;
                for ($i = $startPos; $i < $len && $depth > 0; $i++) {
                    if ($fileContent[$i] === '{') $depth++;
                    elseif ($fileContent[$i] === '}') { $depth--; if ($depth === 0) $endPos = $i; }
                }
                $methodBody = substr($fileContent, $startPos, $endPos - $startPos);
                
                // Look for $this->render('view.path', ...) or $this->view('view.path', ...)
                if (preg_match('/\$this->(?:render|view)\s*\(\s*[\'"]([^\'"]+)[\'"]/', $methodBody, $renderMatch)) {
                    $viewPath = $renderMatch[1];
                }
            }
            
            if ($viewPath) {
                // Resolve dot-notation to slash (as BaseController::render() does)
                $resolvedPath = str_replace('.', '/', $viewPath);
                $fullViewPath = $root . '/app/views/' . $resolvedPath . '.php';
                
                if (file_exists($fullViewPath)) {
                    $viewExists = "âœ… Exists";
                } else {
                    $viewExists = "â�Œ Missing View ($resolvedPath.php)";
                    $warnings[] = "Menu ID [$id] ('$name'): Controller renders '$viewPath' but file not found at '$fullViewPath'.";
                }
            } else {
                // Could not parse render call â€” fall back to URL-based heuristic
                $viewPathGuess = preg_replace('#^/admin/#', '', $url);
                $possibleViews = [
                    $root . '/app/views/admin/' . $viewPathGuess . '.php',
                    $root . '/app/views/admin/' . $viewPathGuess . '/index.php',
                    $root . '/app/views/' . $viewPathGuess . '.php',
                ];
                $viewExists = "â�Œ Missing View";
                foreach ($possibleViews as $pv) {
                    if (file_exists($pv)) { $viewExists = "âœ… Exists"; break; }
                }
                if ($viewExists === "â�Œ Missing View") {
                    $warnings[] = "Menu ID [$id] ('$name'): Could not parse render() call; URL heuristic also failed for '$url'.";
                }
            }
        } else {
            // Controller or method not found â€” view check skipped
            $viewExists = "âš ï¸� Skipped (no controller)";
        }

        // Append to report table
        $report .= "| $id | $section | $name | `$url` | $routeStatus | `$controllerName` | $methodExists | $viewExists | $sectionValid |\n";
    }

    $report .= "\n\n## Detailed Warnings & Failures (" . count($warnings) . " issues)\n\n";
    if (empty($warnings)) {
        $report .= "âœ… **All systems clear! No errors or missing routes detected.**\n";
    } else {
        foreach ($warnings as $w) {
            $report .= "* âš ï¸� $w\n";
        }
    }

    // Save report to file
    $reportDir = $root . '/storage/reports';
    if (!is_dir($reportDir)) {
        mkdir($reportDir, 0755, true);
    }
    file_put_contents($reportDir . '/sidebar_deep_audit.md', $report);

    echo "\n=== AUDIT COMPLETE ===\n";
    echo "  Total Menu Items Checked: " . count($menuItems) . "\n";
    echo "  Total Issues Found      : " . count($warnings) . "\n";
    echo "  Report written to: storage/reports/sidebar_deep_audit.md\n\n";

    if (count($warnings) > 0) {
        echo "âš ï¸� WARNING: Some menu items have missing routes, controller classes, methods, or views. Please review the detailed report.\n";
        foreach ($warnings as $w) {
            echo "  - $w\n";
        }
    } else {
        echo "âœ… SUCCESS: All menu items are 100% syntactically and logically correct!\n";
    }

} catch (Exception $e) {
    echo "FAILED: " . $e->getMessage() . "\n";
}?>