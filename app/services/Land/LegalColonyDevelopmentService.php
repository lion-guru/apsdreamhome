<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use Exception;
use \App\Traits\ServiceTenantTrait;

/**
 * LegalColonyDevelopmentService — Complete legal colony development pipeline.
 *
 * Ties together the entire lifecycle of colony development:
 *   Phase 1: Land Acquisition (lead → agreement → registration → mutation)
 *   Phase 2: Master Planning (phases, blocks, roads, parks, amenities)
 *   Phase 3: Plot Cutting (legal compliance, RERA carpet area, setbacks)
 *   Phase 4: RERA Registration (project registration, milestones, compliance)
 *   Phase 5: Development (costs, vendors, progress tracking)
 *   Phase 6: Pricing (cost-based + legal minimum rate guard)
 *   Phase 7: Sales Ready (launch readiness checklist, inventory activation)
 *
 * All public methods are null-safe (return arrays, never throw).
 */
class LegalColonyDevelopmentService
{
    use \App\Traits\ServiceTenantTrait;

    /** @var Database */
    private $db;

    /** @var \PDO|null */
    private $pdo;

    // ── Pipeline stage constants ──────────────────────────────
    const STAGE_LAND_ACQUISITION  = 'land_acquisition';
    const STAGE_MASTER_PLANNING   = 'master_planning';
    const STAGE_PLOT_CUTTING      = 'plot_cutting';
    const STAGE_RERA_REGISTRATION = 'rera_registration';
    const STAGE_DEVELOPMENT       = 'development';
    const STAGE_PRICING           = 'pricing';
    const STAGE_SALES_READY       = 'sales_ready';

    // ── Legal minimum: RERA requires 30% open space in UP ────
    const RERA_MIN_OPEN_SPACE_PCT = 30.0;
    const RERA_MIN_ROAD_WIDTH_FT  = 20.0;
    const RERA_MAX_PLOT_DEPTH_RATIO = 3.0; // depth <= 3x width

    // ── Colony type options ───────────────────────────────────
    const COLONY_TYPES = ['residential', 'commercial', 'mixed', 'industrial', 'farmhouse'];

    public function __construct()
    {
        try {
            $this->db  = Database::getInstance();
            $this->pdo = $this->db->getPdo();
        } catch (Exception $e) {
            $this->db  = null;
            $this->pdo = null;
        }
    }

    // ================================================================
    //  PHASE 1: LAND ACQUISITION
    // ================================================================

