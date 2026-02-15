<?php
header('Content-Type: application/json');
require_once '../../controllers/ProjectController.php';

try {
    $projectController = new Admin\Controllers\ProjectController($conn);
    
    switch($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if(isset($_GET['id'])) {
                $project = $projectController->find($_GET['id']);
                echo json_encode(['success' => true, 'data' => $project]);
            } else {
                $projects = $projectController->index();
                echo json_encode(['success' => true, 'data' => $projects]);
            }
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if($projectController->create($data)) {
                echo json_encode(['success' => true, 'message' => 'Project created successfully']);
            } else {
                throw new Exception('Failed to create project');
            }
            break;
            
        case 'PUT':
            if(!isset($_GET['id'])) {
                throw new Exception('Project ID is required');
            }
            $data = json_decode(file_get_contents('php://input'), true);
            if($projectController->update($_GET['id'], $data)) {
                echo json_encode(['success' => true, 'message' => 'Project updated successfully']);
            } else {
                throw new Exception('Failed to update project');
            }
            break;
            
        case 'DELETE':
            if(!isset($_GET['id'])) {
                throw new Exception('Project ID is required');
            }
            if($projectController->delete($_GET['id'])) {
                echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
            } else {
                throw new Exception('Failed to delete project');
            }
            break;
            
        default:
            throw new Exception('Method not allowed');
    }
} catch(Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}