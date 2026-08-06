<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\MlmRoyaltyService;

/**
 * MlmRankController — Fully DB-Driven MLM Rank Admin CRUD
 *
 * Admin can change rank names, GBV thresholds, commission rates,
 * royalty eligibility, rewards — ALL from this panel without touching code.
 *
 * Routes:
 *   GET  /admin/network/ranks             — list all ranks
 *   GET  /admin/network/ranks/{id}/edit   — edit form for a rank
 *   POST /admin/network/ranks/{id}/update — save changes
 *   GET  /admin/network/royalty           — royalty pool distribution panel
 *   POST /admin/network/royalty/distribute — trigger monthly distribution
 */
class MlmRankController extends AdminController
{
    private MlmRoyaltyService $royaltyService;

    public function __construct()
    {
        parent::__construct();
        $this->royaltyService = new MlmRoyaltyService($this->db);
    }

    /* ── List all ranks ─────────────────────────────────────────────── */
    public function ranksIndex(): void
    {
        $this->requireAdmin();
        $ranks = $this->royaltyService->getAllRanks();
        $this->render('admin.network.ranks.index', [
            'page_title' => 'MLM Rank Structure Management',
            'ranks'      => $ranks,
        ]);
    }

    /* ── Edit form ──────────────────────────────────────────────────── */
    public function rankEdit(int $id): void
    {
        $this->requireAdmin();
        $rank = $this->royaltyService->getRank($id);
        if (!$rank) {
            $this->setFlash('error', 'Rank not found.');
            $this->redirect('/admin/network/ranks');
            return;
        }
        $this->render('admin.network.ranks.edit', [
            'page_title' => 'Edit Rank: ' . ($rank['rank_name'] ?? ''),
            'rank'       => $rank,
        ]);
    }

    /* ── Save rank changes ──────────────────────────────────────────── */
    public function rankUpdate(int $id): void
    {
        $this->requireAdmin();

        $data = [
            'rank_name'              => strip_tags($_POST['rank_name']             ?? ''),
            'min_gbv'                => (float)($_POST['min_gbv']                  ?? 0),
            'max_gbv'                => (float)($_POST['max_gbv']                  ?? 0),
            'commission_rate'        => (float)($_POST['commission_rate']           ?? 0),
            'royalty_eligible'       => (int)($_POST['royalty_eligible']            ?? 0),
            'royalty_pool_share_pct' => (float)($_POST['royalty_pool_share_pct']   ?? 0),
            'leadership_bonus_pct'   => (float)($_POST['leadership_bonus_pct']     ?? 0),
            'profit_share_eligible'  => (int)($_POST['profit_share_eligible']       ?? 0),
            'reward_name'            => strip_tags($_POST['reward_name']           ?? ''),
            'reward_description'     => strip_tags($_POST['reward_description']    ?? ''),
            'reward_value'           => (float)($_POST['reward_value']             ?? 0),
            'sort_order'             => (int)($_POST['sort_order']                 ?? 0),
            'is_active'              => (int)($_POST['is_active']                  ?? 1),
        ];

        $ok = $this->royaltyService->updateRank($id, $data);

        if ($ok) {
            $this->setFlash('success', 'Rank "' . htmlspecialchars($data['rank_name']) . '" updated successfully.');
        } else {
            $this->setFlash('error', 'Update failed. Please try again.');
        }
        $this->redirect('/admin/network/ranks');
    }

    /* ── Royalty Pool Distribution Panel ───────────────────────────── */
    public function royaltyPool(): void
    {
        $this->requireAdmin();

        $month = (int)($_GET['month'] ?? date('n'));
        $year  = (int)($_GET['year']  ?? date('Y'));

        $poolTotal     = $this->royaltyService->getMonthlyPoolTotal($month, $year);
        $distribution  = $this->royaltyService->getMonthlyDistribution($month, $year);
        $eligibleCount = count($this->royaltyService->getEligibleRoyaltyMembers());

        $this->render('admin.network.royalty-pool', [
            'page_title'     => 'MLM Royalty Pool Distribution',
            'month'          => $month,
            'year'           => $year,
            'pool_total'     => $poolTotal,
            'distribution'   => $distribution,
            'eligible_count' => $eligibleCount,
        ]);
    }

    /* ── Trigger distribution ───────────────────────────────────────── */
    public function distributePool(): void
    {
        $this->requireAdmin();

        $month = (int)($_POST['month'] ?? date('n'));
        $year  = (int)($_POST['year']  ?? date('Y'));

        $result = $this->royaltyService->distributeMonthlyPool($month, $year);

        if ($result['success']) {
            $this->setFlash('success',
                'Distribution complete. ₹' . number_format($result['total_pool'], 2) .
                ' distributed to ' . $result['distributed_to'] . ' members.'
            );
        } else {
            $this->setFlash('error', 'Distribution failed: ' . ($result['error'] ?? 'Unknown error'));
        }

        $this->redirect('/admin/network/royalty?month=' . $month . '&year=' . $year);
    }
}
