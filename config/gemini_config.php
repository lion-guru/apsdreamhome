<?php

// Gemini AI Configuration - Secure from .env
return [
    'api_key' => $_ENV['GEMINI_API_KEY'] ?? '',
    'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent',
    'project_id' => $_ENV['GEMINI_PROJECT_ID'] ?? '',
    'model' => 'gemini-1.5-flash',
    'temperature' => 0.7,
    'max_tokens' => 1024
];
