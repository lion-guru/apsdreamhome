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
        try {
            $db = Database::getInstance()->getConnection();
            $tid = $this->tenantId();

            $sql = "SELECT p.*, c.name as colony_name, c.slug as colony_slug
                    FROM projects p
                    LEFT JOIN colonies c ON p.colony_id = c.id
                    WHERE (p.id = ? OR LOWER(REPLACE(p.name, ' ', '-')) = LOWER(?))
                    AND p.tenant_id = ?
                    LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([(int)$slug, $slug, $tid]);
            $project = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$project) {
                http_response_code(404);
                $this->render('errors/404', ['page_title' => 'Project Not Found']);
                return;
            }

            $images = [];
            if (!empty($project['images'])) {
                $decoded = json_decode($project['images'], true);
                if (is_array($decoded)) $images = $decoded;
            }

            $this->render('pages/project_detail', [
                'page_title' => $project['name'] . ' - APS Dream Home',
                'project' => $project,
                'images' => $images,
            ]);
        } catch (\Exception $e) {
            error_log("ProjectController::projectDetails: " . $e->getMessage());
            http_response_code(500);
            $this->render('errors/500', ['page_title' => 'Error']);
        }
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
        return $this->colonyDetail($slug);
    }

    public function suyodayColony()
    {
        return $this->colonyDetail('suryoday-colony');
    }

    public function raghunatNagri()
    {
        return $this->colonyDetail('raghunath-nagri-motiram');
    }

    public function brajRadhaNagri()
    {
        return $this->colonyDetail('braj-radha-nagri');
    }

    public function budhBiharColony()
    {
        return $this->colonyDetail('budh-bihar-colony');
    }

    public function awadhpuri()
    {
        return $this->colonyDetail('motiram-jhangha-road');
    }

    public function budhaCity()
    {
        return $this->projectDetails('budha-city');
    }

    public function suyodayColonyPage()
    {
        return $this->colonyDetail('suryoday-colony');
    }

    public function projectsByLocation($location = null)
    {
        try {
            $db = Database::getInstance()->getConnection();
            $tid = $this->tenantId();
            $stmt = $db->prepare("SELECT * FROM projects WHERE tenant_id = ? ORDER BY is_featured DESC, name");
            $stmt->execute([$tid]);
            $projects = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            $this->render('pages/projects_by_location', [
                'page_title' => 'Projects by Location - APS Dream Home',
                'projects' => $projects,
                'location' => $location,
            ]);
        } catch (\Exception $e) {
            error_log("ProjectController::projectsByLocation: " . $e->getMessage());
            http_response_code(500);
            $this->render('errors/500', ['page_title' => 'Error']);
        }
    }

    public function location($slug = null)
    {
        return $this->colonyDetail($slug);
    }

    public function plotMap()
    {
        return parent::plotMap();
    }
}