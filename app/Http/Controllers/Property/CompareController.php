<?php

namespace App\Http\Controllers\Property;

use App\Http\Controllers\Controller;
use App\Core\Database\Database;

/**
 * Property Comparison Controller
 * Handles property comparison functionality
 */
class CompareController extends Controller
{
    private $db;
    private $pdo;
    
    public function __construct()
    {
        parent::__construct();
        $this->db = Database::getInstance();
        $this->pdo = $this->db->getConnection();
    }
    
    /**
     * Show comparison page with property selection
     */
    public function index()
    {
        try {
            // Get available properties for comparison
            $properties = $this->getAvailableProperties();
            
            // Get user's saved comparison sessions if logged in
            $sessions = [];
            if (isset($_SESSION['user_id'])) {
                $sessions = $this->getUserSessions($_SESSION['user_id']);
            }
            
            $data = [
                'page_title' => 'Compare Properties - APS Dream Home',
                'properties' => $properties,
                'sessions' => $sessions,
                'max_compare' => 4,
                'min_compare' => 2
            ];
            
            return $this->render('properties/compare', $data);
            
        } catch (\Exception $e) {
            error_log("CompareController::index error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load comparison page');
            return $this->redirect('/properties');
        }
    }
    
    /**
     * Show comparison results
     */
    public function compare()
    {
        try {
            $propertyIds = $_GET['properties'] ?? [];
            
            if (count($propertyIds) < 2 || count($propertyIds) > 4) {
                $this->setFlash('error', 'Please select 2 to 4 properties to compare');
                return $this->redirect('/compare');
            }
            
            // Get property details
            $properties = $this->getPropertiesByIds($propertyIds);
            
            if (count($properties) < 2) {
                $this->setFlash('error', 'Selected properties not found');
                return $this->redirect('/compare');
            }
            
            // Save comparison session if user is logged in
            if (isset($_SESSION['user_id'])) {
                $this->saveComparisonSession($_SESSION['user_id'], $propertyIds);
            }
            
            // Get comparison features
            $comparison = $this->generateComparison($properties);
            
            $data = [
                'page_title' => 'Property Comparison Results - APS Dream Home',
                'properties' => $properties,
                'comparison' => $comparison,
                'share_url' => $this->generateShareUrl($propertyIds)
            ];
            
            return $this->render('properties/compare_results', $data);
            
        } catch (\Exception $e) {
            error_log("CompareController::compare error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to compare properties');
            return $this->redirect('/compare');
        }
    }
    
    /**
     * Save comparison for logged-in users
     */
    public function save()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Login required']);
            }
            
            $propertyIds = $_POST['property_ids'] ?? [];
            $sessionName = $_POST['session_name'] ?? 'Comparison ' . date('Y-m-d H:i');
            
            if (count($propertyIds) < 2) {
                return $this->jsonResponse(['success' => false, 'message' => 'Select at least 2 properties']);
            }
            
            $sessionId = $this->saveComparisonSession($_SESSION['user_id'], $propertyIds, $sessionName);
            
            return $this->jsonResponse([
                'success' => true,
                'message' => 'Comparison saved successfully',
                'session_id' => $sessionId
            ]);
            
        } catch (\Exception $e) {
            error_log("CompareController::save error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to save comparison']);
        }
    }
    
    /**
     * Load saved comparison
     */
    public function load($sessionId)
    {
        try {
            $session = $this->getSessionById($sessionId);
            
            if (!$session) {
                $this->setFlash('error', 'Comparison session not found');
                return $this->redirect('/compare');
            }
            
            // Get property IDs from session
            $propertyIds = $this->getSessionPropertyIds($sessionId);
            
            if (empty($propertyIds)) {
                $this->setFlash('error', 'No properties in this comparison');
                return $this->redirect('/compare');
            }
            
            // Redirect to compare with these properties
            $params = http_build_query(['properties' => $propertyIds]);
            return $this->redirect('/compare/results?' . $params);
            
        } catch (\Exception $e) {
            error_log("CompareController::load error: " . $e->getMessage());
            $this->setFlash('error', 'Failed to load comparison');
            return $this->redirect('/compare');
        }
    }
    
