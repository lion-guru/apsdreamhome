<?php

require_once __DIR__ . '/vendor/autoload.php';

use PHPUnit\TextUI\TestRunner;
use PHPUnit\TextUI\Command;

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Run the tests
$command = new Command();
$command->run(['phpunit', 'tests/unit/Http/ResponseTest.php']);
