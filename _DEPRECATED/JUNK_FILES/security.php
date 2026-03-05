<?php

return array (
  'production_mode' => true,
  'security_headers' => 
  array (
    'X-Content-Type-Options' => 'nosniff',
    'X-Frame-Options' => 'DENY',
    'X-XSS-Protection' => '1; mode=block',
    'Referrer-Policy' => 'strict-origin-when-cross-origin',
  ),
  'file_upload_limits' => 
  array (
    'max_size' => '10MB',
    'allowed_types' => 
    array (
      0 => 'jpg',
      1 => 'jpeg',
      2 => 'png',
      3 => 'gif',
      4 => 'pdf',
      5 => 'doc',
      6 => 'docx',
    ),
    'upload_path' => 'uploads/',
  ),
  'session_config' => 
  array (
    'timeout' => 3600,
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict',
  ),
  'rate_limiting' => 
  array (
    'enabled' => true,
    'requests_per_minute' => 60,
    'burst_limit' => 10,
  ),
);
