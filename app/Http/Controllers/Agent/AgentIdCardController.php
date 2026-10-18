<?php
namespace App\Http\Controllers\Agent;

use App\Http\Controllers\BaseController;

class AgentIdCardController extends BaseController
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
        $user = $this->db->fetchOne("SELECT id, name, email, phone, role, referral_code, created_at FROM users WHERE id = ? AND role = 'agent'", [$this->userId]);
        if (!$user) {
            $this->redirect('/agent/login');
            return;
        }
        $rank = $this->db->fetchOne("SELECT COUNT(*) AS c FROM users WHERE role = 'agent' AND id < ?", [$this->userId]);
        $rankLabel = ((int)($rank['c'] ?? 0) < 10) ? 'Gold' : 'Silver';
        $rankClass = $rankLabel === 'Gold' ? 'bg-warning text-dark' : 'bg-secondary';
        $referralCode = $user['referral_code'] ?: ('AGT' . str_pad((string)$user['id'], 6, '0', STR_PAD_LEFT));
        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=' . urlencode($referralCode);
        return $this->render('agent/id_card', [
            'page_title' => 'My ID Card',
            'user' => $user,
            'rankBadge' => ['label' => $rankLabel, 'class' => $rankClass],
            'referralCode' => $referralCode,
            'qrCodeUrl' => $qrCodeUrl,
        ]);
    }
}
