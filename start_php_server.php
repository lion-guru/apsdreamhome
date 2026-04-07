<?php
/**
 * Built-in PHP Server Startup
 * Use this if XAMPP is not working
 */

$port = 8000;
$host = 'localhost';
$projectRoot = __DIR__;

echo "Starting PHP Built-in Server...\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "Project: $projectRoot\n";
echo "URL: http://$host:$port\n\n";

echo "Starting server...\n";

// Start the server
$command = "php -S $host:$port -t $projectRoot";
echo "Command: $command\n\n";

echo "Server should be running at: http://$host:$port\n";
echo "Press Ctrl+C to stop the server\n\n";

// Execute the command
passthru($command);
