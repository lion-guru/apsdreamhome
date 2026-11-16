<?php
/**
 * Root Error View proxy to errors/generic.php
 */
$error_code = $error_code ?? 500;
$error_title = $error_title ?? 'Something Went Wrong';
$error_message = $message ?? ($error_message ?? 'An error occurred while processing your request.');

require __DIR__ . '/errors/generic.php';
