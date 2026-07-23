<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;
use Exception;

class PropertyController extends BaseController
{
    protected function skipCsrfProtection(): bool
    {
        return true;
    }

    public function index()
    {
        header('Content-Type: application/json');
        try {
            $limit = (int)($_GET['limit'] ?? 50);
            $offset = (int)($_GET['offset'] ?? 0);
            $type = $_GET['type'] ?? '';
            $status = $_GET['status'] ?? 'active';

            $sql = "SELECT id, title, description, price, location, city, type, status,
                           featured, area_sqft, bedrooms, bathrooms, property_type_id, created_at
                    FROM properties WHERE status = ?";
            $params = [$status];

            if ($type) {
                $sql .= " AND type = ?";
                $params[] = $type;
            }

            $sql .= " ORDER BY id DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;

            $rows = $this->db->fetchAll($sql, $params);

            echo json_encode([
                'success' => true,
                'data' => $rows,
                'count' => count($rows),
            ]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function properties()
    {
        // Browse properties
    }

    public function property($id)
    {
        // Property detail
    }

    public function search()
    {
        // Search properties
    }

    public function similar($id)
    {
        // Similar properties
    }

    public function colonyProperties($colonyId)
    {
        // Colony properties
    }

    public function getFeatured()
    {
        // Featured properties
    }

    public function marketplace()
    {
        // Marketplace
    }

    public function premium()
    {
        // Premium properties
    }

    public function getTypes()
    {
        // Property types
    }

    public function getCities()
    {
        // Cities
    }
}