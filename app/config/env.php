<?php

// TODO: Add proper error handling with try-catch blocks


function loadEnv() {
    $envFile = __DIR__ . '/../../.env';
    if (!file_exists($envFile)) {
        throw new Exception('.env file not found');
    }

    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '#') === 0 || empty(trim($line))) {
            continue;
        }

        list($key, $value) = explode('=', $line, 2);
        
        // Remove quotes if present
        $value = trim($value, '"\'');
        
        putenv(sprintf('%s=%s', $key, $value));
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

loadEnv();