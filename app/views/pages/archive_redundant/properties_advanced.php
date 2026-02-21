<?php
/**
 * Legacy Properties Advanced Redirector
 * Redirects legacy property listing requests to the new MVC routes
 */

require_once __DIR__ . '/init.php';

// Construct the new URL with all original query parameters
$queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';
$newUrl = BASE_URL . 'properties' . $queryString;

// Perform the 301 Permanent Redirect
header("HTTP/1.1 301 Moved Permanently");
header("Location: " . $newUrl);
exit();
