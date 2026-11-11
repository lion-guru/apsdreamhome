<?php
/**
 * SmartSearchService — Semantic property search using RAG embeddings
 */

namespace App\Services;

use App\Core\Database\Database;
use App\Core\Config\Config;
use App\Services\AI\RAGService;

class SmartSearchService
{
    private $db;
    private $ragManager;

    public function __construct()
    {
        $this->db = Database::getInstance();
        
        if (class_exists('App\Services\AI\RAGService')) {
            $this->ragManager = RAGService::getInstance();
        }
    }

    /**
     * Search properties using semantic search
     */
    public function searchProperties(string $query, array $filters = [], int $limit = 10): array
    {
        $results = [];

        // 1. Try semantic search via RAG if available
        if ($this->ragManager) {
            try {
                $ragResults = $this->ragManager->search($query, $limit);
                foreach ($ragResults as $result) {
                    if (isset($result['metadata']['property_id'])) {
                        $results[] = [
                            'type' => 'rag',
                            'property_id' => $result['metadata']['property_id'],
                            'score' => $result['score'],
                            'source' => 'rag',
                            'snippet' => substr($result['content'], 0, 200),
                        ];
                    }
                }
            } catch (\Throwable $e) {
                error_log('RAG search failed: ' . $e->getMessage());
            }
        }

        // 2. Fallback to database keyword search with scoring
        $dbResults = $this->searchDatabase($query, $filters, $limit);
        foreach ($dbResults as $result) {
            $results[] = array_merge($result, ['source' => 'database']);
        }

        // 3. Deduplicate and sort by score
        $results = $this->deduplicateAndSort($results, $limit);

        return $results;
    }

    /**
     * Database keyword search with relevance scoring
     */
    private function searchDatabase(string $query, array $filters, int $limit): array
    {
        $keywords = $this->extractKeywords($query);
        if (empty($keywords)) return [];

        $whereConditions = [];
        $params = [];

        // Build search conditions for each keyword (real schema: plots has no
        // name/city/state; colonies has location not city; no plot_types table)
        $searchFields = [
            'p.plot_number', 'p.description', 'p.facing',
            'c.name', 'c.location',
            'p.plot_type'
        ];

        foreach ($keywords as $keyword) {
            $like = "%$keyword%";
            $conditions = [];
            foreach ($searchFields as $field) {
                $conditions[] = "$field LIKE ?";
                $params[] = $like;
            }
            $whereConditions[] = '(' . implode(' OR ', $conditions) . ')';
        }

        // Apply filters (real columns: total_price, plot_type enum, location)
        if (!empty($filters['city'])) {
            $whereConditions[] = "c.location = ?";
            $params[] = $filters['city'];
        }
        if (!empty($filters['min_price'])) {
            $whereConditions[] = "p.total_price >= ?";
            $params[] = $filters['min_price'];
        }
        if (!empty($filters['max_price'])) {
            $whereConditions[] = "p.total_price <= ?";
            $params[] = $filters['max_price'];
        }
        if (!empty($filters['plot_type'])) {
            $whereConditions[] = "p.plot_type = ?";
            $params[] = $filters['plot_type'];
        }
        if (!empty($filters['status'])) {
            $whereConditions[] = "p.status = ?";
            $params[] = $filters['status'];
        }

        $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

        $sql = "
            SELECT 
                p.id, p.plot_number as name, p.description, p.total_price as base_price, p.status, p.plot_number,
                p.area_sqft as plot_size, p.facing, p.road_width_ft,
                c.id as colony_id, c.name as colony_name, c.location as city, c.id as state,
                p.plot_type, p.plot_type as plot_type_desc,
                0 as image_count
            FROM plots p
            LEFT JOIN colonies c ON p.colony_id = c.id
            $whereClause
            ORDER BY p.created_at DESC
            LIMIT ?
        ";

        $params[] = $limit * 2; // Get more for scoring

        $results = $this->db->fetchAll($sql, $params);

        // Score results based on keyword matches
        $scoredResults = [];
        foreach ($results as $row) {
            $score = $this->calculateRelevanceScore($row, $keywords);
            if ($score > 0) {
                $scoredResults[] = array_merge($row, [
                    'relevance_score' => $score,
                ]);
            }
        }

        // Sort by score descending
        usort($scoredResults, fn($a, $b) => $b['relevance_score'] <=> $a['relevance_score']);

        return array_slice($scoredResults, 0, $limit);
    }

