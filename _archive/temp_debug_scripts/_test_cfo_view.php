<?php
require_once 'C:\xampp\htdocs\apsdreamhome\app\Core\Database.php';
\App\Core\Database\Database::getInstance();
$data = [
    'top_performers' => [
        'associate' => ['name' => 'Test Associate', 'level' => 'Gold', 'metric' => '500000'],
        'agent' => ['name' => 'Test Agent', 'level' => 'Pro', 'metric' => '2000000'],
        'employee' => ['name' => 'Test Employee', 'level' => 'Senior', 'metric' => '300']
    ]
];
extract($data);
include 'C:\xampp\htdocs\apsdreamhome\app\views\admin\dashboards\cfo.php';
echo 'Done';?>