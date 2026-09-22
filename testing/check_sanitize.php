<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../app/Helpers/InputValidator.php';

use App\Helpers\InputValidator;

echo "sanitize: " . InputValidator::sanitize('Hello <b>World</b>') . PHP_EOL;
echo "sanitize: " . InputValidator::sanitize('"Quoted"') . PHP_EOL;
echo "sanitize: " . InputValidator::sanitize('Test & Test') . PHP_EOL;
echo "sanitize: " . InputValidator::sanitize('<script>alert(1)</script>') . PHP_EOL;