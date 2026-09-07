<?php
$line = '$router->get("/admin/erp", "App\\Http\\Controllers\\Admin\\AdminController@erpOverview");';
echo "Line: $line\n";

$pattern = '/->(?:get|post|put|delete|patch)\s*\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([A-Za-z\\\\]+)@([a-zA-Z]+)/';
echo "Pattern: $pattern\n";

if (preg_match($pattern, $line, $m)) {
    echo "Route: {$m[1]}\n";
    echo "Class: {$m[2]}\n";
    echo "Method: {$m[3]}\n";
} else {
    echo "NO MATCH\n";
}