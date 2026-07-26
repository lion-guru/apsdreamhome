<?php

namespace App\Services\Land;

use App\Core\Database\Database;
use Exception;

/**
 * PipelineWorkflowService — Auto-advance stages, validate transitions, track history.
 *
 * Stage transitions:
 *   land_acquisition → master_planning  (when acquisition registered)
 *   master_planning  → plot_cutting     (when layout approved + plots config valid)
 *   plot_cutting     → rera_registration (when plots generated + compliance passed)
 *   rera_registration → development     (when RERA number registered)
 *   development      → pricing          (when dev costs recorded)
 *   pricing          → sales_ready      (when pricing applied + all checklist passes)
 */
class PipelineWorkflowService
{
    /** @var Database */
    private $db;

    /** @var LegalColonyDevelopmentService */
    private $pipeline;

    // Valid stage transitions
    const STAGE_ORDER = [
        'land_acquisition'  => 1,
        'master_planning'   => 2,
        'plot_cutting'      => 3,
        'rera_registration' => 4,
        'development'       => 5,
        'pricing'           => 6,
        'sales_ready'       => 7,
    ];

    const VALID_TRANSITIONS = [
        'land_acquisition'  => 'master_planning',
        'master_planning'   => 'plot_cutting',
        'plot_cutting'      => 'rera_registration',
        'rera_registration' => 'development',
        'development'       => 'pricing',
        'pricing'           => 'sales_ready',
    ];

    public function __construct()
    {
        try {
            $this->db       = Database::getInstance();
            $this->pipeline = new LegalColonyDevelopmentService();
        } catch (Exception $e) {
            $this->db       = null;
            $this->pipeline = null;
        }
    }

