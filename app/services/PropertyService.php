<?php
namespace App\Services;

class PropertyService 
{
    private $db;
    
    public function __construct() {
        $this->db = new PDO("mysql:host=localhost;port=3307;dbname=apsdreamhome;charset=utf8mb4", "root", "");
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    
    public function getProperties($filters = [], $limit = 20, $offset = 0) {
        $sql = "SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name 
                FROM plots p 
                LEFT JOIN colonies c ON p.colony_id = c.id 
                LEFT JOIN districts d ON c.district_id = d.id 
                LEFT JOIN states s ON d.state_id = s.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['colony_id'])) {
            $sql .= " AND p.colony_id = :colony_id";
            $params[':colony_id'] = $filters['colony_id'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND p.total_price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND p.total_price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        if (!empty($filters['min_area'])) {
            $sql .= " AND p.area_sqft >= :min_area";
            $params[':min_area'] = $filters['min_area'];
        }
        
        if (!empty($filters['max_area'])) {
            $sql .= " AND p.area_sqft <= :max_area";
            $params[':max_area'] = $filters['max_area'];
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProperty($id) {
        $stmt = $this->db->prepare("SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name 
                                     FROM plots p 
                                     LEFT JOIN colonies c ON p.colony_id = c.id 
                                     LEFT JOIN districts d ON c.district_id = d.id 
                                     LEFT JOIN states s ON d.state_id = s.id 
                                     WHERE p.id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getColonies($filters = []) {
        $sql = "SELECT c.*, d.name as district_name, s.name as state_name 
                FROM colonies c 
                LEFT JOIN districts d ON c.district_id = d.id 
                LEFT JOIN states s ON d.state_id = s.id 
                WHERE c.is_active = 1";
        
        $params = [];
        
        if (!empty($filters['district_id'])) {
            $sql .= " AND c.district_id = :district_id";
            $params[':district_id'] = $filters['district_id'];
        }
        
        $sql .= " ORDER BY c.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getProjects($filters = []) {
        $sql = "SELECT * FROM projects WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['project_type'])) {
            $sql .= " AND project_type = :project_type";
            $params[':project_type'] = $filters['project_type'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params[':status'] = $filters['status'];
        }
        
        $sql .= " ORDER BY created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getResellProperties($filters = []) {
        $sql = "SELECT * FROM resell_properties WHERE listing_status = 'active'";
        
        $params = [];
        
        if (!empty($filters['property_type'])) {
            $sql .= " AND property_type = :property_type";
            $params[':property_type'] = $filters['property_type'];
        }
        
        if (!empty($filters['min_price'])) {
            $sql .= " AND expected_price >= :min_price";
            $params[':min_price'] = $filters['min_price'];
        }
        
        if (!empty($filters['max_price'])) {
            $sql .= " AND expected_price <= :max_price";
            $params[':max_price'] = $filters['max_price'];
        }
        
        $sql .= " ORDER BY featured DESC, listing_date DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStates() {
        $stmt = $this->db->prepare("SELECT * FROM states WHERE is_active = 1 ORDER BY name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getDistricts($state_id = null) {
        $sql = "SELECT d.*, s.name as state_name 
                FROM districts d 
                LEFT JOIN states s ON d.state_id = s.id 
                WHERE d.is_active = 1";
        
        if ($state_id) {
            $sql .= " AND d.state_id = :state_id";
        }
        
        $sql .= " ORDER BY d.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($state_id ? [':state_id' => $state_id] : []);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function searchProperties($query, $filters = []) {
        $sql = "SELECT p.*, c.name as colony_name, d.name as district_name, s.name as state_name 
                FROM plots p 
                LEFT JOIN colonies c ON p.colony_id = c.id 
                LEFT JOIN districts d ON c.district_id = d.id 
                LEFT JOIN states s ON d.state_id = s.id 
                WHERE (p.plot_number LIKE :query OR c.name LIKE :query OR d.name LIKE :query OR s.name LIKE :query)";
        
        $params = [':query' => '%' . $query . '%'];
        
        // Add other filters
        if (!empty($filters['colony_id'])) {
            $sql .= " AND p.colony_id = :colony_id";
            $params[':colony_id'] = $filters['colony_id'];
        }
        
        $sql .= " ORDER BY p.created_at DESC LIMIT 50";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>