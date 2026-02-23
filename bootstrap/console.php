<?php

use Illuminate\Foundation\Console\Kernel;

$app = require_once __DIR__.'/app.php';

$kernel = $app->make(Kernel::class);

$kernel->bootstrap();

return $kernel;
