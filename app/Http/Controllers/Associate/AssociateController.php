<?php

namespace App\Http\Controllers\Associate;

use App\Services\Associate\AssociateService;
use App\Http\Controllers\BaseController;

/**
 * Associate Controller - APS Dream Home
 * Associate management and relationship tracking
 * Custom MVC implementation without Laravel dependencies
 */
class AssociateController extends BaseController
{
    private $associateService;

    public function __construct()
    {
        $this->associateService = new AssociateService();
    }

    /**
     * Display associate dashboard
     */
    public function dashboard()
    {
        try {
            $associates = $this->associateService->getAllAssociates();
            $activeAssociates = $this->associateService->getActiveAssociates();
            
            $data = [
                'page_title' => 'Associate Dashboard - APS Dream Home',
                'associates' => $associates,
                'active_associates' => $activeAssociates,
                'total_associates' => count($associates),
                'active_count' => count($activeAssociates)
            ];
            
            $this->render('associate/dashboard', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading associate dashboard', $e->getMessage());
        }
    }

    /**
     * Display associate list
     */
    public function index()
    {
        try {
            $associates = $this->associateService->getAllAssociates();
            
            $data = [
                'page_title' => 'Associates - APS Dream Home',
                'associates' => $associates,
                'total_count' => count($associates)
            ];
            
            $this->render('associate/index', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading associates', $e->getMessage());
        }
    }

    /**
     * Display create associate form
     */
    public function create()
    {
        $data = [
            'page_title' => 'Create Associate - APS Dream Home',
            'action' => '/associates/store'
        ];
        
        $this->render('associate/create', $data);
    }

    /**
     * Store new associate
     */
    public function store()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'address' => $_POST['address'] ?? '',
                    'commission_rate' => $_POST['commission_rate'] ?? 0,
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                // Validate required fields
                if (empty($data['name']) || empty($data['email'])) {
                    throw new Exception('Name and email are required');
                }
                
                $associateId = $this->associateService->createAssociate($data);
                
                if ($associateId) {
                    header('Location: /associates');
                    exit;
                } else {
                    throw new Exception('Failed to create associate');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error creating associate', $e->getMessage());
        }
    }

    /**
     * Display edit associate form
     */
    public function edit($id)
    {
        try {
            $associate = $this->associateService->getAssociateById($id);
            
            if (!$associate) {
                $this->renderError('Associate not found', 'Associate with ID ' . $id . ' not found');
                return;
            }
            
            $data = [
                'page_title' => 'Edit Associate - APS Dream Home',
                'associate' => $associate,
                'action' => '/associates/update/' . $id
            ];
            
            $this->render('associate/edit', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading associate', $e->getMessage());
        }
    }

    /**
     * Update associate
     */
    public function update($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = [
                    'name' => $_POST['name'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'phone' => $_POST['phone'] ?? '',
                    'address' => $_POST['address'] ?? '',
                    'commission_rate' => $_POST['commission_rate'] ?? 0,
                    'status' => $_POST['status'] ?? 'active'
                ];
                
                // Validate required fields
                if (empty($data['name']) || empty($data['email'])) {
                    throw new Exception('Name and email are required');
                }
                
                $result = $this->associateService->updateAssociate($id, $data);
                
                if ($result) {
                    header('Location: /associates');
                    exit;
                } else {
                    throw new Exception('Failed to update associate');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error updating associate', $e->getMessage());
        }
    }

    /**
     * Delete associate
     */
    public function delete($id)
    {
        try {
            $result = $this->associateService->deleteAssociate($id);
            
            if ($result) {
                header('Location: /associates');
                exit;
            } else {
                throw new Exception('Failed to delete associate');
            }
        } catch (Exception $e) {
            $this->renderError('Error deleting associate', $e->getMessage());
        }
    }

    /**
     * Display associate details
     */
    public function show($id)
    {
        try {
            $associate = $this->associateService->getAssociateById($id);
            $metrics = $this->associateService->getAssociateMetrics($id, date('Y-m-01'), date('Y-m-t'));
            $salesHistory = $this->associateService->getAssociateSalesHistory($id);
            
            if (!$associate) {
                $this->renderError('Associate not found', 'Associate with ID ' . $id . ' not found');
                return;
            }
            
            $data = [
                'page_title' => 'Associate Details - APS Dream Home',
                'associate' => $associate,
                'metrics' => $metrics,
                'sales_history' => $salesHistory
            ];
            
            $this->render('associate/show', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading associate details', $e->getMessage());
        }
    }

    /**
     * Display associate performance metrics
     */
    public function metrics($id)
    {
        try {
            $associate = $this->associateService->getAssociateById($id);
            
            if (!$associate) {
                $this->renderError('Associate not found', 'Associate with ID ' . $id . ' not found');
                return;
            }
            
            $startDate = $_GET['start_date'] ?? date('Y-m-01');
            $endDate = $_GET['end_date'] ?? date('Y-m-t');
            
            $metrics = $this->associateService->getAssociateMetrics($id, $startDate, $endDate);
            
            $data = [
                'page_title' => 'Associate Metrics - APS Dream Home',
                'associate' => $associate,
                'metrics' => $metrics,
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
            
            $this->render('associate/metrics', $data);
        } catch (Exception $e) {
            $this->renderError('Error loading associate metrics', $e->getMessage());
        }
    }

    /**
     * Update associate status
     */
    public function updateStatus($id)
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $status = $_POST['status'] ?? 'active';
                
                $result = $this->associateService->updateAssociateStatus($id, $status);
                
                if ($result) {
                    header('Location: /associates');
                    exit;
                } else {
                    throw new Exception('Failed to update associate status');
                }
            }
        } catch (Exception $e) {
            $this->renderError('Error updating associate status', $e->getMessage());
        }
    }
}
