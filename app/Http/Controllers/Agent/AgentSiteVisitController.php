<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;

class AgentSiteVisitController extends BaseController
{
    protected $db;
    protected $userId;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
        $this->layout = 'layouts/agent';
        $this->userId = (int)($_SESSION['user_id'] ?? 0);
    }

    public function index()
    {
        if ($this->userId <= 0) {
            $this->redirect('/agent/login');
            return;
        }
        $items = $this->db->fetchAll("SELECT sv.*, p.plot_number, c.name AS colony_name FROM site_visits sv LEFT JOIN plots p ON p.id = sv.plot_id LEFT JOIN colonies c ON c.id = sv.colony_id WHERE (sv.agent_id = ? OR sv.assigned_to = ?) ORDER BY sv.visit_date DESC, sv.id DESC", [$this->userId, $this->userId]);
        return $this->render('agent/site_visits', [
            'page_title' => 'My Site Visits',
            'items' => $items ?: [],
        ]);
    }

    public function show($id)
    {
        if ($this->userId <= 0) {
            $this->redirect('/agent/login');
            return;
        }
        $item = $this->db->fetchOne("SELECT sv.*, p.plot_number, c.name AS colony_name FROM site_visits sv LEFT JOIN plots p ON p.id = sv.plot_id LEFT JOIN colonies c ON c.id = sv.colony_id WHERE sv.id = ? AND (sv.agent_id = ? OR sv.assigned_to = ?)", [(int)$id, $this->userId, $this->userId]);
        if (!$item) {
            $this->redirect('/agent/site-visits');
            return;
        }
        return $this->render('agent/site_visit_detail', [
            'page_title' => 'Site Visit Detail',
            'item' => $item,
        ]);
    }
}
