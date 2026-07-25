<?php
/**
 * ExecutiveAIController — Unified AI Assistant for all executive roles
 * 
 * Routes:
 *   GET  /admin/ai/executive-assistant  → Dashboard with chat interface
 *   POST /admin/ai/executive-assistant/chat → Process chat message (AJAX)
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\ExecutiveAIService;

class ExecutiveAIController extends AdminController
{
    private $aiService;

    public function __construct()
    {
        parent::__construct();
        $this->aiService = new ExecutiveAIService();
    }

    public function skipCsrfProtection(): bool
    {
        return true;
    }

    /**
     * Executive AI Assistant Dashboard
     */
    public function index()
    {
        @session_start();
        $role = $_SESSION['role'] ?? $_SESSION['admin_role'] ?? 'admin';
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;
        $userName = $_SESSION['admin_name'] ?? $_SESSION['user_name'] ?? 'User';

        $roleInfo = $this->aiService->getRoleInfo($role);

        $data = [
            'page_title' => 'AI Assistant — ' . ($roleInfo['title'] ?? 'Executive'),
            'role' => $role,
            'role_title' => $roleInfo['title'] ?? 'Executive',
            'user_name' => $userName,
            'focus_areas' => $roleInfo['focus'] ?? [],
        ];

        $this->render('admin/ai/executive_assistant', $data);
    }

    /**
     * Process chat message (AJAX endpoint)
     */
    public function chat()
    {
        @session_start();
        header('Content-Type: application/json');

        $role = $_SESSION['role'] ?? $_SESSION['admin_role'] ?? 'admin';
        $userId = $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0;

        $input = json_decode(file_get_contents('php://input'), true);
        $message = trim($input['message'] ?? $_POST['message'] ?? '');

        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message is required.']);
            exit;
        }

        $response = $this->aiService->chat($message, $role, $userId);
        echo json_encode($response);
        exit;
    }
}
