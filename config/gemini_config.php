<?php

// Function to parse a .env file and set environment variables
function loadEnv($filePath) {
    if (file_exists($filePath)) {
        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv("$name=$value");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$projectRoot = __DIR__ . '/..';
loadEnv($projectRoot . '/.env');

// Gemini AI Configuration - Updated from database
return [
    'api_key' => $_ENV['GEMINI_API_KEY'] ?? '',
    'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent',
    'project_id' => '',
    'model' => 'gemini-1.5-flash',
    'temperature' => 0.7,
    'max_tokens' => 8192,
    'enabled' => false
];