    /**
     * Extract meaningful keywords from query
     */
    private function extractKeywords(string $query): array
    {
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'from', 'up', 'about', 'into', 'through', 'during', 'before', 'after', 'above', 'below', 'between', 'among', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her', 'us', 'them', 'my', 'your', 'his', 'her', 'its', 'our', 'their', 'this', 'that', 'these', 'those', 'am', 'are', 'is', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'what', 'which', 'who', 'whom', 'whose', 'where', 'when', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 'just', 'now', 'then', 'here', 'there', 'when', 'where', 'why', 'how', 'all', 'any', 'both', 'each', 'few', 'more', 'most', 'other', 'some', 'such', 'no', 'nor', 'not', 'only', 'own', 'same', 'so', 'than', 'too', 'very', 'just', 'now', 'then', 'here', 'there', 'plot', 'plots', 'property', 'properties', 'colony', 'colonies', 'buy', 'sell', 'rent', 'lease', 'book', 'booking', 'emi', 'loan', 'price', 'cost', 'budget', 'area', 'size', 'sqft', 'sq', 'ft', 'feet', 'meter', 'meter', 'acre', 'bigha', 'katha', 'decimal'];

        $words = preg_split('/[\s,.;:!?()]+/', strtolower($query), -1, PREG_SPLIT_NO_EMPTY);
        $keywords = [];

        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) >= 2 && !in_array($word, $stopWords)) {
                $keywords[] = $word;
            }
        }

        return array_unique($keywords);
    }

    /**
     * Calculate relevance score for a property
     */
    private function calculateRelevanceScore(array $row, array $keywords): float
    {
        $score = 0;
        $text = implode(' ', [
            $row['name'] ?? '',
            $row['description'] ?? '',
            $row['colony_name'] ?? '',
            $row['plot_type'] ?? '',
            $row['plot_type_desc'] ?? '',
            $row['city'] ?? '',
            $row['state'] ?? '',
        ]);

        $textLower = strtolower($text);

        foreach ($keywords as $keyword) {
            $count = substr_count($textLower, strtolower($keyword));
            if ($count > 0) {
                // Weight matches in name/title higher
                $weight = 1.0;
                if (stripos($row['name'] ?? '', $keyword) !== false) $weight = 3.0;
                if (stripos($row['colony_name'] ?? '', $keyword) !== false) $weight = 2.0;
                $score += $count * $weight;
            }
        }

        // Boost for exact phrase matches
        $queryLower = strtolower(implode(' ', $keywords));
        if (stripos($textLower, $queryLower) !== false) {
            $score += 5.0;
        }

        return $score;
    }

    /**
     * Deduplicate and sort results
     */
    private function deduplicateAndSort(array $results, int $limit): array
    {
        $seen = [];
        $unique = [];

        foreach ($results as $result) {
            $key = $result['property_id'] ?? ($result['id'] ?? '');
            if (!$key) continue;
            
            if (!isset($seen[$key]) || ($result['relevance_score'] ?? $result['score'] ?? 0) > ($seen[$key]['relevance_score'] ?? $seen[$key]['score'] ?? 0)) {
                $seen[$key] = $result;
            }
        }

        $unique = array_values($seen);
        
        usort($unique, function($a, $b) {
            $scoreA = $a['relevance_score'] ?? $a['score'] ?? 0;
            $scoreB = $b['relevance_score'] ?? $b['score'] ?? 0;
            return $scoreB <=> $scoreA;
        });

        return array_slice($unique, 0, $limit);
    }

    /**
     * Get search suggestions/autocomplete
     */
    public function getSuggestions(string $partial, int $limit = 5): array
    {
        $partial = strtolower(trim($partial));
        if (strlen($partial) < 2) return [];

        $suggestions = [];

        // Colony names
        $colonies = $this->db->fetchAll(
            "SELECT name FROM colonies WHERE name LIKE ? AND status = 'active' LIMIT ?",
            ["$partial%", $limit]
        );
        foreach ($colonies as $c) {
            $suggestions[] = ['type' => 'colony', 'text' => $c['name']];
        }

        // Property codes (plots has plot_number/plot_code, no name)
        $properties = $this->db->fetchAll(
            "SELECT plot_number as name FROM plots WHERE plot_number LIKE ? AND status IN ('available','booked') LIMIT ?",
            ["$partial%", $limit]
        );
        foreach ($properties as $p) {
            $suggestions[] = ['type' => 'property', 'text' => $p['name']];
        }

        // Locations (colonies.location, not city)
        $cities = $this->db->fetchAll(
            "SELECT DISTINCT location as city FROM colonies WHERE location LIKE ? AND status = 'active' LIMIT ?",
            ["$partial%", $limit]
        );
        // filters() uses distinct city query below (same fix there)
        foreach ($cities as $c) {
            $suggestions[] = ['type' => 'city', 'text' => $c['city']];
        }

        // Plot types (plots.plot_type enum, no plot_types table)
        $types = $this->db->fetchAll(
            "SELECT DISTINCT plot_type as name FROM plots WHERE plot_type LIKE ? LIMIT ?",
            ["$partial%", $limit]
        );
        foreach ($types as $t) {
            $suggestions[] = ['type' => 'type', 'text' => $t['name']];
        }

        return array_slice($suggestions, 0, $limit);
    }
}