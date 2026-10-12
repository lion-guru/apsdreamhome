<?php

namespace App\Http\Controllers\Admin;

use App\Services\FarmerAcquisitionService;
use App\Core\Database\Database;
use PDO;

/**
 * Farmer Land Acquisition Bainama & Milestone Compensation Disbursement Desk
 */
class FarmerAcquisitionController extends AdminController
{
    private FarmerAcquisitionService $acquisitionService;

    public function __construct()
    {
        parent::__construct();
        $this->acquisitionService = new FarmerAcquisitionService();
    }

    public function index()
    {
        $this->requireAdmin();

        $status = trim((string)($_GET['status'] ?? 'all'));
        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        $db = Database::getInstance();
        $pdo = $db->getConnection();

        $sql = "SELECT fla.*, c.name AS colony_name
                FROM farmer_land_acquisitions fla
                LEFT JOIN colonies c ON fla.target_colony_id = c.id
                WHERE 1=1";
        $params = [];

        if ($colonyId) {
            $sql .= " AND fla.target_colony_id = ?";
            $params[] = $colonyId;
        }
        if ($status !== 'all') {
            $sql .= " AND fla.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY fla.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $acquisitions = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $stats = $this->acquisitionService->getSummaryStats();
        $colonies = $pdo->query("SELECT id, name FROM colonies WHERE is_active = 1 ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->render('admin/land_acquisition/index', [
            'page_title' => 'Farmer Land Acquisition & Bainama Milestones Desk',
            'acquisitions' => $acquisitions,
            'stats' => $stats,
            'colonies' => $colonies,
            'current_colony' => $colonyId,
            'current_status' => $status
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $data = [
            'tenant_id' => 1,
            'target_colony_id' => !empty($_POST['target_colony_id']) ? (int)$_POST['target_colony_id'] : null,
            'farmer_name' => trim((string)($_POST['farmer_name'] ?? '')),
            'farmer_phone' => trim((string)($_POST['farmer_phone'] ?? '')),
            'farmer_aadhaar' => trim((string)($_POST['farmer_aadhaar'] ?? '')),
            'farmer_pan' => trim((string)($_POST['farmer_pan'] ?? '')),
            'bank_account_no' => trim((string)($_POST['bank_account_no'] ?? '')),
            'bank_ifsc' => trim((string)($_POST['bank_ifsc'] ?? '')),
            'bank_name' => trim((string)($_POST['bank_name'] ?? '')),
            'mauza_village' => trim((string)($_POST['mauza_village'] ?? '')),
            'tehsil' => trim((string)($_POST['tehsil'] ?? 'Sadar')),
            'district' => trim((string)($_POST['district'] ?? 'Gorakhpur')),
            'gata_number' => trim((string)($_POST['gata_number'] ?? '')),
            'fasli_year' => trim((string)($_POST['fasli_year'] ?? '1431-1436 Fasli')),
            'khatauni_verified' => !empty($_POST['khatauni_verified']) ? 1 : 0,
            'area_bigha' => (float)($_POST['area_bigha'] ?? 0.0),
            'rate_per_bigha' => (float)($_POST['rate_per_bigha'] ?? 0.0),
            'agreement_date' => !empty($_POST['agreement_date']) ? $_POST['agreement_date'] : date('Y-m-d'),
            'target_registry_date' => !empty($_POST['target_registry_date']) ? $_POST['target_registry_date'] : date('Y-m-d', strtotime('+6 months')),
            'remarks' => trim((string)($_POST['remarks'] ?? ''))
        ];

        $res = $this->acquisitionService->createAcquisition($data);
        if ($res['success']) {
            $_SESSION['flash_success'] = "Land acquisition registered: {$res['deal_token']}";
        } else {
            $_SESSION['flash_error'] = $res['message'];
        }

        $this->redirect('/admin/farmer-acquisitions');
    }

    public function show($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $acq = $this->acquisitionService->getAcquisitionDetails($id);
        if (!$acq) {
            $_SESSION['flash_error'] = 'Acquisition deal not found.';
            $this->redirect('/admin/farmer-acquisitions');
        }

        $milestones = $this->acquisitionService->getMilestones($id);

        $this->render('admin/land_acquisition/show', [
            'page_title' => 'Land Deal — Gata ' . htmlspecialchars($acq['gata_number']) . ' (' . htmlspecialchars($acq['farmer_name']) . ')',
            'acquisition' => $acq,
            'milestones' => $milestones
        ]);
    }

    public function disburseMilestone($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $mId = (int)$id;
        $acqId = (int)($_POST['acquisition_id'] ?? 0);

        $payoutData = [
            'payment_mode' => trim((string)($_POST['payment_mode'] ?? 'rtgs')),
            'payment_ref' => trim((string)($_POST['payment_ref'] ?? '')),
            'tds_deducted' => (float)($_POST['tds_deducted'] ?? 0.0),
            'disbursed_at' => !empty($_POST['disbursed_at']) ? $_POST['disbursed_at'] : date('Y-m-d')
        ];

        $res = $this->acquisitionService->disburseMilestone($mId, $payoutData);
        if ($res['success']) {
            $_SESSION['flash_success'] = "Milestone payment disbursed. Voucher: {$res['voucher_token']}";
        } else {
            $_SESSION['flash_error'] = $res['message'];
        }

        if ($acqId > 0) {
            $this->redirect('/admin/farmer-acquisitions/' . $acqId);
        } else {
            $this->redirect('/admin/farmer-acquisitions');
        }
    }

    public function updateStatus($id)
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();

        $id = (int)$id;
        $status = trim((string)($_POST['status'] ?? 'bayana_agreement'));
        $remarks = trim((string)($_POST['remarks'] ?? ''));

        $this->acquisitionService->updateStatus($id, $status, $remarks);
        $_SESSION['flash_success'] = 'Deal status updated to ' . str_replace('_', ' ', $status);
        $this->redirect('/admin/farmer-acquisitions/' . $id);
    }

    public function printVoucher($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $db = Database::getInstance();
        $sql = "SELECT fcm.*, fla.deal_token, fla.farmer_name, fla.farmer_phone, fla.farmer_aadhaar,
                       fla.farmer_pan, fla.bank_account_no, fla.bank_ifsc, fla.bank_name,
                       fla.mauza_village, fla.tehsil, fla.district, fla.gata_number,
                       fla.area_bigha, fla.area_sqft, c.name AS colony_name
                FROM farmer_compensation_milestones fcm
                JOIN farmer_land_acquisitions fla ON fcm.acquisition_id = fla.id
                LEFT JOIN colonies c ON fla.target_colony_id = c.id
                WHERE fcm.id = ?
                LIMIT 1";
        $voucher = $db->fetch($sql, [$id]);

        if (!$voucher) {
            http_response_code(404);
            echo "Payment voucher not found.";
            exit;
        }

        $this->view('admin/land_acquisition/voucher_print', [
            'voucher' => $voucher,
            'page_title' => 'Land Compensation Payout Voucher #' . ($voucher['voucher_token'] ?? $voucher['id'])
        ]);
    }
}
