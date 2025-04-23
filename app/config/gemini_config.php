<?php

return [
    'api_key' => getenv('GEMINI_API_KEY'),
    'api_url' => 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent',
    'model' => 'gemini-2.0-flash'
];