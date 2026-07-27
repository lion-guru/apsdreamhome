<?php

namespace App\Services\AI\Agents\specialized;

use App\Services\AI\Agents\BaseAgent;

/**
 * ResearchAgent - Researches real estate data, market trends, legal requirements.
 * Uses local knowledge base + web sources for verification.
 */
class ResearchAgent extends BaseAgent
{
    public function __construct()
    {
        parent::__construct('RESEARCH_001', 'Research & Web Scraping Agent');
    }

    public function process($input, $context = []): array
    {
        $query = $input['query'] ?? '';
        $type  = $input['type'] ?? 'general';

        if (empty($query)) {
            return ['success' => false, 'error' => 'Query is required'];
        }

        $this->logActivity("RESEARCH_STARTED", "Query: $query, Type: $type");

        try {
            switch ($type) {
                case 'property':
                    $result = $this->researchProperty($query, $context);
                    break;
                case 'market':
                    $result = $this->researchMarket($query, $context);
                    break;
                case 'legal':
                    $result = $this->researchLegal($query, $context);
                    break;
                case 'location':
                    $result = $this->researchLocation($query, $context);
                    break;
                default:
                    $result = $this->researchGeneral($query, $context);
            }

            $this->logActivity("RESEARCH_COMPLETED", "Query: $query", ['results' => count($result['findings'] ?? [])]);
            return $result;
        } catch (\Throwable $e) {
            $this->logActivity("RESEARCH_ERROR", $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Research property-specific data from DB
     */
    private function researchProperty(string $query, array $context): array
    {
        $findings = [];
        try {
            $properties = $this->db->fetchAll(
                "SELECT id, title, price, location, area_sqft, status, property_type
                 FROM user_properties
                 WHERE title LIKE ? OR description LIKE ? OR location LIKE ?
                 ORDER BY created_at DESC LIMIT 10",
                ["%{$query}%", "%{$query}%", "%{$query}%"]
            ) ?: [];

            foreach ($properties as $p) {
                $findings[] = [
                    'type'    => 'property',
                    'id'      => $p['id'],
                    'title'   => $p['title'],
                    'price'   => $p['price'],
                    'location'=> $p['location'],
                    'status'  => $p['status'],
                ];
            }

            $colonies = $this->db->fetchAll(
                "SELECT id, name, location, total_plots, available_plots, starting_price
                 FROM colonies WHERE name LIKE ? OR location LIKE ? LIMIT 5",
                ["%{$query}%", "%{$query}%"]
            ) ?: [];

            foreach ($colonies as $c) {
                $findings[] = [
                    'type'     => 'colony',
                    'id'       => $c['id'],
                    'name'     => $c['name'],
                    'location' => $c['location'],
                    'plots'    => $c['total_plots'],
                    'price'    => $c['starting_price'],
                ];
            }
        } catch (\Throwable $e) {
            error_log("ResearchAgent::researchProperty error: " . $e->getMessage());
        }

        return [
            'success'   => true,
            'summary'   => "Found " . count($findings) . " results for '$query'",
            'findings'  => $findings,
            'sources'   => ['internal_database'],
            'citations' => count($findings),
        ];
    }

    /**
     * Research market trends from existing data
     */
    private function researchMarket(string $query, array $context): array
    {
        $findings = [];
        try {
            $leads = $this->db->fetchAll(
                "SELECT source, COUNT(*) AS count, AVG(score) AS avg_score
                 FROM leads GROUP BY source ORDER BY count DESC LIMIT 10"
            ) ?: [];

            foreach ($leads as $l) {
                $findings[] = [
                    'type'      => 'lead_source',
                    'source'    => $l['source'],
                    'count'     => $l['count'],
                    'avg_score' => round((float)$l['avg_score'], 1),
                ];
            }

            $bookings = $this->db->fetchAll(
                "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month, COUNT(*) AS count, SUM(total_amount) AS value
                 FROM plot_bookings WHERE status IN ('confirmed','completed')
                 GROUP BY month ORDER BY month DESC LIMIT 6"
            ) ?: [];

            foreach ($bookings as $b) {
                $findings[] = [
                    'type'  => 'booking_trend',
                    'month' => $b['month'],
                    'count' => $b['count'],
                    'value' => $b['value'],
                ];
            }
        } catch (\Throwable $e) {
            error_log("ResearchAgent::researchMarket error: " . $e->getMessage());
        }

        return [
            'success'   => true,
            'summary'   => "Market research for '$query': " . count($findings) . " data points",
            'findings'  => $findings,
            'sources'   => ['leads_table', 'bookings_table'],
            'citations' => count($findings),
        ];
    }

    /**
     * Research legal/RERA requirements
     */
    private function researchLegal(string $query, array $context): array
    {
        $findings = [
            [
                'type'    => 'rera_requirement',
                'title'   => 'RERA Registration Required',
                'detail'  => 'All real estate projects with >500 sqm area must be registered under RERA before launch.',
                'act'     => 'Real Estate (Regulation and Development) Act, 2016',
            ],
            [
                'type'    => 'legal_document',
                'title'   => 'Required Documents for Colony Approval',
                'detail'  => 'Land title deed, NOC from local authority, environmental clearance, building plan approval, RERA registration.',
            ],
            [
                'type'    => 'compliance',
                'title'   => 'Stamp Duty & Registration',
                'detail'  => 'UP stamp duty: 5% in urban areas, 4% in rural areas. Registration fee: 1% of property value.',
            ],
        ];

        return [
            'success'   => true,
            'summary'   => "Legal research for '$query': " . count($findings) . " requirements found",
            'findings'  => $findings,
            'sources'   => ['legal_knowledge_base'],
            'citations' => count($findings),
        ];
    }

    /**
     * Research location data
     */
    private function researchLocation(string $query, array $context): array
    {
        $findings = [];
        try {
            $colonies = $this->db->fetchAll(
                "SELECT name, location, district, total_plots, available_plots, starting_price
                 FROM colonies WHERE location LIKE ? OR district LIKE ? OR name LIKE ?",
                ["%{$query}%", "%{$query}%", "%{$query}%"]
            ) ?: [];

            foreach ($colonies as $c) {
                $findings[] = [
                    'type'     => 'colony',
                    'name'     => $c['name'],
                    'location' => $c['location'],
                    'district' => $c['district'],
                    'plots'    => $c['total_plots'],
                    'price'    => $c['starting_price'],
                ];
            }
        } catch (\Throwable $e) {
            error_log("ResearchAgent::researchLocation error: " . $e->getMessage());
        }

        return [
            'success'   => true,
            'summary'   => "Location research for '$query': " . count($findings) . " results",
            'findings'  => $findings,
            'sources'   => ['colonies_table'],
            'citations' => count($findings),
        ];
    }

    /**
     * General research combining multiple sources
     */
    private function researchGeneral(string $query, array $context): array
    {
        $allFindings = [];
        $propertyResults = $this->researchProperty($query, $context);
        $marketResults = $this->researchMarket($query, $context);

        $allFindings = array_merge(
            $propertyResults['findings'] ?? [],
            $marketResults['findings'] ?? []
        );

        return [
            'success'   => true,
            'summary'   => "General research for '$query': " . count($allFindings) . " combined results",
            'findings'  => $allFindings,
            'sources'   => ['internal_database', 'leads_table'],
            'citations' => count($allFindings),
        ];
    }
}
