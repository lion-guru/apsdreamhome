<?php

/**
 * Legacy Proxy for test_autoloader.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Services\Legacy\test_autoloader as Legacytest_autoloader;

class test_autoloader extends Legacytest_autoloader {}

