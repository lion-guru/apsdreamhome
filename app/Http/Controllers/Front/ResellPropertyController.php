<?php
namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\ResellPropertyService;
use App\Services\NotificationService;
use App\Traits\TenantAwareTrait;

class ResellPropertyController extends BaseController
{
    use TenantAwareTrait;
    public function __construct() { parent::__construct(); }

    private function resell(): ResellPropertyService { return new ResellPropertyService($this->db); }
    private function notif(): NotificationService { return new NotificationService($this->db); }

    public function index()
    {
        $properties = $this->resell()->listProperties($_GET, 30);
        $this->data = array_merge($this->data, [
            'page_title' => 'Resell Properties - APS Dream Home',
            'page_heading' => 'Resell Properties Marketplace',
            'properties' => $properties,
        ]);
        return $this->render('pages/resell_properties_public', $this->data);
    }

    public function show($id)
    {
        $property = $this->resell()->getProperty((int)$id);
        $valuation = null;
        if ($property) {
            $this->resell()->recordAnalytics((int)$id, 'view', ['source' => 'public']);
            $valuation = $this->resell()->getLatestValuation((int)$id);
        }
        $this->data = array_merge($this->data, [
            'page_title' => ($property['title'] ?? 'Property') . ' - APS Dream Home',
            'page_heading' => 'Property Details',
            'property' => $property,
            'valuation' => $valuation,
        ]);
        return $this->render('pages/resell_property_detail', $this->data);
    }

    public function submit()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = (int)($_SESSION['user_id'] ?? 0);
            if (!$userId) {
                $_SESSION['error'] = 'Please login to list a property';
                return $this->render('auth/customer_login', $this->data);
            }
            $data = $_POST;
            $data['user_id'] = $userId;
            $result = $this->resell()->createProperty($data);
            if (!empty($result['ok'])) {
                $this->notif()->send($userId, 'email', 'Property Listed Successfully', 'Your property "' . ($data['title'] ?? 'Untitled') . '" has been submitted for review.', ['property_id' => $result['id']]);
                $_SESSION['success'] = 'Property submitted! It will be visible after admin approval.';
                header('Location: ' . BASE_URL . '/user/properties');
                exit;
            }
            $_SESSION['error'] = $result['error'] ?? 'Failed to submit property';
        }
        $this->data = array_merge($this->data, [
            'page_title' => 'List Resell Property',
            'page_heading' => 'Submit Your Property for Resale',
        ]);
        return $this->render('pages/resell_property_submit', $this->data);
    }
}
