<?php
/**
 * Associate Call Recording Controller
 * Handles call recordings tied to leads
 */

namespace App\Http\Controllers\Associate;

use App\Core\Controller\BaseController;
use App\Services\CallRecordingService;

class CallRecordingController extends BaseController
{
    private $callRecordingService;

    public function __construct()
    {
        parent::__construct();
        $this->requireAssociateLogin();
        $this->layout = 'layouts/associate';
        $this->callRecordingService = new CallRecordingService();
    }

    /**
     * List call recordings for associate's leads
     */
    public function index()
    {
        $associateId = $_SESSION['associate_id'] ?? $_SESSION['user_id'] ?? 0;

        $filters = [
            'lead_id' => $_GET['lead_id'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'duration_min' => $_GET['duration_min'] ?? '',
            'search' => $_GET['search'] ?? '',
        ];

        $recordings = $this->callRecordingService->getAssociateRecordings($associateId, $filters);
        $stats = $this->callRecordingService->getRecordingStats($associateId);

        return $this->render('associate/call_recordings/index', [
            'page_title' => 'Call Recordings',
            'recordings' => $recordings,
            'stats' => $stats,
            'filters' => $filters,
        ]);
    }

    /**
     * Play call recording
     */
    public function play($id)
    {
        $associateId = $_SESSION['associate_id'] ?? $_SESSION['user_id'] ?? 0;

        $recording = $this->callRecordingService->getRecording($id, $associateId);
        if (!$recording) {
            $_SESSION['error'] = 'Recording not found or access denied';
            $this->redirect(BASE_URL . '/associate/call-recordings');
            return;
        }

        return $this->render('associate/call_recordings/play', [
            'page_title' => 'Play Call Recording',
            'recording' => $recording,
        ]);
    }

    /**
     * Download call recording
     */
    public function download($id)
    {
        $associateId = $_SESSION['associate_id'] ?? $_SESSION['user_id'] ?? 0;

        $recording = $this->callRecordingService->getRecording($id, $associateId);
        if (!$recording || empty($recording['file_path'])) {
            $_SESSION['error'] = 'Recording file not found';
            $this->redirect(BASE_URL . '/associate/call-recordings');
            return;
        }

        $filePath = $recording['file_path'];
        if (!file_exists($filePath)) {
            $_SESSION['error'] = 'Recording file not found on disk';
            $this->redirect(BASE_URL . '/associate/call-recordings');
            return;
        }

        header('Content-Type: audio/mpeg');
        header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
        header('Content-Length: ' . filesize($filePath));
        readfile($filePath);
        exit;
    }

    /**
     * Delete call recording
     */
    public function delete($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/associate/call-recordings');
            return;
        }

        $associateId = $_SESSION['associate_id'] ?? $_SESSION['user_id'] ?? 0;

        $result = $this->callRecordingService->deleteRecording($id, $associateId);

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/associate/call-recordings');
    }

    /**
     * Add note to recording
     */
    public function addNote($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(BASE_URL . '/associate/call-recordings');
            return;
        }

        $associateId = $_SESSION['associate_id'] ?? $_SESSION['user_id'] ?? 0;

        $result = $this->callRecordingService->addNote($id, $associateId, $_POST['note'] ?? '');

        $_SESSION[$result['success'] ? 'success' : 'error'] = $result['message'];
        $this->redirect(BASE_URL . '/associate/call-recordings/' . $id . '/play');
    }

    /**
     * Get recording for lead (AJAX)
     */
    public function getForLead($leadId)
    {
        $associateId = $_SESSION['associate_id'] ?? $_SESSION['user_id'] ?? 0;

        $recordings = $this->callRecordingService->getLeadRecordings($leadId, $associateId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'recordings' => $recordings]);
        exit;
    }
}