<?php

namespace App\Services;

use App\Core\Database;
use PDO;

/**
 * FormSelectDataService
 * 
 * Provides common methods to fetch data for form select dropdowns
 * Eliminates repetitive code across controllers for getting dropdown options
 */
class FormSelectDataService
{
    /**
     * Get customers for select dropdown
     * 
     * @param array $filters Optional filters (status, etc.)
     * @return array
     */
    public static function getCustomers($filters = [])
    {
        try {
            $db = Database::getInstance();
            $where = ["role = 'customer'"];
            $params = [];
            
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT id, name, email FROM users {$whereClause} ORDER BY name ASC";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getCustomers: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get associates for select dropdown
     * 
     * @param array $filters Optional filters (status, etc.)
     * @return array
     */
    public static function getAssociates($filters = [])
    {
        try {
            $db = Database::getInstance();
            $where = ["role = 'associate'"];
            $params = [];
            
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            } else {
                // Default to active associates
                $where[] = "status = 'active'";
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT id, name, email FROM users {$whereClause} ORDER BY name ASC";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getAssociates: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get agents (admin, support, associate) for select dropdown
     * 
     * @param array $filters Optional filters (status, roles, etc.)
     * @return array
     */
    public static function getAgents($filters = [])
    {
        try {
            $db = Database::getInstance();
            $where = ["role IN ('admin', 'support', 'associate', 'manager')"];
            $params = [];
            
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            } else {
                $where[] = "status = 'active'";
            }
            
            if (!empty($filters['roles'])) {
                $roles = is_array($filters['roles']) ? $filters['roles'] : [$filters['roles']];
                $placeholders = implode(',', array_fill(0, count($roles), '?'));
                $where[0] = "role IN ($placeholders)";
                $params = array_merge($params, $roles);
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT id, name, email, role FROM users {$whereClause} ORDER BY name ASC";
            
            $stmt = $db->prepare($sql);
            $paramIndex = 1;
            foreach ($params as $val) {
                $stmt->bindValue($paramIndex++, $val);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getAgents: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get properties for select dropdown
     * 
     * @param array $filters Optional filters (status, type, featured, etc.)
     * @param array $columns Columns to select (default: id, title, location)
     * @return array
     */
    public static function getProperties($filters = [], $columns = ['id', 'title', 'location'])
    {
        try {
            $db = Database::getInstance();
            $columnList = implode(', ', $columns);
            $where = ["1=1"];
            $params = [];
            
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['type'])) {
                $where[] = "type = :type";
                $params['type'] = $filters['type'];
            }
            
            if (isset($filters['featured']) && $filters['featured'] !== '') {
                $where[] = "featured = :featured";
                $params['featured'] = (int)$filters['featured'];
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT {$columnList} FROM properties {$whereClause} ORDER BY title ASC";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getProperties: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get plots for select dropdown
     * 
     * @param array $filters Optional filters (status, colony_id, etc.)
     * @param array $columns Columns to select (default: id, plot_number, area)
     * @return array
     */
    public static function getPlots($filters = [], $columns = ['id', 'plot_number', 'area'])
    {
        try {
            $db = Database::getInstance();
            $columnList = implode(', ', $columns);
            $where = ["is_active = 1"];
            $params = [];
            
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['colony_id'])) {
                $where[] = "colony_id = :colony_id";
                $params['colony_id'] = $filters['colony_id'];
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT {$columnList} FROM plots {$whereClause} ORDER BY plot_number ASC";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getPlots: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get projects/colonies for select dropdown
     * 
     * @param array $filters Optional filters (status, state_id, district_id, etc.)
     * @param array $columns Columns to select (default: id, name, location)
     * @return array
     */
    public static function getProjects($filters = [], $columns = ['id', 'name', 'location'])
    {
        try {
            $db = Database::getInstance();
            $columnList = implode(', ', $columns);
            $where = ["is_active = 1"];
            $params = [];
            
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }
            
            if (!empty($filters['state_id'])) {
                $where[] = "state_id = :state_id";
                $params['state_id'] = $filters['state_id'];
            }
            
            if (!empty($filters['district_id'])) {
                $where[] = "district_id = :district_id";
                $params['district_id'] = $filters['district_id'];
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT {$columnList} FROM colonies {$whereClause} ORDER BY name ASC";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getProjects: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get states for select dropdown
     * 
     * @param array $filters Optional filters (is_active, etc.)
     * @param array $columns Columns to select (default: id, name)
     * @return array
     */
    public static function getStates($filters = [], $columns = ['id', 'name'])
    {
        try {
            $db = Database::getInstance();
            $columnList = implode(', ', $columns);
            $where = [];
            $params = [];
            
            if (!isset($filters['is_active']) || $filters['is_active'] !== false) {
                $where[] = "is_active = 1";
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql = "SELECT {$columnList} FROM states {$whereClause} ORDER BY name ASC";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getStates: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get districts for select dropdown
     * 
     * @param array $filters Optional filters (state_id, is_active, etc.)
     * @param bool $withStateName Include state name in result
     * @param array $columns Columns to select (default: id, name)
     * @return array
     */
    public static function getDistricts($filters = [], $withStateName = false, $columns = ['id', 'name'])
    {
        try {
            $db = Database::getInstance();
            
            if ($withStateName) {
                $columnList = 'd.id, d.name, d.state_id, s.name as state_name';
                $sql = "SELECT {$columnList} FROM districts d 
                        LEFT JOIN states s ON d.state_id = s.id";
            } else {
                $columnList = implode(', ', array_map(fn($col) => "d.$col", $columns));
                $sql = "SELECT {$columnList} FROM districts d";
            }
            
            $where = [];
            $params = [];
            
            if (!isset($filters['is_active']) || $filters['is_active'] !== false) {
                $where[] = "d.is_active = 1";
            }
            
            if (!empty($filters['state_id'])) {
                $where[] = "d.state_id = :state_id";
                $params['state_id'] = $filters['state_id'];
            }
            
            $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
            $sql .= " {$whereClause} ORDER BY s.name, d.name ASC";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getDistricts: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get all states with their districts (nested array)
     * 
     * @param array $filters Optional filters
     * @return array Array of states with nested districts
     */
    public static function getStatesWithDistricts($filters = [])
    {
        try {
            $states = self::getStates($filters);
            
            foreach ($states as &$state) {
                $districts = self::getDistricts(['state_id' => $state['id']], false);
                $state['districts'] = $districts;
            }
            
            return $states;
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getStatesWithDistricts: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get employees for select dropdown
     * 
     * @param array $filters Optional filters (status, department, etc.)
     * @return array
     */
    public static function getEmployees($filters = [])
    {
        try {
            $db = Database::getInstance();
            $where = ["role = 'employee'"];
            $params = [];
            
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            } else {
                $where[] = "status = 'active'";
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT id, name, email FROM users {$whereClause} ORDER BY name ASC";
            
            $stmt = $db->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue(':' . $key, $val);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getEmployees: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get users by role for select dropdown
     * 
     * @param string|array $roles Role(s) to fetch
     * @param array $filters Optional filters (status, etc.)
     * @return array
     */
    public static function getUsersByRole($roles, $filters = [])
    {
        try {
            $db = Database::getInstance();
            $roleArray = is_array($roles) ? $roles : [$roles];
            $placeholders = implode(',', array_fill(0, count($roleArray), '?'));
            
            $where = ["role IN ($placeholders)"];
            $params = $roleArray;
            
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = :status";
                $params['status'] = $filters['status'];
            }
            
            $whereClause = 'WHERE ' . implode(' AND ', $where);
            $sql = "SELECT id, name, email, role FROM users {$whereClause} ORDER BY name ASC";
            
            $stmt = $db->prepare($sql);
            $paramIndex = 1;
            foreach ($roleArray as $role) {
                $stmt->bindValue($paramIndex++, $role);
            }
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $stmt->bindValue(':' . 'status', $filters['status']);
            }
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('Error in FormSelectDataService::getUsersByRole: ' . $e->getMessage());
            return [];
        }
    }
}
