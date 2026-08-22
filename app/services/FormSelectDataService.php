<?php

namespace App\Services;

use App\Core\Database\Database;
use PDO;

/**
 * FormSelectDataService - Centralized dropdown data provider
 * 
 * Provides static methods for form select dropdowns across all controllers.
 * Eliminates repetitive inline SQL queries.
 */
class FormSelectDataService
{
    private static function getConnection()
    {
        $db = Database::getInstance();
        return method_exists($db, 'getConnection') ? $db->getConnection() : $db;
    }

    private static function tid(): int
    {
        if (class_exists('\App\Core\Middleware\TenantContext')) {
            try {
                return (int) \App\Core\Middleware\TenantContext::getId();
            } catch (\Throwable $e) {
                return 1;
            }
        }
        return 1;
    }

    public static function getCustomers(array $filters = []): array
    {
        try {
            $conn = self::getConnection();
            $where = ["role = 'customer'"];
            $params = [];
            $tid = self::tid();
            if ($tid > 1) {
                $where[] = "tenant_id = ?";
                $params[] = $tid;
            }
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            $sql = "SELECT id, name, email FROM users WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('FormSelectDataService::getCustomers: ' . $e->getMessage());
            return [];
        }
    }

    public static function getAssociates(array $filters = []): array
    {
        try {
            $conn = self::getConnection();
            $where = ["role = 'associate'"];
            $params = [];
            $tid = self::tid();
            if ($tid > 1) {
                $where[] = "tenant_id = ?";
                $params[] = $tid;
            }
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            } else {
                $where[] = "status = 'active'";
            }
            $sql = "SELECT id, name, email FROM users WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('FormSelectDataService::getAssociates: ' . $e->getMessage());
            return [];
        }
    }

    public static function getAgents(array $filters = []): array
    {
        try {
            $conn = self::getConnection();
            $where = ["role IN ('admin', 'support', 'associate', 'manager')"];
            $params = [];
            $tid = self::tid();
            if ($tid > 1) {
                $where[] = "tenant_id = ?";
                $params[] = $tid;
            }
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            } else {
                $where[] = "status = 'active'";
            }
            $sql = "SELECT id, name, email, role FROM users WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('FormSelectDataService::getAgents: ' . $e->getMessage());
            return [];
        }
    }

    public static function getProperties(array $filters = []): array
    {
        try {
            $conn = self::getConnection();
            $where = ["1=1"];
            $params = [];
            $tid = self::tid();
            if ($tid > 1) {
                $where[] = "tenant_id = ?";
                $params[] = $tid;
            }
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            if (isset($filters['featured']) && $filters['featured'] !== '') {
                $where[] = "featured = ?";
                $params[] = (int)$filters['featured'];
            }
            $sql = "SELECT id, title, city as location FROM properties WHERE " . implode(' AND ', $where) . " ORDER BY title ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('FormSelectDataService::getProperties: ' . $e->getMessage());
            return [];
        }
    }

    public static function getPlots(array $filters = []): array
    {
        try {
            $conn = self::getConnection();
            $where = ["is_active = 1"];
            $params = [];
            $tid = self::tid();
            if ($tid > 1) {
                $where[] = "tenant_id = ?";
                $params[] = $tid;
            }
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            if (!empty($filters['colony_id'])) {
                $where[] = "colony_id = ?";
                $params[] = $filters['colony_id'];
            }
            $sql = "SELECT id, plot_number, area_sqft FROM plots WHERE " . implode(' AND ', $where) . " ORDER BY plot_number ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('FormSelectDataService::getPlots: ' . $e->getMessage());
            return [];
        }
    }

    public static function getColonies(array $filters = []): array
    {
        try {
            $conn = self::getConnection();
            $where = ["is_active = 1"];
            $params = [];
            $tid = self::tid();
            if ($tid > 1) {
                $where[] = "tenant_id = ?";
                $params[] = $tid;
            }
            $sql = "SELECT id, name, name as location FROM colonies WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('FormSelectDataService::getColonies: ' . $e->getMessage());
            return [];
        }
    }

    public static function getStates(array $filters = []): array
    {
        try {
            $conn = self::getConnection();
            $where = [];
            if (!isset($filters['is_active']) || $filters['is_active'] !== false) {
                $where[] = "is_active = 1";
            }
            $sql = "SELECT id, name FROM states" . (!empty($where) ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY name ASC";
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('FormSelectDataService::getStates: ' . $e->getMessage());
            return [];
        }
    }

    public static function getDistricts(array $filters = []): array
    {
        try {
            $conn = self::getConnection();
            $where = [];
            $params = [];
            if (!isset($filters['is_active']) || $filters['is_active'] !== false) {
                $where[] = "d.is_active = 1";
            }
            if (!empty($filters['state_id'])) {
                $where[] = "d.state_id = ?";
                $params[] = $filters['state_id'];
            }
            $sql = "SELECT d.id, d.name, d.state_id FROM districts d" . (!empty($where) ? " WHERE " . implode(' AND ', $where) : "") . " ORDER BY d.name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('FormSelectDataService::getDistricts: ' . $e->getMessage());
            return [];
        }
    }

    public static function getEmployees(array $filters = []): array
    {
        try {
            $conn = self::getConnection();
            $where = ["role = 'employee'"];
            $params = [];
            $tid = self::tid();
            if ($tid > 1) {
                $where[] = "tenant_id = ?";
                $params[] = $tid;
            }
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            } else {
                $where[] = "status = 'active'";
            }
            $sql = "SELECT id, name, email FROM users WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('FormSelectDataService::getEmployees: ' . $e->getMessage());
            return [];
        }
    }

    public static function getUsersByRole($roles, array $filters = []): array
    {
        try {
            $conn = self::getConnection();
            $roleArray = is_array($roles) ? $roles : [$roles];
            $placeholders = implode(',', array_fill(0, count($roleArray), '?'));
            $where = ["role IN ($placeholders)"];
            $params = $roleArray;
            $tid = self::tid();
            if ($tid > 1) {
                $where[] = "tenant_id = ?";
                $params[] = $tid;
            }
            if (!empty($filters['status']) && $filters['status'] !== 'all') {
                $where[] = "status = ?";
                $params[] = $filters['status'];
            }
            $sql = "SELECT id, name, email, role FROM users WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            error_log('FormSelectDataService::getUsersByRole: ' . $e->getMessage());
            return [];
        }
    }
}
