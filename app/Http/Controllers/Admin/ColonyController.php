<?php

namespace App\Http\Controllers\Admin;

/**
 * Unified Colony Controller — single entry point /admin/colonies/
 *
 * Merges the useful surface of the three historical systems:
 *  - LocationAdminController: state/district filters, basic land fields
 *    (land_cost, min_price_per_sqft, block_count, phase)
 *  - ColonyController: full-schema CRUD, tabbed detail view
 *  - ColonyPipelineController: development workflow lives in its own
 *    controller/routes (/admin/colony-pipeline/*); this controller
 *    surfaces it via a Pipeline tab with deep links (no duplicated logic).
 *
 * Conventions (custom MVC, NOT Laravel):
 *  - raw SQL via $this->db->fetchAll/fetchOne + prepare/execute
 *  - views: $this->render('admin/colonies/...', [...]) (slash notation)
 *  - POST: $_SERVER['REQUEST_METHOD'] guard + $this->validateCsrfOrFail()
 *  - flash via $_SESSION['success']/$_SESSION['error'] + redirect()
 *  - tenant writes scoped with $this->tenantId()
 *  - route params arrive positionally (see routes/router.php
 *    matchDynamicRoute + call_user_func_array)
 */
class ColonyController extends AdminController
{
    /**
     * Dashboard — all colonies with district/state names.
     * Filters (GET): search, state_id, district_id.
     */
    public function index()
    {
        $this->requireAdmin();

        try {
            $search = trim($_GET['search'] ?? '');
            $state_id = (int)($_GET['state_id'] ?? 0);
            $district_id = (int)($_GET['district_id'] ?? 0);

            $where = [];
            $params = [];

            if ($search !== '') {
                $where[] = "(c.name LIKE ? OR c.slug LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
            }
            if ($district_id > 0) {
                $where[] = "c.district_id = ?";
                $params[] = $district_id;
            } elseif ($state_id > 0) {
                $where[] = "d.state_id = ?";
                $params[] = $state_id;
            }

            $whereClause = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

            $colonies = $this->db->fetchAll(
                "SELECT c.*, d.name AS district_name, s.name AS state_name
                 FROM colonies c
                 LEFT JOIN districts d ON d.id = c.district_id
                 LEFT JOIN states s ON s.id = d.state_id
                 $whereClause
                 ORDER BY c.name",
                $params
            );

            $states = $this->db->fetchAll(
                "SELECT id, name FROM states WHERE is_active = 1 ORDER BY name"
            );
            $districts = $this->db->fetchAll(
                "SELECT id, name, state_id FROM districts WHERE is_active = 1 ORDER BY name"
            );

            $this->render('admin/colonies/index', [
                'colonies' => $colonies,
                'states' => $states,
                'districts' => $districts,
                'search' => $search,
                'state_id' => $state_id,
                'district_id' => $district_id,
            ]);
        } catch (\Throwable $e) {
            error_log("ColonyController@index: " . $e->getMessage());
            $this->renderError('Colony list failed', 'Could not load colonies. Please try again.');
        }
    }

    /**
     * Detail — tabbed 360° view (Overview | Inventory | Plots | Pipeline | Finance).
     */
    public function show($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        try {
            $colony = $this->db->fetchOne(
                "SELECT c.*, d.name AS district_name, s.name AS state_name
                 FROM colonies c
                 LEFT JOIN districts d ON d.id = c.district_id
                 LEFT JOIN states s ON s.id = d.state_id
                 WHERE c.id = ?",
                [$id]
            );

            if (!$colony) {
                $_SESSION['error'] = 'Colony not found';
                redirect('/admin/colonies');
                return;
            }

            $plots = $this->db->fetchAll(
                "SELECT * FROM plots WHERE colony_id = ? ORDER BY plot_number",
                [$id]
            );

            // Current approved layout (if any) for the Pipeline tab summary.
            $layout = null;
            try {
                $layout = $this->db->fetchOne(
                    "SELECT * FROM colony_layouts WHERE colony_id = ?
                     ORDER BY is_current DESC, version DESC LIMIT 1",
                    [$id]
                );
            } catch (\Throwable $e) {
                error_log("ColonyController@show layout lookup: " . $e->getMessage());
            }

            $this->render('admin/colonies/show', [
                'colony' => $colony,
                'plots' => $plots,
                'layout' => $layout,
            ]);
        } catch (\Throwable $e) {
            error_log("ColonyController@show: " . $e->getMessage());
            $this->renderError('Colony detail failed', 'Could not load colony details.');
        }
    }

