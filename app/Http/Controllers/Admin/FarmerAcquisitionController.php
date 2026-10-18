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

    /**
     * S258 Plot-Gata-Kisan Link Desk.
     * One screen joining plots.gata_number <-> land_parcels.khasra_no
     * <-> farmer_land_holdings <-> land_deals. Zero new tables.
     */
    public function plotGataLink()
    {
        $this->requireAdmin();
        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : 6;

        $pdo = Database::getInstance()->getConnection();

        $cols = $pdo->query("SELECT id, name, is_active FROM colonies ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $colony = null;
        foreach ($cols as $c) {
            if ((int)$c['id'] === $colonyId) { $colony = $c; break; }
        }
        if (!$colony) {
            $_SESSION['flash_error'] = 'Colony not found.';
            $this->redirect('/admin/farmer-acquisitions');
        }

        $st = $pdo->prepare("SELECT * FROM land_parcels WHERE colony_id = ? ORDER BY khasra_no ASC");
        $st->execute([$colonyId]);
        $parcels = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $st = $pdo->prepare("SELECT gata_number, COUNT(*) AS c, COALESCE(SUM(area_sqft),0) AS sqft,
                COALESCE(SUM(status = 'available'),0) AS av, COALESCE(SUM(status = 'booked'),0) AS bk,
                COALESCE(SUM(status = 'sold'),0) AS so, COALESCE(SUM(total_price),0) AS val
            FROM plots WHERE colony_id = ? AND deleted_at IS NULL GROUP BY gata_number");
        $st->execute([$colonyId]);
        $agg = [];
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $a) {
            $agg[(string)($a['gata_number'] ?? '')] = $a;
        }

        $khasras = array_values(array_unique(array_column($parcels, 'khasra_no')));
        $holdings = [];
        if ($khasras) {
            $ph = implode(',', array_fill(0, count($khasras), '?'));
            $st = $pdo->prepare("SELECT * FROM farmer_land_holdings WHERE khasra_number IN ($ph)");
            $st->execute($khasras);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $h) {
                $holdings[$h['khasra_number']][] = $h;
            }
        }

        $fids = [];
        foreach ($holdings as $lst) {
            foreach ($lst as $h) {
                if (!empty($h['farmer_id'])) $fids[(int)$h['farmer_id']] = true;
            }
        }
        $fnames = [];
        if ($fids) {
            $ph = implode(',', array_fill(0, count($fids), '?'));
            foreach (['farmers', 'farmer_profiles'] as $t) {
                try {
                    $st = $pdo->prepare("SELECT * FROM `$t` WHERE id IN ($ph)");
                    $st->execute(array_keys($fids));
                    foreach ($st->fetchAll(PDO::FETCH_ASSOC) ?: [] as $f) {
                        $nm = $f['full_name'] ?? $f['name'] ?? $f['farmer_name'] ?? null;
                        $fnames[$t . ':' . $f['id']] = $nm ?: ('#' . $f['id']);
                    }
                } catch (\Throwable $e) { /* table may not exist — skip */ }
            }
        }

        $st = $pdo->prepare("SELECT * FROM land_deals WHERE colony_id = ? ORDER BY id DESC");
        $st->execute([$colonyId]);
        $deals = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $st = $pdo->prepare("SELECT id, plot_number, block, area_sqft, status FROM plots
            WHERE colony_id = ? AND deleted_at IS NULL AND status = 'available' AND land_parcel_id IS NULL
            ORDER BY plot_number ASC LIMIT 200");
        $st->execute([$colonyId]);
        $unlinked = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $orphans = [];
        foreach ($agg as $g => $a) {
            if ($g === '') continue;
            $found = false;
            foreach ($parcels as $pc) {
                if ((string)$pc['khasra_no'] === (string)$g) { $found = true; break; }
            }
            if (!$found) $orphans[] = $a;
        }
        $nullGata = $agg[''] ?? null;

        $linked = 0; $tAcres = 0.0; $tPlots = 0;
        foreach ($parcels as $pc) {
            $tAcres += (float)($pc['area_acres'] ?? 0);
            $g = (string)$pc['khasra_no'];
            if (isset($agg[$g])) $linked += (int)$agg[$g]['c'];
        }
        foreach ($agg as $a) $tPlots += (int)$a['c'];
        $adv = 0.0; $bal = 0.0;
        foreach ($deals as $d) {
            $adv += (float)($d['advance_paid'] ?? 0);
            $bal += (float)($d['balance_amount'] ?? 0);
        }

        $this->render('admin/land_acquisition/plot_gata_link', [
            'page_title' => 'Plot-Gata-Kisan Link — ' . $colony['name'],
            'colonies' => $cols,
            'colony' => $colony,
            'colony_id' => $colonyId,
            'parcels' => $parcels,
            'agg' => $agg,
            'holdings' => $holdings,
            'fnames' => $fnames,
            'deals' => $deals,
            'unlinked' => $unlinked,
            'orphans' => $orphans,
            'null_gata' => $nullGata,
            'stats' => [
                'parcels' => count($parcels), 'acres' => $tAcres,
                'plots' => $tPlots, 'linked' => $linked,
                'advance' => $adv, 'balance' => $bal,
            ],
        ]);
    }

    public function updateParcelOwner()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $colonyId = (int)($_POST['colony_id'] ?? 6);
        $back = '/admin/land-plot-link?colony_id=' . $colonyId;
        $pid = (int)($_POST['parcel_id'] ?? 0);
        $name = trim((string)($_POST['owner_name'] ?? ''));
        $phone = trim((string)($_POST['owner_phone'] ?? ''));
        if ($pid <= 0 || $name === '') {
            $_SESSION['flash_error'] = 'Parcel + owner (kisan) name required.';
            $this->redirect($back);
        }
        if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,15}$/', $phone)) {
            $_SESSION['flash_error'] = 'Phone number invalid.';
            $this->redirect($back);
        }
        $pdo = Database::getInstance()->getConnection();
        $st = $pdo->prepare("UPDATE land_parcels SET owner_name = ?, owner_phone = ? WHERE id = ? AND tenant_id = 1");
        $st->execute([$name, ($phone !== '' ? $phone : null), $pid]);
        $_SESSION['flash_success'] = $st->rowCount()
            ? "Kisan naam update ho gaya (parcel #$pid)."
            : 'Koi badlav nahi (parcel mila nahi?).';
        $this->redirect($back);
    }

    public function linkPlotParcel()
    {
        $this->requireAdmin();
        $this->validateCsrfOrFail();
        $colonyId = (int)($_POST['colony_id'] ?? 6);
        $back = '/admin/land-plot-link?colony_id=' . $colonyId;
        $plotId = (int)($_POST['plot_id'] ?? 0);
        $parcelId = (int)($_POST['parcel_id'] ?? 0);
        if ($plotId <= 0 || $parcelId <= 0) {
            $_SESSION['flash_error'] = 'Plot + gata (parcel) dono chuno.';
            $this->redirect($back);
        }
        $pdo = Database::getInstance()->getConnection();
        $st = $pdo->prepare("SELECT id, khasra_no, colony_id FROM land_parcels WHERE id = ? LIMIT 1");
        $st->execute([$parcelId]);
        $parcel = $st->fetch(PDO::FETCH_ASSOC);
        $st = $pdo->prepare("SELECT id, plot_number, colony_id, status FROM plots WHERE id = ? LIMIT 1");
        $st->execute([$plotId]);
        $plot = $st->fetch(PDO::FETCH_ASSOC);
        if (!$parcel || !$plot) {
            $_SESSION['flash_error'] = 'Plot ya parcel nahi mila.';
            $this->redirect($back);
        }
        if ((int)$parcel['colony_id'] !== (int)$plot['colony_id'] || (int)$plot['colony_id'] !== $colonyId) {
            $_SESSION['flash_error'] = 'Plot aur gata alag colony ke hain — link nahi hoga.';
            $this->redirect($back);
        }
        if ($plot['status'] !== 'available') {
            $_SESSION['flash_error'] = 'Sirf available plot ka gata link badlo (booked/sold history freeze hai). Plot ' . $plot['plot_number'] . ' = ' . $plot['status'] . '.';
            $this->redirect($back);
        }
        $st = $pdo->prepare("UPDATE plots SET land_parcel_id = ?, gata_number = ? WHERE id = ?");
        $st->execute([$parcelId, $parcel['khasra_no'], $plotId]);
        $_SESSION['flash_success'] = 'Plot ' . $plot['plot_number'] . ' ab ' . $parcel['khasra_no'] . ' se linked.';
        $this->redirect($back);
    }
}
