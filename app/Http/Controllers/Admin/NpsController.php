<?php

namespace App\Http\Controllers\Admin;

use App\Services\NpsService;

class NpsController extends AdminController
{
    private $service;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try { $this->service = new NpsService($this->db); } catch (\Throwable $e) { $this->service = null; }
    }

    public function index()
    {
        $surveys = $this->service ? $this->service->getAllSurveys(true) : [];
        $stats = [];
        if ($this->service && !empty($surveys)) {
            $stats = $this->service->getStats($surveys[0]['id']);
        }
        return $this->render('admin.nps.index', [
            'page_title' => 'NPS Surveys',
            'page_heading' => 'Customer Satisfaction Surveys',
            'surveys' => $surveys,
            'stats' => $stats
        ]);
    }

    public function create()
    {
        return $this->render('admin.nps.create', [
            'page_title' => 'Create NPS Survey',
            'page_heading' => 'Create New Survey'
        ]);
    }

    public function store()
    {
        if (!$this->service) return $this->redirect(BASE_URL . '/admin/nps');
        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? null,
            'question_text' => $_POST['question_text'] ?? 'How likely are you to recommend us to a friend or colleague?',
            'scale_min_label' => $_POST['scale_min_label'] ?? 'Not at all likely',
            'scale_max_label' => $_POST['scale_max_label'] ?? 'Extremely likely',
            'follow_up_question' => $_POST['follow_up_question'] ?? null,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'send_immediately' => !empty($_POST['send_immediately']) ? 1 : 0,
            'delay_days' => !empty($_POST['delay_days']) ? (int)$_POST['delay_days'] : null,
            'delay_hours' => !empty($_POST['delay_hours']) ? (int)$_POST['delay_hours'] : null,
            'trigger_event' => $_POST['trigger_event'] ?? 'manual',
            'created_by' => $this->getUserId()
        ];
        $id = $this->service->createSurvey($data);
        $this->setFlash($id ? 'success' : 'error', $id ? "Survey #$id created" : 'Failed to create survey');
        return $this->redirect(BASE_URL . '/admin/nps/show/' . $id);
    }

    public function show($id = 0)
    {
        $id = is_numeric($id) ? (int)$id : 0;
        if (!$this->service || !$id) return $this->redirect(BASE_URL . '/admin/nps');
        $survey = $this->service->getSurveyById($id);
        if (!$survey) {
            $this->setFlash('error', 'Survey not found');
            return $this->redirect(BASE_URL . '/admin/nps');
        }
        $responses = $this->service->getResponses($id, 50);
        $stats = $this->service->getStats($id);
        return $this->render('admin.nps.show', [
            'page_title' => $survey['title'],
            'page_heading' => $survey['title'],
            'survey' => $survey,
            'responses' => $responses,
            'stats' => $stats
        ]);
    }

    public function edit($id = 0)
    {
        $id = is_numeric($id) ? (int)$id : 0;
        if (!$this->service || !$id) return $this->redirect(BASE_URL . '/admin/nps');
        $survey = $this->service->getSurveyById($id);
        if (!$survey) {
            $this->setFlash('error', 'Survey not found');
            return $this->redirect(BASE_URL . '/admin/nps');
        }
        return $this->render('admin.nps.edit', [
            'page_title' => 'Edit Survey',
            'page_heading' => 'Edit Survey #' . $id,
            'survey' => $survey
        ]);
    }

    public function update()
    {
        $id = (int)($_POST['id'] ?? 0);
        if (!$this->service || !$id) return $this->redirect(BASE_URL . '/admin/nps');
        $data = [
            'title' => $_POST['title'] ?? '',
            'description' => $_POST['description'] ?? null,
            'question_text' => $_POST['question_text'] ?? 'How likely are you to recommend us to a friend or colleague?',
            'scale_min_label' => $_POST['scale_min_label'] ?? 'Not at all likely',
            'scale_max_label' => $_POST['scale_max_label'] ?? 'Extremely likely',
            'follow_up_question' => $_POST['follow_up_question'] ?? null,
            'is_active' => !empty($_POST['is_active']) ? 1 : 0,
            'send_immediately' => !empty($_POST['send_immediately']) ? 1 : 0,
            'delay_days' => !empty($_POST['delay_days']) ? (int)$_POST['delay_days'] : null,
            'delay_hours' => !empty($_POST['delay_hours']) ? (int)$_POST['delay_hours'] : null,
            'trigger_event' => $_POST['trigger_event'] ?? 'manual'
        ];
        $success = $this->service->updateSurvey($id, $data);
        $this->setFlash($success ? 'success' : 'error', $success ? "Survey #$id updated" : 'Failed to update survey');
        return $this->redirect(BASE_URL . '/admin/nps/show/' . $id);
    }

    public function delete()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($this->service && $id) {
            $this->service->deleteSurvey($id);
            $this->setFlash('success', 'Survey deleted');
        }
        return $this->redirect(BASE_URL . '/admin/nps');
    }

    public function send()
    {
        if ($this->service) {
            $count = $this->service->sendDueSurveys();
            $this->setFlash('success', "Sent $count surveys");
        }
        return $this->redirect(BASE_URL . '/admin/nps');
    }

    public function processTriggers()
    {
        if ($this->service) {
            $count = $this->service->processTriggers();
            $this->setFlash('success', "Processed $count survey triggers");
        }
        return $this->redirect(BASE_URL . '/admin/nps');
    }

    protected function getUserId(): int { return (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0); }

    private function pdo(): \PDO
    {
        $db = $this->db;
        if (is_object($db) && method_exists($db, 'getPdo')) return $db->getPdo();
        return $db;
    }
}
