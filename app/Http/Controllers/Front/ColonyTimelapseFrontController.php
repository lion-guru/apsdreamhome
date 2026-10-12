<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

class ColonyTimelapseFrontController extends BaseController
{
    /**
     * GET /colony/{id}/timelapse
     */
    public function show($id)
    {
        $colonyId = (int)$id;

        $colony = $this->db->fetchOne("SELECT * FROM colonies WHERE id = ?", [$colonyId]);
        if (!$colony) {
            header('HTTP/1.0 404 Not Found');
            echo "Colony not found.";
            return;
        }

        $milestones = $this->db->fetchAll(
            "SELECT * FROM colony_timelapse_milestones 
             WHERE colony_id = ? 
             ORDER BY stage_order ASC, id ASC",
            [$colonyId]
        ) ?: [];

        $totalPct = 0;
        $count = count($milestones);
        foreach ($milestones as $m) {
            $totalPct += (int)$m['completion_pct'];
        }
        $overallProgress = $count > 0 ? round($totalPct / $count) : 0;

        $this->layout = 'layouts/base';
        $this->render('pages/colony_timelapse', [
            'page_title' => $colony['name'] . ' - Construction Timelapse & Drone Progress',
            'colony' => $colony,
            'milestones' => $milestones,
            'overall_progress' => $overallProgress
        ]);
    }
}
