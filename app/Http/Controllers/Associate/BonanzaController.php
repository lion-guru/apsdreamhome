<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Core\Middleware\TenantContext;

/**
 * Associate-facing bonanza races: my progress + top-10 leaderboard.
 * Route: GET /associate/bonanza-races
 */
class BonanzaController extends BaseController
{
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    public function races()
    {
        $this->requireAuth();
        $userId = (int)$_SESSION['user_id'];
        $myRaces = [];
        $leaders = [];
        $activeRace = null;
        try {
            $db = \App\Core\Database\Database::getInstance();
            $tid = TenantContext::getId();
            $tSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $tParams = $tid > 1 ? [$tid] : [];
            $races = $db->fetchAll(
                "SELECT * FROM sales_races WHERE is_active = 1 AND start_date <= CURDATE() AND end_date >= CURDATE()" . $tSql . " ORDER BY end_date ASC",
                $tParams
            ) ?: [];
            // my associate_id for progress mapping
            $assoc = $db->fetchOne("SELECT id FROM associates WHERE user_id = ? LIMIT 1", [$userId]);
            $myAssocId = (int)($assoc['id'] ?? 0);
            foreach ($races as $race) {
                $mine = 0;
                if ($myAssocId > 0) {
                    $mine = (int)($db->fetchOne(
                        "SELECT COUNT(*) c FROM plot_bookings WHERE associate_id = ? AND status != 'cancelled'
                         AND DATE(COALESCE(booking_date, created_at)) BETWEEN ? AND ?",
                        [$myAssocId, $race['start_date'], $race['end_date']]
                    )['c'] ?? 0);
                }
                $myRaces[] = ['race' => $race, 'mine' => $mine];
                if ($activeRace === null) {
                    $activeRace = $race;
                    $leaders = \App\Http\Controllers\Admin\BonanzaController::standings(
                        $db, (string)$race['start_date'], (string)$race['end_date'], 10
                    );
                }
            }
        } catch (\Throwable $e) {
            error_log('[Associate BonanzaController::races] ' . $e->getMessage());
        }
        $this->render('associate/bonanza_races', [
            'page_title' => 'Bonanza Races - Associate Portal',
            'my_races' => $myRaces,
            'leaders' => $leaders,
            'active_race' => $activeRace,
        ], 'layouts/associate');
    }
}
