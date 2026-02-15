<?php
require_once(__DIR__ . '/../includes/functions/role_helper.php');
enforceRole(['admin','superadmin']);
$logfile = __DIR__ . '/../logs/automation.log';
if (!file_exists($logfile)) {
    echo json_encode(['success'=>true,'log'=>'No log file found.']);
    exit;
}
$lines = file($logfile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$last = array_slice($lines, -100);
$log = '';
foreach ($last as $line) {
    $row = explode(',', $line);
    $log .= htmlspecialchars($row[0]) . ',' . htmlspecialchars($row[1]) . ',' . htmlspecialchars($row[2]) . "\n";
}
echo json_encode(['success'=>true,'log'=>$log]);
