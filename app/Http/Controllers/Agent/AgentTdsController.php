<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;

class AgentTdsController extends BaseController
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
        $items = $this->db->fetchAll("SELECT * FROM tds_register WHERE deductee_user_id = ? ORDER BY transaction_date DESC, id DESC", [$this->userId]);
        $summary = $this->db->fetch("SELECT COUNT(*) AS entries, COALESCE(SUM(gross_amount),0) AS gross, COALESCE(SUM(tds_amount),0) AS tds, COALESCE(SUM(total_tds),0) AS total FROM tds_register WHERE deductee_user_id = ?", [$this->userId]);
        return $this->render('agent/tds', [
            'page_title' => 'My TDS',
            'items' => $items ?: [],
            'summary' => $summary ?: ['entries' => 0, 'gross' => 0, 'tds' => 0, 'total' => 0],
        ]);
    }
}
