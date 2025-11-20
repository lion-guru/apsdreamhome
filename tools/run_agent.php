<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../includes/config.php';
use App\Core\Agent\Agent;
$agent = new Agent();
$res = $agent->runDailyOps();
echo ($res['online'] ? 'online' : 'offline') . "\n";
echo $res['planned'] . "\n";