    /**
     * Delete saved comparison
     */
    public function delete($sessionId)
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                return $this->jsonResponse(['success' => false, 'message' => 'Login required']);
            }
            
            $sql = "DELETE FROM property_comparison_sessions 
                    WHERE id = ? AND user_id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$sessionId, $_SESSION['user_id']]);
            
            return $this->jsonResponse(['success' => true, 'message' => 'Comparison deleted']);
            
        } catch (\Exception $e) {
            error_log("CompareController::delete error: " . $e->getMessage());
            return $this->jsonResponse(['success' => false, 'message' => 'Failed to delete']);
        }
    }
    
    /**
     * Get available properties for comparison
     */
    private function getAvailableProperties()
    {
        $sql = "SELECT p.id, p.title, p.price, p.location, p.area_sqft, 
                       p.bedrooms, p.bathrooms, p.status, p.rera_status,
                       pi.image_path as primary_image
                FROM properties p
                LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
                WHERE p.status IN ('available', 'under_construction')
                ORDER BY p.created_at DESC
                LIMIT 50";
        
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get properties by IDs
     */
    private function getPropertiesByIds($propertyIds)
    {
        if (empty($propertyIds)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($propertyIds), '?'));
        
        $sql = "SELECT p.*, 
                       pi.image_path as primary_image,
                       pa.name as agent_name,
                       pa.phone as agent_phone
                FROM properties p
                LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_primary = 1
                LEFT JOIN property_agents pa ON p.agent_id = pa.id
                WHERE p.id IN ($placeholders)
                ORDER BY FIELD(p.id, $placeholders)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(array_merge($propertyIds, $propertyIds));
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Generate comparison analysis
     */
    private function generateComparison($properties)
    {
        $comparison = [
            'price_range' => [
                'min' => min(array_column($properties, 'price')),
                'max' => max(array_column($properties, 'price')),
                'avg' => array_sum(array_column($properties, 'price')) / count($properties)
            ],
            'area_range' => [
                'min' => min(array_column($properties, 'area_sqft')),
                'max' => max(array_column($properties, 'area_sqft')),
                'avg' => array_sum(array_column($properties, 'area_sqft')) / count($properties)
            ],
            'price_per_sqft' => [],
            'amenities_count' => [],
            'rera_status' => [],
            'best_value' => null,
            'best_location' => null,
            'largest_area' => null
        ];
        
        // Calculate price per sqft and find best properties
        $bestValue = null;
        $bestLocation = null;
        $largestArea = null;
        $minPricePerSqft = PHP_FLOAT_MAX;
        
        foreach ($properties as $property) {
            $pricePerSqft = $property['area_sqft'] > 0 ? $property['price'] / $property['area_sqft'] : 0;
            $comparison['price_per_sqft'][$property['id']] = round($pricePerSqft, 2);
            
            // Find best value (lowest price per sqft)
            if ($pricePerSqft > 0 && $pricePerSqft < $minPricePerSqft) {
                $minPricePerSqft = $pricePerSqft;
                $bestValue = $property['id'];
            }
            
            // Find largest area
            if ($largestArea === null || $property['area_sqft'] > $comparison['area_range']['max']) {
                $largestArea = $property['id'];
            }
        }
        
        $comparison['best_value'] = $bestValue;
        $comparison['largest_area'] = $largestArea;
        
        return $comparison;
    }
    
    /**
     * Save comparison session
     */
    private function saveComparisonSession($userId, $propertyIds, $sessionName = null)
    {
        try {
            // Create session
            $sql = "INSERT INTO property_comparison_sessions 
                    (user_id, name, created_at, expires_at) 
                    VALUES (?, ?, NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY))";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$userId, $sessionName ?? 'Comparison ' . date('Y-m-d H:i')]);
            $sessionId = $this->pdo->lastInsertId();
            
            // Save property comparisons
            foreach ($propertyIds as $index => $propertyId) {
                $sql = "INSERT INTO property_comparisons 
                        (user_id, session_id, property_id, sort_order, created_at) 
                        VALUES (?, ?, ?, ?, NOW())";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$userId, $sessionId, $propertyId, $index + 1]);
            }
            
            return $sessionId;
            
        } catch (\Exception $e) {
            error_log("CompareController::saveComparisonSession error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user's saved sessions
     */
    private function getUserSessions($userId)
    {
        $sql = "SELECT pcs.id, pcs.name, pcs.created_at,
                       COUNT(pc.id) as property_count,
                       GROUP_CONCAT(p.title SEPARATOR ' vs ') as property_names
                FROM property_comparison_sessions pcs
                LEFT JOIN property_comparisons pc ON pcs.id = pc.session_id
                LEFT JOIN properties p ON pc.property_id = p.id
                WHERE pcs.user_id = ? AND pcs.expires_at > NOW()
                GROUP BY pcs.id
                ORDER BY pcs.created_at DESC
                LIMIT 10";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get session by ID
     */
    private function getSessionById($sessionId)
    {
        $sql = "SELECT * FROM property_comparison_sessions WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get property IDs from session
     */
    private function getSessionPropertyIds($sessionId)
    {
        $sql = "SELECT property_id FROM property_comparisons 
                WHERE session_id = ? 
                ORDER BY sort_order";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
    
    /**
     * Generate share URL
     */
    private function generateShareUrl($propertyIds)
    {
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'];
        $params = http_build_query(['properties' => $propertyIds]);
        return $baseUrl . '/compare/results?' . $params;
    }
    
    /**
     * JSON response helper
     */
    private function jsonResponse($data)
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
