<?php
/**
 * Module 4: MLM Commission Engine + Rank System
 *
 * MLMCommissionController
 *
 * Admin UI over the MLM commission engine. 19 actions covering:
 *   - Dashboard (rank distribution, monthly commission, clawback alert, pending payouts)
 *   - Commissions ledger list + detail
 *   - Payout batches (create / view / approve / mark paid)
 *   - Associate ranks (list + detail + manual promote)
 *   - Rank benefits (read-only 6-tier table)
 *   - Clawbacks (list + view + recover)
 *   - Cron log
 *   - JSON API for rank distribution chart
 *
 * URL prefix: /admin/mlm/* (extends existing /admin/mlm routes)
 * All actions require admin auth, POSTs validate CSRF, queries wrapped in try/catch.
 */

namespace App\Http\Controllers\Admin;

use App\Services\MLM\MLMCommissionEngine;
use App\Services\HybridCommissionEngine;
use Exception;

class MLMCommissionController extends AdminController
{
    /** @var \PDO|null */
    protected $db;

    /** @var MLMCommissionEngine */
    protected $engine;

    /** @var HybridCommissionEngine */
    protected $hybridEngine;

    public function __construct()
    {
        parent::__construct();
        try {
            $this->db = \App\Core\Database\Database::getInstance();
            if (method_exists($this->db, 'getPdo')) {
                $this->db = $this->db->getPdo();
            }
        } catch (\Exception $e) {
            $this->db = null;
        }
        try {
            $this->engine = new MLMCommissionEngine(
                $this->db instanceof \PDO ? $this->db : null
            );
        } catch (\Exception $e) {
            $this->engine = new MLMCommissionEngine();
        }
        try {
            $this->hybridEngine = new HybridCommissionEngine(
                $this->db instanceof \PDO ? $this->db : null
            );
        } catch (\Exception $e) {
            $this->hybridEngine = null;
        }
    }

    /* =============================================================
     *  DASHBOARD
     * ============================================================= */

    public function index()
    {
        $this->requireAdmin();
        $stats = $this->engine->getDashboardStats();
        $rankBenefits = $this->engine->getRankBenefits();
        $cron = $this->engine->getRecentCronRuns(5);

        $this->data = array_merge($this->data, [
            'page_title'   => 'MLM Commission Dashboard',
            'page_heading' => 'MLM Commission Engine — Dashboard',
            'stats'        => $stats,
            'rankBenefits' => $rankBenefits,
            'cron'         => $cron,
        ]);
        return $this->render('admin/mlm/dashboard', $this->data);
    }

    /* =============================================================
     *  COMMISSIONS LEDGER
     * ============================================================= */

