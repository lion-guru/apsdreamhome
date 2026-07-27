<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use Exception;

/**
 * ColonyHealthService — Automated health scoring for colony development pipeline.
 *
 * Evaluates each colony across 7 pipeline stages and produces:
 *   - Overall health score (0-100)
 *   - Per-stage health breakdown with status, issues, and suggestions
 *   - Risk flags (delayed, over-budget, stalled)
 *   - Recommended next actions
 *
 * Scoring weights:
 *   Stage completion:    40% (how far along the pipeline)
 *   Data completeness:   20% (required fields filled)
 *   Financial health:    20% (costs within budget, pricing applied)
 *   Compliance:          20% (RERA, legal, documentation)
 */
class ColonyHealthService
{
    /** @var Database */
    private $db;

    // Stage weights for overall score
    const STAGE_WEIGHTS = [
        'land_acquisition'  => 14,
        'master_planning'   => 14,
        'plot_cutting'      => 14,
        'rera_registration' => 14,
        'development'       => 16,
        'pricing'           => 14,
        'sales_ready'       => 14,
    ];

    const STAGE_ORDER = [
        'land_acquisition'  => 1,
        'master_planning'   => 2,
        'plot_cutting'      => 3,
        'rera_registration' => 4,
        'development'       => 5,
        'pricing'           => 6,
        'sales_ready'       => 7,
    ];

