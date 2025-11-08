<?php
/**
 * API - MLM Operations
 * Handle MLM-related operations for associates
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

try {
    global $pdo;

    if (!$pdo) {
        sendJsonResponse(['success' => false, 'error' => 'Database connection not available'], 500);
    }

    $request_uri = $_SERVER['REQUEST_URI'];
    $path_segments = explode('/', trim($request_uri, '/'));
    $endpoint = end($path_segments);

    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            switch ($endpoint) {
                case 'dashboard':
                    // Get MLM dashboard data
                    session_start();
                    if (!isset($_SESSION['user_id'])) {
                        sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
                    }

                    $associateMLM = new \App\Models\AssociateMLM();
                    $associate_mlm = $associateMLM->getAssociateMLM($_SESSION['user_id']);

                    if (!$associate_mlm) {
                        sendJsonResponse(['success' => false, 'error' => 'Not registered as associate'], 404);
                    }

                    $dashboard_data = $associateMLM->getDashboardData($_SESSION['user_id']);

                    sendJsonResponse([
                        'success' => true,
                        'data' => $dashboard_data
                    ]);
                    break;

                case 'genealogy':
                    // Get genealogy tree
                    session_start();
                    if (!isset($_SESSION['user_id'])) {
                        sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
                    }

                    $associateMLM = new \App\Models\AssociateMLM();
                    $levels = $_GET['levels'] ?? 3;
                    $genealogy = $associateMLM->getGenealogy($_SESSION['user_id'], $levels);

                    sendJsonResponse([
                        'success' => true,
                        'data' => $genealogy
                    ]);
                    break;

                case 'downline':
                    // Get downline structure
                    session_start();
                    if (!isset($_SESSION['user_id'])) {
                        sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
                    }

                    $associateMLM = new \App\Models\AssociateMLM();
                    $levels = $_GET['levels'] ?? 3;
                    $downline = $associateMLM->getDownline($_SESSION['user_id'], $levels);

                    sendJsonResponse([
                        'success' => true,
                        'data' => $downline
                    ]);
                    break;

                case 'levels':
                    // Get MLM level configuration
                    $associateMLM = new \App\Models\AssociateMLM();
                    $levels = $associateMLM->getLevelConfig();

                    sendJsonResponse([
                        'success' => true,
                        'data' => $levels
                    ]);
                    break;

                case 'rank':
                    // Get associate rank and achievements
                    session_start();
                    if (!isset($_SESSION['user_id'])) {
                        sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
                    }

                    $associateMLM = new \App\Models\AssociateMLM();
                    $rank_info = $associateMLM->getAssociateRank($_SESSION['user_id']);

                    sendJsonResponse([
                        'success' => true,
                        'data' => $rank_info
                    ]);
                    break;

                default:
                    sendJsonResponse(['success' => false, 'error' => 'Invalid endpoint'], 404);
            }
            break;

        case 'POST':
            switch ($endpoint) {
                case 'register':
                    // Register as associate
                    $input = json_decode(file_get_contents('php://input'), true);

                    $required = ['sponsor_id'];
                    foreach ($required as $field) {
                        if (!isset($input[$field]) || empty($input[$field])) {
                            sendJsonResponse(['success' => false, 'error' => "Field '{$field}' is required"], 400);
                        }
                    }

                    session_start();
                    if (!isset($_SESSION['user_id'])) {
                        sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
                    }

                    $user_id = $_SESSION['user_id'];

                    // Check if already registered
                    $associateMLM = new \App\Models\AssociateMLM();
                    $existing = $associateMLM->getAssociateMLM($user_id);
                    if ($existing) {
                        sendJsonResponse(['success' => false, 'error' => 'Already registered as associate'], 409);
                    }

                    // Check if sponsor exists
                    $sponsor_mlm = $associateMLM->getAssociateMLM($input['sponsor_id']);
                    if (!$sponsor_mlm) {
                        sendJsonResponse(['success' => false, 'error' => 'Invalid sponsor'], 404);
                    }

                    // Determine position (left/right leg)
                    $position = $input['position'] ?? 'left';

                    // Check if position is available
                    if (!$associateMLM->isPositionAvailable($input['sponsor_id'], $position)) {
                        $position = ($position === 'left') ? 'right' : 'left';
                        if (!$associateMLM->isPositionAvailable($input['sponsor_id'], $position)) {
                            sendJsonResponse(['success' => false, 'error' => 'No positions available under this sponsor'], 400);
                        }
                    }

                    // Register associate
                    $associate_data = [
                        'user_id' => $user_id,
                        'sponsor_id' => $input['sponsor_id'],
                        'placement_id' => $input['sponsor_id'], // Direct placement under sponsor
                        'level' => 1,
                        'position' => $position,
                        'status' => 'active',
                        'joining_date' => date('Y-m-d')
                    ];

                    $success = $associateMLM->createAssociate($associate_data);

                    if ($success) {
                        // Update sponsor's downline count
                        $associateMLM->updateLevel($input['sponsor_id']);

                        sendJsonResponse([
                            'success' => true,
                            'message' => 'Successfully registered as associate',
                            'data' => [
                                'associate_id' => $user_id,
                                'sponsor_id' => $input['sponsor_id'],
                                'position' => $position,
                                'level' => 1
                            ]
                        ]);
                    } else {
                        sendJsonResponse(['success' => false, 'error' => 'Registration failed'], 500);
                    }
                    break;

                case 'commission':
                    // Process commission (admin only)
                    session_start();
                    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                        sendJsonResponse(['success' => false, 'error' => 'Admin access required'], 403);
                    }

                    $input = json_decode(file_get_contents('php://input'), true);

                    $required = ['associate_id', 'sale_amount', 'sale_id'];
                    foreach ($required as $field) {
                        if (!isset($input[$field]) || empty($input[$field])) {
                            sendJsonResponse(['success' => false, 'error' => "Field '{$field}' is required"], 400);
                        }
                    }

                    $associateMLM = new \App\Models\AssociateMLM();
                    $commissions = $associateMLM->processCommission([
                        'associate_id' => $input['associate_id'],
                        'sale_amount' => $input['sale_amount'],
                        'sale_id' => $input['sale_id']
                    ]);

                    sendJsonResponse([
                        'success' => true,
                        'message' => 'Commission processed successfully',
                        'data' => [
                            'commissions_paid' => count($commissions),
                            'total_commission' => array_sum(array_column($commissions, 'commission_amount')),
                            'commissions' => $commissions
                        ]
                    ]);
                    break;

                default:
                    sendJsonResponse(['success' => false, 'error' => 'Invalid endpoint'], 404);
            }
            break;

        default:
            sendJsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
    }

} catch (Exception $e) {
    error_log('API MLM Error: ' . $e->getMessage());
    sendJsonResponse([
        'success' => false,
        'error' => 'Internal server error',
        'message' => $e->getMessage()
    ], 500);
}

/**
 * Send JSON response
 */
function sendJsonResponse($data, $status_code = 200) {
    http_response_code($status_code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
