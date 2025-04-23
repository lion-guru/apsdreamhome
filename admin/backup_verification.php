<?php
/**
 * Backup Verification Interface
 * Manage and monitor backup integrity verification
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/backup/integrity_checker.php';
require_once __DIR__ . '/../includes/backup/backup_manager.php';
require_once __DIR__ . '/../includes/middleware/rate_limit_middleware.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['auser'])) {
    header('location:index.php');
    exit();
}

// Apply rate limiting
$rateLimitMiddleware->handle('admin');

// Initialize managers
$integrityChecker = new BackupIntegrityChecker();
$backupManager = new BackupManager();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'verify_backup':
            $file = $_POST['file'] ?? '';
            $result = $integrityChecker->verifyBackup($file);
            echo json_encode($result);
            break;
            
        case 'get_history':
            $history = $integrityChecker->getVerificationHistory();
            echo json_encode($history);
            break;
            
        case 'list_backups':
            $backups = $backupManager->listBackups();
            echo json_encode($backups);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backup Verification - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .backup-card {
            margin-bottom: 20px;
        }
        .verification-badge {
            font-size: 0.9em;
        }
        .backup-info {
            font-size: 0.9em;
            color: #6c757d;
        }
        .verification-history {
            max-height: 400px;
            overflow-y: auto;
        }
        .status-success { color: #198754; }
        .status-failed { color: #dc3545; }
        .verification-time {
            font-size: 0.8em;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/../includes/templates/dynamic_header.php'; ?>
    <div class="container py-4">
        <h1 class="mb-4">Backup Verification</h1>
        
        <!-- Available Backups -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Available Backups</div>
                    <div class="card-body">
                        <div id="backupsList"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Verification History -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Verification History</div>
                    <div class="card-body verification-history">
                        <div id="verificationHistory"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Modal -->
    <div class="modal fade" id="verificationModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Verifying Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 100%"></div>
                    </div>
                    <div id="verificationStatus" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
        
        // Load backups
        function loadBackups() {
            $.get('backup_verification.php', { action: 'list_backups' }, function(backups) {
                const $backupsList = $('#backupsList').empty();
                
                if (backups.length === 0) {
                    $backupsList.append('<p>No backups available.</p>');
                    return;
                }

                backups.forEach(backup => {
                    $backupsList.append(`
                        <div class="card backup-card">
                            <div class="card-body">
                                <h5 class="card-title">${backup.file}</h5>
                                <div class="backup-info">
                                    <p>
                                        Type: ${backup.type}<br>
                                        Size: ${backup.size}<br>
                                        Created: ${backup.date}
                                    </p>
                                </div>
                                <button class="btn btn-primary verify-backup" 
                                        data-file="${backup.path}">
                                    Verify Backup
                                </button>
                            </div>
                        </div>
                    `);
                });
            });
        }

        // Load verification history
        function loadHistory() {
            $.get('backup_verification.php', { action: 'get_history' }, function(history) {
                const $history = $('#verificationHistory').empty();
                
                if (history.length === 0) {
                    $history.append('<p>No verification history available.</p>');
                    return;
                }

                const table = $('<table class="table">').appendTo($history);
                table.append(`
                    <thead>
                        <tr>
                            <th>File</th>
                            <th>Status</th>
                            <th>Duration</th>
                            <th>Timestamp</th>
                        </tr>
                    </thead>
                `);
                
                const tbody = $('<tbody>').appendTo(table);
                
                history.reverse().forEach(record => {
                    tbody.append(`
                        <tr>
                            <td>${record.file}</td>
                            <td>
                                <span class="badge ${record.success ? 'bg-success' : 'bg-danger'}">
                                    ${record.success ? 'Success' : 'Failed'}
                                </span>
                                ${record.error ? `<br><small class="text-danger">${record.error}</small>` : ''}
                            </td>
                            <td>${record.duration ? record.duration + 's' : 'N/A'}</td>
                            <td>${record.timestamp}</td>
                        </tr>
                    `);
                });
            });
        }

        // Verify backup
        $(document).on('click', '.verify-backup', function() {
            const file = $(this).data('file');
            const $status = $('#verificationStatus').empty();
            
            verificationModal.show();
            
            $.post('backup_verification.php', {
                action: 'verify_backup',
                file: file
            }, function(result) {
                if (result.success) {
                    $status.html(`
                        <div class="alert alert-success">
                            Backup verified successfully!<br>
                            Duration: ${result.duration}s
                        </div>
                    `);
                } else {
                    $status.html(`
                        <div class="alert alert-danger">
                            Verification failed: ${result.error}
                        </div>
                    `);
                }
                
                // Reload data
                loadBackups();
                loadHistory();
                
                // Auto-close modal after 3 seconds
                setTimeout(() => {
                    verificationModal.hide();
                }, 3000);
            });
        });

        // Initial load
        loadBackups();
        loadHistory();
    </script>
    <?php include __DIR__ . '/../includes/templates/new_footer.php'; ?>
</body>
</html>
