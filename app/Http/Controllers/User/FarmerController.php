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

            $this->view('farmers/dashboard', $data);
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

            $this->view('farmers/list', $data);
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

            $this->view('farmers/create', $data);
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
                $this->redirect('/farmers/list');
            } else {
                throw new \Exception('Failed to create farmer');
            }
        } catch (\Throwable $e) {
            error_log("FarmerController::store() error: " . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/farmers/create');
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

            $this->view('farmers/show', $data);
        } catch (\Throwable $e) {
            error_log("FarmerController::show() error: " . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/farmers/list');
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

            $this->view('farmers/edit', $data);
        } catch (\Throwable $e) {
            error_log("FarmerController::edit() error: " . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/farmers/list');
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
                $this->redirect('/farmers/list');
            } else {
                throw new \Exception('Failed to update farmer');
            }
        } catch (\Throwable $e) {
            error_log("FarmerController::update() error: " . $e->getMessage());
            $this->setFlash('error', $e->getMessage());
            $this->redirect("/farmers/{$id}/edit");
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

        $this->redirect('/farmers/list');
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

            $this->view('farmers/search', $data);
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
        } catch (\Exception $e) {
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
