<?php
namespace App\Http\Controllers\Admin;
use App\Services\CRMVoiceService;

class CRMVoiceController extends AdminController {
    public function index() {
        $this->requireAdmin();
        $service = new CRMVoiceService();
        $userId = $_SESSION['admin_id'] ?? 0;
        $recent = $service->getRecentVoiceActivity($userId);
        $stats = $service->getVoiceStats();
        return $this->render('admin/crm/voice/index', ['recent' => $recent, 'stats' => $stats, 'page_title' => 'Voice CRM']);
    }

    public function callLead($id) {
        $this->requireAdmin();
        require_once __DIR__ . '/../../../Services/CRMService.php';
        $crm = new \App\Services\CRMService();
        $lead = $crm->getLeadById((int)$id);
        if (!$lead) { $this->setFlash('error', 'Lead not found'); return $this->redirect('/admin/crm/voice'); }
        return $this->render('admin/crm/voice/call', ['lead' => $lead, 'page_title' => 'Voice Call - ' . ($lead['name'] ?? '')]);
    }

    public function dictateNote() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error' => 'POST required']); exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        $service = new CRMVoiceService();
        $result = $service->saveVoiceNote(
            (int)($input['lead_id'] ?? 0),
            $_SESSION['admin_id'] ?? 0,
            $input['transcript'] ?? ''
        );
        echo json_encode($result);
        exit;
    }

    public function voiceCommand() {
        $this->requireAdmin();
        header('Content-Type: application/json');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['error' => 'POST required']); exit; }
        $input = json_decode(file_get_contents('php://input'), true);
        $service = new CRMVoiceService();
        $result = $service->processVoiceCommand($input['command'] ?? '', $_SESSION['admin_id'] ?? 0);
        echo json_encode($result);
        exit;
    }
}
