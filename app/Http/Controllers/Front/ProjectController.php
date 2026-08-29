<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Front\PageController;
use App\Core\Database\Database;
use Exception;
use App\Traits\TenantAwareTrait;

class ProjectController extends PageController
{
    use TenantAwareTrait;
    public function projects()
    {
        return parent::projects();
    }

    public function projectDetails($slug = null)
    {
        return parent::projectDetails($slug);
    }

    public function colonies()
    {
        return parent::colonies();
    }

    public function colonyDetail($slug = null)
    {
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = $this->tenantId();
            $sql = "SELECT c.*, (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status != 'sold' AND p.tenant_id = ?) as total_plots, (SELECT COUNT(*) FROM plots p WHERE p.colony_id = c.id AND p.status = 'available' AND p.tenant_id = ?) as available_plots FROM colonies c WHERE c.slug = ? AND c.is_active = 1 LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$tid, $tid, $slug]);
            $colony = $stmt->fetch(\PDO::FETCH_ASSOC);
            if (!$colony) {
                http_response_code(404);
                $this->render('errors/404', ['page_title' => 'Colony Not Found']);
                return;
            }
            $stmt2 = $db->prepare("SELECT * FROM plots WHERE colony_id = ? AND status = 'available' AND tenant_id = ? ORDER BY plot_number LIMIT 50");
            $stmt2->execute([$colony['id'], $tid]);
            $availablePlots = $stmt2->fetchAll(\PDO::FETCH_ASSOC);
            $mapData = ['type' => 'FeatureCollection', 'features' => []];
            $this->render('pages/colony_detail', ['page_title' => $colony['name'] . ' - APS Dream Home', 'colony' => $colony, 'availablePlots' => $availablePlots, 'mapData' => $mapData]);
        } catch (\Exception $e) {
            error_log("ProjectController::colonyDetail: " . $e->getMessage());
            http_response_code(500);
            $this->render('errors/500', ['page_title' => 'Error']);
        }
    }

    public function colonyPlots($slug = null)
    {
        return parent::colonyPlots($slug);
    }

    public function suyodayColony()
    {
        return parent::suyodayColony();
    }

    public function raghunatNagri()
    {
        return parent::raghunatNagri();
    }

    public function brajRadhaNagri()
    {
        return parent::brajRadhaNagri();
    }

    public function budhBiharColony()
    {
        return parent::budhBiharColony();
    }

    public function awadhpuri()
    {
        return parent::awadhpuri();
    }

    public function budhaCity()
    {
        return parent::budhaCity();
    }

    public function suyodayColonyPage()
    {
        return parent::suyodayColonyPage();
    }

    public function projectsByLocation($location = null)
    {
        return parent::projectsByLocation($location);
    }

    public function location($slug = null)
    {
        return parent::location($slug);
    }

    public function plotMap()
    {
        return parent::plotMap();
    }
}