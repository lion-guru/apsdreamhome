<?php

namespace App\Http\Controllers\Admin;

use App\Services\ReferralService;
use App\Services\CommissionAgreementService;
use App\Services\RankService;
use Exception;
use Throwable;
use PDO;

/**
 * NetworkController
 * Admin UI endpoints for MLM network management.
 */
class NetworkController extends AdminController
{
    private $referralService;
    private $agreementService;
    private $rankService;

    public function __construct()
    {
        parent::__construct();

        $this->referralService = new ReferralService();
        $this->agreementService = new CommissionAgreementService();
        $this->rankService = new RankService();
    }

    public function index(): void
    {
        $this->data['ranks'] = $this->rankService->getRanks();
        $this->data['page_title'] = $this->mlSupport->translate('MLM Network Inspector');
        $this->render('admin/mlm_network_inspector');
    }

    public function searchUsers(): void
    {
        $query = trim($this->request->get('query') ?? '');
        if ($query === '') {
            $this->jsonResponse(['success' => true, 'data' => []]);
            return;
        }

        try {
            // Using PDO from BaseController instead of mysqli
            $sql = "SELECT id, name, email FROM users WHERE email LIKE ? OR name LIKE ? LIMIT 10";
            $stmt = $this->db->prepare($sql);
            $like = '%' . $query . '%';
            $stmt->execute([$like, $like]);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->jsonResponse(['success' => true, 'data' => $rows]);
        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }

    public function networkTree(): void
    {
        $userId = (int) ($this->request->get('user_id') ?? 0);
        if ($userId <= 0) {
            $this->jsonError('user_id required', 400);
            return;
        }

        $maxDepth = (int) ($this->request->get('depth') ?? 5);
        $query = $this->request->get('query') ?? null;
        $rank = $this->request->get('rank') ?? null;

        $options = [];
        if ($query) {
            $options['query'] = $query;
        }
        if ($rank) {
            $options['rank'] = $rank;
        }

        try {
            $tree = $this->referralService->getNetworkTree($userId, $maxDepth, $options);
            $this->jsonResponse(['success' => true, 'data' => $tree]);
        } catch (Exception $e) {
            $this->jsonError($e->getMessage(), 500);
        }
    }
}
