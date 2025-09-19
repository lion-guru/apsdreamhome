<?php
/**
 * Sample Property Data Generator
 * 
 * This script populates the database with sample property data for testing.
 * It should only be run in a development environment.
 */

// Prevent direct access
defined('SECURE_ACCESS') or die('Direct access not permitted');

// Include configuration and functions
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';

// Only allow this script to run in development environment
if (!defined('ENVIRONMENT') || ENVIRONMENT !== 'development') {
    die('This script can only be run in a development environment.');
}

// Start secure session
start_secure_session('aps_dream_home');

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    die('Access denied. Only administrators can run this script.');
}

// Set headers
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sample Property Data Generator</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { padding: 2rem 0; background-color: #f8f9fa; }
        .container { max-width: 1000px; }
        .log { 
            max-height: 500px; 
            overflow-y: auto; 
            background: #f8f9fa; 
            padding: 1rem; 
            border-radius: 0.25rem; 
            margin: 1rem 0; 
            border: 1px solid #dee2e6; 
        }
        .log-entry { 
            margin: 0.5rem 0; 
            padding: 0.5rem; 
            border-radius: 0.25rem;
            font-family: monospace;
            font-size: 0.875rem;
        }
        .success { background-color: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .error { background-color: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .info { background-color: #e2f3fd; color: #0c5460; border-left: 4px solid #17a2b8; }
        .warning { background-color: #fff3cd; color: #856404; border-left: 4px solid #ffc107; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="h4 mb-0">Sample Property Data Generator</h1>
            </div>
            <div class="card-body">
                <p class="text-muted">This tool will populate the database with sample property data for testing purposes.</p>
                
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <strong>Warning:</strong> This will delete all existing property data and cannot be undone!
                </div>
                
                <form id="generateForm" class="mb-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="numProperties" class="form-label">Number of Properties</label>
                            <input type="number" class="form-control" id="numProperties" value="20" min="1" max="1000">
                        </div>
                        <div class="col-md-4">
                            <label for="agentId" class="form-label">Agent ID</label>
                            <input type="number" class="form-control" id="agentId" value="1" min="1">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" id="generateBtn">
                                <i class="bi bi-magic me-2"></i> Generate Data
                            </button>
                        </div>
                    </div>
                </form>
                
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="truncateFirst" checked>
                        <label class="form-check-label" for="truncateFirst">Clear existing data first</label>
                    </div>
                </div>
                
                <div class="progress mb-3" style="height: 5px; display: none;" id="progressContainer">
                    <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                </div>
                
                <div id="log" class="log">
                    <div class="log-entry info">
                        <i class="bi bi-info-circle me-2"></i> Ready to generate sample data. Configure the options above and click "Generate Data".
                    </div>
                </div>
                
                <div class="mt-3 text-end">
                    <a href="/admin" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Admin
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('generateForm');
            const logContainer = document.getElementById('log');
            const progressContainer = document.getElementById('progressContainer');
            const progressBar = document.getElementById('progressBar');
            const generateBtn = document.getElementById('generateBtn');
            
            // Function to add a log entry
            function addLog(message, type = 'info') {
                const entry = document.createElement('div');
                entry.className = `log-entry ${type}`;
                
                let icon = 'info-circle';
                switch(type) {
                    case 'success': icon = 'check-circle'; break;
                    case 'error': icon = 'exclamation-circle'; break;
                    case 'warning': icon = 'exclamation-triangle'; break;
                }
                
                entry.innerHTML = `
                    <i class="bi bi-${icon} me-2"></i>
                    ${new Date().toLocaleTimeString()}: ${message}
                `;
                
                logContainer.appendChild(entry);
                logContainer.scrollTop = logContainer.scrollHeight;
            }
            
            // Function to update progress
            function updateProgress(percent) {
                progressContainer.style.display = 'block';
                progressBar.style.width = `${percent}%`;
                progressBar.setAttribute('aria-valuenow', percent);
                
                if (percent >= 100) {
                    progressBar.classList.remove('progress-bar-animated');
                    progressBar.classList.add('bg-success');
                }
            }
            
            // Handle form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const numProperties = document.getElementById('numProperties').value;
                const agentId = document.getElementById('agentId').value;
                const truncateFirst = document.getElementById('truncateFirst').checked;
                
                // Reset UI
                logContainer.innerHTML = '';
                progressContainer.style.display = 'block';
                progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated';
                progressBar.style.width = '0%';
                generateBtn.disabled = true;
                
                addLog('Starting data generation...', 'info');
                updateProgress(5);
                
                try {
                    // Call the API to generate data
                    const response = await fetch('generate_sample_data.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `num_properties=${numProperties}&agent_id=${agentId}&truncate_first=${truncateFirst ? 1 : 0}`
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        addLog(result.message, 'success');
                        updateProgress(100);
                        
                        // Display summary
                        if (result.summary) {
                            addLog('\nSummary:', 'info');
                            for (const [key, value] of Object.entries(result.summary)) {
                                addLog(`${key}: ${value}`, 'info');
                            }
                        }
                    } else {
                        throw new Error(result.message || 'Unknown error occurred');
                    }
                    
                } catch (error) {
                    addLog(`Error: ${error.message}`, 'error');
                    updateProgress(0);
                } finally {
                    generateBtn.disabled = false;
                }
            });
        });
    </script>
</body>
</html>
