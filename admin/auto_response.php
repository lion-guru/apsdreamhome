<?php
/**
 * Automated Response Management Interface
 * Configure and monitor automated security responses
 */

require_once __DIR__ . '/../includes/config/config.php';
require_once __DIR__ . '/../includes/security/auto_response.php';
require_once __DIR__ . '/../includes/middleware/rate_limit_middleware.php';

// Check admin authentication
session_start();
if (!isset($_SESSION['auser'])) {
    header('location:index.php');
    exit();
}

// Apply rate limiting
$rateLimitMiddleware->handle('admin');

// Initialize auto-response system
$autoResponseSystem = new AutoResponseSystem();

// Handle AJAX requests
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    switch ($_GET['action']) {
        case 'get_config':
            $config = $autoResponseSystem->getResponseActions();
            echo json_encode($config);
            break;
            
        case 'test_response':
            $type = $_POST['type'] ?? '';
            $data = json_decode($_POST['data'] ?? '{}', true);
            
            $result = $autoResponseSystem->handleIncident($type, $data);
            echo json_encode(['success' => $result]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid action']);
    }
    exit;
}

// Get blocked IPs
function getBlockedIps() {
    $file = __DIR__ . '/../data/security/blocked_ips.json';
    return file_exists($file) ? json_decode(file_get_contents($file), true) : [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Automated Response System - APS Dream Homes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .incident-card {
            margin-bottom: 20px;
        }
        .action-badge {
            margin-right: 5px;
            margin-bottom: 5px;
        }
        .threshold-info {
            font-size: 0.9em;
            color: #6c757d;
        }
        .cooldown-info {
            font-size: 0.9em;
            color: #0d6efd;
        }
        .blocked-ip {
            font-family: monospace;
        }
    </style>
</head>
<body>
    <?php include("../includes/templates/dynamic_header.php"); ?>
    <div class="container py-4">
        <h1 class="mb-4">Automated Response System</h1>
        
        <!-- Response Configuration -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Incident Response Configuration</span>
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#testModal">
                                Test Response
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="responseConfig"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Blocked IPs -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">Currently Blocked IPs</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Blocked Since</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (getBlockedIps() as $ip): ?>
                                    <tr>
                                        <td class="blocked-ip"><?= htmlspecialchars($ip) ?></td>
                                        <td>
                                            <?php
                                            $htaccess = __DIR__ . '/../.htaccess';
                                            if (file_exists($htaccess)) {
                                                $stat = stat($htaccess);
                                                echo date('Y-m-d H:i:s', $stat['mtime']);
                                            } else {
                                                echo 'Unknown';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Test Response Modal -->
    <div class="modal fade" id="testModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Test Automated Response</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="testForm">
                        <div class="mb-3">
                            <label class="form-label">Incident Type</label>
                            <select class="form-select" name="type" required>
                                <!-- Options will be populated by JavaScript -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Test Data (JSON)</label>
                            <textarea class="form-control" name="data" rows="5" required></textarea>
                            <div class="form-text">
                                Enter test data in JSON format based on the selected incident type.
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="runTest">Run Test</button>
                </div>
            </div>
        </div>
    </div>

    <?php include("../includes/templates/new_footer.php"); ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Load response configuration
        function loadConfig() {
            $.get('auto_response.php', { action: 'get_config' }, function(config) {
                const $config = $('#responseConfig').empty();
                const $select = $('#testModal select[name="type"]').empty();
                
                Object.entries(config).forEach(([type, settings]) => {
                    const formattedType = type.split('_')
                        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
                        .join(' ');
                    
                    // Add to test modal dropdown
                    $select.append(`
                        <option value="${type}">${formattedType}</option>
                    `);
                    
                    // Add to main config display
                    const actionBadges = settings.actions.map(action => 
                        `<span class="badge bg-info action-badge">${action}</span>`
                    ).join('');
                    
                    $config.append(`
                        <div class="card incident-card">
                            <div class="card-body">
                                <h5 class="card-title">${formattedType}</h5>
                                <div class="threshold-info mb-2">
                                    Threshold: ${settings.threshold}
                                </div>
                                <div class="cooldown-info mb-2">
                                    Cooldown: ${formatCooldown(settings.cooldown)}
                                </div>
                                <div class="actions mb-2">
                                    ${actionBadges}
                                </div>
                            </div>
                        </div>
                    `);
                });
            });
        }

        // Format cooldown period
        function formatCooldown(seconds) {
            if (seconds === 0) return 'No cooldown';
            if (seconds < 60) return `${seconds} seconds`;
            if (seconds < 3600) return `${Math.floor(seconds / 60)} minutes`;
            return `${Math.floor(seconds / 3600)} hours`;
        }

        // Update test data template
        $('#testModal select[name="type"]').change(function() {
            const type = $(this).val();
            let template = {};
            
            switch (type) {
                case 'brute_force':
                    template = {
                        ip: '192.168.1.100',
                        failed_attempts: 6,
                        username: 'test_user'
                    };
                    break;
                    
                case 'api_abuse':
                    template = {
                        api_key_id: 'key_123',
                        requests_per_minute: 150,
                        endpoint: '/api/search'
                    };
                    break;
                    
                case 'malware_detected':
                    template = {
                        file_path: '/path/to/suspicious/file.php',
                        signature: 'malware_signature_123',
                        ip: '192.168.1.100'
                    };
                    break;
                    
                // Add more templates as needed
            }
            
            $('#testModal textarea[name="data"]').val(JSON.stringify(template, null, 2));
        });

        // Run test
        $('#runTest').click(function() {
            const $form = $('#testForm');
            const $btn = $(this);
            
            try {
                JSON.parse($form.find('[name="data"]').val());
            } catch (e) {
                alert('Invalid JSON data');
                return;
            }
            
            $btn.prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm"></span> Testing...');
            
            $.post('auto_response.php?action=test_response', {
                type: $form.find('[name="type"]').val(),
                data: $form.find('[name="data"]').val()
            }, function(result) {
                if (result.success) {
                    alert('Test completed successfully!');
                    location.reload();
                } else {
                    alert('Test failed: ' + (result.error || 'Unknown error'));
                }
            })
            .always(function() {
                $btn.prop('disabled', false)
                    .text('Run Test');
            });
        });

        // Initial load
        loadConfig();
        
        // Trigger initial template update
        $('#testModal select[name="type"]').trigger('change');
    </script>
</body>
</html>