    /**
     * Create form.
     */
    public function create()
    {
        $this->requireAdmin();

        $states = \App\Models\State::getActive(['id', 'name', 'code']);
        $districts = \App\Models\District::getWithStateName(['id', 'name', 'state_id'], true);

        $this->render('admin/colonies/create', [
            'states' => $states,
            'districts' => $districts,
        ]);
    }

    /**
     * Store — POST /admin/colonies/store. Only real `colonies` columns.
     */
    public function store()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/colonies/create');
            return;
        }
        $this->validateCsrfOrFail();

        $district_id = (int)($_POST['district_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($district_id <= 0 || $name === '') {
            $_SESSION['error'] = 'District and Colony Name are required';
            redirect('/admin/colonies/create');
            return;
        }

        $slug = trim($_POST['slug'] ?? '');
        if ($slug === '') {
            $slug = strtolower($name);
            $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
            $slug = trim($slug, '-');
        }

        try {
            $stmt = $this->db->prepare(
                "INSERT INTO colonies
                 (district_id, name, slug, description, amenities, key_highlights,
                  nearby_places, gallery_images, youtube_video_url, virtual_tour_url,
                  meta_title, meta_description, map_link,
                  total_plots, available_plots, starting_price,
                  image_path, layout_image, banner_image, brochure_path,
                  is_featured, is_active, pipeline_stage, show_plots_publicly,
                  contact_phone, contact_email,
                  land_cost, min_price_per_sqft, phase, block_count,
                  latitude, longitude, tenant_id)
                 VALUES
                 (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $district_id,
                $name,
                $slug,
                trim($_POST['description'] ?? ''),
                trim($_POST['amenities'] ?? ''),
                trim($_POST['key_highlights'] ?? ''),
                trim($_POST['nearby_places'] ?? ''),
                trim($_POST['gallery_images'] ?? ''),
                trim($_POST['youtube_video_url'] ?? ''),
                trim($_POST['virtual_tour_url'] ?? ''),
                trim($_POST['meta_title'] ?? ''),
                trim($_POST['meta_description'] ?? ''),
                trim($_POST['map_link'] ?? ''),
                (int)($_POST['total_plots'] ?? 0),
                (int)($_POST['available_plots'] ?? 0),
                (float)($_POST['starting_price'] ?? 0),
                trim($_POST['image_path'] ?? ''),
                trim($_POST['layout_image'] ?? ''),
                trim($_POST['banner_image'] ?? ''),
                trim($_POST['brochure_path'] ?? ''),
                isset($_POST['is_featured']) ? 1 : 0,
                isset($_POST['is_active']) ? 1 : 0,
                trim($_POST['pipeline_stage'] ?? 'planning'),
                isset($_POST['show_plots_publicly']) ? 1 : 0,
                trim($_POST['contact_phone'] ?? ''),
                trim($_POST['contact_email'] ?? ''),
                (float)($_POST['land_cost'] ?? 0),
                (float)($_POST['min_price_per_sqft'] ?? 0),
                trim($_POST['phase'] ?? 'Phase 1'),
                (int)($_POST['block_count'] ?? 1),
                trim($_POST['latitude'] ?? ''),
                trim($_POST['longitude'] ?? ''),
                $this->tenantId(),
            ]);

            $_SESSION['success'] = 'Colony created successfully';
            redirect('/admin/colonies');
            return;
        } catch (\Throwable $e) {
            error_log("ColonyController@store: " . $e->getMessage());
            $_SESSION['error'] = 'Could not create colony (duplicate name/slug?)';
            redirect('/admin/colonies/create');
            return;
        }
    }

    /**
     * Edit form.
     */
    public function edit($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        $colony = $this->db->fetchOne(
            "SELECT c.*, d.name AS district_name, s.name AS state_name,
                    d.state_id AS state_id
             FROM colonies c
             LEFT JOIN districts d ON d.id = c.district_id
             LEFT JOIN states s ON s.id = d.state_id
             WHERE c.id = ?",
            [$id]
        );

        if (!$colony) {
            $_SESSION['error'] = 'Colony not found';
            redirect('/admin/colonies');
            return;
        }

        $states = \App\Models\State::getActive(['id', 'name', 'code']);
        $districts = \App\Models\District::getWithStateName(['id', 'name', 'state_id'], true);

        $this->render('admin/colonies/edit', [
            'colony' => $colony,
            'states' => $states,
            'districts' => $districts,
        ]);
    }

    /**
     * Update — POST /admin/colonies/update/{id}
     * (legacy alias POST /admin/colonies/{id}/update hits the same method).
     */
    public function update($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/colonies/' . $id . '/edit');
            return;
        }
        $this->validateCsrfOrFail();

        $district_id = (int)($_POST['district_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');

        if ($district_id <= 0 || $name === '') {
            $_SESSION['error'] = 'District and Colony Name are required';
            redirect('/admin/colonies/' . $id . '/edit');
            return;
        }

        try {
            $stmt = $this->db->prepare(
                "UPDATE colonies SET
                  district_id = ?, name = ?, slug = ?, description = ?, amenities = ?,
                  key_highlights = ?, nearby_places = ?, gallery_images = ?,
                  youtube_video_url = ?, virtual_tour_url = ?,
                  meta_title = ?, meta_description = ?, map_link = ?,
                  total_plots = ?, available_plots = ?, starting_price = ?,
                  image_path = ?, layout_image = ?, banner_image = ?, brochure_path = ?,
                  is_featured = ?, is_active = ?, pipeline_stage = ?, show_plots_publicly = ?,
                  contact_phone = ?, contact_email = ?,
                  land_cost = ?, min_price_per_sqft = ?, phase = ?, block_count = ?,
                  latitude = ?, longitude = ?
                 WHERE id = ? AND tenant_id = ?"
            );
            $stmt->execute([
                $district_id,
                $name,
                trim($_POST['slug'] ?? ''),
                trim($_POST['description'] ?? ''),
                trim($_POST['amenities'] ?? ''),
                trim($_POST['key_highlights'] ?? ''),
                trim($_POST['nearby_places'] ?? ''),
                trim($_POST['gallery_images'] ?? ''),
                trim($_POST['youtube_video_url'] ?? ''),
                trim($_POST['virtual_tour_url'] ?? ''),
                trim($_POST['meta_title'] ?? ''),
                trim($_POST['meta_description'] ?? ''),
                trim($_POST['map_link'] ?? ''),
                (int)($_POST['total_plots'] ?? 0),
                (int)($_POST['available_plots'] ?? 0),
                (float)($_POST['starting_price'] ?? 0),
                trim($_POST['image_path'] ?? ''),
                trim($_POST['layout_image'] ?? ''),
                trim($_POST['banner_image'] ?? ''),
                trim($_POST['brochure_path'] ?? ''),
                isset($_POST['is_featured']) ? 1 : 0,
                isset($_POST['is_active']) ? 1 : 0,
                trim($_POST['pipeline_stage'] ?? 'planning'),
                isset($_POST['show_plots_publicly']) ? 1 : 0,
                trim($_POST['contact_phone'] ?? ''),
                trim($_POST['contact_email'] ?? ''),
                (float)($_POST['land_cost'] ?? 0),
                (float)($_POST['min_price_per_sqft'] ?? 0),
                trim($_POST['phase'] ?? 'Phase 1'),
                (int)($_POST['block_count'] ?? 1),
                trim($_POST['latitude'] ?? ''),
                trim($_POST['longitude'] ?? ''),
                $id,
                $this->tenantId(),
            ]);

            $_SESSION['success'] = 'Colony updated successfully';
            redirect('/admin/colonies/' . $id);
            return;
        } catch (\Throwable $e) {
            error_log("ColonyController@update: " . $e->getMessage());
            $_SESSION['error'] = 'Could not update colony';
            redirect('/admin/colonies/' . $id . '/edit');
            return;
        }
    }

    /**
     * Destroy — POST /admin/colonies/destroy/{id}
     * (legacy alias POST /admin/colonies/{id}/destroy hits the same method).
     */
    public function destroy($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('/admin/colonies');
            return;
        }
        $this->validateCsrfOrFail();

        try {
            $stmt = $this->db->prepare("DELETE FROM colonies WHERE id = ? AND tenant_id = ?");
            $stmt->execute([$id, $this->tenantId()]);
            $_SESSION['success'] = 'Colony deleted successfully';
        } catch (\Throwable $e) {
            error_log("ColonyController@destroy: " . $e->getMessage());
            $_SESSION['error'] = 'Cannot delete colony - it may have associated plots/bookings';
        }

        redirect('/admin/colonies');
        return;
    }

    /**
     * Plots in a colony — GET /admin/colonies/{id}/plots
     */
    public function plots($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        try {
            $colony = $this->db->fetchOne(
                "SELECT c.*, d.name AS district_name, s.name AS state_name
                 FROM colonies c
                 LEFT JOIN districts d ON d.id = c.district_id
                 LEFT JOIN states s ON s.id = d.state_id
                 WHERE c.id = ?",
                [$id]
            );

            if (!$colony) {
                $_SESSION['error'] = 'Colony not found';
                redirect('/admin/colonies');
                return;
            }

            $plots = $this->db->fetchAll(
                "SELECT * FROM plots WHERE colony_id = ? ORDER BY plot_number",
                [$id]
            );

            $this->render('admin/colonies/plots', [
                'colony' => $colony,
                'plots' => $plots,
            ]);
        } catch (\Throwable $e) {
            error_log("ColonyController@plots: " . $e->getMessage());
            $this->renderError('Colony plots failed', 'Could not load plots for this colony.');
        }
    }

    /**
     * Financial summary — GET /admin/colonies/{id}/financials
     */
    public function financials($id)
    {
        $this->requireAdmin();
        $id = (int)$id;

        try {
            $colony = $this->db->fetchOne(
                "SELECT c.*, d.name AS district_name, s.name AS state_name
                 FROM colonies c
                 LEFT JOIN districts d ON d.id = c.district_id
                 LEFT JOIN states s ON s.id = d.state_id
                 WHERE c.id = ?",
                [$id]
            );

            if (!$colony) {
                $_SESSION['error'] = 'Colony not found';
                redirect('/admin/colonies');
                return;
            }

            $total_plots_value = 0;
            try {
                $row = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(total_price), 0) AS v FROM plots WHERE colony_id = ?",
                    [$id]
                );
                $total_plots_value = (float)($row['v'] ?? 0);
            } catch (\Throwable $e) {
                error_log("ColonyController@financials plots sum: " . $e->getMessage());
            }

            $total_bookings = 0;
            try {
                $row = $this->db->fetchOne(
                    "SELECT COALESCE(SUM(total_amount), 0) AS v FROM bookings WHERE colony_id = ?",
                    [$id]
                );
                $total_bookings = (float)($row['v'] ?? 0);
            } catch (\Throwable $e) {
                error_log("ColonyController@financials bookings sum: " . $e->getMessage());
            }

            $this->render('admin/colonies/financials', [
                'colony' => $colony,
                'total_bookings' => $total_bookings,
                'total_plots_value' => $total_plots_value,
            ]);
        } catch (\Throwable $e) {
            error_log("ColonyController@financials: " . $e->getMessage());
            $this->renderError('Financial summary failed', 'Could not load financial summary.');
        }
    }

    /**
     * JSON API — colonies of a district (for dependent dropdowns).
     * GET /admin/colonies/api/by-district/{district_id}
     */
    public function getColoniesByDistrict($district_id)
    {
        $this->requireAdmin();
        header('Content-Type: application/json');

        try {
            $stmt = $this->db->prepare(
                "SELECT * FROM colonies WHERE district_id = ? AND is_active = 1 ORDER BY name"
            );
            $stmt->execute([(int)$district_id]);
            echo json_encode($stmt->fetchAll(\PDO::FETCH_ASSOC));
        } catch (\Throwable $e) {
            error_log("ColonyController@getColoniesByDistrict: " . $e->getMessage());
            echo json_encode([]);
        }
        return;
    }
}
