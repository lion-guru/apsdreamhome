<?php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../app/Helpers/InputValidator.php';

use App\Helpers\InputValidator;

$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'phone' => '9876543210',
    'pan' => 'ABCDE1234F',
    'age' => '25'
];

$rules = [
    'name' => 'required|min:2',
    'email' => 'required|email',
    'phone' => 'required|phone',
    'pan' => 'required|pan',
    'age' => 'required|int|min:18|max:60'
];

$validator = InputValidator::make($data)->applyRules($rules);
echo 'Passes: ' . ($validator->passes() ? 'true' : 'false') . "\n";
echo 'Errors: ' . json_encode($validator->errors()) . "\n";
echo 'Validated: ' . json_encode($validator->validated()) . "\n";