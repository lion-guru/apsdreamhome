<?php
/**
 * Location API Controller
 * For smart autocomplete and cascading dropdowns
 */

namespace App\Http\Controllers\Api;

class LocationController extends BaseApiController
{
    public function __construct()
    {
        parent::__construct();
        $this->db = \App\Core\Database\Database::getInstance();
    }
    
    /**
     * Get all countries
     * GET /api/locations/countries
     */
    public function countries()
    {
        $search = $_GET['q'] ?? '';
        
        $sql = "SELECT id, name, iso_code, phone_code FROM countries";
        $params = [];
        
        if ($search) {
            $sql .= " AND name LIKE ?";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY name LIMIT 50";
        
        $countries = $this->db->fetchAll($sql, $params);
        
        $this->jsonResponse($countries);
    }
    
    /**
     * Get states by country
     * GET /api/locations/states?country_id=1
     */
    public function states()
    {
        $countryId = intval($_GET['country_id'] ?? 1);
        $search = $_GET['q'] ?? '';
        
        $sql = "SELECT id, name, code FROM states WHERE country_id = ?";
        $params = [$countryId];
        
        if ($search) {
            $sql .= " AND name LIKE ?";
            $params[] = "%$search%";
        }
        
        $sql .= " ORDER BY name LIMIT 100";
        
        $states = $this->db->fetchAll($sql, $params);
        
        $this->jsonResponse($states);
    }
    
    /**
     * Get districts by state
     * GET /api/locations/districts?state_id=1
     */
    public function districts()
    {
        $stateId = intval($_GET['state_id'] ?? 0);
        $search = $_GET['q'] ?? '';
        
        if (!$stateId) {
            $this->jsonResponse([]);
        }
        
        try {
            $sql = "SELECT id, name FROM districts WHERE state_id = ?";
            $params = [$stateId];
            
            if ($search) {
                $sql .= " AND name LIKE ?";
                $params[] = "%$search%";
            }
            
            $sql .= " ORDER BY name LIMIT 100";
            
            $districts = $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            $districts = [];
        }
        
        $this->jsonResponse($districts);
    }
    
    /**
     * Get cities by district
     * GET /api/locations/cities?district_id=1
     */
    public function cities()
    {
        $districtId = intval($_GET['district_id'] ?? 0);
        $stateId = intval($_GET['state_id'] ?? 0);
        $search = $_GET['q'] ?? '';
        $type = $_GET['type'] ?? '';
        
        if (!$districtId && !$stateId) {
            $this->jsonResponse([]);
        }
        
        try {
            $sql = "SELECT c.id, c.name, c.type, d.name as district_name 
                    FROM cities c 
                    LEFT JOIN districts d ON c.district_id = d.id
                    WHERE 1=1";
            $params = [];
            
            if ($districtId) {
                $sql .= " AND c.district_id = ?";
                $params[] = $districtId;
            }
            
            if ($stateId) {
                $sql .= " AND c.district_id IN (SELECT id FROM districts WHERE state_id = ?)";
                $params[] = $stateId;
            }
            
            if ($search) {
                $sql .= " AND c.name LIKE ?";
                $params[] = "%$search%";
            }
            
            if ($type) {
                $sql .= " AND c.type = ?";
                $params[] = $type;
            }
            
            $sql .= " ORDER BY c.name LIMIT 100";
            
            $cities = $this->db->fetchAll($sql, $params);
        } catch (\Exception $e) {
            $cities = [];
        }
        
        $this->jsonResponse($cities);
    }
    
    /**
     * Search cities (global search)
     * GET /api/locations/search?q=city_name
     */
    public function search()
    {
        $search = $_GET['q'] ?? '';
        
        if (strlen($search) < 2) {
            $this->jsonResponse([]);
        }
        
        try {
            $sql = "SELECT c.id, c.name as city, c.type, d.name as district, s.name as state, co.name as country,
                           CONCAT(c.name, ', ', d.name, ', ', s.name, ', ', co.name) as full_address
                    FROM cities c
                    LEFT JOIN districts d ON c.district_id = d.id
                    LEFT JOIN states s ON d.state_id = s.id
                    LEFT JOIN countries co ON s.country_id = co.id
                    WHERE c.name LIKE ?
                    ORDER BY c.name
                    LIMIT 50";
            
            $results = $this->db->fetchAll($sql, ["%$search%"]);
        } catch (\Exception $e) {
            $results = [];
        }
        
        $this->jsonResponse($results);
    }
    
    /**
     * Lookup by Pincode
     * GET /api/locations/pincode/{pincode}
     */
    public function byPincode($pincode)
    {
        if (empty($pincode) || !preg_match('/^\d{4,10}$/', $pincode)) {
            $this->jsonResponse(['found' => false, 'error' => true, 'message' => 'Valid pincode required (4-10 digits)']);
        }
        
        try {
            $result = $this->db->fetch(
                "SELECT pincode, area_name FROM pincodes WHERE pincode = ? AND is_active = 1 LIMIT 1",
                [$pincode]
            );
        } catch (\Exception $e) {
            $result = null;
        }
        
        if ($result) {
            $this->jsonResponse([
                'found' => true,
                'pincode' => $result['pincode'],
                'area' => $result['area_name'],
                'message' => 'Pincode found'
            ]);
        } else {
            $this->jsonResponse([
                'found' => false,
                'pincode' => $pincode,
                'message' => 'Pincode not found. Please enter details manually.',
                'manual_entry_required' => true
            ]);
        }
    }
    
    /**
     * Search pincodes
     * GET /api/locations/pincodes?q=search
     */
    public function pincodes()
    {
        $search = $_GET['q'] ?? '';
        
        if (strlen($search) < 2) {
            $this->jsonResponse([]);
        }
        
        try {
            $sql = "SELECT p.pincode, p.area_name,
                           c.name as city, d.name as district, s.name as state
                    FROM pincodes p
                    LEFT JOIN cities c ON p.city_id = c.id
                    LEFT JOIN districts d ON p.district_id = d.id
                    LEFT JOIN states s ON p.state_id = s.id
                    WHERE p.is_active = 1 AND (p.pincode LIKE ? OR p.area_name LIKE ?)
                    ORDER BY p.pincode
                    LIMIT 50";
            
            $results = $this->db->fetchAll($sql, ["%$search%", "%$search%"]);
        } catch (\Exception $e) {
            $results = [];
        }
        
        $this->jsonResponse($results);
    }
    
    /**
     * Helper: JSON response
     */
    public function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Helper: Error response
     */
    private function errorResponse($message, $code = 400)
    {
        $this->jsonResponse(['error' => true, 'message' => $message], $code);
    }
}
