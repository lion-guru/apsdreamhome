<?php
$output = shell_exec('node -c ' . escapeshellarg(__DIR__ . '/../assets/js/frontend-enhancements.js') . ' 2>&1');
echo "Node Output:\n$output\n";
