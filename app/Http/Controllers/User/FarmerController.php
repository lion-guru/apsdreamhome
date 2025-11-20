<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Farmer;
use App\Models\FarmerLandHolding;
use App\Models\LandPurchase;
use App\Models\Database;
use PDO;

/**
 * Farmer Controller
 * Handles all farmer management operations
 */
class FarmerController extends Controller
{
    private $farmerModel;
    private $landHoldingModel;
    private $purchaseModel;
    private $db;

    public function __construct()
    {
        parent::__construct();
        $this->farmerModel = new Farmer();
        $this->landHoldingModel = new FarmerLandHolding();
        $this->purchaseModel = new LandPurchase();
        $this->db = Database::getInstance()->getConnection();
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
        } catch (\Exception $e) {
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
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'state_id' => $_POST['state_id'] ?? null,
                'district_id' => $_POST['district_id'] ?? null,
                'aadhar_number' => $_POST['aadhar_number'] ?? '',
                'pan_number' => $_POST['pan_number'] ?? '',
                'bank_account' => $_POST['bank_account'] ?? '',
                'ifsc_code' => $_POST['ifsc_code'] ?? '',
                'status' => 'active'
            ];

            // Validate required fields
            $this->validateFarmerData($data);

            $farmerId = $this->farmerModel->createFarmer($data);

            if ($farmerId) {
                $this->setFlashMessage('success', 'Farmer created successfully!');
                $this->redirect('/farmers');
            } else {
                throw new \Exception('Failed to create farmer');
            }
        } catch (\Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
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
        } catch (\Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/farmers');
        }
    }

    /**
     * Show edit farmer form
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
        } catch (\Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
            $this->redirect('/farmers');
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
                'name' => $_POST['name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'address' => $_POST['address'] ?? '',
                'state_id' => $_POST['state_id'] ?? null,
                'district_id' => $_POST['district_id'] ?? null,
                'aadhar_number' => $_POST['aadhar_number'] ?? '',
                'pan_number' => $_POST['pan_number'] ?? '',
                'bank_account' => $_POST['bank_account'] ?? '',
                'ifsc_code' => $_POST['ifsc_code'] ?? ''
            ];

            // Validate required fields
            $this->validateFarmerData($data);

            $result = $this->farmerModel->updateFarmer($id, $data);

            if ($result) {
                $this->setFlashMessage('success', 'Farmer updated successfully!');
                $this->redirect('/farmers');
            } else {
                throw new \Exception('Failed to update farmer');
            }
        } catch (\Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
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
                $this->setFlashMessage('success', 'Farmer deleted successfully!');
            } else {
                throw new \Exception('Failed to delete farmer');
            }
        } catch (\Exception $e) {
            $this->setFlashMessage('error', $e->getMessage());
        }

        $this->redirect('/farmers');
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
        } catch (\Exception $e) {
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get districts for dropdown
     */
    private function getDistricts()
    {
        $stmt = $this->db->prepare("SELECT * FROM districts ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Handle errors
     */
    private function handleError($message)
    {
        $this->setFlashMessage('error', $message);
        $this->redirect('/farmers');
    }
}
