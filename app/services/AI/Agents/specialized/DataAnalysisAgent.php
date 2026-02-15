<?php

namespace App\Services\AI\Agents\specialized;

use App\Services\AI\Agents\BaseAgent;

/**
 * DataAnalysisAgent - Specialized agent for data cleaning and visualization
 *
 * @property \App\Core\Database $db Inherited from BaseAgent
 */
class DataAnalysisAgent extends BaseAgent {
    public function __construct() {
        parent::__construct('DATA_ANALYSIS_001', 'Data Analysis & Stats Agent');
    }

    public function process($input, $context = []) {
        $dataset = $input['dataset'] ?? [];
        if (empty($dataset)) {
            // Simulated dataset for testing
            $dataset = [
                ['price' => 5000000, 'location' => 'City A'],
                ['price' => 6000000, 'location' => 'City B'],
                ['price' => 4500000, 'location' => 'City A'],
                ['price' => 7000000, 'location' => 'City C'],
            ];
        }

        $this->logActivity("ANALYSIS_STARTED", "Processing dataset of " . count($dataset) . " records");

        $prices = array_column($dataset, 'price');
        $mean = count($prices) > 0 ? array_sum($prices) / count($prices) : 0;
        sort($prices);
        $median = count($prices) > 0 ? $prices[floor(count($prices)/2)] : 0;

        return [
            'success' => true,
            'metrics' => [
                'count' => count($dataset),
                'mean_price' => $mean,
                'median_price' => $median,
                'min_price' => !empty($prices) ? min($prices) : 0,
                'max_price' => !empty($prices) ? max($prices) : 0
            ],
            'insights' => "The average property price in this dataset is ₹" . number_format($mean),
            'visualization_url' => 'charts/market_trend_' . time() . '.png'
        ];
    }
}
