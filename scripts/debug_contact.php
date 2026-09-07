#!/usr/bin/env php
<?php
// Temporarily enable error display to see what's breaking
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Simulate the request
$_SERVER['REQUEST_URI'] = '/contact';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';

require_once __DIR__ . '/../public/index.php';
