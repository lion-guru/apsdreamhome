<?php
echo "Document Root: " . $_SERVER['DOCUMENT_ROOT'] . "\n";
echo "Current File: " . __FILE__ . "\n";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";
phpinfo();
?>