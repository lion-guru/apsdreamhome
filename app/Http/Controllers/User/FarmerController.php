<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\BaseController;
use App\Models\Farmer;
use App\Models\FarmerLandHolding;
use App\Models\LandPurchase;
use App\Core\Security;

/**
 * Farmer Controller
 * Handles all farmer management operations
 */
class FarmerController extends BaseController
{
    private $farmerModel;
    private $landHoldingModel;
    private $purchaseModel;

    public function __construct()
    {
        parent::__construct();
        $this->farmerModel = new Farmer();
        $this->landHoldingModel = new FarmerLandHolding();
        $this->purchaseModel = new LandPurchase();
    }

    /**
     * Display farmer dashboard
     */
    public function index()
    {
        try {
            $farmers = $this->farmerModel->getAllFarmers();
            $statistics = $this->farmerModel->getFarmerStatistics();
            $recentPurchases = $this->purchaseModel->getAllPurchases();

            $data = [
                'farmers' => $farmers,
                'statistics' => $statistics,
                'recent_purchases' => array_slice($recentPurchases, 0, 5),
                'page_title' => 'Farmer Management Dashboard'
            ];

            $this->view('farmers_admin/dashboard', $data);
        } catch (\Throwable $e) {
            error_log("FarmerController::index() error: " . $e->getMessage());
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Display all farmers
     */
    public function list()
    {
        try {
            $farmers = $this->farmerModel->getAllFarmers();
            $statistics = $this->farmerModel->getFarmerStatistics();

            $data = [
                'farmers' => $farmers,
                'statistics' => $statistics,
                'page_title' => 'All Farmers'
            ];

            $this->view('farmers_admin/list', $data);
        } catch (\Throwable $e) {
            error_log("FarmerController::list() error: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Show create farmer form
     */
    public function create()
    {
        try {
            // Get states and districts for dropdown
            $states = $this->getStates();
            $districts = $this->getDistricts();

            $data = [
                'states' => $states,
                'districts' => $districts,
                'page_title' => 'Add New Farmer'
            ];

            $this->view('farmers_admin/create', $data);
        } catch (\Throwable $e) {
            error_log("FarmerController::create() error: " . $e->getMessage());
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Store new farmer
     */
    public function store()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }

            $data = [
                'name' => Security::sanitize($_POST['name']) ?? '',
                'email' => Security::sanitize($_POST['email']) ?? '',
                'phone' => Security::sanitize($_POST['phone']) ?? '',
                'address' => Security::sanitize($_POST['address']) ?? '',
                'state_id' => Security::sanitize($_POST['state_id']) ?? null,
                'district_id' => Security::sanitize($_POST['district_id']) ?? null,
                'aadhar_number' => Security::sanitize($_POST['aadhar_number']) ?? '',
                'pan_number' => Security::sanitize($_POST['pan_number']) ?? '',
                'bank_account' => Security::sanitize($_POST['bank_account']) ?? '',
                'ifsc_code' => Security::sanitize($_POST['ifsc_code']) ?? '',
                'status' => 'active'
            ];

            // Validate required fields
            $this->validateFarmerData($data);

            $farmerId = $this->farmerModel->createFarmer($data);

            if ($farmerId) {
                $this->setFlash('success', 'Farmer created successfully!');
                $this->redirect('/farmers_admin/list');
            } else {
                throw new \Exception('Failed to create farmer');
            }
        } catch (\Throwable $e) {
            error_log("FarmerController::store() error: " . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/farmers_admin/create');
        }
    }

    // ============================================================
    // MISSING ACTIONS (routes existed, methods did not)
    // ============================================================

    /**
     * Form: add a land holding for a farmer.
     * GET /farmers/{id}/add-land-holding
     */
    public function addLandHolding($id)
    {
        try {
            $farmer = $this->farmerModel->getFarmerById((int)$id);
            if (!$farmer) {
                throw new \Exception('Farmer not found');
            }

            return $this->render('farmer_portal/land_holding_form', [
                'page_title' => 'Add Land Holding - ' . ($farmer['name'] ?? ''),
                'farmer'     => $farmer,
                'farmer_id'  => (int)$id,
            ]);
        } catch (\Throwable $e) {
            error_log('FarmerController::addLandHolding() error: ' . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/farmers_admin/list');
        }
    }

    /**
     * Store a land holding for a farmer.
     * POST /farmers/{id}/store-land-holding
     */
    public function storeLandHolding($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }

            $farmerId = (int)$id;
            $farmer = $this->farmerModel->getFarmerById($farmerId);
            if (!$farmer) {
                throw new \Exception('Farmer not found');
            }

            $data = [
                'tenant_id'            => (int)(\App\Core\Middleware\TenantContext::getId() ?: 1),
                'farmer_id'            => $farmerId,
                'khasra_number'        => Security::sanitize($_POST['khasra_number'] ?? '') ?: null,
                'land_area'            => (float)($_POST['land_area'] ?? 0),
                'land_area_unit'       => Security::sanitize($_POST['land_area_unit'] ?? 'acre') ?: 'acre',
                'land_type'            => Security::sanitize($_POST['land_type'] ?? '') ?: null,
                'soil_type'            => Security::sanitize($_POST['soil_type'] ?? '') ?: null,
                'irrigation_source'    => Security::sanitize($_POST['irrigation_source'] ?? '') ?: null,
                'water_source'         => Security::sanitize($_POST['water_source'] ?? '') ?: null,
                'electricity_available' => (int)($_POST['electricity_available'] ?? 0) ? 1 : 0,
                'road_access'          => Security::sanitize($_POST['road_access'] ?? '') ?: null,
                'location'             => Security::sanitize($_POST['location'] ?? '') ?: null,
                'village'              => Security::sanitize($_POST['village'] ?? '') ?: null,
                'tehsil'               => Security::sanitize($_POST['tehsil'] ?? '') ?: null,
                'district'             => Security::sanitize($_POST['district'] ?? '') ?: null,
                'state'                => Security::sanitize($_POST['state'] ?? '') ?: null,
                'land_value'           => (float)($_POST['land_value'] ?? 0),
                'current_status'       => Security::sanitize($_POST['current_status'] ?? 'owned') ?: 'owned',
                'ownership_document'   => Security::sanitize($_POST['ownership_document'] ?? '') ?: null,
                'mutation_document'    => Security::sanitize($_POST['mutation_document'] ?? '') ?: null,
                'acquisition_status'   => Security::sanitize($_POST['acquisition_status'] ?? 'none') ?: 'none',
                'acquisition_date'     => Security::sanitize($_POST['acquisition_date'] ?? '') ?: null,
                'acquisition_amount'   => (float)($_POST['acquisition_amount'] ?? 0),
                'payment_status'       => Security::sanitize($_POST['payment_status'] ?? 'pending') ?: 'pending',
                'payment_received'     => (float)($_POST['payment_received'] ?? 0),
                'remarks'              => Security::sanitize($_POST['remarks'] ?? '') ?: null,
                'created_at'           => date('Y-m-d H:i:s'),
                'updated_at'           => date('Y-m-d H:i:s'),
            ];

            $db = \App\Core\Database\Database::getInstance();
            $db->insert('farmer_land_holdings', $data);

            $this->setFlash('success', 'Land holding added successfully');
            $this->redirect('/farmers_admin/' . $farmerId);
        } catch (\Throwable $e) {
            error_log('FarmerController::storeLandHolding() error: ' . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/farmers/' . (int)$id . '/add-land-holding');
        }
    }

    /**
     * Form: record a land purchase by a farmer.
     * GET /farmers/{id}/add-purchase
     */
    public function addPurchase($id)
    {
        try {
            $farmer = $this->farmerModel->getFarmerById((int)$id);
            if (!$farmer) {
                throw new \Exception('Farmer not found');
            }

            return $this->render('farmer_portal/purchase_form', [
                'page_title' => 'Record Land Purchase - ' . ($farmer['name'] ?? ''),
                'farmer'     => $farmer,
                'farmer_id'  => (int)$id,
            ]);
        } catch (\Throwable $e) {
            error_log('FarmerController::addPurchase() error: ' . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/farmers_admin/list');
        }
    }

    /**
     * Store a land purchase transaction for a farmer.
     * POST /farmers/{id}/store-purchase
     */
    public function storePurchase($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }

            $farmerId = (int)$id;
            $farmer = $this->farmerModel->getFarmerById($farmerId);
            if (!$farmer) {
                throw new \Exception('Farmer not found');
            }

            $amount = (float)($_POST['amount'] ?? 0);
            if ($amount <= 0) {
                throw new \Exception('Purchase amount must be greater than zero');
            }

            $db = \App\Core\Database\Database::getInstance();
            $db->insert('farmer_transactions', [
                'tenant_id'         => (int)(\App\Core\Middleware\TenantContext::getId() ?: 1),
                'farmer_id'         => $farmerId,
                'transaction_type'  => 'land_purchase',
                'amount'            => $amount,
                'payment_method'    => Security::sanitize($_POST['payment_method'] ?? 'bank_transfer') ?: 'bank_transfer',
                'reference_number' => Security::sanitize($_POST['reference_number'] ?? '') ?: null,
                'transaction_date'  => Security::sanitize($_POST['transaction_date'] ?? date('Y-m-d')) ?: date('Y-m-d'),
                'description'      => Security::sanitize($_POST['description'] ?? '') ?: null,
                'status'            => Security::sanitize($_POST['status'] ?? 'completed') ?: 'completed',
                'created_by'        => (int)($_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0),
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);

            $this->setFlash('success', 'Land purchase recorded successfully');
            $this->redirect('/farmers_admin/' . $farmerId);
        } catch (\Throwable $e) {
            error_log('FarmerController::storePurchase() error: ' . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/farmers/' . (int)$id . '/add-purchase');
        }
    }

    /**
     * Show farmer details
     */
    public function show($id)
    {
        try {
            $farmer = $this->farmerModel->getFarmerById($id);
            $landHoldings = $this->landHoldingModel->getLandHoldingsByFarmer($id);

            if (!$farmer) {
                throw new \Exception('Farmer not found');
            }

            $data = [
                'farmer' => $farmer,
                'land_holdings' => $landHoldings,
                'page_title' => 'Farmer Details - ' . $farmer['name']
            ];

            $this->view('farmers_admin/show', $data);
        } catch (\Throwable $e) {
            error_log("FarmerController::show() error: " . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/farmers_admin/list');
        }
    }

    /**
     * Show farmer details
     */
    public function edit($id)
    {
        try {
            $farmer = $this->farmerModel->getFarmerById($id);
            $states = $this->getStates();
            $districts = $this->getDistricts();

            if (!$farmer) {
                throw new \Exception('Farmer not found');
            }

            $data = [
                'farmer' => $farmer,
                'states' => $states,
                'districts' => $districts,
                'page_title' => 'Edit Farmer - ' . $farmer['name']
            ];

            $this->view('farmers_admin/edit', $data);
        } catch (\Throwable $e) {
            error_log("FarmerController::edit() error: " . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/farmers_admin/list');
        }
    }

    /**
     * Update farmer
     */
    public function update($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }

            $data = [
                'name' => Security::sanitize($_POST['name']) ?? '',
                'email' => Security::sanitize($_POST['email']) ?? '',
                'phone' => Security::sanitize($_POST['phone']) ?? '',
                'address' => Security::sanitize($_POST['address']) ?? '',
                'state_id' => Security::sanitize($_POST['state_id']) ?? null,
                'district_id' => Security::sanitize($_POST['district_id']) ?? null,
                'aadhar_number' => Security::sanitize($_POST['aadhar_number']) ?? '',
                'pan_number' => Security::sanitize($_POST['pan_number']) ?? '',
                'bank_account' => Security::sanitize($_POST['bank_account']) ?? '',
                'ifsc_code' => Security::sanitize($_POST['ifsc_code']) ?? ''
            ];

            // Validate required fields
            $this->validateFarmerData($data);

            $result = $this->farmerModel->updateFarmer($id, $data);

            if ($result) {
                $this->setFlash('success', 'Farmer updated successfully!');
                $this->redirect('/farmers_admin/list');
            } else {
                throw new \Exception('Failed to update farmer');
            }
        } catch (\Throwable $e) {
            error_log("FarmerController::update() error: " . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect("/farmers_admin/{$id}/edit");
        }
    }

    /**
     * Delete farmer
     */
    public function delete($id)
    {
        try {
            $result = $this->farmerModel->deleteFarmer($id);

            if ($result) {
                $this->setFlash('success', 'Farmer deleted successfully!');
            } else {
                throw new \Exception('Failed to delete farmer');
            }
        } catch (\Throwable $e) {
            error_log("FarmerController::delete() error: " . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
        }

        $this->redirect('/farmers_admin/list');
    }

    /**
     * Search farmers
     */
    public function search()
    {
        try {
            $searchTerm = $_GET['q'] ?? '';
            $farmers = $this->farmerModel->searchFarmers($searchTerm);

            $data = [
                'farmers' => $farmers,
                'search_term' => $searchTerm,
                'page_title' => 'Search Results - Farmers'
            ];

            $this->view('farmers_admin/search', $data);
        } catch (\Throwable $e) {
            error_log("FarmerController::search() error: " . $e->getMessage());
            $this->handleError($e->getMessage());
        }
    }

    /**
     * Get farmers by state (AJAX)
     */
    public function getByState($stateId)
    {
        try {
            $farmers = $this->farmerModel->getFarmersByState($stateId);
            $this->jsonResponse($farmers);
        } catch (\Throwable $e) {
            $this->jsonError($e->getMessage());
        }
    }

    /**
     * Validate farmer data
     */
    private function validateFarmerData($data)
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }

        if (empty($data['phone'])) {
            $errors[] = 'Phone number is required';
        }

        if (empty($data['address'])) {
            $errors[] = 'Address is required';
        }

        if (empty($data['state_id'])) {
            $errors[] = 'State is required';
        }

        if (empty($data['district_id'])) {
            $errors[] = 'District is required';
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }
    }

    /**
     * Get states for dropdown
     */
    private function getStates()
    {
        $stmt = $this->db->prepare("SELECT * FROM states ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get districts for dropdown
     */
    private function getDistricts()
    {
        $stmt = $this->db->prepare("SELECT * FROM districts ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Handle errors
     */
    private function handleError($message)
    {
        $this->setFlash('error', $message);
        // Show error inline instead of redirecting to avoid redirect loops
        echo '<div class="alert alert-danger m-4">Error: ' . htmlspecialchars($message) . '</div>';
        exit;
    }
}
