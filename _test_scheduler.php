<?php
require 'config/bootstrap.php';
$s = new \App\Core\Agentic\ContinuousScheduler();
echo get_class($s);
