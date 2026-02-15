<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\BaseController;
use App\Models\Property;
use App\Models\Sale;
use Exception;

class PropertyPurchaseController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('auth');
    }

    /**
     * Show the property purchase form
     */
    public function create($id = null)
    {
        if (!$id) {
            $id = $this->input('id');
        }

        if (!$id) {
            $this->redirect('properties');
            return;
        }

        $propertyModel = new Property();
        $property = $propertyModel->getPropertyById($id);

        if (!$property) {
            $this->notFound('Property not found');
            return;
        }

        // Get available agents
        $agents = $this->db->fetchAll("SELECT uid as id, uname as name, uemail as email FROM user WHERE utype = 'agent' AND status = 'active'");

        // Check if user is MLM associate
        $is_associate = $this->db->fetch("SELECT * FROM associates WHERE user_id = ?", [$_SESSION['user_id']]);

        $this->data['property'] = $property;
        $this->data['agents'] = $agents;
        $this->data['is_associate'] = (bool)$is_associate;
        $this->data['page_title'] = "Property Purchase - " . h($property['title']);

        return $this->render('pages/property_purchase');
    }

    /**
     * Process the property purchase
     */
    public function store()
    {
        $propertyId = $this->input('property_id');
        $saleAmount = floatval($this->input('sale_amount'));
        $agentId = $this->input('agent_id') ?: null;
        $buyerId = $_SESSION['user_id'];

        try {
            $saleModel = new Sale();
            $result = $saleModel->processPropertySale($propertyId, $buyerId, $saleAmount, $agentId);

            if ($result['success']) {
                // Invalidate dashboard cache
                if (function_exists('getPerformanceManager')) {
                    getPerformanceManager()->clearCache('query_');
                }

                $this->success($result['message']);
                $this->redirect('property/purchase-success/' . $propertyId);
            } else {
                $this->error($result['message']);
                $this->redirect('property/purchase/' . $propertyId);
            }
        } catch (Exception $e) {
            $this->error('Error processing purchase: ' . $e->getMessage());
            $this->redirect('property/purchase/' . $propertyId);
        }
    }

    /**
     * Show success page
     */
    public function success_page($id)
    {
        $propertyModel = new Property();
        $property = $propertyModel->getPropertyById($id);

        if (!$property) {
            $this->notFound('Property not found');
            return;
        }

        $this->data['property'] = $property;
        $this->data['page_title'] = 'Purchase Successful - ' . h($property['title']);
        return $this->render('pages/property_sale_success');
    }
}
