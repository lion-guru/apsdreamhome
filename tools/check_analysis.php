<?php
// Agent analysis tool - checks PHP syntax, redirects, routes, DB health
$checks = [];
$pass = 0;
$fail = 0;

$controllers = glob('app/Http/Controllers/**/*.php');
$errors = [];
foreach ($controllers as $f) {
    $out = shell_exec("php -l \"$f\" 2>&1");
    if (strpos($out, 'No syntax errors') === false) {
        $errors[] = $f;
    }
}
$checks['php_syntax'] = count($errors) === 0 ? 'PASS' : 'FAIL';
$errors ? $fail++ : $pass++;

$mwFiles = [
    'app/Http/Middleware/AuthMiddleware.php',
    'app/Core/Middleware/AuthMiddleware.php',
    'app/Services/AuthMiddleware.php'
];
$broken = [];
foreach ($mwFiles as $f) {
    if (file_exists($f) && preg_match("/header\('Location: [^']*login\\.php'\)/", file_get_contents($f))) {
        $broken[] = $f;
    }
}
$checks['redirect_php_ext'] = empty($broken) ? 'PASS' : 'FAIL';
empty($broken) ? $pass++ : $fail++;

$routes = file_exists('routes/web.php') && strpos(file_get_contents('routes/web.php'), '<?php') !== false;
$checks['routes_loaded'] = $routes ? 'PASS' : 'FAIL';
$routes ? $pass++ : $fail++;

try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome', 'root', '');
    $tbls = $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema="apsdreamhome"')->fetchColumn();
    $checks["db_tables($tbls)"] = 'PASS';
    $pass++;
} catch (Exception $e) {
    $checks['db_connection'] = 'FAIL';
    $fail++;
}

echo "=== Analysis Summary ===\n";
foreach ($checks as $k => $v) {
    echo str_pad($k, 30) . " [$v]\n";
}
echo "Passed: $pass | Failed: $fail\n";
exit($fail > 0 ? 1 : 0);