    /**
     * Start a new land acquisition process.
     * Creates a colony placeholder + land_acquisitions record.
     *
     * @param array $data Required: land_owner_name, total_area_acres, estimated_cost
     *                    Optional: colony_name, location, gata_numbers[]
     * @return array{success: bool, colony_id?: int, acquisition_id?: int, error?: string}
     */
    public function startLandAcquisition(array $data): array
    {
        try {
            $ownerName   = trim($data['land_owner_name'] ?? '');
            $totalAcres  = floatval($data['total_area_acres'] ?? 0);
            $estCost     = floatval($data['estimated_cost'] ?? 0);
            $colonyName  = trim($data['colony_name'] ?? '');
            $location    = trim($data['location'] ?? '');

            if (empty($ownerName) || $totalAcres <= 0) {
                return ['success' => false, 'error' => 'Land owner name and total area (acres) are required'];
            }

            $totalAreaSqft = $totalAcres * 43560;

            $this->db->beginTransaction();

            // Create colony placeholder
            $colonyId = $this->db->insert('colonies', [
                'name'                 => $colonyName ?: 'New Colony - ' . $ownerName,
                'slug'                 => $this->generateSlug($colonyName ?: 'new-colony-' . time()),
                'location'             => $location,
                'total_area_acres'     => $totalAcres,
                'total_area_sqft'      => $totalAreaSqft,
                'total_plots'          => 0,
                'available_plots'      => 0,
                'status'               => 'planning',
                'is_active'            => 1,
                'pipeline_stage'       => self::STAGE_LAND_ACQUISITION,
                'land_owner_name'      => $ownerName,
                'estimated_land_cost'  => $estCost,
                'created_at'           => date('Y-m-d H:i:s'),
            ]);

            // Create land acquisition record (land_deals is the base table; land_acquisitions is a VIEW)
            $acquisitionId = $this->db->insert('land_deals', [
                'colony_id'           => $colonyId,
                'total_area_sqft'     => $totalAreaSqft,
                'total_consideration' => $estCost,
                'advance_paid'        => floatval($data['advance_paid'] ?? 0),
                'balance_amount'      => $estCost - floatval($data['advance_paid'] ?? 0),
                'status'              => 'in_progress',
                'created_at'          => date('Y-m-d H:i:s'),
            ]);

            // Log activity
            $this->logActivity($colonyId, 'land_acquisition_started', [
                'owner'          => $ownerName,
                'area_acres'     => $totalAcres,
                'estimated_cost' => $estCost,
            ]);

            $this->db->commit();

            return [
                'success'        => true,
                'colony_id'      => $colonyId,
                'acquisition_id' => $acquisitionId,
            ];
        } catch (Exception $e) {
            $this->safeRollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Update land acquisition status through legal workflow.
     *
     * @param int    $acquisitionId
     * @param string $newStatus  in_progress|registered|mutated|closed|cancelled
     * @param array  $details    Optional: registration_number, mutation_number, stamp_duty, etc.
     */
    public function updateAcquisitionStatus(int $acquisitionId, string $newStatus, array $details = []): array
    {
        try {
            $acq = $this->db->fetchOne(
                "SELECT * FROM land_acquisitions WHERE id = ?",
                [$acquisitionId]
            );
            if (!$acq) {
                return ['success' => false, 'error' => 'Land acquisition not found'];
            }

            $validTransitions = [
                'in_progress' => ['registered', 'cancelled'],
                'registered'  => ['mutated', 'cancelled'],
                'mutated'     => ['closed'],
            ];

            $current = $acq['status'];
            if (isset($validTransitions[$current]) && !in_array($newStatus, $validTransitions[$current])) {
                return ['success' => false, 'error' => "Cannot transition from '{$current}' to '{$newStatus}'"];
            }

            $updates = ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')];

            if ($newStatus === 'registered') {
                $updates['registration_date']    = $details['registration_date'] ?? date('Y-m-d');
                $updates['registration_number']  = $details['registration_number'] ?? '';
                $updates['sub_registrar_office'] = $details['sub_registrar_office'] ?? '';
                $updates['stamp_duty_amount']    = floatval($details['stamp_duty_amount'] ?? 0);
                $updates['registration_fee']     = floatval($details['registration_fee'] ?? 0);
            }

            if ($newStatus === 'mutated') {
                $updates['mutation_status'] = 'completed';
                $updates['mutation_number'] = $details['mutation_number'] ?? '';
                $updates['mutation_date']   = $details['mutation_date'] ?? date('Y-m-d');
            }

            $setClauses = [];
            $params     = [];
            foreach ($updates as $key => $val) {
                $setClauses[] = "{$key} = ?";
                $params[]     = $val;
            }
            $params[] = $acquisitionId;

            $this->db->execute(
                "UPDATE land_deals SET " . implode(', ', $setClauses) . " WHERE id = ?",
                $params
            );

            // Update colony pipeline stage
            if ($newStatus === 'registered') {
                $this->updateColonyStage((int)$acq['colony_id'], self::STAGE_MASTER_PLANNING);
            }

            $this->logActivity((int)$acq['colony_id'], 'acquisition_status_changed', [
                'from' => $current,
                'to'   => $newStatus,
            ]);

            return ['success' => true, 'status' => $newStatus];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ================================================================
    //  PHASE 2: MASTER PLANNING
    // ================================================================

    /**
     * Create a master plan for the colony with phases, blocks, amenities.
     *
     * @param int   $colonyId
     * @param array $plan  phases[], blocks[], amenities[], roads[], parks[]
     * @return array{success: bool, layout_id?: int, error?: string}
     */
    public function createMasterPlan(int $colonyId, array $plan): array
    {
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            // Validate RERA compliance
            $roadAreaPct    = floatval($plan['road_area_pct'] ?? 15);
            $parkAreaPct    = floatval($plan['park_area_pct'] ?? 10);
            $amenityAreaPct = floatval($plan['amenity_area_pct'] ?? 5);
            $openSpacePct   = $roadAreaPct + $parkAreaPct + $amenityAreaPct;

            if ($openSpacePct < self::RERA_MIN_OPEN_SPACE_PCT) {
                return [
                    'success' => false,
                    'error'   => "RERA requires minimum " . self::RERA_MIN_OPEN_SPACE_PCT . "% open space. Current plan: {$openSpacePct}%",
                ];
            }

            // Validate road widths
            $minRoadWidth = floatval($plan['min_road_width_ft'] ?? 30);
            if ($minRoadWidth < self::RERA_MIN_ROAD_WIDTH_FT) {
                return [
                    'success' => false,
                    'error'   => "Road width must be at least " . self::RERA_MIN_ROAD_WIDTH_FT . " ft per local norms",
                ];
            }

            $this->db->beginTransaction();

            // Deactivate old layouts
            $this->db->execute(
                "UPDATE colony_layouts SET is_current = 0 WHERE colony_id = ?",
                [$colonyId]
            );

            $phases    = $plan['phases'] ?? [];
            $blocks    = $plan['blocks'] ?? [];
            $amenities = $plan['amenities'] ?? [];

            $layoutId = $this->db->insert('colony_layouts', [
                'colony_id'      => $colonyId,
                'layout_name'    => trim($plan['layout_name'] ?? 'Master Plan v1'),
                'version'        => trim($plan['version'] ?? '1.0'),
                'layout_type'    => trim($plan['layout_type'] ?? 'residential'),
                'road_area_pct'  => $roadAreaPct,
                'common_area_pct' => $parkAreaPct + $amenityAreaPct,
                'is_current'     => 1,
                'plot_map_json'  => json_encode([
                    'phases'             => $phases,
                    'blocks'             => $blocks,
                    'amenities'          => $amenities,
                    'open_space_pct'     => $openSpacePct,
                    'min_road_width_ft'  => $minRoadWidth,
                ]),
                'notes'          => trim($plan['notes'] ?? ''),
                'total_plots'    => 0,
                'total_area_sqft' => 0,
                'status'         => 'draft',
                'created_at'     => date('Y-m-d H:i:s'),
            ]);

            // Update colony
            $this->db->execute(
                "UPDATE colonies SET
                    total_area_acres = ?,
                    pipeline_stage = ?,
                    updated_at = NOW()
                 WHERE id = ?",
                [
                    floatval($plan['total_area_acres'] ?? $colony['total_area_acres'] ?? 0),
                    self::STAGE_MASTER_PLANNING,
                    $colonyId,
                ]
            );

            $this->logActivity($colonyId, 'master_plan_created', [
                'layout_id'       => $layoutId,
                'phases'          => count($phases),
                'blocks'          => count($blocks),
                'amenities'       => count($amenities),
                'open_space_pct'  => $openSpacePct,
            ]);

            $this->db->commit();

            return ['success' => true, 'layout_id' => $layoutId];
        } catch (Exception $e) {
            $this->safeRollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ================================================================
    //  PHASE 3: PLOT CUTTING (Legal Compliance)
    // ================================================================

    /**
     * Generate plots with RERA legal compliance checks.
     *
     * @param int   $colonyId
     * @param array $config  Plot cutting config (width, length, sizes, blocks, etc.)
     * @return array{success: bool, count?: int, compliance?: array, error?: string}
     */
    public function generatePlotsLegal(int $colonyId, array $config): array
    {
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            // ── Legal compliance pre-checks ────────────────────
            $compliance = $this->runComplianceChecks($colonyId, $config);

            if (!empty($compliance['violations'])) {
                return [
                    'success'     => false,
                    'compliance'  => $compliance,
                    'error'       => 'RERA compliance violations found. Fix issues before generating plots.',
                ];
            }

            // ── Transform pipeline form config → PlotCutterService config ──
            $totalLandSqft = floatval($config['total_land_sqft'] ?? 0);
            $plotWidth     = floatval($config['plot_width_ft'] ?? 30);
            $plotLength    = floatval($config['plot_length_ft'] ?? 40);
            $blockNames    = array_map('trim', explode(',', $config['blocks'] ?? 'A'));
            $plotsPerBlock = intval($config['plots_per_block'] ?? 20);

            $plotSizes = [
                ['width' => $plotWidth, 'length' => $plotLength, 'area' => $plotWidth * $plotLength, 'count' => $plotsPerBlock * count($blockNames)],
            ];

            // Derive land dimensions: assume rectangular plot with ~4:3 ratio
            $landWidth  = round(sqrt($totalLandSqft * 3 / 4) * 1.05);
            $landLength = round($totalLandSqft / max($landWidth, 1));

            $cutterConfig = [
                'colony_id'       => $colonyId,
                'total_land_sqft' => $totalLandSqft,
                'land_width_ft'   => $landWidth,
                'land_length_ft'  => $landLength,
                'block_name'      => $blockNames[0],
                'road_width_ft'   => floatval($config['road_width_ft'] ?? 30),
                'park_area_pct'   => floatval($config['park_area_pct'] ?? 10),
                'amenity_area_pct'=> floatval($config['amenity_area_pct'] ?? 5),
                'plot_sizes'      => $plotSizes,
                'created_by'      => $config['created_by'] ?? 1,
            ];

            // ── Generate plots via PlotCutterService ───────────
            $cutter = new PlotCutterService();
            $result = $cutter->generatePlots($cutterConfig);

            if (!$result['success']) {
                return ['success' => false, 'error' => $result['error'] ?? $result['message'] ?? 'Plot generation failed'];
            }

            $plots = $result['plots'] ?? [];

            // ── Per-plot legal validation ──────────────────────
            $plotViolations = [];
            foreach ($plots as $idx => $plot) {
                $width  = floatval($plot['width_ft'] ?? $plot['width'] ?? 0);
                $length = floatval($plot['length_ft'] ?? $plot['length'] ?? 0);
                $area   = floatval($plot['area_sqft'] ?? $plot['area'] ?? 0);

                if ($width > 0 && $length / $width > self::RERA_MAX_PLOT_DEPTH_RATIO) {
                    $plotViolations[] = "Plot #{$idx}: Depth ratio {$length}/{$width} exceeds max 3:1";
                }

                if ($area < 120) {
                    $plotViolations[] = "Plot #{$idx}: Area {$area} sqft below 120 sqft minimum";
                }
            }

            if (!empty($plotViolations)) {
                return [
                    'success'    => false,
                    'violations' => $plotViolations,
                    'compliance' => $compliance,
                    'error'      => count($plotViolations) . ' plot-level violations found',
                ];
            }

            // ── Persist plots ──────────────────────────────────
            $persistResult = $cutter->persistPlots($colonyId, $plots, $config['created_by'] ?? 1);

            if (!$persistResult['success']) {
                return ['success' => false, 'error' => $persistResult['message'] ?? 'Failed to persist plots'];
            }

            // Update colony counts
            $this->db->execute(
                "UPDATE colonies SET
                    total_plots = (SELECT COUNT(*) FROM plots WHERE colony_id = ?),
                    available_plots = (SELECT COUNT(*) FROM plots WHERE colony_id = ? AND status = 'available'),
                    pipeline_stage = ?,
                    updated_at = NOW()
                 WHERE id = ?",
                [$colonyId, $colonyId, self::STAGE_PLOT_CUTTING, $colonyId]
            );

            $this->logActivity($colonyId, 'plots_generated_legal', [
                'count'          => $persistResult['count'],
                'compliance'     => 'passed',
                'open_space_pct' => $compliance['open_space_pct'] ?? 0,
            ]);

            return [
                'success'    => true,
                'count'      => $persistResult['count'],
                'compliance' => $compliance,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Run RERA compliance checks on plot configuration.
     */
    public function runComplianceChecks(int $colonyId, array $config): array
    {
        $violations = [];
        $warnings   = [];

        $totalLand    = floatval($config['total_land_sqft'] ?? 0);
        $roadPct      = floatval($config['road_area_pct'] ?? $config['road_width_ft'] ?? 15);
        $parkPct      = floatval($config['park_area_pct'] ?? 10);
        $amenityPct   = floatval($config['amenity_area_pct'] ?? 5);
        $openSpacePct = $roadPct + $parkPct + $amenityPct;

        // Check 1: Open space percentage
        if ($openSpacePct < self::RERA_MIN_OPEN_SPACE_PCT) {
            $violations[] = "Open space {$openSpacePct}% below RERA minimum " . self::RERA_MIN_OPEN_SPACE_PCT . "%";
        }

        // Check 2: Road width
        $minRoadWidth = floatval($config['road_width_ft'] ?? 30);
        if ($minRoadWidth < self::RERA_MIN_ROAD_WIDTH_FT) {
            $violations[] = "Road width {$minRoadWidth}ft below minimum " . self::RERA_MIN_ROAD_WIDTH_FT . "ft";
        }

        // Check 3: Saleable area ratio
        $saleablePct = 100 - $openSpacePct;
        if ($saleablePct > 70) {
            $warnings[] = "Saleable area {$saleablePct}% is high. Consider increasing open space for better RERA compliance.";
        }

        // Check 4: Minimum land size for colony
        $minLandAcres = 1.0;
        $landAcres    = $totalLand / 43560;
        if ($landAcres < $minLandAcres) {
            $warnings[] = "Colony area {$landAcres} acres is less than recommended {$minLandAcres} acres minimum.";
        }

        // Check 5: Existing plots conflict
        $existingPlots = $this->db->selectOne(
            "SELECT COUNT(*) as c FROM plots WHERE colony_id = ? AND status NOT IN ('available', 'hold')",
            [$colonyId]
        );
        if (($existingPlots['c'] ?? 0) > 0) {
            $warnings[] = "{$existingPlots['c']} plots are already booked/sold. Re-generating may affect them.";
        }

        return [
            'passed'         => empty($violations),
            'violations'     => $violations,
            'warnings'       => $warnings,
            'open_space_pct' => $openSpacePct,
            'road_width_ft'  => $minRoadWidth,
            'land_acres'     => $landAcres,
        ];
    }

    // ================================================================
    //  PHASE 4: RERA REGISTRATION
    // ================================================================

    /**
     * Create a RERA project registration entry.
     *
     * @param int   $colonyId
     * @param array $reraData  rera_number, state_code, builder_name, builder_license, etc.
     */
    public function registerRERA(int $colonyId, array $reraData): array
    {
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            $reraNumber = trim($reraData['rera_number'] ?? '');
            if (empty($reraNumber)) {
                return ['success' => false, 'error' => 'RERA number is required'];
            }

            // Check for duplicate RERA
            $existing = $this->db->selectOne(
                "SELECT COUNT(*) as c FROM rera_projects WHERE rera_number = ?",
                [$reraNumber]
            );
            if (($existing['c'] ?? 0) > 0) {
                return ['success' => false, 'error' => 'RERA number already registered'];
            }

            // Get plot and area stats
            $plotStats = $this->db->selectOne(
                "SELECT COUNT(*) as total, SUM(area_sqft) as total_area FROM plots WHERE colony_id = ?",
                [$colonyId]
            );

            $reraId = $this->db->insert('rera_projects', [
                'rera_number'       => $reraNumber,
                'state_code'        => trim($reraData['state_code'] ?? 'UP'),
                'project_name'      => $colony['name'] ?? '',
                'builder_name'      => trim($reraData['builder_name'] ?? 'APS Dream Homes Pvt. Ltd.'),
                'builder_license'   => trim($reraData['builder_license'] ?? ''),
                'project_type'      => trim($reraData['project_type'] ?? 'Residential Plotted'),
                'status'            => 'Registered',
                'registration_date' => $reraData['registration_date'] ?? date('Y-m-d'),
                'validity_date'     => $reraData['validity_date'] ?? date('Y-m-d', strtotime('+5 years')),
                'city'              => trim($reraData['city'] ?? 'Gorakhpur'),
                'district'          => trim($reraData['district'] ?? 'Gorakhpur'),
                'area_sqm'          => round(($plotStats['total_area'] ?? 0) * 0.0929, 2),
                'total_units'       => $plotStats['total'] ?? 0,
                'address'           => $colony['location'] ?? '',
                'is_active'         => 1,
                'created_at'        => date('Y-m-d H:i:s'),
            ]);

            // Link colony to RERA
            $this->db->execute(
                "UPDATE colonies SET
                    rera_project_id = ?,
                    rera_number = ?,
                    pipeline_stage = ?,
                    updated_at = NOW()
                 WHERE id = ?",
                [$reraId, $reraNumber, self::STAGE_RERA_REGISTRATION, $colonyId]
            );

            // Create initial milestones
            $this->createDefaultMilestones($reraId);

            $this->logActivity($colonyId, 'rera_registered', [
                'rera_number' => $reraNumber,
                'rera_id'     => $reraId,
            ]);

            return ['success' => true, 'rera_id' => $reraId, 'rera_number' => $reraNumber];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Create default RERA milestones for a project.
     */
    private function createDefaultMilestones(int $reraId): void
    {
        $milestones = [
            ['name' => 'RERA Registration Approved',           'type' => 'registration',       'due_months' => 0],
            ['name' => 'Layout Plan Approved by Authority',    'type' => 'layout_approval',    'due_months' => 1],
            ['name' => 'Road Construction Started',            'type' => 'construction_start',  'due_months' => 3],
            ['name' => 'Park & Open Areas Developed',          'type' => 'plinth_completion',   'due_months' => 6],
            ['name' => 'Drainage & Sewerage Complete',         'type' => 'plinth_completion',   'due_months' => 6],
            ['name' => 'Electricity Infrastructure',           'type' => 'structure_completion','due_months' => 9],
            ['name' => 'Water Supply Connection',              'type' => 'structure_completion','due_months' => 9],
            ['name' => 'Common Area Amenities Built',          'type' => 'finishing_start',     'due_months' => 12],
            ['name' => 'Boundary Wall Completed',              'type' => 'structure_completion','due_months' => 6],
            ['name' => 'Entry Gate & Security',                'type' => 'finishing_start',     'due_months' => 10],
            ['name' => 'Occupation Certificate Applied',       'type' => 'occupancy_certificate','due_months' => 18],
            ['name' => 'Possession Ready',                     'type' => 'handover',            'due_months' => 24],
        ];

        foreach ($milestones as $m) {
            $this->db->insert('rera_milestones', [
                'project_id'     => $reraId,
                'milestone_name' => $m['name'],
                'milestone_type' => $m['type'],
                'planned_date'   => date('Y-m-d', strtotime("+{$m['due_months']} months")),
                'status'         => 'pending',
                'created_at'     => date('Y-m-d H:i:s'),
            ]);
        }
    }

    // ================================================================
    //  PHASE 5: DEVELOPMENT COST TRACKING
    // ================================================================

    /**
     * Record a development cost with full GST compliance.
     *
     * @param int   $colonyId
     * @param array $costData  cost_type, amount, gst_rate, vendor_name, etc.
     */
    public function recordDevelopmentCost(int $colonyId, array $costData): array
    {
        try {
            $costType    = trim($costData['cost_type'] ?? '');
            $amount      = floatval($costData['amount'] ?? 0);
            $gstRate     = floatval($costData['gst_rate'] ?? 18);
            $vendorName  = trim($costData['vendor_name'] ?? '');
            $description = trim($costData['work_description'] ?? '');
            $invoiceNo   = trim($costData['invoice_number'] ?? '');

            if (empty($costType) || $amount <= 0) {
                return ['success' => false, 'error' => 'Cost type and valid amount required'];
            }

            $validCostTypes = [
                'road', 'drainage', 'water_supply', 'electricity', 'sewerage',
                'park_landscaping', 'boundary_wall', 'gate', 'common_area',
                'stamping_registration', 'legal_fees', 'brokerage',
                'architecture', 'survey', 'soil_testing', 'other',
            ];
            if (!in_array($costType, $validCostTypes)) {
                return ['success' => false, 'error' => "Invalid cost type: {$costType}"];
            }

            $gstAmount   = $amount * $gstRate / 100;
            $totalWithGst = $amount + $gstAmount;
            $tdsRate     = floatval($costData['tds_rate'] ?? 0);
            $tdsAmount   = $amount * $tdsRate / 100;
            $netPayable  = $totalWithGst - $tdsAmount;

            $costId = $this->db->insert('colony_development_costs', [
                'colony_id'        => $colonyId,
                'cost_type'        => $costType,
                'vendor_id'        => !empty($costData['vendor_id']) ? intval($costData['vendor_id']) : null,
                'vendor_name'      => $vendorName,
                'work_description' => $description,
                'invoice_number'   => $invoiceNo,
                'invoice_date'     => $costData['invoice_date'] ?? date('Y-m-d'),
                'amount'           => $amount,
                'gst_rate'         => $gstRate,
                'gst_amount'       => $gstAmount,
                'tds_section'      => $costData['tds_section'] ?? '',
                'tds_amount'       => $tdsAmount,
                'payment_status'   => floatval($costData['paid_amount'] ?? 0) >= $totalWithGst ? 'paid' : 'partial',
                'paid_amount'      => floatval($costData['paid_amount'] ?? 0),
                'balance_amount'   => max($netPayable - floatval($costData['paid_amount'] ?? 0), 0),
                'status'           => $costData['status'] ?? 'planned',
                'created_at'       => date('Y-m-d H:i:s'),
            ]);

            $this->logActivity($colonyId, 'dev_cost_recorded', [
                'cost_id' => $costId,
                'type'    => $costType,
                'amount'  => $amount,
                'gst'     => $gstAmount,
                'vendor'  => $vendorName,
            ]);

            return [
                'success'         => true,
                'cost_id'         => $costId,
                'total_with_gst'  => $totalWithGst,
                'tds_amount'      => $tdsAmount,
                'net_payable'     => $netPayable,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ================================================================
    //  PHASE 6: PRICING (Legal Minimum Rate Guard)
    // ================================================================

    /**
     * Calculate and apply pricing with legal minimum rate enforcement.
     *
     * @param int   $colonyId
     * @param array $pricingConfig  markup settings, premium percentages, etc.
     */
    public function calculateAndApplyPricing(int $colonyId, array $pricingConfig = []): array
    {
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            // ── Step 1: Get pricing breakdown ──────────────────
            $pricingService = new ColonyPricingService();
            $pricing = $pricingService->calculateColonyPricing($colonyId);

            if (!$pricing['success']) {
                return $pricing;
            }

            // ── Step 2: Legal minimum rate check ───────────────
            $minCostPerSqft = $pricing['min_price_per_sqft'] ?? 0;
            $suggestedPrice = $pricing['base_price_per_sqft'] ?? 0;
            $overridePrice  = floatval($pricingConfig['override_price_per_sqft'] ?? 0);

            $finalPrice = $overridePrice > 0 ? $overridePrice : $suggestedPrice;

            // Store minimum as cost floor
            $this->db->execute(
                "UPDATE colonies SET min_price_per_sqft = ? WHERE id = ?",
                [$minCostPerSqft, $colonyId]
            );

            // Legal guard: cannot price below land+development cost
            if ($finalPrice < $minCostPerSqft && empty($pricingConfig['force_below_cost'])) {
                return [
                    'success'       => false,
                    'error'         => "Pricing ₹{$finalPrice}/sqft is below cost floor ₹{$minCostPerSqft}/sqft. Use force flag to override.",
                    'cost_floor'    => $minCostPerSqft,
                    'suggested'     => $suggestedPrice,
                    'pricing'       => $pricing,
                ];
            }

            // ── Step 3: Apply premiums ─────────────────────────
            $premiums = [
                'corner_plot'         => floatval($pricingConfig['corner_premium_pct'] ?? 10) / 100,
                'park_facing'         => floatval($pricingConfig['park_facing_premium_pct'] ?? 15) / 100,
                'road_width_ft'       => floatval($pricingConfig['wide_road_premium_pct'] ?? 8) / 100,
                'wide_road_threshold' => floatval($pricingConfig['wide_road_threshold'] ?? 40),
            ];

            if (!empty($pricingConfig['block_premiums'])) {
                $premiums['block'] = $pricingConfig['block_premiums'];
            }
            if (!empty($pricingConfig['phase_premiums'])) {
                $premiums['phase'] = $pricingConfig['phase_premiums'];
            }

            $applyResult = $pricingService->applyPricingToColony($colonyId, $finalPrice, $premiums);

            if ($applyResult['success']) {
                $this->db->execute(
                    "UPDATE colonies SET pipeline_stage = ?, starting_price = ?, updated_at = NOW() WHERE id = ?",
                    [self::STAGE_PRICING, $applyResult['min_price'] ?? 0, $colonyId]
                );
            }

            $this->logActivity($colonyId, 'pricing_applied', [
                'base_price'  => $finalPrice,
                'min_cost'    => $minCostPerSqft,
                'plots_count' => $applyResult['plots_updated'] ?? 0,
                'total_value' => $applyResult['total_value'] ?? 0,
            ]);

            return [
                'success'      => true,
                'pricing'      => $pricing,
                'apply_result' => $applyResult,
                'base_price'   => $finalPrice,
                'cost_floor'   => $minCostPerSqft,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ================================================================
    //  PHASE 7: SALES READINESS
    // ================================================================

    /**
     * Check if colony is ready for sales launch.
     * Returns a readiness checklist.
     */
    public function getSalesReadinessChecklist(int $colonyId): array
    {
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            $checks = [];

            // 1. Land acquisition completed
            $acq = $this->db->selectOne(
                "SELECT status FROM land_acquisitions WHERE colony_id = ? ORDER BY id DESC LIMIT 1",
                [$colonyId]
            );
            $checks[] = [
                'label'  => 'Land Acquisition Registered',
                'passed' => in_array($acq['status'] ?? '', ['registered', 'mutated', 'closed']),
                'status' => $acq['status'] ?? 'not_started',
            ];

            // 2. Master plan created
            $layout = $this->db->selectOne(
                "SELECT status FROM colony_layouts WHERE colony_id = ? AND is_current = 1",
                [$colonyId]
            );
            $checks[] = [
                'label'  => 'Master Plan Created',
                'passed' => !empty($layout['status']),
                'status' => $layout['status'] ?? 'not_started',
            ];

            // 3. Plots generated
            $plotCount = $this->db->selectOne(
                "SELECT COUNT(*) as c FROM plots WHERE colony_id = ?",
                [$colonyId]
            );
            $checks[] = [
                'label'  => 'Plots Generated',
                'passed' => ($plotCount['c'] ?? 0) > 0,
                'status' => ($plotCount['c'] ?? 0) > 0 ? 'complete' : 'pending',
                'detail' => ($plotCount['c'] ?? 0) . ' plots',
            ];

            // 4. Pricing applied
            $pricedPlots = $this->db->selectOne(
                "SELECT COUNT(*) as c FROM plots WHERE colony_id = ? AND total_price > 0",
                [$colonyId]
            );
            $checks[] = [
                'label'  => 'Pricing Applied',
                'passed' => ($pricedPlots['c'] ?? 0) > 0,
                'status' => ($pricedPlots['c'] ?? 0) > 0 ? 'complete' : 'pending',
            ];

            // 5. RERA registered
            $rera = $this->db->selectOne(
                "SELECT status FROM rera_projects WHERE rera_number = ? LIMIT 1",
                [$colony['rera_number'] ?? '']
            );
            $checks[] = [
                'label'  => 'RERA Registration',
                'passed' => ($rera['status'] ?? '') === 'Registered',
                'status' => $rera['status'] ?? 'not_started',
            ];

            // 6. Development costs tracked
            $devCost = $this->db->selectOne(
                "SELECT SUM(amount) as total FROM colony_development_costs WHERE colony_id = ?",
                [$colonyId]
            );
            $checks[] = [
                'label'  => 'Development Costs Recorded',
                'passed' => ($devCost['total'] ?? 0) > 0,
                'status' => ($devCost['total'] ?? 0) > 0 ? 'complete' : 'pending',
                'detail' => '₹' . number_format($devCost['total'] ?? 0),
            ];

            // Calculate overall readiness
            $passedCount = count(array_filter($checks, fn($c) => $c['passed']));
            $totalChecks = count($checks);
            $readinessPct = $totalChecks > 0 ? round(($passedCount / $totalChecks) * 100) : 0;

            return [
                'success'       => true,
                'checks'        => $checks,
                'passed_count'  => $passedCount,
                'total_checks'  => $totalChecks,
                'readiness_pct' => $readinessPct,
                'is_ready'      => $readinessPct === 100,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ================================================================
    //  PIPELINE OVERVIEW
    // ================================================================

    /**
     * Get complete pipeline status for a colony.
     */
    public function getPipelineStatus(int $colonyId): array
    {
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            $currentStage = $colony['pipeline_stage'] ?? self::STAGE_LAND_ACQUISITION;

            $stages = [
                self::STAGE_LAND_ACQUISITION  => ['label' => 'Land Acquisition',  'icon' => 'fa-file-contract',   'order' => 1],
                self::STAGE_MASTER_PLANNING   => ['label' => 'Master Planning',   'icon' => 'fa-drafting-compass', 'order' => 2],
                self::STAGE_PLOT_CUTTING      => ['label' => 'Plot Cutting',      'icon' => 'fa-vector-square',    'order' => 3],
                self::STAGE_RERA_REGISTRATION => ['label' => 'RERA Registration', 'icon' => 'fa-stamp',            'order' => 4],
                self::STAGE_DEVELOPMENT       => ['label' => 'Development',       'icon' => 'fa-hard-hat',         'order' => 5],
                self::STAGE_PRICING           => ['label' => 'Pricing',           'icon' => 'fa-tag',              'order' => 6],
                self::STAGE_SALES_READY       => ['label' => 'Sales Ready',       'icon' => 'fa-rocket',           'order' => 7],
            ];

            $currentOrder = $stages[$currentStage]['order'] ?? 0;

            // Enrich each stage
            foreach ($stages as $key => &$stage) {
                $stage['is_current']   = ($key === $currentStage);
                $stage['is_completed'] = ($stage['order'] < $currentOrder);
                $stage['is_pending']   = ($stage['order'] > $currentOrder);
            }

            // Quick stats
            $plotStats = $this->db->fetchOne(
                "SELECT COUNT(*) as total,
                        SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold,
                        SUM(total_price) as total_value
                 FROM plots WHERE colony_id = ?",
                [$colonyId]
            );

            $readiness = $this->getSalesReadinessChecklist($colonyId);

            return [
                'success'       => true,
                'colony'        => $colony,
                'current_stage' => $currentStage,
                'stages'        => $stages,
                'plot_stats'    => $plotStats,
                'readiness'     => $readiness,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get all colonies with their pipeline stages.
     *
     * @param string|null $filterStage  Optional filter by stage
     * @return array
     */
    public function getAllColoniesWithPipeline(?string $filterStage = null): array
    {
        try {
            $sql  = "SELECT c.*, 
                        (SELECT COUNT(*) FROM plots WHERE colony_id = c.id) as plot_count,
                        (SELECT COUNT(*) FROM plots WHERE colony_id = c.id AND status = 'available') as available_count,
                        (SELECT COUNT(*) FROM plots WHERE colony_id = c.id AND status = 'sold') as sold_count,
                        (SELECT SUM(amount) FROM colony_development_costs WHERE colony_id = c.id) as dev_cost_total,
                        (SELECT status FROM land_acquisitions WHERE colony_id = c.id ORDER BY id DESC LIMIT 1) as acquisition_status
                     FROM colonies c
                     WHERE c.is_active = 1";
            $params = [];

            if ($filterStage) {
                $sql .= " AND c.pipeline_stage = ?";
                $params[] = $filterStage;
            }

            $sql .= " ORDER BY c.id DESC";

            $colonies = $this->db->select($sql, $params);

            return ['success' => true, 'colonies' => $colonies ?? []];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ================================================================
    //  PRIVATE HELPERS
    // ================================================================

    private function updateColonyStage(int $colonyId, string $stage): void
    {
        $this->db->execute(
            "UPDATE colonies SET pipeline_stage = ?, updated_at = NOW() WHERE id = ?",
            [$stage, $colonyId]
        );
    }

    private function logActivity(int $colonyId, string $action, array $context = []): void
    {
        try {
            $this->db->insert('user_activity_logs_unified', [
                'user_id'     => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
                'action'      => $action,
                'context'     => json_encode(array_merge(['colony_id' => $colonyId], $context)),
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent'  => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at'  => date('Y-m-d H:i:s'),
            ]);
        } catch (Exception $e) {
        // Activity logging is best-effort
        error_log($e->getMessage());
        }
    }

    private function generateSlug(string $text): string
    {
        $slug = strtolower(trim($text));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        return trim($slug, '-');
    }

    private function safeRollback(): void
    {
        try {
            if ($this->db) {
                $this->db->rollBack();
            }
        } catch (Exception $e) {
        // Rollback failure is non-fatal
        error_log($e->getMessage());
        }
    }
}