    public function commissions()
    {
        $this->requireAdmin();
        $commissions = [];
        try {
            $where = "1=1";
            $params = [];
            if (!empty($_GET['associate_id'])) {
                $where .= " AND l.beneficiary_user_id = ?";
                $params[] = (int)$_GET['associate_id'];
            }
            if (!empty($_GET['status'])) {
                $where .= " AND l.status = ?";
                $params[] = $_GET['status'];
            }
            if (!empty($_GET['from'])) {
                $where .= " AND l.created_at >= ?";
                $params[] = $_GET['from'];
            }
            if (!empty($_GET['to'])) {
                $where .= " AND l.created_at < DATE_ADD(?, INTERVAL 1 DAY)";
                $params[] = $_GET['to'];
            }
            $sql = "
                SELECT l.*, u.name AS beneficiary_name, su.name AS source_name
                FROM mlm_commission_ledger l
                LEFT JOIN users u ON u.id = l.beneficiary_user_id
                LEFT JOIN users su ON su.id = l.source_user_id
                WHERE {$where}
                ORDER BY l.created_at DESC
                LIMIT 200
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $commissions = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $commissions = [];
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'MLM Commissions Ledger',
            'page_heading' => 'MLM Commissions Ledger',
            'commissions'  => $commissions,
            'filters'      => [
                'associate_id' => $_GET['associate_id'] ?? '',
                'status'       => $_GET['status'] ?? '',
                'from'         => $_GET['from'] ?? '',
                'to'           => $_GET['to'] ?? '',
            ],
        ]);
        return $this->render('admin/mlm/commissions', $this->data);
    }

    public function commissionDetail($id)
    {
        $this->requireAdmin();
        $commission = null;
        try {
            $stmt = $this->db->prepare("
                SELECT l.*, u.name AS beneficiary_name, u.email AS beneficiary_email, su.name AS source_name
                FROM mlm_commission_ledger l
                LEFT JOIN users u ON u.id = l.beneficiary_user_id
                LEFT JOIN users su ON su.id = l.source_user_id
                WHERE l.id = ?
                LIMIT 1
            ");
            $stmt->execute([(int)$id]);
            $commission = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            $commission = null;
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'Commission Detail #' . $id,
            'page_heading' => 'Commission Detail',
            'commission'   => $commission,
        ]);
        return $this->render('admin/mlm/commission-detail', $this->data);
    }

    /* =============================================================
     *  PAYOUT BATCHES
     * ============================================================= */

    public function payouts()
    {
        return $this->payoutBatches();
    }

    public function payoutBatches()
    {
        $this->requireAdmin();
        $batches = [];
        try {
            $batches = $this->db->query("SELECT * FROM mlm_payout_batches ORDER BY period_year DESC, period_month DESC LIMIT 50")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $batches = [];
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'MLM Payout Batches',
            'page_heading' => 'Payout Batches',
            'batches'      => $batches,
        ]);
        return $this->render('admin/mlm/payout-batches', $this->data);
    }

    public function payoutBatchCreate()
    {
        $this->requireAdmin();

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->validateCsrfOrFail();
            $year = (int)($_POST['period_year'] ?? date('Y'));
            $month = (int)($_POST['period_month'] ?? date('n'));
            $userId = $this->currentUserId();
            $batchId = $this->engine->createPayoutBatch($year, $month, $userId);
            if ($batchId > 0) {
                $this->setFlash('success', "Payout batch created for {$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT));
                return $this->redirect('/admin/mlm/payouts/batches/' . $batchId);
            }
            $this->setFlash('error', 'Failed to create payout batch — see error log');
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'Create Payout Batch',
            'page_heading' => 'Create Payout Batch',
            'default_year' => (int)($_GET['year'] ?? date('Y')),
            'default_month'=> (int)($_GET['month'] ?? (int)date('n')),
        ]);
        return $this->render('admin/mlm/payout-batch-create', $this->data);
    }

    public function payoutBatchStore()
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->validateCsrfOrFail();
            $year = (int)($_POST['period_year'] ?? date('Y'));
            $month = (int)($_POST['period_month'] ?? date('n'));
            $userId = $this->currentUserId();
            $batchId = $this->engine->createPayoutBatch($year, $month, $userId);
            if ($batchId > 0) {
                $this->setFlash('success', "Payout batch created for {$year}-" . str_pad($month, 2, '0', STR_PAD_LEFT));
                return $this->redirect('/admin/mlm/payouts/batches/' . $batchId);
            }
            $this->setFlash('error', 'Failed to create payout batch');
            return $this->redirect('/admin/mlm/payouts/batches/create');
        }
        return $this->redirect('/admin/mlm/payouts/batches');
    }

    public function payoutBatchView($id)
    {
        $this->requireAdmin();
        $bundle = $this->engine->getPayoutBatch((int)$id);
        $this->data = array_merge($this->data, [
            'page_title'   => 'Payout Batch #' . $id,
            'page_heading' => 'Payout Batch Detail',
            'batch'        => $bundle['batch'],
            'payouts'      => $bundle['payouts'],
        ]);
        return $this->render('admin/mlm/payout-batch-view', $this->data);
    }

    public function payoutBatchApprove($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $userId = $this->currentUserId();
        $ok = $this->engine->approvePayoutBatch((int)$id, $userId);
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Payout batch approved' : 'Failed to approve batch');

        // Create department request for FIN (payout processing)
        if ($ok) {
            try {
                $batch = $this->engine->getPayoutBatch((int)$id);
                if ($batch && !empty($batch['batch'])) {
                    $deptService = new \App\Services\DepartmentRequestService();
                    $deptService->submitRequest([
                        'request_type' => 'approval',
                        'department_code' => 'FIN',
                        'title' => 'Payout Batch Approved - Ready for Processing',
                        'description' => "Payout batch #{$id} has been approved. Ready for payment processing.",
                        'priority' => 'high',
                        'requester_id' => $userId,
                        'requester_role' => 'admin',
                        'requester_name' => 'Admin',
                        'related_entity_type' => 'payout_batch',
                        'related_entity_id' => $id,
                    ]);
                }
            } catch (\Exception $e) {
                error_log('[MLMCommissionController] payoutBatchApprove department request error: ' . $e->getMessage());
            }
        }

        return $this->redirect('/admin/mlm/payouts/batches/' . (int)$id);
    }

    public function payoutPaidForm($payoutId)
    {
        $this->requireAdmin();
        $payout = [];
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT p.*, u.name AS associate_name, a.level AS associate_rank, b.batch_number FROM mlm_payouts p LEFT JOIN users u ON p.associate_user_id = u.id LEFT JOIN associates a ON a.user_id = p.associate_user_id LEFT JOIN mlm_payout_batches b ON p.batch_id = b.id WHERE p.id = ?{$tidSql}");
            $stmt->execute(array_merge([(int)$payoutId], $tidParams));
            $payout = $stmt->fetch(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Throwable $e) {
            error_log('payoutPaidForm: ' . $e->getMessage());
        }
        $this->data = array_merge($this->data, [
            'payout'       => $payout,
            'page_title'   => 'Mark Payout Paid',
            'page_heading' => 'Mark Payout Paid',
        ]);
        return $this->render('admin/mlm/payout-paid-form', $this->data);
    }

    public function payoutMarkPaid($payoutId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $mode = $_POST['payment_mode'] ?? 'bank_transfer';
        $txnMeta = [
            'bank_account'    => $_POST['bank_account'] ?? null,
            'ifsc'            => $_POST['ifsc'] ?? null,
            'upi_id'          => $_POST['upi_id'] ?? null,
            'transaction_ref' => $_POST['transaction_ref'] ?? null,
            'cheque_number'   => $_POST['cheque_number'] ?? null,
        ];
        $userId = $this->currentUserId();
        $ok = $this->engine->markPayoutPaid((int)$payoutId, $mode, $txnMeta, $userId);
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Payout marked as paid' : 'Failed to mark payout as paid');

        // Redirect back to the batch view
        $batchId = 0;
        try {
            [$tidSql, $tidParams] = $this->tenantWhere();
            $stmt = $this->db->prepare("SELECT batch_id FROM mlm_payouts WHERE id = ?{$tidSql}");
            $stmt->execute(array_merge([(int)$payoutId], $tidParams));
            $batchId = (int)($stmt->fetchColumn() ?: 0);
        } catch (\Exception $e) {
            $batchId = 0;
        }
        $url = $batchId > 0 ? "/admin/mlm/payouts/batches/{$batchId}" : '/admin/mlm/payouts/batches';
        return $this->redirect($url);
    }

    /* =============================================================
     *  ASSOCIATE RANKS
     * ============================================================= */

    public function associateRanks()
    {
        $this->requireAdmin();
        $rows = [];
        try {
            $rows = $this->db->query("
                SELECT a.id, a.user_id, a.level, a.status, u.name, u.email
                FROM associates a
                LEFT JOIN users u ON u.id = a.user_id
                ORDER BY a.id ASC
                LIMIT 200
            ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $rows = [];
        }

        $enriched = [];
        foreach ($rows as $r) {
            $aid = (int)$r['id'];
            $rankInfo = $this->engine->getAssociateRank($aid);
            $r['rank_info'] = $rankInfo;
            $r['next_rank'] = $rankInfo['next_rank'] ?? null;
            $r['progress_pct'] = $rankInfo['progress_pct'] ?? 0;
            $r['leg_count'] = $rankInfo['leg_count'] ?? 0;
            $r['lifetime_sales'] = $rankInfo['lifetime_sales'] ?? 0;
            $enriched[] = $r;
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'Associate Ranks',
            'page_heading' => 'Associate Ranks',
            'associates'   => $enriched,
            'rankBenefits' => $this->engine->getRankBenefits(),
        ]);
        return $this->render('admin/mlm/associate-ranks', $this->data);
    }

    public function associateRankView($associateId)
    {
        $this->requireAdmin();
        $aid = (int)$associateId;
        $rankInfo = $this->engine->getAssociateRank($aid);
        $history = $this->engine->getRankHistory($aid);
        $payouts = $this->engine->getAssociatePayouts($aid, 12);
        $clawbackLog = $this->engine->getClawbackLog($aid, 25);

        $associateRow = null;
        try {
            $stmt = $this->db->prepare("
                SELECT a.*, u.name, u.email, u.phone
                FROM associates a
                LEFT JOIN users u ON u.id = a.user_id
                WHERE a.id = ?
                LIMIT 1
            ");
            $stmt->execute([$aid]);
            $associateRow = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            $associateRow = null;
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'Associate Rank #' . $aid,
            'page_heading' => 'Associate Rank Detail',
            'associate'    => $associateRow,
            'rankInfo'     => $rankInfo,
            'history'      => $history,
            'payouts'      => $payouts,
            'clawbackLog'  => $clawbackLog,
            'rankBenefits' => $this->engine->getRankBenefits(),
        ]);
        return $this->render('admin/mlm/associate-rank-view', $this->data);
    }

    public function rankPromoteManual($associateId)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $userId = $this->currentUserId();
        $ok = $this->engine->applyRankPromotion((int)$associateId, $userId);
        $this->setFlash($ok ? 'success' : 'error', $ok ? 'Associate promoted (if eligible)' : 'No promotion was applicable');
        return $this->redirect('/admin/mlm/associate-ranks/' . (int)$associateId);
    }

    public function promoteAll()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $result = $this->engine->runRankPromotions();
            $promoted = is_array($result) ? count($result) : (int)$result;
            if ($promoted > 0) {
                $this->setFlash('success', "{$promoted} associate(s) promoted to higher ranks.");
            } else {
                $this->setFlash('info', 'No associates currently qualify for rank promotion.');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Rank promotion failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/mlm/associate-ranks');
    }

    /* =============================================================
     *  RANK BENEFITS (READ-ONLY)
     * ============================================================= */

    public function rankBenefits()
    {
        $this->requireAdmin();
        $benefits = $this->engine->getRankBenefits();
        $this->data = array_merge($this->data, [
            'page_title'   => 'Rank Benefits',
            'page_heading' => 'Rank Benefits & Rate Cards',
            'benefits'     => $benefits,
        ]);
        return $this->render('admin/mlm/rank-benefits', $this->data);
    }

    /* =============================================================
     *  CLAWBACKS
     * ============================================================= */

    public function clawbacks()
    {
        $this->requireAdmin();
        $rows = [];
        try {
            $rows = $this->db->query("
                SELECT c.*, u.name AS beneficiary_name
                FROM mlm_clawback_log c
                LEFT JOIN users u ON u.id = c.beneficiary_user_id
                ORDER BY c.created_at DESC
                LIMIT 200
            ")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            $rows = [];
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'Clawback Log',
            'page_heading' => 'Clawback Log',
            'clawbacks'    => $rows,
        ]);
        return $this->render('admin/mlm/clawbacks', $this->data);
    }

    public function clawbackView($id)
    {
        $this->requireAdmin();
        $row = null;
        try {
            $stmt = $this->db->prepare("
                SELECT c.*, u.name AS beneficiary_name, su.name AS source_name
                FROM mlm_clawback_log c
                LEFT JOIN users u ON u.id = c.beneficiary_user_id
                LEFT JOIN users su ON su.id = c.source_user_id
                WHERE c.id = ?
                LIMIT 1
            ");
            $stmt->execute([(int)$id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        } catch (\Exception $e) {
            $row = null;
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'Clawback Detail #' . $id,
            'page_heading' => 'Clawback Detail',
            'clawback'     => $row,
        ]);
        return $this->render('admin/mlm/clawback-view', $this->data);
    }

    public function clawbackRecover($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $via = $_POST['recovered_via'] ?? 'manual_payment';
        $amt = (float)($_POST['recovered_amount'] ?? 0);
        $allowed = ['future_commission', 'manual_payment', 'write_off'];
        if (!in_array($via, $allowed, true)) {
            $via = 'manual_payment';
        }
        try {
            $stmt = $this->db->prepare("
                UPDATE mlm_clawback_log
                SET status = ?, recovered_via = ?, recovered_date = CURDATE(),
                    recovered_amount = ?, updated_at = NOW()
                WHERE id = ?
            ");
            $finalStatus = $via === 'write_off' ? 'written_off' : 'recovered';
            $stmt->execute([$finalStatus, $via, $amt, (int)$id]);
            $this->setFlash('success', "Clawback marked as {$finalStatus}");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to mark clawback as recovered: ' . $e->getMessage());
        }
        return $this->redirect('/admin/mlm/clawbacks/' . (int)$id);
    }

    public function processClawbacksNow()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        try {
            $result = $this->engine->processClawbacks(30);
            $count = $result['processed'] ?? 0;
            $amount = $result['amount'] ?? 0.0;
            if ($count > 0) {
                $this->setFlash('success', "Processed {$count} clawbacks totaling ₹" . number_format($amount, 2));
            } else {
                $this->setFlash('info', 'No new clawbacks to process (no installments overdue 30+ days with paid commissions).');
            }
        } catch (\Exception $e) {
            $this->setFlash('error', 'Clawback processing failed: ' . $e->getMessage());
        }
        return $this->redirect('/admin/mlm/clawbacks');
    }

    /* =============================================================
     *  CRON LOG
     * ============================================================= */

    public function cronLog()
    {
        $this->requireAdmin();
        $runs = $this->engine->getRecentCronRuns(50);
        $this->data = array_merge($this->data, [
            'page_title'   => 'MLM Cron Log',
            'page_heading' => 'MLM Cron Run History',
            'runs'         => $runs,
        ]);
        return $this->render('admin/mlm/cron-log', $this->data);
    }

    /* =============================================================
     *  JSON API
     * ============================================================= */

    public function apiRankDistribution()
    {
        $this->requireAdmin();
        $dist = $this->engine->getRankDistribution();
        $benefits = $this->engine->getRankBenefits();
        $payload = [];
        foreach (MLMCommissionEngine::RANK_ORDER as $rank) {
            $ben = null;
            foreach ($benefits as $b) {
                if ($b['rank_name'] === $rank) {
                    $ben = $b;
                    break;
                }
            }
            $payload[] = [
                'rank'             => $rank,
                'count'            => (int)($dist[$rank] ?? 0),
                'min_leg_count'    => (int)($ben['min_leg_count'] ?? 0),
                'min_volume'       => (float)($ben['min_qualifying_volume'] ?? 0),
                'direct_sale_pct'  => (float)($ben['direct_sale_pct'] ?? 0),
                'l1_pct'           => (float)($ben['l1_pct'] ?? 0),
                'l2_pct'           => (float)($ben['l2_pct'] ?? 0),
                'l3_pct'           => (float)($ben['l3_pct'] ?? 0),
                'color'            => $ben['color_code'] ?? '#94a3b8',
                'icon'             => $ben['badge_icon'] ?? 'fa-user',
            ];
        }
        return $this->json([
            'success' => true,
            'data'    => $payload,
            'meta'    => ['total' => array_sum(array_column($payload, 'count'))],
        ]);
    }

    /* =============================================================
     *  PAYOUT SIMULATOR (Admin Calculator)
     * ============================================================= */

    public function payoutSimulator()
    {
        $this->requireAdmin();

        $rankSlabs = [];
        if ($this->hybridEngine) {
            $rankSlabs = $this->hybridEngine->loadRankSlabsFromDb();
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'MLM Payout Simulator',
            'page_heading' => 'Commission Payout Calculator',
            'rank_slabs'   => $rankSlabs,
        ]);

        $this->render('admin/mlm/payout-simulator');
    }

    public function payoutSimulateApi()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $saleAmount = (float)($_POST['sale_amount'] ?? 0);
        $rankSlug   = $_POST['rank_slug'] ?? 'associate';

        if ($saleAmount <= 0) {
            echo json_encode(['success' => false, 'error' => 'Sale amount must be positive']);
            return;
        }

        if (!$this->hybridEngine) {
            echo json_encode(['success' => false, 'error' => 'HybridCommissionEngine not available']);
            return;
        }

        $result = $this->hybridEngine->simulatePayout($saleAmount, $rankSlug);
        echo json_encode($result);
    }

    public function royaltyPool()
    {
        $this->requireAdmin();

        $status = [];
        if ($this->hybridEngine) {
            $status = $this->hybridEngine->getRoyaltyPoolStatus();
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'Royalty Pool',
            'page_heading' => 'Site Manager Royalty Pool (2%)',
            'pool'         => $status,
        ]);

        $this->render('admin/mlm/royalty-pool');
    }

    /**
     * Save updated MLM plan parameters (rank benefits + settings).
     */
    public function planUpdate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $saved = 0;
        $errors = [];

        // 1. Update mlm_rank_benefits per rank
        // Differential Model: direct_sale_pct = rank's own rate; l1_pct/l2_pct/l3_pct = 0 (not used)
        // Upline overrides computed dynamically: Upline Rate − Rate of Level Below
        if (!empty($_POST['benefits'])) {
            foreach ($_POST['benefits'] as $rankName => $data) {
                try {
                    $legs = max(0, (int)($data['min_legs'] ?? 0));
                    $vol = max(0, (float)($data['min_volume'] ?? 0));
                    $direct = max(0, min(100, (float)($data['direct_sale_pct'] ?? 1)));
                    $l1 = 0; // Differential model: not used
                    $l2 = 0;
                    $l3 = 0;
                    $order = (int)($data['rank_order'] ?? 0);

                     $this->db->query(
                        "UPDATE mlm_rank_benefits SET min_leg_count = ?, min_qualifying_volume = ?, direct_sale_pct = ?, l1_pct = ?, l2_pct = ?, l3_pct = ?, rank_order = ? WHERE rank_name = ?",
                        [$legs, $vol, $direct, $l1, $l2, $l3, $order, $rankName]
                    );
                    $saved++;
                } catch (\Throwable $e) {
                    $errors[] = "Failed to update {$rankName}: " . $e->getMessage();
                }
            }
        }

        // 2. Update global settings
        if (!empty($_POST['settings'])) {
            foreach ($_POST['settings'] as $key => $value) {
                $allowed = ['global_cap_pct', 'royalty_pool_pct', 'min_qualifying_volume', 'escrow_release_threshold'];
                if (!in_array($key, $allowed, true)) continue;
                try {
                    $val = max(0, (float)$value);
                    $this->db->query(
                        "INSERT INTO mlm_settings (setting_key, setting_value, updated_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
                        [$key, (string)$val]
                    );
                    $saved++;
                } catch (\Throwable $e) {
                // mlm_settings may not exist — skip gracefully
                error_log($e->getMessage());
                }
            }
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
        } else {
            $this->setFlash('success', "MLM plan updated successfully ({$saved} fields saved).");
        }

        return $this->redirect('/admin/mlm/plan-editor');
    }

    /* =============================================================
     *  HELPERS
     * ============================================================= */

    protected function currentUserId(): int
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        return (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0);
    }

    /**
     * Local CSRF guard. Mirrors AdminController pattern.
     */
    protected function validateCsrfOrFail(): void
    {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            http_response_code(403);
            if (!headers_sent()) {
                header('Content-Type: text/html; charset=utf-8');
            }
            echo '<h2>Invalid CSRF token</h2>';
            exit;
        }
    }

    /* =============================================================
     *  MLM PLAN EDITOR — full CRUD for rank benefits + mlm_settings
     * ============================================================= */

    /**
     * GET /admin/mlm/plan-editor
     * Renders the plan editor with:
     *   - $benefits  : rank rows from mlm_rank_benefits
     *   - $levels    : level rows from mlm_levels
     *   - $settings  : all rows from mlm_settings as key=>value map
     */
    public function planEditor()
    {
        $this->requireAdmin();

        $benefits = [];
        $levels   = [];
        $settings = [];

        try {
            if ($this->db) {
                $tid = (int)$this->tenantId();

                $stmt = $this->db->prepare("SELECT * FROM mlm_rank_benefits WHERE tenant_id = ? ORDER BY rank_order ASC");
                $stmt->execute([$tid]);
                $benefits = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                $stmt = $this->db->prepare("SELECT * FROM mlm_levels ORDER BY level_number ASC");
                $stmt->execute();
                $levels = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                $stmt = $this->db->prepare("SELECT setting_key, setting_value FROM mlm_settings WHERE tenant_id = ?");
                $stmt->execute([$tid]);
                $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

                foreach ($rows as $r) {
                    $settings[$r['setting_key']] = $r['setting_value'];
                }
            }
        } catch (\Exception $e) {
            error_log('[MLMCommissionController] planEditor DB error: ' . $e->getMessage());
        }

        $this->data = array_merge($this->data, [
            'page_title'   => 'MLM Plan Editor',
            'page_heading' => 'MLM Plan Editor — Rates, Ranks & Settings',
            'benefits'     => $benefits,
            'levels'       => $levels,
            'settings'     => $settings,
        ]);

        return $this->render('admin/mlm/plan-editor', $this->data);
    }

    /**
     * POST /admin/mlm/plan-editor/update
     * Saves:
     *   - $_POST['benefits'][rank_name] => [direct_sale_pct, min_volume, min_legs, badge_icon, color_code, rank_order]
     *   - $_POST['settings'][key]       => value   (all mlm_settings rows)
     */
    public function planEditorUpdate()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $base = defined('BASE_URL') ? BASE_URL : '';
        $errors = [];
        $saved  = 0;

        try {
            if (!$this->db) {
                throw new \Exception('Database not available');
            }
            $tid = (int)$this->tenantId();

            // ── 1. Update rank benefits ──────────────────────────────
            $benefitsPOST = $_POST['benefits'] ?? [];
            foreach ($benefitsPOST as $rankName => $fields) {
                $directPct  = max(0, min(100, (float)($fields['direct_sale_pct'] ?? 0)));
                $minVolume  = max(0, (int)($fields['min_volume'] ?? 0));
                $minLegs    = max(0, (int)($fields['min_legs'] ?? 0));
                $rankOrder  = max(1, (int)($fields['rank_order'] ?? 1));
                $badgeIcon  = preg_replace('/[^a-z0-9\-_]/', '', $fields['badge_icon'] ?? 'fa-user');
                $colorCode  = preg_replace('/[^#a-fA-F0-9]/', '', $fields['color_code'] ?? '#94a3b8');

                $stmt = $this->db->prepare("
                    UPDATE mlm_rank_benefits
                    SET direct_sale_pct = ?,
                        min_qualifying_volume = ?,
                        min_leg_count   = ?,
                        rank_order      = ?,
                        badge_icon      = ?,
                        color_code      = ?,
                        updated_at      = NOW()
                    WHERE rank_name = ? AND tenant_id = ?
                ");
                $stmt->execute([$directPct, $minVolume, $minLegs, $rankOrder, $badgeIcon, $colorCode, $rankName, $tid]);
                $saved += $stmt->rowCount();
            }

            // ── 2. Update mlm_settings ───────────────────────────────
            $settingsPOST = $_POST['settings'] ?? [];
            $allowedSettings = [
                'global_cap_pct', 'royalty_pool_pct', 'min_qualifying_volume', 'escrow_release_threshold',
                'generation_bonus_pct', 'generation_bonus_enabled',
                'gen1_match_pct', 'gen2_match_pct', 'gen3_match_pct',
                'infinity_override_pct', 'infinity_override_enabled', 'infinity_min_rank',
                'matching_bonus_enabled', 'matching_max_levels',
                'rank_bonus_enabled', 'rank_bonus_amounts',
                'min_monthly_volume', 'qualification_required',
            ];

            foreach ($allowedSettings as $key) {
                if (!array_key_exists($key, $settingsPOST)) {
                    continue;
                }
                $value = trim($settingsPOST[$key]);

                // Validate numeric fields
                if (in_array($key, ['global_cap_pct','royalty_pool_pct','generation_bonus_pct',
                    'gen1_match_pct','gen2_match_pct','gen3_match_pct','infinity_override_pct'])) {
                    $value = (string)max(0, min(100, (float)$value));
                }
                if (in_array($key, ['min_qualifying_volume','escrow_release_threshold','min_monthly_volume'])) {
                    $value = (string)max(0, (int)$value);
                }
                if (in_array($key, ['generation_bonus_enabled','infinity_override_enabled',
                    'matching_bonus_enabled','rank_bonus_enabled','qualification_required'])) {
                    $value = $value === '1' ? '1' : '0';
                }
                if ($key === 'matching_max_levels') {
                    $value = (string)max(1, min(10, (int)$value));
                }
                if ($key === 'infinity_min_rank') {
                    $valid = ['associate','sr_associate','bdm','sr_bdm','vice_president','president','site_manager'];
                    if (!in_array($value, $valid)) {
                        $value = 'vice_president';
                    }
                }
                if ($key === 'rank_bonus_amounts') {
                    // Validate JSON
                    $decoded = json_decode($value, true);
                    if (!is_array($decoded)) {
                        $errors[] = "Invalid JSON for rank_bonus_amounts";
                        continue;
                    }
                }

                $stmt = $this->db->prepare("
                    INSERT INTO mlm_settings (tenant_id, setting_key, setting_value)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
                ");
                $stmt->execute([$tid, $key, $value]);
                $saved++;
            }

            if (!empty($errors)) {
                \App\Core\Session::flash('error', 'Saved with warnings: ' . implode('; ', $errors));
            } else {
                \App\Core\Session::flash('success', "Plan saved successfully. {$saved} settings updated.");
            }

        } catch (\Exception $e) {
            error_log('[MLMCommissionController] planEditorUpdate error: ' . $e->getMessage());
            \App\Core\Session::flash('error', 'Save failed: ' . $e->getMessage());
        }

        $base = defined('BASE_URL') ? BASE_URL : '';
        header('Location: ' . $base . '/admin/mlm/plan-editor');
        exit;
    }
}
