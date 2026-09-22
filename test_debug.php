<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'config/bootstrap.php';
require 'app/Helpers/InputValidator.php';

$v = InputValidator::make(['name' => 'John']);
echo 'Test 1: ' . ($v instanceof InputValidator ? 'PASS' : 'FAIL') . PHP_EOL;
$_POST['test'] = 'value';
$v2 = InputValidator::make();
echo 'Test 2: ' . ($v2 instanceof InputValidator ? 'PASS' : 'FAIL') . PHP_EOL;
echo 'Done' . PHP_EOL;