    /**
     * Auto-check if a colony can advance to the next stage.
     * Returns what's blocking it and what's needed.
     */
    public function checkAdvanceReadiness(int $colonyId): array
    {
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            $currentStage = $colony['pipeline_stage'] ?? 'land_acquisition';
            $nextStage    = self::VALID_TRANSITIONS[$currentStage] ?? null;

            if (!$nextStage) {
                return [
                    'success'   => true,
                    'can_advance' => false,
                    'message'   => 'Colony is already at the final stage (sales_ready)',
                    'current'   => $currentStage,
                ];
            }

            // Check requirements for next stage
            $requirements = $this->getStageRequirements($colonyId, $nextStage);
            $allPassed    = true;
            $blocking     = [];

            foreach ($requirements as $req) {
                if (!$req['met']) {
                    $allPassed = false;
                    $blocking[] = $req;
                }
            }

            return [
                'success'       => true,
                'can_advance'   => $allPassed,
                'current_stage' => $currentStage,
                'next_stage'    => $nextStage,
                'requirements'  => $requirements,
                'blocking'      => $blocking,
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get requirements for advancing to a specific stage.
     */
    private function getStageRequirements(int $colonyId, string $targetStage): array
    {
        $reqs = [];

        switch ($targetStage) {
            case 'master_planning':
                $acq = $this->db->fetchOne(
                    "SELECT status FROM land_deals WHERE colony_id = ? ORDER BY id DESC LIMIT 1",
                    [$colonyId]
                );
                $reqs[] = [
                    'label' => 'Land acquisition registered',
                    'met'   => in_array($acq['status'] ?? '', ['registered', 'mutated', 'closed']),
                    'detail' => 'Status: ' . ($acq['status'] ?? 'not_started'),
                ];
                break;

            case 'plot_cutting':
                $layout = $this->db->fetchOne(
                    "SELECT status, road_area_pct, common_area_pct FROM colony_layouts WHERE colony_id = ? AND is_current = 1",
                    [$colonyId]
                );
                $reqs[] = [
                    'label' => 'Master plan created',
                    'met'   => !empty($layout['status']),
                    'detail' => 'Layout: ' . ($layout['layout_name'] ?? 'none'),
                ];
                $openSpace = ($layout['road_area_pct'] ?? 0) + ($layout['common_area_pct'] ?? 0);
                $reqs[] = [
                    'label' => 'Open space ≥ 30% (RERA)',
                    'met'   => $openSpace >= 30,
                    'detail' => 'Current: ' . number_format($openSpace, 1) . '%',
                ];
                break;

            case 'rera_registration':
                $plotCount = $this->db->fetchOne(
                    "SELECT COUNT(*) as c FROM plots WHERE colony_id = ?",
                    [$colonyId]
                );
                $reqs[] = [
                    'label' => 'Plots generated',
                    'met'   => ($plotCount['c'] ?? 0) > 0,
                    'detail' => ($plotCount['c'] ?? 0) . ' plots',
                ];
                break;

            case 'development':
                $rera = $this->db->fetchOne(
                    "SELECT status FROM rera_projects WHERE rera_number = (SELECT rera_number FROM colonies WHERE id = ?) LIMIT 1",
                    [$colonyId]
                );
                $reqs[] = [
                    'label' => 'RERA registered',
                    'met'   => ($rera['status'] ?? '') === 'Registered',
                    'detail' => 'RERA: ' . ($rera['rera_number'] ?? 'not registered'),
                ];
                break;

            case 'pricing':
                $devCost = $this->db->fetchOne(
                    "SELECT SUM(amount) as total FROM colony_development_costs WHERE colony_id = ?",
                    [$colonyId]
                );
                $reqs[] = [
                    'label' => 'Development costs recorded',
                    'met'   => ($devCost['total'] ?? 0) > 0,
                    'detail' => '₹' . number_format($devCost['total'] ?? 0),
                ];
                break;

            case 'sales_ready':
                $pricedPlots = $this->db->fetchOne(
                    "SELECT COUNT(*) as c FROM plots WHERE colony_id = ? AND total_price > 0",
                    [$colonyId]
                );
                $reqs[] = [
                    'label' => 'Pricing applied to plots',
                    'met'   => ($pricedPlots['c'] ?? 0) > 0,
                    'detail' => ($pricedPlots['c'] ?? 0) . ' priced plots',
                ];
                $colony = $this->db->fetchOne("SELECT starting_price FROM colonies WHERE id = ?", [$colonyId]);
                $reqs[] = [
                    'label' => 'Starting price set',
                    'met'   => ($colony['starting_price'] ?? 0) > 0,
                    'detail' => '₹' . number_format($colony['starting_price'] ?? 0),
                ];
                break;
        }

        return $reqs;
    }

    /**
     * Force-advance a colony to the next stage (admin override).
     */
    public function advanceStage(int $colonyId, string $reason = ''): array
    {
        try {
            $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
            if (!$colony) {
                return ['success' => false, 'error' => 'Colony not found'];
            }

            $currentStage = $colony['pipeline_stage'] ?? 'land_acquisition';
            $nextStage    = self::VALID_TRANSITIONS[$currentStage] ?? null;

            if (!$nextStage) {
                return ['success' => false, 'error' => 'Already at final stage'];
            }

            $this->db->beginTransaction();

            // Update stage
            $this->db->execute(
                "UPDATE colonies SET pipeline_stage = ?, updated_at = NOW() WHERE id = ?",
                [$nextStage, $colonyId]
            );

            // Log transition
            $this->db->insert('user_activity_logs_unified', [
                'user_id'    => $_SESSION['admin_id'] ?? 0,
                'action'     => 'pipeline_stage_advanced',
                'context'    => json_encode([
                    'colony_id'    => $colonyId,
                    'from'         => $currentStage,
                    'to'           => $nextStage,
                    'reason'       => $reason,
                    'admin_override' => true,
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            $this->db->commit();

            return [
                'success'    => true,
                'from_stage' => $currentStage,
                'to_stage'   => $nextStage,
            ];
        } catch (Exception $e) {
            try { $this->db->rollBack(); } catch (Exception $x) {}
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get stage transition history for a colony.
     */
    public function getStageHistory(int $colonyId): array
    {
        try {
            $logs = $this->db->fetchAll(
                "SELECT * FROM user_activity_logs_unified
                 WHERE action = 'pipeline_stage_advanced'
                 AND JSON_EXTRACT(context, '$.colony_id') = ?
                 ORDER BY created_at DESC",
                [$colonyId]
            );

            $history = [];
            foreach ($logs as $log) {
                $ctx = json_decode($log['context'] ?? '{}', true);
                $history[] = [
                    'from'      => $ctx['from'] ?? '',
                    'to'        => $ctx['to'] ?? '',
                    'reason'    => $ctx['reason'] ?? '',
                    'override'  => $ctx['admin_override'] ?? false,
                    'admin_id'  => $log['user_id'] ?? 0,
                    'timestamp' => $log['created_at'] ?? '',
                ];
            }

            return ['success' => true, 'history' => $history];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
