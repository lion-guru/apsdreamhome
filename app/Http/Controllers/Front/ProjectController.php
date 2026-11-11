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

            $sql = "SELECT p.*, c.name as colony_name, c.slug as colony_slug,
                           d.name as district_name, s.name as state_name,
                           (SELECT COUNT(*) FROM plots WHERE colony_id = p.colony_id AND tenant_id = ?) as colony_total_plots,
                           (SELECT COUNT(*) FROM plots WHERE colony_id = p.colony_id AND status = 'available' AND tenant_id = ?) as colony_available_plots
                    FROM projects p
                    LEFT JOIN colonies c ON p.colony_id = c.id
                    LEFT JOIN districts d ON p.district_id = d.id
                    LEFT JOIN states s ON p.state_id = s.id
                    WHERE (p.id = ? OR LOWER(REPLACE(p.name, ' ', '-')) = LOWER(?))
                    AND p.tenant_id = ?
                    LIMIT 1";
            $stmt = $db->prepare($sql);
            $stmt->execute([$tid, $tid, (int)$slug, $slug, $tid]);
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

            $milestones = [];
            if (!empty($project['milestone_json'])) {
                $mDecoded = json_decode($project['milestone_json'], true);
                if (is_array($mDecoded)) $milestones = $mDecoded;
            }

            // Fetch related active projects
            $relStmt = $db->prepare("
                SELECT p.*, c.name as colony_name, c.slug as colony_slug, d.name as district_name
                FROM projects p
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON p.district_id = d.id
                WHERE p.id != ? AND p.tenant_id = ? AND p.status != 'cancelled'
                ORDER BY p.is_featured DESC, p.created_at DESC
                LIMIT 3
            ");
            $relStmt->execute([$project['id'], $tid]);
            $related_projects = $relStmt->fetchAll(\PDO::FETCH_ASSOC);

            $this->render('pages/project_detail', [
                'page_title' => $project['name'] . ' - APS Dream Home',
                'project' => $project,
                'images' => $images,
                'milestones' => $milestones,
                'related_projects' => $related_projects,
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
        // NOTE (S450): this used to delegate to projectDetails('budha-city'),
        // which looks the slug up in the `projects` table. There is no such
        // row (the table holds only Braj Radha Nagri x2 + SURYODAY COLONY), so
        // the page 404'd for every visitor - even though the Budha City
        // marketing page HAS been built (app/views/locations/kushinagar-budha-city.php).
        return $this->locationLanding('budha-city');
    }

    /**
     * Location landing pages (app/views/locations/*).
     *
     * NOTE (S450): six complete marketing/SEO landing pages were built but
     * never wired to a route, so they were unreachable. Five are layout
     * partials; one (bohisawagar) is a full standalone HTML document and is
     * emitted as-is by the render() self-rendering safeguard.
     */
    public function locationLanding($slug = null)
    {
        if ($slug === null || $slug === '') {
            // A LITERAL route (no {param}) makes the router call the handler
            // with NO arguments, so the slug has to come from the path.
            $path = (string)(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '');
            $slug = basename(rtrim($path, '/'));
        }

        $map = [
            'budha-city'      => ['kushinagar-budha-city', 'Budha City - Kushinagar'],
            'suryoday-colony' => ['gorakhpur-suryoday-colony', 'Suryoday Colony - Gorakhpur'],
            'raghunath-nagri' => ['gorakhpur-raghunath-nagri', 'Raghunath Nagri - Gorakhpur'],
            'bohisawagar'     => ['gorakhpur-bohisawagar', 'Bohisawagar - Gorakhpur'],
            'ram-nagri'       => ['lucknow-ram-nagri', 'Ram Nagri - Lucknow'],
            'ganga-nagri'     => ['varanasi-ganga-nagri', 'Ganga Nagri - Varanasi'],
        ];

        $key = strtolower(trim((string)$slug));
        if (!isset($map[$key])) {
            http_response_code(404);
            $this->render('errors/404', ['page_title' => 'Page Not Found']);
            return;
        }

        [$view, $title] = $map[$key];

        // The gallery images live in public/assets/images/amenities/ and the
        // views prefix them with BASE_URL . '/assets/images/'. Captions below
        // were checked against the actual files, not invented.
        $amenities = [
            ['title' => 'Site Development & Drainage', 'image' => 'amenities/1.jpg', 'alt_text' => $title . ' - site development and drainage works'],
            ['title' => 'Hospital / Medical Facility', 'image' => 'amenities/2.jpg', 'alt_text' => $title . ' - hospital'],
            ['title' => 'Landscaped Parks & Walkways', 'image' => 'amenities/3.jpg', 'alt_text' => $title . ' - landscaped park'],
            ['title' => 'Wide Roads & Covered Parking', 'image' => 'amenities/4.jpg', 'alt_text' => $title . ' - internal roads and parking'],
            ['title' => 'Street Lighting at Night', 'image' => 'amenities/5.jpg', 'alt_text' => $title . ' - lit internal road'],
            ['title' => 'School & Education', 'image' => 'amenities/6.jpg', 'alt_text' => $title . ' - school campus'],
            ['title' => '24x7 Security', 'image' => 'amenities/7.jpg', 'alt_text' => $title . ' - security'],
        ];

        // The partials guard with isset()/empty() and fall back to their own
        // copy, but pass defaults so PHP emits no undefined-variable
        // warnings.
        $this->render('locations/' . $view, [
            'page_title' => $title,
            'project'    => null,
            'video'      => [],
            'videos'     => [],
            'amenity'    => [],
            'amenities'  => $amenities,
        ]);
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