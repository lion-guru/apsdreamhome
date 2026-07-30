<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use App\Services\SystemLogger;
use Exception;

/**
 * PlotCutterService — Core plot-cutting algorithm for Indian real-estate colonies.
 *
 * Given raw land dimensions and a set of acceptable plot sizes, this service:
 *   1. Subtracts roads, parks, and amenity areas.
 *   2. Generates a grid of plots (largest-first greedy fill).
 *   3. Flags corner and park-facing plots.
 *   4. Persists the result inside a DB transaction.
 *
 * All public methods are null-safe (return arrays, never throw).
 */
class PlotCutterService
{
    /** @var Database */
    private $db;

    /** @var SystemLogger */
    private $logger;

    /** @var \PDO|null */
    private $pdo;

    // ── Facing direction constants ──────────────────────────────
    private const FACING_NORTH = 'North';
    private const FACING_SOUTH = 'South';
    private const FACING_EAST  = 'East';
    private const FACING_WEST  = 'West';

    public function __construct()
    {
        try {
            $this->db     = Database::getInstance();
            $this->pdo    = $this->db->getPdo();
            $this->logger = new SystemLogger();
        } catch (Exception $e) {
            $this->log('error', 'PlotCutterService init failed', ['error' => $e->getMessage()]);
            $this->db     = null;
            $this->pdo    = null;
            $this->logger = null;
        }
    }

    // ================================================================
    //  PUBLIC API
    // ================================================================

    /**
     * Generate plots for a colony (preview only — nothing is saved).
     *
     * @param array $config See class-level doc-block for shape.
     * @return array{success: bool, summary: array, plots: array}
     */
    public function getPlotPreview(array $config): array
    {
        return $this->generatePlots($config);
    }

