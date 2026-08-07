<?php
/**
 * Voice Assistant Controller
 */

namespace App\Http\Controllers\Api;

use App\Services\VoiceAssistantService;
use App\Http\Controllers\BaseController;

class VoiceAssistantController extends BaseController
{
    private $assistant;

    public function skipCsrfProtection(): bool
    {
        return true;
    }

    public function __construct()
    {
        parent::__construct();
        $this->assistant = new VoiceAssistantService();
    }

    /**
     * Process voice query (API)
     */
    public function query()
    {
        @session_start();

        $input = json_decode(file_get_contents('php://input'), true);
        $query = $input['query'] ?? $_POST['query'] ?? '';

        if (empty($query)) {
            echo json_encode(['success' => false, 'message' => 'No query provided']);
            return;
        }

        $response = $this->assistant->processQuery($query);
        echo json_encode($response);
    }

    /**
     * Voice Assistant UI page
     */
    public function index()
    {
        @session_start();
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['admin_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        return $this->render('admin/ai/voice_assistant', [
            'page_title' => 'AI Voice Assistant',
        ]);
    }
}