    public function __construct()
    {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            $this->db = null;
        }
    }

    /**
     * Calculate health score for a single colony.
     */
    public function getColonyHealth(int $colonyId): array
    {
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            $currentStage = $colony['pipeline_stage'] ?? 'land_acquisition';
            $currentStageNum = self::STAGE_ORDER[$currentStage] ?? 1;

            // Run per-stage checks
            $stages = $this->evaluateAllStages($colonyId, $colony);

            // Calculate overall score
            $overallScore = 0;
            $totalWeight = 0;
            foreach ($stages as $stageData) {
                $weight = self::STAGE_WEIGHTS[$stageData['key']] ?? 14;
                $overallScore += ($stageData['score'] * $weight / 100);
                $totalWeight += $weight;
            }
            $overallScore = $totalWeight > 0 ? round($overallScore * 100 / $totalWeight) : 0;

            // Detect risks
            $risks = $this->detectRisks($colonyId, $colony, $stages);

            // Generate recommendations
            $recommendations = $this->generateRecommendations($colonyId, $colony, $stages, $risks);

            // Health grade
            $grade = $this->getGrade($overallScore);

            return [
                'success'          => true,
                'colony_id'        => $colonyId,
                'colony_name'      => $colony['name'] ?? '',
                'current_stage'    => $currentStage,
                'overall_score'    => $overallScore,
                'grade'            => $grade,
                'stages'           => $stages,
                'risks'            => $risks,
                'recommendations'  => $recommendations,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get health scores for all active colonies (dashboard overview).
     */
    public function getAllColoniesHealth(): array
    {
        try {
            $colonies = $this->db->fetchAll(
                "SELECT * FROM colonies WHERE is_active = 1 ORDER BY name"
            );

            $results = [];
            foreach ($colonies as $colony) {
                $health = $this->getColonyHealth((int)$colony['id']);
                if ($health['success']) {
                    $results[] = [
                        'colony_id'      => (int)$colony['id'],
                        'name'           => $colony['name'],
                        'current_stage'  => $health['current_stage'],
                        'overall_score'  => $health['overall_score'],
                        'grade'          => $health['grade'],
                        'risk_count'     => count($health['risks']),
                        'top_risk'       => !empty($health['risks']) ? $health['risks'][0]['message'] : null,
                        'recommendation' => !empty($health['recommendations']) ? $health['recommendations'][0]['action'] : null,
                    ];
                }
            }

            return ['success' => true, 'colonies' => $results];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    // ─── Stage Evaluation ────────────────────────────────────────

    /**
     * Find colonies with health score below a threshold (for alerts).
     * @param int $threshold Score below which to alert (default 50)
     */
    public function getColoniesBelowThreshold(int $threshold = 50): array
    {
        try {
            $allHealth = $this->getAllColoniesHealth();
            if (!$allHealth['success']) {
                return [];
            }

            $alerts = [];
            foreach ($allHealth['colonies'] as $colony) {
                if (($colony['overall_score'] ?? 100) < $threshold) {
                    $alerts[] = [
                        'colony_id'   => $colony['colony_id'],
                        'name'        => $colony['name'],
                        'score'       => $colony['overall_score'],
                        'grade'       => $colony['grade']['letter'] ?? 'F',
                        'risk_count'  => $colony['risk_count'],
                        'top_risk'    => $colony['top_risk'],
                        'recommendation' => $colony['recommendation'],
                    ];
                }
            }

            // Sort by score ascending (worst first)
            usort($alerts, fn($a, $b) => $a['score'] <=> $b['score']);

            return $alerts;
        } catch (Exception $e) {
            error_log('getColoniesBelowThreshold error: ' . $e->getMessage());
            return [];
        }
    }

    private function evaluateAllStages(int $colonyId, array $colony): array
    {
        $currentStage = $colony['pipeline_stage'] ?? 'land_acquisition';
        $currentNum = self::STAGE_ORDER[$currentStage] ?? 1;

        $stages = [];
        foreach (self::STAGE_ORDER as $key => $num) {
            $isComplete = $num < $currentNum;
            $isCurrent  = $num === $currentNum;
            $isFuture   = $num > $currentNum;

            $evaluation = $this->evaluateStage($colonyId, $colony, $key, $isComplete, $isCurrent);

            $stages[] = [
                'key'       => $key,
                'label'     => ucwords(str_replace('_', ' ', $key)),
                'order'     => $num,
                'status'    => $isComplete ? 'completed' : ($isCurrent ? 'current' : 'pending'),
                'score'     => $evaluation['score'],
                'issues'    => $evaluation['issues'],
                'metrics'   => $evaluation['metrics'],
            ];
        }

        return $stages;
    }

    private function evaluateStage(int $colonyId, array $colony, string $stage, bool $isComplete, bool $isCurrent): array
    {
        $score = 100;
        $issues = [];
        $metrics = [];

        switch ($stage) {
            case 'land_acquisition':
                $deal = $this->db->fetchOne(
                    "SELECT * FROM land_deals WHERE colony_id = ? ORDER BY id DESC LIMIT 1",
                    [$colonyId]
                );
                $leadCount = $this->db->fetchOne(
                    "SELECT COUNT(*) as c FROM land_leads WHERE district = ?",
                    [$colony['district'] ?? '']
                );

                $metrics['deal_status'] = $deal['status'] ?? 'none';
                $metrics['leads_found'] = (int)($leadCount['c'] ?? 0);

                if (!$deal) {
                    $score = $isComplete ? 0 : ($isCurrent ? 30 : 0);
                    $issues[] = 'No land deal record found';
                } elseif ($deal['status'] !== 'registered') {
                    $score = 70;
                    $issues[] = "Deal status is '{$deal['status']}', not yet registered";
                }
                if ((float)($colony['total_area_acres'] ?? 0) <= 0) {
                    $score = min($score, 20);
                    $issues[] = 'Land area not specified';
                }
                break;

            case 'master_planning':
                $layout = $this->db->fetchOne(
                    "SELECT * FROM colony_layouts WHERE colony_id = ? AND is_current = 1",
                    [$colonyId]
                );

                $metrics['has_layout'] = !empty($layout);
                $metrics['layout_status'] = $layout['status'] ?? 'none';

                if (!$layout) {
                    $score = $isComplete ? 10 : ($isCurrent ? 20 : 0);
                    $issues[] = 'No master plan created';
                } else {
                    $openSpace = ($layout['road_area_pct'] ?? 0) + ($layout['common_area_pct'] ?? 0);
                    $metrics['open_space_pct'] = $openSpace;
                    if ($openSpace < 30) {
                        $score = min($score, 60);
                        $issues[] = "Open space {$openSpace}% < 30% RERA minimum";
                    }
                    if ($layout['status'] !== 'approved') {
                        $score = min($score, 80);
                        $issues[] = "Layout status: {$layout['status']}";
                    }
                }
                break;

            case 'plot_cutting':
                $plotStats = $this->db->fetchOne(
                    "SELECT COUNT(*) as total,
                            SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                            SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold
                     FROM plots WHERE colony_id = ?",
                    [$colonyId]
                );

                $metrics['total_plots'] = (int)($plotStats['total'] ?? 0);
                $metrics['available_plots'] = (int)($plotStats['available'] ?? 0);
                $metrics['sold_plots'] = (int)($plotStats['sold'] ?? 0);

                if ($plotStats['total'] == 0) {
                    $score = $isComplete ? 0 : ($isCurrent ? 10 : 0);
                    $issues[] = 'No plots generated';
                } else {
                    $ratio = (float)($colony['total_area_acres'] ?? 0) > 0
                        ? ($plotStats['total'] / ((float)$colony['total_area_acres'] * 43560 / 1200))
                        : 0;
                    $metrics['plot_density_ratio'] = round($ratio, 2);
                }
                break;

            case 'rera_registration':
                $rera = null;
                if (!empty($colony['rera_number'])) {
                    $rera = $this->db->fetchOne(
                        "SELECT * FROM rera_projects WHERE rera_number = ? LIMIT 1",
                        [$colony['rera_number']]
                    );
                }
                $milestoneCount = 0;
                $milestoneDone = 0;
                if ($rera) {
                    $milestones = $this->db->fetchOne(
                        "SELECT COUNT(*) as total,
                                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as done
                         FROM rera_milestones WHERE project_id = ?",
                        [$rera['id']]
                    );
                    $milestoneCount = (int)($milestones['total'] ?? 0);
                    $milestoneDone = (int)($milestones['done'] ?? 0);
                }

                $metrics['has_rera'] = !empty($colony['rera_number']);
                $metrics['rera_number'] = $colony['rera_number'] ?? '';
                $metrics['milestones_total'] = $milestoneCount;
                $metrics['milestones_done'] = $milestoneDone;

                if (empty($colony['rera_number'])) {
                    $score = $isComplete ? 0 : ($isCurrent ? 15 : 0);
                    $issues[] = 'RERA number not assigned';
                } elseif ($milestoneCount > 0 && $milestoneDone < $milestoneCount) {
                    $pct = round(($milestoneDone / $milestoneCount) * 100);
                    $score = min($score, max(40, $pct));
                    $issues[] = "{$milestoneDone}/{$milestoneCount} milestones completed ({$pct}%)";
                }
                break;

            case 'development':
                $devCost = $this->db->fetchOne(
                    "SELECT COUNT(*) as count, COALESCE(SUM(amount),0) as total,
                            COALESCE(SUM(paid_amount),0) as paid
                     FROM colony_development_costs WHERE colony_id = ?",
                    [$colonyId]
                );

                $metrics['cost_entries'] = (int)($devCost['count'] ?? 0);
                $metrics['total_cost'] = (float)($devCost['total'] ?? 0);
                $metrics['paid_cost'] = (float)($devCost['paid'] ?? 0);

                if ($devCost['count'] == 0) {
                    $score = $isComplete ? 10 : ($isCurrent ? 25 : 0);
                    $issues[] = 'No development costs recorded';
                } elseif ($devCost['total'] > 0 && $devCost['paid'] < $devCost['total'] * 0.5) {
                    $score = min($score, 60);
                    $pctPaid = round(($devCost['paid'] / $devCost['total']) * 100);
                    $issues[] = "Only {$pctPaid}% of development costs paid";
                }
                break;

            case 'pricing':
                $pricedPlots = $this->db->fetchOne(
                    "SELECT COUNT(*) as total,
                            SUM(CASE WHEN total_price > 0 THEN 1 ELSE 0 END) as priced
                     FROM plots WHERE colony_id = ?",
                    [$colonyId]
                );

                $metrics['total_plots'] = (int)($pricedPlots['total'] ?? 0);
                $metrics['priced_plots'] = (int)($pricedPlots['priced'] ?? 0);
                $metrics['starting_price'] = (float)($colony['starting_price'] ?? 0);

                if ($pricedPlots['total'] == 0) {
                    $score = $isComplete ? 5 : ($isCurrent ? 20 : 0);
                    $issues[] = 'No plots to price';
                } elseif ($pricedPlots['priced'] == 0) {
                    $score = min($score, 30);
                    $issues[] = 'No pricing applied to plots';
                } elseif ($pricedPlots['priced'] < $pricedPlots['total']) {
                    $pct = round(($pricedPlots['priced'] / $pricedPlots['total']) * 100);
                    $score = min($score, max(50, $pct));
                    $issues[] = "Only {$pct}% of plots priced";
                }
                if ((float)($colony['starting_price'] ?? 0) <= 0) {
                    $score = min($score, 20);
                    $issues[] = 'Starting price not set';
                }
                break;

            case 'sales_ready':
                $checklist = $this->getSalesReadinessItems($colonyId);
                $passed = 0;
                $total = count($checklist);
                foreach ($checklist as $item) {
                    if ($item['met']) $passed++;
                }

                $metrics['checklist_passed'] = $passed;
                $metrics['checklist_total'] = $total;

                if ($total > 0) {
                    $pct = round(($passed / $total) * 100);
                    $score = $pct;
                    if ($passed < $total) {
                        $missing = array_filter($checklist, fn($c) => !$c['met']);
                        $issues[] = ($total - $passed) . " readiness checks failing";
                    }
                } else {
                    $score = 0;
                    $issues[] = 'Sales readiness not evaluated';
                }
                break;
        }

        return [
            'score'   => max(0, min(100, $score)),
            'issues'  => $issues,
            'metrics' => $metrics,
        ];
    }

    private function getSalesReadinessItems(int $colonyId): array
    {
        $items = [];

        // Check each critical item
        $checks = [
            ['Land deal registered', "SELECT COUNT(*) as c FROM land_deals WHERE colony_id = ? AND status = 'registered'"],
            ['Master plan approved', "SELECT COUNT(*) as c FROM colony_layouts WHERE colony_id = ? AND status = 'approved'"],
            ['Plots generated', "SELECT COUNT(*) as c FROM plots WHERE colony_id = ?"],
            ['RERA registered', "SELECT COUNT(*) as c FROM colonies WHERE id = ? AND rera_number IS NOT NULL AND rera_number != ''"],
            ['Development costs recorded', "SELECT COUNT(*) as c FROM colony_development_costs WHERE colony_id = ?"],
            ['Pricing applied', "SELECT COUNT(*) as c FROM plots WHERE colony_id = ? AND total_price > 0"],
        ];

        foreach ($checks as [$label, $sql]) {
            $row = $this->db->fetchOne($sql, [$colonyId]);
            $items[] = [
                'label' => $label,
                'met'   => ($row['c'] ?? 0) > 0,
            ];
        }

        return $items;
    }

    // ─── Risk Detection ──────────────────────────────────────────

    private function detectRisks(int $colonyId, array $colony, array $stages): array
    {
        $risks = [];

        // Check for stalled colony (same stage for too long)
        $updated = $colony['updated_at'] ?? $colony['created_at'] ?? '';
        if ($updated) {
            $daysSinceUpdate = (int)((time() - strtotime($updated)) / 86400);
            if ($daysSinceUpdate > 30) {
                $risks[] = [
                    'level'   => 'high',
                    'message' => "Stalled for {$daysSinceUpdate} days (no updates)",
                    'type'    => 'stalled',
                ];
            } elseif ($daysSinceUpdate > 14) {
                $risks[] = [
                    'level'   => 'medium',
                    'message' => "No updates in {$daysSinceUpdate} days",
                    'type'    => 'slow_progress',
                ];
            }
        }

        // Check budget overrun
        $devCost = $this->db->fetchOne(
            "SELECT COALESCE(SUM(amount),0) as total FROM colony_development_costs WHERE colony_id = ?",
            [$colonyId]
        );
        $estimated = floatval($colony['estimated_land_cost'] ?? 0);
        if ($estimated > 0 && ($devCost['total'] ?? 0) > $estimated * 0.5) {
            $risks[] = [
                'level'   => 'medium',
                'message' => 'Development costs exceeding 50% of land cost',
                'type'    => 'budget',
            ];
        }

        // Check RERA milestones delayed
        if (!empty($colony['rera_number'])) {
            $rera = $this->db->fetchOne(
                "SELECT id FROM rera_projects WHERE rera_number = ? LIMIT 1",
                [$colony['rera_number']]
            );
            if ($rera) {
                $delayed = $this->db->fetchOne(
                    "SELECT COUNT(*) as c FROM rera_milestones WHERE project_id = ? AND status = 'delayed'",
                    [$rera['id']]
                );
                if (($delayed['c'] ?? 0) > 0) {
                    $risks[] = [
                        'level'   => 'high',
                        'message' => "{$delayed['c']} RERA milestone(s) delayed",
                        'type'    => 'rera_delay',
                    ];
                }
            }
        }

        // Check low inventory
        $plots = $this->db->fetchOne(
            "SELECT COUNT(*) as total,
                    SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as avail
             FROM plots WHERE colony_id = ?",
            [$colonyId]
        );
        if (($plots['total'] ?? 0) > 0 && ($plots['avail'] ?? 0) > 0) {
            $availPct = round(($plots['avail'] / $plots['total']) * 100);
            if ($availPct < 20) {
                $risks[] = [
                    'level'   => 'medium',
                    'message' => "Only {$availPct}% plots remaining ({$plots['avail']}/{$plots['total']})",
                    'type'    => 'low_inventory',
                ];
            }
        }

        return $risks;
    }

    // ─── Recommendations ──────────────────────────────────────────

    private function generateRecommendations(int $colonyId, array $colony, array $stages, array $risks): array
    {
        $recs = [];
        $currentStage = $colony['pipeline_stage'] ?? 'land_acquisition';

        // Find weakest stage
        $weakest = null;
        $weakestScore = 101;
        foreach ($stages as $s) {
            if ($s['status'] !== 'completed' && $s['score'] < $weakestScore) {
                $weakestScore = $s['score'];
                $weakest = $s;
            }
        }

        if ($weakest && $weakest['score'] < 50) {
            $recs[] = [
                'priority' => 'high',
                'action'   => "Focus on {$weakest['label']} (score: {$weakest['score']}%)",
                'detail'   => !empty($weakest['issues']) ? $weakest['issues'][0] : '',
            ];
        }

        // Stage-specific recommendations
        switch ($currentStage) {
            case 'land_acquisition':
                $recs[] = ['priority' => 'medium', 'action' => 'Complete land deal registration', 'detail' => 'Register the sale deed at sub-registrar office'];
                break;
            case 'master_planning':
                $recs[] = ['priority' => 'medium', 'action' => 'Get layout approved', 'detail' => 'Submit master plan for municipal approval'];
                break;
            case 'plot_cutting':
                $recs[] = ['priority' => 'medium', 'action' => 'Apply RERA-compliant plot sizes', 'detail' => 'Ensure 120sqft minimum per RERA guidelines'];
                break;
            case 'rera_registration':
                $recs[] = ['priority' => 'high', 'action' => 'Complete RERA registration', 'detail' => 'Apply on UPRERA portal with all documents'];
                break;
            case 'development':
                $recs[] = ['priority' => 'medium', 'action' => 'Record development costs', 'detail' => 'Track all infrastructure expenses for pricing'];
                break;
            case 'pricing':
                $recs[] = ['priority' => 'medium', 'action' => 'Apply feasibility-based pricing', 'detail' => 'Use ColonyFeasibilityService for optimal price/sqft'];
                break;
            case 'sales_ready':
                $recs[] = ['priority' => 'low', 'action' => 'Complete sales readiness checklist', 'detail' => 'Ensure all compliance items are met'];
                break;
        }

        // Risk-based recommendations
        foreach ($risks as $risk) {
            if ($risk['level'] === 'high') {
                $recs[] = [
                    'priority' => 'high',
                    'action'   => "Address risk: {$risk['message']}",
                    'detail'   => $risk['type'],
                ];
            }
        }

        return array_slice($recs, 0, 5);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function getGrade(int $score): array
    {
        if ($score >= 90) return ['letter' => 'A+', 'label' => 'Excellent', 'color' => 'success'];
        if ($score >= 80) return ['letter' => 'A',  'label' => 'Very Good', 'color' => 'success'];
        if ($score >= 70) return ['letter' => 'B+', 'label' => 'Good',      'color' => 'info'];
        if ($score >= 60) return ['letter' => 'B',  'label' => 'Fair',      'color' => 'info'];
        if ($score >= 50) return ['letter' => 'C',  'label' => 'Average',   'color' => 'warning'];
        if ($score >= 30) return ['letter' => 'D',  'label' => 'Poor',      'color' => 'danger'];
        return ['letter' => 'F', 'label' => 'Critical', 'color' => 'danger'];
    }
}
