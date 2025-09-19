<?php
/**
 * Database Update Script for APS Dream Homes
 * 
 * This script applies the latest database schema updates for the properties module.
 * It should be run after deploying new code that requires database changes.
 */

// Set error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Prevent direct access to this file
define('SECURE_ACCESS', true);

// Include configuration
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Start secure session
start_secure_session('aps_dream_home');

// Only allow admins to run this script
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die("<h1>Access Denied</h1><p>You must be logged in as an administrator to run this script.</p>");
}

// Set headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>APS Dream Homes - Database Update</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            padding: 2rem 0;
            background-color: #f8f9fa;
        }
        .update-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
        .log-entry {
            font-family: monospace;
            margin: 0.5rem 0;
            padding: 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .info {
            background-color: #e2f3fd;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        .warning {
            background-color: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .progress {
            height: 1.5rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="update-container">
            <div class="text-center mb-4">
                <h1><i class="bi bi-database-gear me-2"></i> APS Dream Homes</h1>
                <h2>Database Update</h2>
                <p class="text-muted">Applying database schema updates for Properties Module</p>
            </div>

            <div class="progress mb-4">
                <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
            </div>

            <div id="logOutput" class="mb-4">
                <!-- Log entries will be added here -->
            </div>

            <div class="text-end">
                <a href="/admin" class="btn btn-primary" id="finishButton" style="display: none;">
                    <i class="bi bi-check-circle me-1"></i> Finish
                </a>
            </div>
        </div>
    </div>

    <script>
        // Function to update the log
        function updateLog(message, type = 'info') {
            const logOutput = document.getElementById('logOutput');
            const logEntry = document.createElement('div');
            logEntry.className = `log-entry ${type}`;
            logEntry.innerHTML = `<i class="bi bi-${getIcon(type)}"></i> ${message}`;
            logOutput.appendChild(logEntry);
            logEntry.scrollIntoView({ behavior: 'smooth' });
        }

        // Function to get icon based on log type
        function getIcon(type) {
            const icons = {
                'success': 'check-circle',
                'error': 'exclamation-circle',
                'warning': 'exclamation-triangle',
                'info': 'info-circle'
            };
            return icons[type] || 'info-circle';
        }

        // Function to update progress bar
        function updateProgress(percent) {
            const progressBar = document.getElementById('progressBar');
            progressBar.style.width = `${percent}%`;
            progressBar.setAttribute('aria-valuenow', percent);
            
            if (percent >= 100) {
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-success');
                document.getElementById('finishButton').style.display = 'inline-block';
            }
        }

        // Start the update process when the page loads
        document.addEventListener('DOMContentLoaded', async () => {
            updateLog('Starting database update process...', 'info');
            updateProgress(10);
            
            try {
                // Execute the update via AJAX
                const response = await fetch('update_database_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=update_database'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    updateLog('Database update completed successfully!', 'success');
                    updateProgress(100);
                } else {
                    updateLog(`Error: ${result.message}`, 'error');
                    updateProgress(50);
                }
                
                // Display any log messages
                if (result.logs && result.logs.length > 0) {
                    result.logs.forEach(log => {
                        updateLog(log.message, log.type);
                    });
                }
                
            } catch (error) {
                updateLog(`Fatal error: ${error.message}`, 'error');
                updateProgress(0);
            }
        });
    </script>
</body>
</html>
