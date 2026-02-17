<?php
/**
 * Backup Manager Interface
 * Manage database backups and restoration
 */

require_once __DIR__ . '/core/init.php';
require_once __DIR__ . '/../../../includes/backup/backup_manager.php';
require_once __DIR__ . '/../../../includes/middleware/rate_limit_middleware.php';

if (!isSuperAdmin()) {
    header('Location: login.php');
    exit();
}

// Apply rate limiting
$rateLimitMiddleware->handle('admin');

// Initialize backup manager
$backupManager = new BackupManager();

// Handle AJAX requests
if (isset($_GET['action'])) {
    require_once __DIR__ . '/../includes/log_admin_action_db.php';
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'create_backup':
            $type = $_GET['type'] ?? 'daily';
            $result = $backupManager->createBackup($type);
            log_admin_action_db(
                $result['success'] ? 'create_backup' : 'create_backup_failed',
                'Backup type: ' . $type . ' | Result: ' . json_encode($result)
            );
            echo json_encode($result);
            break;
            
        case 'list_backups':
            $type = $_GET['type'] ?? null;
            $backups = $backupManager->listBackups($type);
            echo json_encode($backups);
            break;
            
        case 'restore_backup':
            if (!isset($_GET['file'])) {
                log_admin_action_db('restore_backup_failed', 'No backup file specified');
                echo json_encode(['error' => 'No backup file specified']);
                break;
            }
            $result = $backupManager->restoreBackup($_GET['file']);
            log_admin_action_db(
                $result['success'] ? 'restore_backup' : 'restore_backup_failed',
                'Backup file: ' . $_GET['file'] . ' | Result: ' . json_encode($result)
            );
            echo json_encode($result);
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
    <title>Backup Manager - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .backup-card {
            margin-bottom: 20px;
        }
        .backup-actions {
            display: flex;
            gap: 10px;
        }
        .status-success { color: #198754; }
        .status-failed { color: #dc3545; }
        .backup-info { font-size: 0.9em; color: #6c757d; }
    </style>
</head>
<body>
    <?php 
    $header_path = __DIR__ . '/../includes/dynamic_header.php';
    if (file_exists($header_path)) {
        include $header_path;
    } else {
        // Fallback to admin_header.php if dynamic_header is missing
        include __DIR__ . '/admin_header.php';
    }
    ?>
    <div class="container py-4">
        <h1 class="mb-4">Database Backup Manager</h1>
        
        <!-- Create Backup -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Create New Backup</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <select id="backupType" class="form-select">
                                    <option value="daily">Daily Backup</option>
                                    <option value="weekly">Weekly Backup</option>
                                    <option value="monthly">Monthly Backup</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <button id="createBackup" class="btn btn-primary">
                                    Create Backup
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Backup List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Available Backups</span>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-secondary filter-btn active" data-type="all">All</button>
                                <button class="btn btn-sm btn-outline-secondary filter-btn" data-type="daily">Daily</button>
                                <button class="btn btn-sm btn-outline-secondary filter-btn" data-type="weekly">Weekly</button>
                                <button class="btn btn-sm btn-outline-secondary filter-btn" data-type="monthly">Monthly</button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="backupsList"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Restore Confirmation Modal -->
    <div class="modal fade" id="restoreModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirm Restore</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to restore this backup? This will overwrite the current database.</p>
                    <p><strong>Warning:</strong> This action cannot be undone!</p>
                    <div id="restoreDetails"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmRestore">Restore</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedBackup = null;
        const restoreModal = new bootstrap.Modal(document.getElementById('restoreModal'));

        // Load backups
        function loadBackups(type = null) {
            $.get('backup_manager.php', { 
                action: 'list_backups',
                type: type
            }, function(backups) {
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
                                        Type: ${backup.type} | 
                                        Size: ${backup.size} | 
                                        Created: ${backup.date}
                                    </p>
                                </div>
                                <div class="backup-actions">
                                    <button class="btn btn-warning btn-sm restore-backup" 
                                            data-file="${backup.path}">
                                        Restore
                                    </button>
                                    <a href="${backup.path}" 
                                       class="btn btn-info btn-sm" 
                                       download>
                                        Download
                                    </a>
                                </div>
                            </div>
                        </div>
                    `);
                });
            });
        }

        // Create backup
        $('#createBackup').click(function() {
            const type = $('#backupType').val();
            const $btn = $(this);
            
            $btn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm"></span> Creating...');
            
            $.get('backup_manager.php', { 
                action: 'create_backup',
                type: type
            }, function(result) {
                if (result.success) {
                    alert('Backup created successfully!');
                    loadBackups();
                } else {
                    alert('Failed to create backup: ' + result.error);
                }
            })
            .always(function() {
                $btn.prop('disabled', false)
                    .text('Create Backup');
            });
        });

        // Filter backups
        $('.filter-btn').click(function() {
            $('.filter-btn').removeClass('active');
            $(this).addClass('active');
            
            const type = $(this).data('type');
            loadBackups(type === 'all' ? null : type);
        });

        // Show restore confirmation
        $(document).on('click', '.restore-backup', function() {
            selectedBackup = $(this).data('file');
            $('#restoreDetails').text(`File: ${selectedBackup}`);
            restoreModal.show();
        });

        // Confirm restore
        $('#confirmRestore').click(function() {
            if (!selectedBackup) return;
            
            const $btn = $(this);
            $btn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm"></span> Restoring...');
            
            $.get('backup_manager.php', { 
                action: 'restore_backup',
                file: selectedBackup
            }, function(result) {
                if (result.success) {
                    alert('Database restored successfully!');
                } else {
                    alert('Failed to restore database: ' + result.error);
                }
                restoreModal.hide();
            })
            .always(function() {
                $btn.prop('disabled', false)
                    .text('Restore');
            });
        });

        // Initial load
        loadBackups();
    </script>
    <?php 
    $footer_path = __DIR__ . '/../includes/dynamic_footer.php';
    if (file_exists($footer_path)) {
        include $footer_path;
    } else {
        // Fallback to admin_footer.php if dynamic_footer is missing
        include __DIR__ . '/admin_footer.php';
    }
    ?>
</body>
</html>
