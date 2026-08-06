<?php
$output = shell_exec('php -l ' . escapeshellarg(__DIR__ . '/../app/Http/Controllers/Admin/SalaryController.php') . ' 2>&1');
echo "<pre>$output</pre>";
