<?php
/**
 * SmartSearchController — REST API endpoints for semantic property search
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\SmartSearchService;

class SmartSearchController extends BaseController
{
    private $searchService;

    public function __construct()
    {
        parent::__construct();
        $this->searchService = new SmartSearchService();
    }

    protected function skipCsrfProtection(): bool
    {
        return true; // Bearer-token API; CSRF handled by auth
    }

    protected function requireAuth(array $allowedRoles = []): int {
        while (ob_get_level()) { ob_end_clean(); }
        $userId = (int)($GLOBALS['api_user_id'] ?? 0);
        if (!$userId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            exit;
        }
        if (!empty($allowedRoles)) {
            $role = $GLOBALS['api_user_role'] ?? '';
            if (!in_array($role, $allowedRoles, true)) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Forbidden']);
                exit;
            }
        }
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $GLOBALS['api_user_role'] ?? '';
        $_SESSION['role'] = $_SESSION['user_role'];
        return $userId;
    }

    /**
     * Search properties using semantic search
     * POST /api/ai/search
     * Body: { query: string, filters?: object, limit?: int }
     */
    public function search(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('Method not allowed', 405);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input || !isset($input['query']) || empty(trim($input['query']))) {
            $this->jsonError('Query is required', 400);
            return;
        }

        $query = trim($input['query']);
        $filters = $input['filters'] ?? [];
        $limit = isset($input['limit']) ? (int)$input['limit'] : 10;

        $results = $this->searchService->searchProperties($query, $filters, $limit);

        $this->jsonSuccess([
            'query' => $query,
            'results' => $results,
            'count' => count($results),
        ]);
    }

    /**
     * Get search suggestions/autocomplete
     * GET /api/ai/search/suggestions?q=partial&limit=5
     */
    public function suggestions(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $partial = $_GET['q'] ?? '';
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 5;

        if (strlen(trim($partial)) < 2) {
            $this->jsonSuccess(['suggestions' => []]);
            return;
        }

        $suggestions = $this->searchService->getSuggestions($partial, $limit);

        $this->jsonSuccess([
            'partial' => $partial,
            'suggestions' => $suggestions,
        ]);
    }

    /**
     * Get search filters metadata
     * GET /api/ai/search/filters
     */
    public function filters(): void
    {
        $this->requireAuth(['customer', 'associate', 'agent', 'employee', 'admin', 'super_admin']);
        
        $db = \App\Core\Database\Database::getInstance();
        
        // Get unique locations (colonies.location, not city)
        $cities = $db->fetchAll("SELECT DISTINCT location as city FROM colonies WHERE location IS NOT NULL AND location != '' AND status = 'active' ORDER BY location");

        // Get plot types (plots.plot_type enum, no plot_types table)
        $typesRaw = $db->fetchAll("SELECT DISTINCT plot_type as name FROM plots WHERE plot_type IS NOT NULL ORDER BY plot_type");
        $types = array_map(function($t) {
            return ['id' => $t['name'], 'name' => $t['name'], 'description' => $t['name']];
        }, $typesRaw);

        // Get price range (total_price, not base_price)
        $priceRange = $db->fetch("SELECT MIN(total_price) as min_price, MAX(total_price) as max_price FROM plots WHERE status != 'cancelled'");
        
        // Get plot statuses
        $statuses = $db->fetchAll("SELECT DISTINCT status FROM plots WHERE status IS NOT NULL");

        $this->jsonSuccess([
            'cities' => array_column($cities, 'city'),
            'plot_types' => $types,
            'price_range' => [
                'min' => (int)($priceRange['min_price'] ?? 0),
                'max' => (int)($priceRange['max_price'] ?? 0),
            ],
            'statuses' => array_column($statuses, 'status'),
        ]);
    }
}