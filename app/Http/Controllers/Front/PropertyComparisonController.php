<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\PropertyComparisonService;
use App\Traits\TenantAwareTrait;

/**
 * Property Comparison - Side-by-side compare up to 4 properties
 */
class PropertyComparisonController extends BaseController
{
    use TenantAwareTrait;
    private $service;

    public function __construct($db = null, $auth = null, array $config = [])
    {
        parent::__construct($db, $auth, $config);
        try {
            $this->service = new PropertyComparisonService($this->db);
        } catch (\Throwable $e) {
            $this->service = null;
        }
    }

    private function getSessionId(): string
    {
        if (empty($_SESSION['comparison_session'])) {
            $_SESSION['comparison_session'] = bin2hex(random_bytes(16));
        }
        return $_SESSION['comparison_session'];
    }

    public function index()
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $comp = $this->service ? $this->service->getOrCreateActive($userId, $this->getSessionId()) : ['id' => 0, 'property_ids' => '[]'];
        $ids = json_decode($comp['property_ids'] ?? '[]', true) ?: [];
        $properties = $this->service ? $this->service->getProperties($ids) : [];
        $comparison = $this->service ? $this->service->computeComparisonData($properties) : [];
        return $this->render('pages.property_comparison.index', [
            'page_title' => 'Compare Properties Side-by-Side',
            'page_heading' => 'Property Comparison',
            'properties' => $properties,
            'comparison' => $comparison,
            'comp_id' => $comp['id'] ?? 0,
            'share_token' => $comp['share_token'] ?? '',
            'count' => count($properties)
        ]);
    }

    public function add()
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $propertyId = (int)($_POST['property_id'] ?? $_GET['property_id'] ?? 0);
        $redirect = $_POST['redirect'] ?? $_GET['redirect'] ?? '';
        if (!$this->service || !$propertyId) {
            return $this->redirect($redirect ?: (BASE_URL . '/properties'));
        }
        $comp = $this->service->getOrCreateActive($userId, $this->getSessionId());
        $result = $this->service->addProperty($comp['id'], $propertyId);
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        }
        if (!empty($result['error'])) {
            $_SESSION['comparison_error'] = $result['error'];
        }
        return $this->redirect($redirect ?: (BASE_URL . '/property-comparison'));
    }

    public function remove()
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        $propertyId = (int)($_POST['property_id'] ?? $_GET['property_id'] ?? 0);
        if ($this->service && $propertyId) {
            $comp = $this->service->getOrCreateActive($userId, $this->getSessionId());
            $this->service->removeProperty($comp['id'], $propertyId);
        }
        return $this->redirect(BASE_URL . '/property-comparison');
    }

    public function clear()
    {
        $userId = (int)($_SESSION['user_id'] ?? 0);
        if ($this->service) {
            $comp = $this->service->getOrCreateActive($userId, $this->getSessionId());
            $this->service->clear($comp['id']);
        }
        return $this->redirect(BASE_URL . '/property-comparison');
    }

    public function share()
    {
        $token = $_GET['token'] ?? '';
        if (!$this->service || !$token) {
            return $this->redirect(BASE_URL . '/property-comparison');
        }
        $comp = $this->service->getByToken($token);
        if (!$comp) {
            return $this->render('pages.property_comparison.index', [
                'page_title' => 'Comparison Not Found',
                'properties' => [],
                'comparison' => [],
                'count' => 0,
                'not_found' => true
            ]);
        }
        $ids = json_decode($comp['property_ids'] ?? '[]', true) ?: [];
        $properties = $this->service->getProperties($ids);
        $comparison = $this->service->computeComparisonData($properties);
        return $this->render('pages.property_comparison.index', [
            'page_title' => 'Shared Property Comparison',
            'properties' => $properties,
            'comparison' => $comparison,
            'count' => count($properties),
            'shared' => true,
            'view_count' => $comp['view_count'] ?? 0
        ]);
    }
}
