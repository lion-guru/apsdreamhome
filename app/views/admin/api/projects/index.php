<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../core/init.php';

// Verify admin authentication
if (!isAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized'))]);
    exit();
}

use App\Models\Project;

try {
    $projectModel = new Project();
    
    // Global CSRF validation for state-changing methods
    if (in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'])) {
        $csrf_token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!verifyCSRFToken($csrf_token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Security validation failed'))]);
            exit();
        }

        // RBAC: Only Super Admin and Manager can modify projects
        if (!hasRole('superadmin') && !hasRole('manager')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => h($mlSupport->translate('Unauthorized: Only Super Admin and Manager can modify projects'))]);
            exit();
        }
    }

    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if(isset($_GET['id'])) {
                $project = $projectModel->getProjectById(intval($_GET['id']));
                // Sanitize project data
                if ($project) {
                    foreach ($project as $key => $value) {
                        if (is_string($value)) $project[$key] = h($value);
                    }
                }
                echo json_encode(['success' => true, 'data' => $project]);
            } else {
                $projects = $projectModel->getAllProjects();
                // Sanitize projects data
                foreach ($projects as &$proj) {
                    foreach ($proj as $key => $value) {
                        if (is_string($value)) $proj[$key] = h($value);
                    }
                }
                echo json_encode(['success' => true, 'data' => $projects]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if($projectModel->create($data)) {
                echo json_encode(['success' => true, 'message' => h($mlSupport->translate('Project created successfully'))]);
            } else {
                throw new Exception($mlSupport->translate('Failed to create project'));
            }
            break;
            
        case 'PUT':
            if(!isset($_GET['id'])) {
                throw new Exception($mlSupport->translate('Project ID is required'));
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if($projectModel->update(intval($_GET['id']), $data)) {
                echo json_encode(['success' => true, 'message' => h($mlSupport->translate('Project updated successfully'))]);
            } else {
                throw new Exception($mlSupport->translate('Failed to update project'));
            }
            break;
            
        case 'DELETE':
            if(!isset($_GET['id'])) {
                throw new Exception($mlSupport->translate('Project ID is required'));
            }
            if($projectModel->delete(intval($_GET['id']))) {
                echo json_encode(['success' => true, 'message' => h($mlSupport->translate('Project deleted successfully'))]);
            } else {
                throw new Exception($mlSupport->translate('Failed to delete project'));
            }
            break;
            
        default:
            http_response_code(405);
            throw new Exception($mlSupport->translate('Method not allowed'));
    }
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => h($mlSupport->translate($e->getMessage()))]);
}
