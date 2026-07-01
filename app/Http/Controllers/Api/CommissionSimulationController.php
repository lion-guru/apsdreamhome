<?php
/**
 * CommissionSimulationController
 *
 * API endpoints for simulating commission calculations.
 * Useful for:
 *   - Admin "what-if" analysis before approving bookings
 *   - Testing commission engine with sample data
 *   - Showing agents projected earnings before deal closure
 *
 * Routes:
 *   POST /api/commission/simulate     — Simulate commission for a booking or amount
 *   POST /api/commission/simulate-bulk — Simulate for multiple amounts
 *   GET  /api/commission/tds           — Calculate TDS for an amount
 *   GET  /api/commission/summary/{id}  — Get commission summary for a booking
 */

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Services\MLM\CommissionManager;
use App\Services\MLM\TdsConfigService;
use App\Services\HybridCommissionEngine;
use App\Services\MLM\MLMCommissionEngine;

class CommissionSimulationController extends BaseController
{
    /**
     * POST /api/commission/simulate
     *
     * Body JSON:
     *   booking_id (int)       — Simulate for existing booking
     *   OR
     *   amount (float)         — Sale amount to simulate
     *   project_type (string)  — 'colony' or 'generic' (default: 'colony')
     *   agent_user_id (int)    — Agent's user ID (optional, for upline walk)
     */
    public function simulate()
    {
        $this->requireAdmin();
        $data = $this->getJsonInput();

        // If booking_id provided, simulate for real booking
        if (!empty($data['booking_id'])) {
            $manager = new CommissionManager();
            $existing = $manager->getExistingCommissions((int)$data['booking_id']);

            if (!empty($existing)) {
                return $this->json([
                    'success' => true,
                    'mode' => 'existing',
                    'booking_id' => (int)$data['booking_id'],
                    'commissions' => $existing,
                    'total' => round(array_sum(array_column($existing, 'amount')), 2),
                ]);
            }

            // Calculate without writing (dry run)
            return $this->simulateDryRun((int)$data['booking_id']);
        }

        // Otherwise simulate with provided amount
        $amount = (float)($data['amount'] ?? 0);
        if ($amount <= 0) {
            return $this->json(['success' => false, 'error' => 'Amount must be > 0'], 400);
        }

        $projectType = $data['project_type'] ?? 'colony';
        $agentUserId = (int)($data['agent_user_id'] ?? 0);

        $results = $this->simulateByAmount($amount, $projectType, $agentUserId);

        return $this->json([
            'success' => true,
            'mode' => 'simulation',
            'input' => [
                'amount' => $amount,
                'project_type' => $projectType,
                'agent_user_id' => $agentUserId,
            ],
            'results' => $results,
        ]);
    }

    /**
     * POST /api/commission/simulate-bulk
     *
     * Body JSON:
     *   amounts: [100000, 200000, 500000, ...]
     *   project_type: 'colony'
     */
    public function simulateBulk()
    {
        $this->requireAdmin();
        $data = $this->getJsonInput();

        $amounts = $data['amounts'] ?? [];
        if (empty($amounts) || !is_array($amounts)) {
            return $this->json(['success' => false, 'error' => 'amounts array required'], 400);
        }

        $projectType = $data['project_type'] ?? 'colony';
        $results = [];

        foreach ($amounts as $amt) {
            $amount = (float)$amt;
            if ($amount > 0) {
                $results[] = [
                    'input_amount' => $amount,
                    'simulation' => $this->simulateByAmount($amount, $projectType),
                ];
            }
        }

        return $this->json([
            'success' => true,
            'count' => count($results),
            'results' => $results,
        ]);
    }

    /**
     * GET /api/commission/tds?amount=X&section=194H&pan=AAAAA1234A
     */
    public function tdsCalc()
    {
        $this->requireAdmin();
        $amount = (float)($_GET['amount'] ?? 0);
        $section = $_GET['section'] ?? '194H';
        $pan = $_GET['pan'] ?? null;

        if ($amount <= 0) {
            return $this->json(['success' => false, 'error' => 'Amount must be > 0'], 400);
        }

        $tdsService = new TdsConfigService();
        $result = $tdsService->calculate($section, $amount, $pan);

        return $this->json([
            'success' => true,
            'input' => ['amount' => $amount, 'section' => $section, 'pan' => $pan],
            'calculation' => $result,
        ]);
    }

    /**
     * GET /api/commission/summary/{bookingId}
     */
    public function summary($bookingId)
    {
        $this->requireAdmin();
        $manager = new CommissionManager();
        $summary = $manager->getBookingSummary((int)$bookingId);

        return $this->json([
            'success' => true,
            'summary' => $summary,
        ]);
    }

