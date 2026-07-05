<?php
namespace App\Http\Controllers\Admin;
use App\Services\MeetingService;

class MeetingController extends AdminController {
    public function index() {
        $this->requireAdmin();
        $service = new MeetingService();
        $filters = [];
        if (!empty($_GET['status'])) $filters['status'] = $_GET['status'];
        if (!empty($_GET['user_id'])) $filters['user_id'] = (int)$_GET['user_id'];
        if (!empty($_GET['date_from'])) $filters['date_from'] = $_GET['date_from'];
        if (!empty($_GET['date_to'])) $filters['date_to'] = $_GET['date_to'];
        $meetings = $service->getMeetings($filters);
        $stats = $service->getStats();
        return $this->render('admin/meetings/index', ['meetings' => $meetings, 'stats' => $stats, 'page_title' => 'Meetings']);
    }

    public function schedule() {
        $this->requireAdmin();
        return $this->render('admin/meetings/schedule', ['page_title' => 'Schedule Meeting']);
    }

    public function store() {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/meetings');
        $service = new MeetingService();
        $result = $service->createMeeting([
            'lead_id' => (int)($_POST['lead_id'] ?? 0),
            'user_id' => (int)($_POST['user_id'] ?? 0),
            'meeting_type' => $_POST['meeting_type'] ?? 'site_visit',
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? '',
            'location' => $_POST['location'] ?? '',
            'start_time' => $_POST['start_time'] ?? '',
            'end_time' => $_POST['end_time'] ?? null,
            'created_by' => $_SESSION['admin_id'] ?? null,
        ]);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Meeting scheduled' : 'Error');
        return $this->redirect('/admin/meetings');
    }

    public function show($id) {
        $this->requireAdmin();
        $service = new MeetingService();
        $meeting = $service->getMeetingById((int)$id);
        if (!$meeting) { $this->setFlash('error', 'Meeting not found'); return $this->redirect('/admin/meetings'); }
        return $this->render('admin/meetings/show', ['meeting' => $meeting, 'page_title' => 'Meeting Details']);
    }

    public function update($id) {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/meetings');
        $service = new MeetingService();
        $data = [];
        foreach (['lead_id','user_id','meeting_type','title','description','location','start_time','end_time','status','notes','outcome'] as $f) {
            if (isset($_POST[$f])) $data[$f] = $_POST[$f];
        }
        $result = $service->updateMeeting((int)$id, $data);
        $this->setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Meeting updated' : 'Error');
        return $this->redirect('/admin/meetings');
    }

    public function cancel($id) {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/meetings');
        $service = new MeetingService();
        $service->updateMeeting((int)$id, ['status' => 'cancelled']);
        $this->setFlash('success', 'Meeting cancelled');
        return $this->redirect('/admin/meetings');
    }

    public function complete($id) {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return $this->redirect('/admin/meetings');
        $service = new MeetingService();
        $service->completeMeeting((int)$id, $_POST['outcome'] ?? '', $_POST['notes'] ?? '');
        $this->setFlash('success', 'Meeting marked complete');
        return $this->redirect('/admin/meetings');
    }

    public function calendar() {
        $this->requireAdmin();
        $service = new MeetingService();
        $userId = (int)($_GET['user_id'] ?? $_SESSION['admin_id'] ?? 0);
        $start = $_GET['start'] ?? date('Y-m-01');
        $end = $_GET['end'] ?? date('Y-m-t');
        $events = $service->getCalendarEvents($userId, $start . ' 00:00:00', $end . ' 23:59:59');
        header('Content-Type: application/json');
        echo json_encode(array_map(function($e) {
            return ['id' => $e['id'], 'title' => $e['title'] . ' - ' . ($e['lead_name'] ?? ''), 'start' => $e['start_time'], 'end' => $e['end_time'] ?? $e['start_time'], 'color' => $e['status'] === 'completed' ? '#22c55e' : ($e['status'] === 'cancelled' ? '#ef4444' : '#3b82f6')];
        }, $events));
        exit;
    }
}
