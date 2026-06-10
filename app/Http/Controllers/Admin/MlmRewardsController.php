<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;

class MlmRewardsController extends AdminController
{
    protected $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }

    public function rankCriteria()
    {
        $this->requireAdmin();
        $criteria = [];
        try {
            $criteria = $this->db->fetchAll("SELECT * FROM mlm_rank_criteria ORDER BY FIELD(rank, 'bronze','silver','gold','platinum','diamond','crown')") ?: [];
        } catch (\Exception $e) {
            // Table may not exist
        }

        return $this->render('admin/mlm-rewards/rank-criteria', [
            'page_title' => 'MLM Rank Criteria',
            'criteria' => $criteria
        ]);
    }

    public function storeRankCriteria()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/mlm/rank-criteria');
        }

        $id = intval($_POST['id'] ?? 0);
        $data = [
            'rank' => $_POST['rank'] ?? '',
            'min_monthly_sales' => floatval($_POST['min_monthly_sales'] ?? 0),
            'min_team_size' => intval($_POST['min_team_size'] ?? 0),
            'min_active_downlines' => intval($_POST['min_active_downlines'] ?? 0),
            'min_monthly_commission' => floatval($_POST['min_monthly_commission'] ?? 0)
        ];

        if ($id > 0) {
            $this->db->update('mlm_rank_criteria', $data, ['id' => $id]);
            $_SESSION['success'] = 'Rank criteria updated successfully!';
        } else {
            $this->db->insert('mlm_rank_criteria', $data);
            $_SESSION['success'] = 'Rank criteria added successfully!';
        }

        $this->redirect('/admin/mlm/rank-criteria');
    }

    public function upgrades()
    {
        $this->requireAdmin();
        $upgrades = [];
        try {
            $upgrades = $this->db->fetchAll("
                SELECT ru.*, u.name as associate_name
                FROM mlm_rank_upgrades ru
                JOIN users u ON u.id = ru.associate_id
                ORDER BY ru.upgrade_date DESC
            ") ?: [];
        } catch (\Exception $e) {
            // Table may not exist
        }

        return $this->render('admin/mlm-rewards/upgrades', [
            'page_title' => 'Rank Upgrades',
            'upgrades' => $upgrades
        ]);
    }

    public function withdrawals()
    {
        $this->requireAdmin();

        $requests = [];
        $stats = ['total' => 0, 'approved' => 0, 'rejected' => 0, 'pending' => 0, 'processed' => 0];
        try {
            $requests = $this->db->fetchAll("
                SELECT wr.*, u.name as associate_name, u.email as associate_email
                FROM mlm_withdrawal_requests wr
                JOIN users u ON u.id = wr.associate_id
                ORDER BY wr.request_date DESC
            ") ?: [];

            $counts = $this->db->fetchAll("SELECT status, COUNT(*) as cnt FROM mlm_withdrawal_requests GROUP BY status") ?: [];
            foreach ($counts as $row) {
                $key = strtolower($row['status']);
                if (isset($stats[$key])) {
                    $stats[$key] = intval($row['cnt']);
                }
                $stats['total'] += intval($row['cnt']);
            }
        } catch (\Exception $e) {
            // Table may not exist
        }

        return $this->render('admin/mlm-rewards/withdrawals', [
            'page_title' => 'Withdrawal Requests',
            'requests' => $requests,
            'stats' => $stats
        ]);
    }

    public function updateWithdrawalStatus($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/mlm/withdrawals');
        }

        $status = $_POST['status'] ?? '';
        $adminNotes = $_POST['admin_notes'] ?? '';

        if (!in_array($status, ['approved', 'rejected', 'processed'])) {
            $_SESSION['error'] = 'Invalid status.';
            $this->redirect('/admin/mlm/withdrawals');
        }

        $update = ['status' => $status, 'admin_notes' => $adminNotes];
        if ($status === 'processed' || $status === 'approved') {
            $update['processed_date'] = date('Y-m-d H:i:s');
        }

        $this->db->update('mlm_withdrawal_requests', $update, ['id' => intval($id)]);

        $_SESSION['success'] = 'Withdrawal request ' . $status . ' successfully!';
        $this->redirect('/admin/mlm/withdrawals');
    }

    public function rewards()
    {
        $this->requireAdmin();
        try {
            $rewards = $this->db->fetchAll("
                SELECT rh.*, u.name as associate_name
                FROM reward_history rh
                JOIN users u ON u.id = rh.associate_id
                ORDER BY rh.reward_date DESC
            ") ?: [];
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }

        $this->render('admin/mlm-rewards/rewards', [
            'page_title' => 'Reward History',
            'rewards' => $rewards
        ]);
    }
}