    /**
     * Simulate commission for an existing booking without writing.
     */
    private function simulateDryRun(int $bookingId): array
    {
        try {
            // Use HybridCommissionEngine for colony projects
            $engine = new HybridCommissionEngine();
            $bookingCtx = $this->resolveBookingContext($bookingId);
            if (!$bookingCtx) {
                return $this->json(['success' => false, 'error' => 'Booking not found or missing data'], 404);
            }
            $calculation = $engine->processPipelineCommission(
                $bookingId,
                $bookingCtx['receipt_id'],
                $bookingCtx['amount'],
                $bookingCtx['agent_id']
            );

            $tdsService = new TdsConfigService();
            $tdsResults = [];

            foreach ($calculation['entries'] ?? [] as $entry) {
                $tds = $tdsService->calculateForCommission($entry['amount']);
                $tdsResults[] = [
                    'user_id' => $entry['beneficiary_user_id'],
                    'gross' => $entry['amount'],
                    'tds' => $tds['tds_amount'],
                    'net' => $tds['net_payable'],
                ];
            }

            return $this->json([
                'success' => true,
                'mode' => 'dry_run',
                'booking_id' => $bookingId,
                'entries' => $calculation['entries'] ?? [],
                'total_gross' => $calculation['total'] ?? 0,
                'tds_breakdown' => $tdsResults,
                'total_tds' => round(array_sum(array_column($tdsResults, 'tds')), 2),
                'total_net' => round(array_sum(array_column($tdsResults, 'net')), 2),
            ]);
        } catch (\Exception $e) {
            return $this->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Simulate commission by amount (not tied to a real booking).
     */
    private function simulateByAmount(float $amount, string $projectType, int $agentUserId = 0): array
    {
        $results = [
            'total_amount' => $amount,
            'tracks' => [],
            'tds' => [],
        ];

        if ($projectType === 'colony') {
            // HybridEngine: Track A (slab) + Track B (performance) + Track C (escrow)
            $results['tracks']['track_a_slab'] = round($amount * 0.15, 2); // 15% slab
            $results['tracks']['track_b_performance'] = round($amount * 0.03, 2); // 3% performance
            $results['tracks']['track_c_escrow'] = round($amount * 0.02, 2); // 2% escrow
            $results['total_commission'] = round(
                $results['tracks']['track_a_slab'] +
                $results['tracks']['track_b_performance'] +
                $results['tracks']['track_c_escrow'],
                2
            );

            // Apply 20% global cap
            $cap = round($amount * 0.20, 2);
            if ($results['total_commission'] > $cap) {
                $results['cap_applied'] = true;
                $results['cap_amount'] = $cap;
                $results['total_commission'] = $cap;
            }
        } else {
            // MLMEngine: direct + L1/L2/L3
            $results['tracks']['direct_sale'] = round($amount * 0.02, 2); // 2%
            $results['tracks']['l1_override'] = round($amount * 0.03, 2); // 3%
            $results['tracks']['l2_override'] = round($amount * 0.015, 2); // 1.5%
            $results['tracks']['l3_override'] = round($amount * 0.01, 2); // 1%
            $results['total_commission'] = round(
                array_sum($results['tracks']),
                2
            );
        }

        // TDS calculation
        $tdsService = new TdsConfigService();
        $tds = $tdsService->calculateForCommission($results['total_commission']);
        $results['tds'] = $tds;
        $results['net_payable'] = $tds['net_payable'];

        return $results;
    }

    /**
     * Resolve booking context needed by HybridCommissionEngine::processPipelineCommission().
     *
     * @return array{receipt_id: int, amount: float, agent_id: int}|null
     */
    private function resolveBookingContext(int $bookingId): ?array
    {
        try {
            $db = \App\Core\Database\Database::getInstance();
            $pdo = method_exists($db, 'getPdo') ? $db->getPdo() : $db;

            $stmt = $pdo->prepare("
                SELECT 
                    pb.id,
                    pb.total_plot_value,
                    pb.booking_amount,
                    pb.total_amount,
                    pb.associate_id,
                    IFNULL(pb.booking_amount, pb.total_amount * 0.1) AS amount,
                    u.id AS agent_id
                FROM plot_bookings pb
                JOIN users u ON u.id = pb.associate_id
                WHERE pb.id = ?
                LIMIT 1
            ");
            $stmt->execute([$bookingId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return [
                'receipt_id' => (int)$row['id'],
                'amount'     => (float)($row['amount'] ?? $row['booking_amount'] ?? 0),
                'agent_id'   => (int)$row['agent_id'],
            ];
        } catch (\Exception $e) {
            error_log('[CommissionSimulationController] resolveBookingContext failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get JSON input from request body.
     */
    private function getJsonInput(): array
    {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}
