<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;

class AgentLeaderboardController extends BaseController
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
        $rows = $this->db->fetchAll("SELECT u.id, u.name, u.referral_code, COUNT(pb.id) AS bookings, COALESCE(SUM(pb.total_plot_value),0) AS revenue, COALESCE(SUM(pb.commission_amount),0) AS commission FROM users u LEFT JOIN plot_bookings pb ON (pb.created_by = u.id OR pb.associate_id = u.id) AND pb.deleted_at IS NULL WHERE u.role = 'agent' AND u.status = 'active' GROUP BY u.id, u.name, u.referral_code ORDER BY revenue DESC, bookings DESC");
        $rank = 1;
        foreach ($rows as &$row) {
            $row['rank'] = $rank++;
        }
        return $this->render('agent/leaderboard', [
            'page_title' => 'Agent Leaderboard',
            'rows' => $rows ?: [],
            'currentUserId' => $this->userId,
        ]);
    }
}
