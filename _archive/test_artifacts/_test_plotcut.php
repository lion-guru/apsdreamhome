<?php
define('APP_ROOT', dirname(__DIR__));
require_once APP_ROOT . '/app/Core/Autoloader.php';

use App\Services\Land\PlotCutterService;

$landWidth  = (int) round(sqrt(165528.0 * 3.0 / 4.0) * 1.05);
$landLength = (int) round(165528.0 / max($landWidth, 1));

$config = [
    'colony_id'        => 3,
    'total_land_sqft'  => 165528,
    'land_width_ft'    => $landWidth,
    'land_length_ft'   => $landLength,
    'block_name'       => 'A',
    'road_width_ft'    => 30,
    'park_area_pct'    => 10,
    'amenity_area_pct' => 5,
    'plot_sizes'       => [
        ['width' => 30, 'length' => 40, 'area' => 1200, 'count' => 20],
    ],
    'created_by'       => 1,
];

$cutter = new PlotCutterService();
$result = $cutter->generatePlots($config);

$plots = $result['plots'] ?? [];
echo "Plot count: " . count($plots) . "\n";

if (!empty($plots)) {
    echo "Keys: " . implode(', ', array_keys($plots[0])) . "\n";
    echo "Plot 0: " . json_encode($plots[0]) . "\n";
    echo "Plot 1: " . json_encode($plots[1]) . "\n";
}