    /**
     * Generate plots and persist them to the database.
     *
     * @param array $config
     * @return array{success: bool, count: int, summary: array}
     */
    public function generateAndPersist(array $config): array
    {
        try {
            $result = $this->generatePlots($config);
            if (!$result['success']) {
                return $result;
            }

            $persisted = $this->persistPlots(
                (int) $config['colony_id'],
                $result['plots'],
                (int) ($config['created_by'] ?? 0)
            );

            if (!$persisted['success']) {
                return $persisted;
            }

            return [
                'success' => true,
                'count'   => $persisted['count'],
                'summary' => $result['summary'],
            ];
        } catch (Exception $e) {
            $this->log('error', 'generateAndPersist failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Core algorithm — cuts raw land into a grid of plots.
     *
     * @param array $config
     * @return array{success: bool, summary: array, plots: array}
     */
    public function generatePlots(array $config): array
    {
        try {
            // ── Validate required fields ──────────────────────
            $requiredKeys = ['colony_id', 'total_land_sqft', 'land_width_ft', 'land_length_ft', 'block_name'];
            foreach ($requiredKeys as $key) {
                if (!isset($config[$key]) || (is_string($config[$key]) && $config[$key] === '')) {
                    return ['success' => false, 'error' => "Missing required config key: {$key}"];
                }
            }

            if (empty($config['plot_sizes']) || !is_array($config['plot_sizes'])) {
                return ['success' => false, 'error' => 'plot_sizes array is required and must not be empty'];
            }

            // ── Extract config with defaults ──────────────────
            $totalLandSqft = (float) $config['total_land_sqft'];
            $landWidthFt   = (float) $config['land_width_ft'];
            $landLengthFt  = (float) $config['land_length_ft'];
            $blockName     = strtoupper(trim($config['block_name']));
            $roadWidthFt   = (float) ($config['road_width_ft'] ?? 30);
            $mainRoadFt    = (float) ($config['main_road_ft'] ?? 40);
            $parkPct       = (float) ($config['park_area_pct'] ?? 7);
            $amenityPct    = (float) ($config['amenity_area_pct'] ?? 3);

            // Sort plot sizes largest-first (greedy: biggest first)
            $plotSizes = $config['plot_sizes'];
            usort($plotSizes, function ($a, $b) {
                return ($b['area'] ?? ($b['width'] * $b['length']))
                     <=> ($a['area'] ?? ($a['width'] * $a['length']));
            });

            // ── Step 1: Calculate reserved areas ──────────────
            $parkArea      = $totalLandSqft * $parkPct / 100;
            $amenityArea   = $totalLandSqft * $amenityPct / 100;
            $mainRoadArea  = $mainRoadFt * $landWidthFt;

            $saleableArea  = $totalLandSqft - $parkArea - $amenityArea - $mainRoadArea;

            if ($saleableArea <= 0) {
                return [
                    'success' => false,
                    'error'   => 'Saleable area is zero or negative after subtracting park, amenity, and main road.',
                ];
            }

            // ── Step 2: Calculate internal road areas ────────
            $internalRoadArea = $this->calculateInternalRoadArea(
                $landWidthFt,
                $landLengthFt - $mainRoadFt,
                $roadWidthFt
            );

            $finalSaleable = $saleableArea - $internalRoadArea;

            if ($finalSaleable <= 0) {
                return [
                    'success' => false,
                    'error'   => 'Saleable area is zero or negative after subtracting internal roads.',
                ];
            }

            // ── Step 3: Generate plot grid ────────────────────
            $plots = $this->buildPlotGrid(
                $blockName,
                $landWidthFt,
                $landLengthFt,
                $mainRoadFt,
                $roadWidthFt,
                $parkArea,
                $plotSizes,
                $finalSaleable
            );

            if (empty($plots)) {
                return [
                    'success' => false,
                    'error'   => 'Could not generate any plots — land dimensions may be too small for configured plot sizes.',
                ];
            }

            // ── Step 4: Build summary ────────────────────────
            $totalSaleableSqft = 0;
            $blockStats        = [];
            foreach ($plots as $p) {
                $totalSaleableSqft += $p['area_sqft'];
                $b = $p['block'];
                if (!isset($blockStats[$b])) {
                    $blockStats[$b] = ['plot_count' => 0, 'area_sqft' => 0.0];
                }
                $blockStats[$b]['plot_count']++;
                $blockStats[$b]['area_sqft'] += $p['area_sqft'];
            }

            $summary = [
                'total_plots'       => count($plots),
                'total_saleable_sqft' => round($totalSaleableSqft, 2),
                'road_area_sqft'    => round($internalRoadArea + $mainRoadArea, 2),
                'park_area_sqft'    => round($parkArea, 2),
                'amenity_area_sqft' => round($amenityArea, 2),
                'internal_road_area_sqft' => round($internalRoadArea, 2),
                'main_road_area_sqft'     => round($mainRoadArea, 2),
                'land_area_sqft'    => round($totalLandSqft, 2),
                'utilisation_pct'   => round(($totalSaleableSqft / $totalLandSqft) * 100, 2),
                'blocks'            => $blockStats,
            ];

            $this->log('info', 'Plots generated', [
                'colony_id'    => $config['colony_id'],
                'total_plots'  => count($plots),
                'saleable_sqft' => $finalSaleable,
            ]);

            return [
                'success' => true,
                'summary' => $summary,
                'plots'   => $plots,
            ];
        } catch (Exception $e) {
            $this->log('error', 'generatePlots exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Persist generated plots inside a single transaction.
     *
     * @param int   $colonyId
     * @param array $plots     Output from generatePlots()['plots']
     * @param int   $createdBy User id performing the action
     * @return array{success: bool, count: int}
     */
    public function persistPlots(int $colonyId, array $plots, int $createdBy): array
    {
        try {
            if (empty($plots)) {
                return ['success' => false, 'error' => 'No plots to persist'];
            }

            $this->db->beginTransaction();

            // ── Insert each plot ──────────────────────────────
            $insertSql = "INSERT INTO plots (
                colony_id, plot_number, block, sector, plot_type,
                area_sqft, area_sqm, width_ft, length_ft,
                dimension_label, frontage_ft, depth_ft,
                price_per_sqft, base_price_per_sqft, total_price,
                facing, corner_plot, park_facing, road_width_ft,
                status, is_active, created_by, created_at, updated_at
            ) VALUES (
                :colony_id, :plot_number, :block, :sector, :plot_type,
                :area_sqft, :area_sqm, :width_ft, :length_ft,
                :dimension_label, :frontage_ft, :depth_ft,
                :price_per_sqft, :base_price_per_sqft, :total_price,
                :facing, :corner_plot, :park_facing, :road_width_ft,
                'available', 1, :created_by, NOW(), NOW()
            )";

            $stmt = $this->pdo->prepare($insertSql);

            foreach ($plots as $plot) {
                $areaSqm = $plot['area_sqft'] * 0.092903;
                $stmt->execute([
                    ':colony_id'         => $colonyId,
                    ':plot_number'       => $plot['plot_number'],
                    ':block'             => $plot['block'],
                    ':sector'            => $plot['sector'] ?? '',
                    ':plot_type'         => $plot['plot_type'] ?? 'residential',
                    ':area_sqft'         => round($plot['area_sqft'], 2),
                    ':area_sqm'          => round($areaSqm, 2),
                    ':width_ft'          => round($plot['width_ft'], 2),
                    ':length_ft'         => round($plot['length_ft'], 2),
                    ':dimension_label'   => $plot['dimension_label'],
                    ':frontage_ft'       => round($plot['width_ft'], 2),
                    ':depth_ft'          => round($plot['length_ft'], 2),
                    ':price_per_sqft'    => 0,
                    ':base_price_per_sqft' => 0,
                    ':total_price'       => 0,
                    ':facing'            => $plot['facing'],
                    ':corner_plot'       => $plot['corner_plot'] ? 1 : 0,
                    ':park_facing'       => $plot['park_facing'] ? 1 : 0,
                    ':road_width_ft'     => $plot['road_width_ft'] ?? 0,
                    ':created_by'        => $createdBy,
                ]);
            }

            // ── Update colony totals ─────────────────────────
            $plotCount = count($plots);
            $totalArea = array_sum(array_column($plots, 'area_sqft'));

            $this->db->execute(
                "UPDATE colonies SET
                    total_plots      = total_plots + :tp,
                    available_plots  = available_plots + :ap,
                    updated_at       = NOW()
                WHERE id = :cid",
                ['tp' => $plotCount, 'ap' => $plotCount, 'cid' => $colonyId]
            );

            // ── Create a colony_layouts record ───────────────
            $blockSummary = [];
            foreach ($plots as $p) {
                $b = $p['block'];
                if (!isset($blockSummary[$b])) {
                    $blockSummary[$b] = 0;
                }
                $blockSummary[$b]++;
            }

            $layoutData = [
                'colony_id'    => $colonyId,
                'layout_name'  => 'Layout ' . ($plots[0]['block'] ?? 'A') . ' v1',
                'version'      => 1,
                'layout_type'  => 'grid',
                'road_area_pct' => 0,
                'common_area_pct' => 0,
                'is_current'   => 1,
                'total_plots'  => $plotCount,
                'total_area_sqft' => round($totalArea, 2),
                'notes'        => 'Auto-generated by PlotCutterService',
                'plot_map_json' => json_encode([
                    'blocks'   => $blockSummary,
                    'generated_at' => date('Y-m-d H:i:s'),
                    'created_by' => $createdBy,
                ]),
                'status'       => 'active',
            ];

            // Remove is_current from others first
            $this->db->execute(
                "UPDATE colony_layouts SET is_current = 0 WHERE colony_id = :cid",
                ['cid' => $colonyId]
            );

            $this->db->insert('colony_layouts', $layoutData);

            $this->db->commit();

            $this->log('info', 'Plots persisted', [
                'colony_id' => $colonyId,
                'count'     => $plotCount,
            ]);

            return ['success' => true, 'count' => $plotCount];
        } catch (Exception $e) {
            $this->safeRollback();
            $this->log('error', 'persistPlots failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Delete all plots for a colony (use before regenerating).
     *
     * @param int $colonyId
     * @return array{success: bool, deleted: int}
     */
    public function deletePlotsByColony(int $colonyId): array
    {
        try {
            $countRow = $this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM plots WHERE colony_id = :cid",
                ['cid' => $colonyId]
            );
            $existingCount = (int) ($countRow['cnt'] ?? 0);

            if ($existingCount === 0) {
                return ['success' => true, 'deleted' => 0];
            }

            $this->db->beginTransaction();

            $this->db->execute(
                "DELETE FROM plots WHERE colony_id = :cid",
                ['cid' => $colonyId]
            );

            $this->db->execute(
                "UPDATE colonies SET
                    total_plots     = 0,
                    available_plots = 0,
                    updated_at      = NOW()
                WHERE id = :cid",
                ['cid' => $colonyId]
            );

            $this->db->execute(
                "UPDATE colony_layouts SET is_current = 0 WHERE colony_id = :cid",
                ['cid' => $colonyId]
            );

            $this->db->commit();

            $this->log('info', 'Plots deleted', [
                'colony_id' => $colonyId,
                'deleted'   => $existingCount,
            ]);

            return ['success' => true, 'deleted' => $existingCount];
        } catch (Exception $e) {
            $this->safeRollback();
            $this->log('error', 'deletePlotsByColony failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get layout history for a colony.
     *
     * @param int $colonyId
     * @return array
     */
    public function getLayoutsForColony(int $colonyId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM colony_layouts WHERE colony_id = :cid ORDER BY version DESC",
                ['cid' => $colonyId]
            );
        } catch (Exception $e) {
            $this->log('error', 'getLayoutsForColony failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    // ================================================================
    //  PRIVATE — ROAD CALCULATION
    // ================================================================

    /**
     * Calculate the total area consumed by internal roads.
     *
     * Horizontal roads run across the full width (spaced every ~200 ft of
     * available length). Vertical roads run along the full length (spaced
     * every ~300 ft of width). Intersection overlap is subtracted once.
     *
     * @param float $landWidthFt
     * @param float $availableLengthFt  Land length after the main road
     * @param float $roadWidthFt
     * @return float  Area in sqft
     */
    private function calculateInternalRoadArea(
        float $landWidthFt,
        float $availableLengthFt,
        float $roadWidthFt
    ): float {
        if ($availableLengthFt <= 0 || $landWidthFt <= 0 || $roadWidthFt <= 0) {
            return 0.0;
        }

        // Horizontal roads: one every ~200 ft
        $hSpacing  = 200.0;
        $hRoads    = max(1, (int) floor($availableLengthFt / $hSpacing));
        $hRoadArea = $hRoads * $roadWidthFt * $landWidthFt;

        // Vertical roads: one every ~300 ft
        $vSpacing  = 300.0;
        $vRoads    = max(1, (int) floor($landWidthFt / $vSpacing));
        $vRoadArea = $vRoads * $roadWidthFt * $availableLengthFt;

        // Intersection overlap (each crossing counted twice)
        $overlap = $hRoads * $vRoads * $roadWidthFt * $roadWidthFt;

        return $hRoadArea + $vRoadArea - $overlap;
    }

    // ================================================================
    //  PRIVATE — GRID GENERATION
    // ================================================================

    /**
     * Build the actual plot grid inside the available land area.
     *
     * Strategy:
     *   - Divide the land into blocks separated by vertical roads.
     *   - Within each block, fill rows top-to-bottom with the largest
     *     fitting plot size, then fall back to smaller sizes.
     *
     * @param float $landWidthFt
     * @param float $landLengthFt
     * @param float $mainRoadFt
     * @param float $roadWidthFt
     * @param float $parkArea
     * @param array $plotSizes  Sorted largest-first
     * @param float $finalSaleable
     * @return array
     */
    private function buildPlotGrid(
        string $blockName,
        float $landWidthFt,
        float $landLengthFt,
        float $mainRoadFt,
        float $roadWidthFt,
        float $parkArea,
        array $plotSizes,
        float $finalSaleable
    ): array {
        $plots           = [];
        $plotCounter     = 0;
        $seqNumber       = 1;

        // Available plotable area starts below the main road
        $startY = $mainRoadFt;
        $usableLength    = $landLengthFt - $mainRoadFt;

        // Compute block boundaries (split land width into vertical-road-separated strips)
        $vSpacing    = 300.0;
        $blockWidths = $this->computeBlockWidths($landWidthFt, $roadWidthFt, $vSpacing);

        // Park is placed at the top-right corner of the land
        $parkWidthFt  = $this->computeParkWidth($parkArea, $landWidthFt, $usableLength);
        $parkStartX   = $landWidthFt - $parkWidthFt;

        // Iterate over each vertical block
        $xOffset = 0.0;
        foreach ($blockWidths as $bIdx => $blockWidth) {
            $blockLetter = $this->blockLetterFor($blockName, $bIdx);

            $blockStartX = $xOffset + ($bIdx > 0 ? $roadWidthFt * $bIdx : 0);
            $blockEndX   = $blockStartX + $blockWidth;

            // Within each block, fill rows from top to bottom
            $y = $startY;
            while ($y < $landLengthFt - 1) {
                $remainingLength = $landLengthFt - $y;
                $bestFit = $this->findBestFit($plotSizes, $blockWidth, $remainingLength);

                if ($bestFit === null) {
                    // Try one more row with smaller widths by rotating plots
                    $bestFit = $this->findBestFitRotated($plotSizes, $blockWidth, $remainingLength);
                }

                if ($bestFit === null) {
                    break; // Remaining strip too small for any plot
                }

                $pw = $bestFit['width'];
                $pl = $bestFit['length'];

                // Fill the width of this block with plots of this size
                $x = $blockStartX;
                while ($x + $pw <= $blockEndX + 0.5) {
                    $plotCounter++;
                    $seqNumber++;

                    // Check if this plot overlaps with the park area
                    $plotEndX = $x + $pw;
                    $overlapsPark = ($plotEndX > $parkStartX && $x < $landWidthFt && $y < $startY + 80);

                    // Corner detection
                    $isCorner = $this->isCornerPlot(
                        $x, $y, $pw, $pl,
                        $blockStartX, $blockEndX,
                        $startY, $landLengthFt,
                        $landWidthFt, $roadWidthFt,
                        $blockWidths, $bIdx
                    );

                    // Facing based on position
                    $facing = $this->determineFacing($x, $y, $pw, $pl, $landWidthFt, $landLengthFt);

                    $roadAdj = $this->nearestRoadWidth($x, $y, $pw, $landWidthFt, $landLengthFt, $roadWidthFt);

                    $plots[] = [
                        'plot_number'     => $blockLetter . '-' . str_pad($seqNumber, 3, '0', STR_PAD_LEFT),
                        'block'           => $blockLetter,
                        'sector'          => '',
                        'plot_type'       => 'residential',
                        'area_sqft'       => round($pw * $pl, 2),
                        'width_ft'        => round($pw, 2),
                        'length_ft'       => round($pl, 2),
                        'dimension_label' => intval($pw) . 'x' . intval($pl),
                        'corner_plot'     => $isCorner ? 1 : 0,
                        'park_facing'     => $overlapsPark ? 1 : 0,
                        'facing'          => $facing,
                        'road_width_ft'   => $roadAdj,
                        'x_position'      => round($x, 2),
                        'y_position'      => round($y, 2),
                    ];

                    $x += $pw;
                }

                $y += $pl;
            }

            $xOffset = $blockEndX;
        }

        return $plots;
    }

    /**
     * Compute the width of each block when the land is split by vertical roads.
     *
     * @param float $totalWidth
     * @param float $roadWidth
     * @param float $vSpacing
     * @return float[]
     */
    private function computeBlockWidths(float $totalWidth, float $roadWidth, float $vSpacing): array
    {
        $numBlocks = max(1, (int) ceil($totalWidth / $vSpacing));
        // Ensure blocks are at least 60 ft wide
        if ($numBlocks > 1) {
            $totalRoadSpace = ($numBlocks - 1) * $roadWidth;
            $blockW = ($totalWidth - $totalRoadSpace) / $numBlocks;
            if ($blockW < 60 && $numBlocks > 1) {
                $numBlocks = max(1, $numBlocks - 1);
                $totalRoadSpace = ($numBlocks - 1) * $roadWidth;
                $blockW = ($totalWidth - $totalRoadSpace) / $numBlocks;
            }
        } else {
            $blockW = $totalWidth;
        }

        $widths = [];
        for ($i = 0; $i < $numBlocks; $i++) {
            $widths[] = round($blockW, 2);
        }
        return $widths;
    }

    /**
     * Find the best-fitting plot size for the given space.
     *
     * @param array  $plotSizes       Sorted largest-first
     * @param float  $availableWidth
     * @param float  $availableLength
     * @return array|null  ['width', 'length', 'area'] or null
     */
    private function findBestFit(array $plotSizes, float $availableWidth, float $availableLength): ?array
    {
        foreach ($plotSizes as $size) {
            $pw = (float) $size['width'];
            $pl = (float) $size['length'];

            // Normal orientation
            if ($pw <= $availableWidth + 0.5 && $pl <= $availableLength + 0.5) {
                return ['width' => $pw, 'length' => $pl, 'area' => $pw * $pl];
            }
        }
        return null;
    }

    /**
     * Try rotating plots 90° (swap width/length).
     */
    private function findBestFitRotated(array $plotSizes, float $availableWidth, float $availableLength): ?array
    {
        foreach ($plotSizes as $size) {
            $pw = (float) $size['length']; // rotated
            $pl = (float) $size['width'];

            if ($pw <= $availableWidth + 0.5 && $pl <= $availableLength + 0.5) {
                return ['width' => $pw, 'length' => $pl, 'area' => $pw * $pl];
            }
        }
        return null;
    }

    /**
     * Determine how wide the park strip is (park occupies the top-right corner).
     */
    private function computeParkWidth(float $parkArea, float $landWidth, float $usableLength): float
    {
        if ($parkArea <= 0 || $usableLength <= 0) {
            return 0.0;
        }
        // Park occupies a strip along the full usable length, up to 25% of land width
        $maxParkWidth = $landWidth * 0.25;
        $parkWidth    = $parkArea / $usableLength;
        return min($parkWidth, $maxParkWidth);
    }

    // ================================================================
    //  PRIVATE — PLOT CLASSIFICATION
    // ================================================================

    /**
     * Determine if a plot is at a corner position (adjacent to a road intersection).
     */
    private function isCornerPlot(
        float $x, float $y, float $pw, float $pl,
        float $blockStartX, float $blockEndX,
        float $startY, float $totalLength,
        float $landWidth, float $roadWidth,
        array $blockWidths, int $blockIndex
    ): bool {
        $margin = 2.0; // tolerance in ft

        // Top of block (near horizontal road)
        $nearTop = abs($y - $startY) < $margin;
        // Bottom of land
        $nearBottom = abs(($y + $pl) - $totalLength) < $margin;
        // Left edge of block
        $nearLeft = abs($x - $blockStartX) < $margin;
        // Right edge of block
        $nearRight = abs(($x + $pw) - $blockEndX) < $margin;

        // A corner plot is at the intersection of a horizontal and vertical road
        return ($nearTop || $nearBottom) && ($nearLeft || $nearRight);
    }

    /**
     * Determine the facing direction based on the plot's position.
     */
    private function determineFacing(
        float $x, float $y, float $pw, float $pl,
        float $landWidth, float $landLength
    ): string {
        // The "front" of the plot is the side that faces a road.
        // Top plots face north (road runs horizontally above them).
        // Left-side plots face west (road runs vertically to their left).

        $centerX = $x + $pw / 2;
        $centerY = $y + $pl / 2;

        // If the plot is closer to the left edge, it faces west (toward the vertical road)
        if ($centerX < $landWidth * 0.33) {
            return self::FACING_WEST;
        }
        // If closer to the right edge
        if ($centerX > $landWidth * 0.67) {
            return self::FACING_EAST;
        }
        // If closer to the top (below main road)
        if ($centerY < $landLength * 0.5) {
            return self::FACING_NORTH;
        }
        return self::FACING_SOUTH;
    }

    /**
     * Find the nearest road width (useful for premium calculation).
     */
    private function nearestRoadWidth(
        float $x, float $y, float $pw,
        float $landWidth, float $landLength,
        float $internalRoadWidth
    ): float {
        // The main road is at the top (y = 0 to main_road_ft)
        // Internal roads create intersections
        // For simplicity, return the internal road width (30 ft default)
        // Premium logic uses this value in ColonyPricingService
        return $internalRoadWidth;
    }

    /**
     * Generate block letter suffix (A, A1, A2, B, B1, B2 …)
     */
    private function blockLetterFor(string $baseBlock, int $index): string
    {
        if ($index === 0) {
            return $baseBlock;
        }
        return $baseBlock . ($index + 1);
    }

    // ================================================================
    //  PRIVATE — HELPERS
    // ================================================================

    /**
     * Rollback only if a transaction is active.
     */
    private function safeRollback(): void
    {
        try {
            if ($this->pdo && $this->pdo->inTransaction()) {
                $this->db->rollBack();
            }
        } catch (Exception $ignored) {
        // Swallow — nothing useful to do here
        error_log($ignored->getMessage());
        }
    }

    /**
     * Log via SystemLogger with safe fallback.
     */
    private function log(string $level, string $message, array $context = []): void
    {
        try {
            if ($this->logger) {
                $this->logger->log($level, $message, $context);
            }
        } catch (Exception $ignored) {
        // Never let logging break the caller
        error_log($ignored->getMessage());
        }
    }
}
