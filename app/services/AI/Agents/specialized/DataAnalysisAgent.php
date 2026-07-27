<?php

namespace App\Services\AI\Agents\specialized;

use App\Services\AI\Agents\BaseAgent;

/**
 * DataAnalysisAgent - Real statistical analysis and data visualization from actual DB data.
 * Generates insights from leads, bookings, commissions, and property data.
 */
class DataAnalysisAgent extends BaseAgent
{
    public function __construct()
    {
        parent::__construct('DATA_ANALYSIS_001', 'Data Analysis & Stats Agent');
    }

    public function process($input, $context = []): array
    {
        $dataset = $input['dataset'] ?? [];
        $analysisType = $input['type'] ?? 'general';

        $this->logActivity("ANALYSIS_STARTED", "Type: $analysisType, Records: " . count($dataset));

        try {
            switch ($analysisType) {
                case 'leads':
                    $result = $this->analyzeLeads();
                    break;
                case 'bookings':
                    $result = $this->analyzeBookings();
                    break;
                case 'revenue':
                    $result = $this->analyzeRevenue();
                    break;
                case 'properties':
                    $result = $this->analyzeProperties();
                    break;
                case 'mlm':
                    $result = $this->analyzeMLM();
                    break;
                default:
                    $result = !empty($dataset) ? $this->analyzeDataset($dataset) : $this->analyzeOverview();
            }

            $this->logActivity("ANALYSIS_COMPLETED", "Type: $analysisType");
            return $result;
        } catch (\Throwable $e) {
            $this->logActivity("ANALYSIS_ERROR", $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Analyze leads pipeline
     */
    private function analyzeLeads(): array
    {
        $pipeline = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS count, AVG(score) AS avg_score
             FROM leads GROUP BY status ORDER BY count DESC"
        ) ?: [];

        $sources = $this->db->fetchAll(
            "SELECT source, COUNT(*) AS count,
                    COUNT(CASE WHEN status = 'won' THEN 1 END) AS won
             FROM leads GROUP BY source ORDER BY count DESC"
        ) ?: [];

        $scoreDist = $this->db->fetchAll(
            "SELECT
                COUNT(CASE WHEN score >= 80 THEN 1 END) AS hot,
                COUNT(CASE WHEN score >= 50 AND score < 80 THEN 1 END) AS warm,
                COUNT(CASE WHEN score < 50 THEN 1 END) AS cold
             FROM leads"
        ) ?: [0 => ['hot' => 0, 'warm' => 0, 'cold' => 0]];

        $total = array_sum(array_column($pipeline, 'count'));

        return [
            'success'    => true,
            'total'      => $total,
            'pipeline'   => $pipeline,
            'sources'    => $sources,
            'score_dist' => $scoreDist[0] ?? ['hot' => 0, 'warm' => 0, 'cold' => 0],
            'metrics'    => [
                'total_leads' => $total,
                'won'         => $this->countByStatus($pipeline, 'won'),
                'conversion'  => $total > 0 ? round(($this->countByStatus($pipeline, 'won') / $total) * 100, 1) : 0,
                'avg_score'   => $this->avgColumn($pipeline, 'avg_score'),
            ],
        ];
    }

    /**
     * Analyze bookings
     */
    private function analyzeBookings(): array
    {
        $monthly = $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                    COUNT(*) AS count, SUM(total_amount) AS value
             FROM plot_bookings
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
             GROUP BY month ORDER BY month ASC"
        ) ?: [];

        $statusDist = $this->db->fetchAll(
            "SELECT status, COUNT(*) AS count, SUM(total_amount) AS value
             FROM plot_bookings GROUP BY status"
        ) ?: [];

        $totalBookings = array_sum(array_column($statusDist, 'count'));
        $totalValue = array_sum(array_column($statusDist, 'value'));

        return [
            'success'       => true,
            'monthly'       => $monthly,
            'status_dist'   => $statusDist,
            'total_bookings'=> $totalBookings,
            'total_value'   => $totalValue,
            'avg_value'     => $totalBookings > 0 ? round($totalValue / $totalBookings) : 0,
        ];
    }

    /**
     * Analyze revenue streams
     */
    private function analyzeRevenue(): array
    {
        $byType = $this->db->fetchAll(
            "SELECT type, COUNT(*) AS count, SUM(amount) AS total
             FROM mlm_commission_ledger
             GROUP BY type ORDER BY total DESC"
        ) ?: [];

        $monthly = $this->db->fetchAll(
            "SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                    SUM(amount) AS total
             FROM mlm_commission_ledger
             WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY month ORDER BY month ASC"
        ) ?: [];

        $totalRevenue = array_sum(array_column($byType, 'total'));

        return [
            'success'        => true,
            'by_type'        => $byType,
            'monthly'        => $monthly,
            'total_revenue'  => $totalRevenue,
            'avg_per_type'   => count($byType) > 0 ? round($totalRevenue / count($byType)) : 0,
        ];
    }

    /**
     * Analyze properties
     */
    private function analyzeProperties(): array
    {
        $byStatus = $this->db->fetchAll(
            "SELECT COALESCE(status, 'unknown') AS status, COUNT(*) AS count
             FROM user_properties GROUP BY status ORDER BY count DESC"
        ) ?: [];

        $byType = $this->db->fetchAll(
            "SELECT COALESCE(property_type, 'unknown') AS type, COUNT(*) AS count,
                    AVG(price) AS avg_price
             FROM user_properties GROUP BY type ORDER BY count DESC"
        ) ?: [];

        $priceRanges = $this->db->fetchAll(
            "SELECT
                COUNT(CASE WHEN price < 500000 THEN 1 END) AS under_5l,
                COUNT(CASE WHEN price >= 500000 AND price < 2000000 THEN 1 END) AS `5l_to_20l`,
                COUNT(CASE WHEN price >= 2000000 AND price < 5000000 THEN 1 END) AS `20l_to_50l`,
                COUNT(CASE WHEN price >= 5000000 THEN 1 END) AS above_50l
             FROM user_properties WHERE price > 0"
        ) ?: [0 => []];

        $total = array_sum(array_column($byStatus, 'count'));

        return [
            'success'       => true,
            'total'         => $total,
            'by_status'     => $byStatus,
            'by_type'       => $byType,
            'price_ranges'  => $priceRanges[0] ?? [],
        ];
    }

    /**
     * Analyze MLM network performance
     */
    private function analyzeMLM(): array
    {
        $topEarners = $this->db->fetchAll(
            "SELECT u.name, SUM(l.amount) AS total_earned, COUNT(l.id) AS entries
             FROM mlm_commission_ledger l
             JOIN users u ON u.id = l.user_id
             GROUP BY l.user_id ORDER BY total_earned DESC LIMIT 10"
        ) ?: [];

        $byRank = $this->db->fetchAll(
            "SELECT u.role, COUNT(DISTINCT l.user_id) AS users, SUM(l.amount) AS total
             FROM mlm_commission_ledger l
             JOIN users u ON u.id = l.user_id
             GROUP BY u.role ORDER BY total DESC"
        ) ?: [];

        $totalPayouts = $this->db->fetch(
            "SELECT SUM(amount) AS total, COUNT(*) AS entries FROM mlm_commission_ledger"
        ) ?: ['total' => 0, 'entries' => 0];

        return [
            'success'       => true,
            'top_earners'   => $topEarners,
            'by_role'       => $byRank,
            'total_payouts' => (int)($totalPayouts['total'] ?? 0),
            'total_entries' => (int)($totalPayouts['entries'] ?? 0),
        ];
    }

    /**
     * General overview across all data
     */
    private function analyzeOverview(): array
    {
        $stats = [];
        try {
            $stats['leads'] = (int)($this->db->fetch("SELECT COUNT(*) AS c FROM leads")['c'] ?? 0);
            $stats['properties'] = (int)($this->db->fetch("SELECT COUNT(*) AS c FROM user_properties")['c'] ?? 0);
            $stats['bookings'] = (int)($this->db->fetch("SELECT COUNT(*) AS c FROM plot_bookings")['c'] ?? 0);
            $stats['colonies'] = (int)($this->db->fetch("SELECT COUNT(*) AS c FROM colonies")['c'] ?? 0);
            $stats['users'] = (int)($this->db->fetch("SELECT COUNT(*) AS c FROM users")['c'] ?? 0);
            $stats['commissions'] = (int)($this->db->fetch("SELECT COALESCE(SUM(amount),0) AS c FROM mlm_commission_ledger")['c'] ?? 0);
        } catch (\Throwable $e) {
            error_log("DataAnalysisAgent::analyzeOverview error: " . $e->getMessage());
        }

        return [
            'success' => true,
            'type'    => 'overview',
            'stats'   => $stats,
            'insights'=> "System has {$stats['leads']} leads, {$stats['properties']} properties, {$stats['bookings']} bookings, and " . number_format($stats['commissions']) . " in commissions.",
        ];
    }

    /**
     * Analyze a custom dataset (statistical analysis)
     */
    private function analyzeDataset(array $dataset): array
    {
        $prices = array_column($dataset, 'price');
        $prices = array_filter($prices, fn($v) => is_numeric($v) && $v > 0);
        $prices = array_map('floatval', $prices);

        if (empty($prices)) {
            return [
                'success'  => true,
                'metrics'  => ['count' => count($dataset), 'message' => 'No numeric price data found'],
                'insights' => 'Dataset contains no analyzable price data.',
            ];
        }

        sort($prices);
        $count = count($prices);
        $sum = array_sum($prices);
        $mean = $sum / $count;
        $median = $prices[floor($count / 2)];
        $min = min($prices);
        $max = max($prices);
        $variance = array_reduce($prices, fn($carry, $v) => $carry + pow($v - $mean, 2), 0) / $count;
        $stdDev = sqrt($variance);

        return [
            'success' => true,
            'metrics' => [
                'count'       => $count,
                'mean_price'  => round($mean),
                'median_price'=> round($median),
                'min_price'   => round($min),
                'max_price'   => round($max),
                'std_dev'     => round($stdDev),
                'range'       => round($max - $min),
            ],
            'insights' => "Dataset of {$count} records: avg ₹" . number_format(round($mean))
                        . ", median ₹" . number_format(round($median))
                        . ", range ₹" . number_format(round($min)) . " — ₹" . number_format(round($max)),
        ];
    }

    private function countByStatus(array $pipeline, string $status): int
    {
        foreach ($pipeline as $row) {
            if ($row['status'] === $status) return (int)$row['count'];
        }
        return 0;
    }

    private function avgColumn(array $rows, string $column): float
    {
        $valid = array_filter(array_column($rows, $column), fn($v) => $v !== null);
        return !empty($valid) ? round(array_sum($valid) / count($valid), 1) : 0;
    }
}
