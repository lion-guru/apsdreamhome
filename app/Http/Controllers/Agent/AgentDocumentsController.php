<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;

class AgentDocumentsController extends BaseController
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
        $items = $this->db->fetchAll("SELECT * FROM documents WHERE user_id = ? ORDER BY uploaded_on DESC, id DESC", [$this->userId]);
        return $this->render('agent/documents', [
            'page_title' => 'My Documents',
            'items' => $items ?: [],
        ]);
    }
}
