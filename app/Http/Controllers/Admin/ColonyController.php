<?php
namespace App\Http\Controllers\Admin;

class ColonyController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        $colonies = $this->db->fetchAll("
            SELECT c.*, d.name as district_name, s.name as state_name
            FROM colonies c
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            ORDER BY c.name ASC
        ");
        $this->render('admin/colonies/index', [
            'page_title' => 'Colonies - Admin',
            'colonies' => $colonies,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();
        $states = $this->db->fetchAll("SELECT id, name FROM states ORDER BY name");
        $this->render('admin/colonies/create', [
            'page_title' => 'New Colony - Admin',
            'states' => $states,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        $name = $_POST['name'] ?? '';
        $districtId = (int)($_POST['district_id'] ?? 0);
        $slug = $_POST['slug'] ?? strtolower(trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($name)), '-'));
        $slug = preg_replace('/-+/', '-', $slug);

        // Check slug uniqueness
        $existing = $this->db->fetch("SELECT id FROM colonies WHERE slug = ? AND id != ?", [$slug, 0]);
        if ($existing) $slug .= '-' . time();

        $latitude = $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : null;
        $longitude = $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
        $mapLink = trim($_POST['map_link'] ?? '');
        if (empty($mapLink) && $latitude !== null && $longitude !== null) {
            $mapLink = "https://maps.google.com/?q={$latitude},{$longitude}";
        }

        $tid = $this->tenantId();
        $this->db->query("INSERT INTO colonies (district_id, name, slug, description, amenities, key_highlights, nearby_places, map_link, total_plots, available_plots, starting_price, image_path, banner_image, brochure_path, gallery_images, youtube_video_url, layout_image, virtual_tour_url, latitude, longitude, contact_phone, contact_email, meta_title, meta_description, show_plots_publicly, is_featured, is_active, tenant_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $districtId, $name, $slug,
            $_POST['description'] ?? '', $_POST['amenities'] ?? '', $_POST['key_highlights'] ?? '', $_POST['nearby_places'] ?? '',
            $mapLink, (int)($_POST['total_plots'] ?? 0), (int)($_POST['available_plots'] ?? 0),
            (float)($_POST['starting_price'] ?? 0), $_POST['image_path'] ?? '', $_POST['banner_image'] ?? '',
            $_POST['brochure_path'] ?? '', $_POST['gallery_images'] ?? '', $_POST['youtube_video_url'] ?? '',
            $_POST['layout_image'] ?? '', $_POST['virtual_tour_url'] ?? '',
            $latitude, $longitude,
            $_POST['contact_phone'] ?? '', $_POST['contact_email'] ?? '',
            $_POST['meta_title'] ?? '', $_POST['meta_description'] ?? '',
            isset($_POST['show_plots_publicly']) ? 1 : 0,
            isset($_POST['is_featured']) ? 1 : 0,
            isset($_POST['is_active']) ? 1 : 0,
            $tid,
        ]);
        $newId = $this->db->lastInsertId();
        $this->setFlash('success', 'Colony "' . htmlspecialchars($name) . '" created! Next: add plots to start bookings.');
        $this->redirect('/admin/colonies/' . $newId);
    }

    public function show($id)
    {
        $this->requireAdmin();
        $colony = $this->db->fetch("
            SELECT c.*, d.name as district_name, s.name as state_name
            FROM colonies c
            LEFT JOIN districts d ON c.district_id = d.id
            LEFT JOIN states s ON d.state_id = s.id
            WHERE c.id = ?
        ", [$id]);
        if (!$colony) {
            $this->setFlash('error', 'Colony not found');
            $this->redirect('/admin/colonies');
            return;
        }

        // Plot statistics
        $plotStats = $this->db->fetchOne("
            SELECT 
                COUNT(*) as total_plots,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available_plots,
                SUM(CASE WHEN status = 'booked' THEN 1 ELSE 0 END) as booked_plots,
                SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as sold_plots,
                SUM(CASE WHEN status = 'hold' THEN 1 ELSE 0 END) as hold_plots,
                SUM(total_price) as total_value,
                AVG(price_per_sqft) as avg_price_per_sqft,
                AVG(area_sqft) as avg_area_sqft,
                SUM(CASE WHEN corner_plot = 1 THEN 1 ELSE 0 END) as corner_plots,
                SUM(CASE WHEN park_facing = 1 THEN 1 ELSE 0 END) as park_facing_plots,
                SUM(CASE WHEN road_width_ft >= 40 THEN 1 ELSE 0 END) as wide_road_plots
            FROM plots WHERE colony_id = ?
        ", [$id]);

        // Block-wise breakdown
        $blockStats = $this->db->fetchAll("
            SELECT 
                COALESCE(block, 'Unassigned') as block,
                COUNT(*) as count,
                SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available,
                SUM(total_price) as block_value,
                AVG(price_per_sqft) as avg_ppsft
            FROM plots WHERE colony_id = ?
            GROUP BY block
            ORDER BY block
        ", [$id]);

        // Development costs
        $devCosts = $this->db->fetchAll("
            SELECT 
                cost_type,
                SUM(amount) as total_amount,
                COUNT(*) as entries,
                SUM(CASE WHEN payment_status = 'paid' THEN amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN payment_status = 'partial' THEN amount ELSE 0 END) as partial_amount,
                SUM(CASE WHEN payment_status = 'unpaid' THEN amount ELSE 0 END) as unpaid_amount
            FROM colony_development_costs
            WHERE colony_id = ?
            GROUP BY cost_type
            ORDER BY cost_type
        ", [$id]);

        $totalDevCost = 0;
        $paidDevCost = 0;
        foreach ($devCosts as &$dc) {
            $totalDevCost += $dc['total_amount'];
            $paidDevCost += $dc['paid_amount'];
        }

        // Current layout
        $layout = $this->db->fetch("
            SELECT cl.*, 
                   COUNT(p.id) as generated_plots
            FROM colony_layouts cl
            LEFT JOIN plots p ON p.layout_id = cl.id
            WHERE cl.colony_id = ? AND cl.is_current = 1
            GROUP BY cl.id
        ", [$id]);

        // Plots
        $plots = $this->db->fetchAll("SELECT * FROM plots WHERE colony_id = ? ORDER BY block, plot_number", [$id]);

        // Price history count
        $priceHistoryCount = (int) $this->db->fetchColumn("
            SELECT COUNT(*) FROM price_history ph
            JOIN plots p ON ph.plot_id = p.id
            WHERE p.colony_id = ?
        ", [$id]);

        $this->render('admin/colonies/show', [
            'page_title' => $colony['name'] . ' - Admin',
            'colony' => $colony,
            'plots' => $plots,
            'plot_stats' => $plotStats,
            'block_stats' => $blockStats,
            'dev_costs' => $devCosts,
            'total_dev_cost' => $totalDevCost,
            'paid_dev_cost' => $paidDevCost,
            'layout' => $layout,
            'price_history_count' => $priceHistoryCount,
        ]);
    }

    public function edit($id)
    {
        $this->requireAdmin();
        $colony = $this->db->fetch("SELECT * FROM colonies WHERE id = ?", [$id]);
        if (!$colony) {
            $this->setFlash('error', 'Colony not found');
            $this->redirect('/admin/colonies');
            return;
        }
        $states = $this->db->fetchAll("SELECT id, name FROM states ORDER BY name");
        $districts = $this->db->fetchAll("SELECT id, name FROM districts WHERE state_id = ? ORDER BY name", [$colony['district_id']]);
        $this->render('admin/colonies/edit', [
            'page_title' => 'Edit ' . $colony['name'] . ' - Admin',
            'colony' => $colony, 'states' => $states, 'districts' => $districts,
        ]);
    }

    public function update($id)
    {
        $this->requireAdmin();
        $slug = $_POST['slug'] ?? '';
        $existing = $this->db->fetch("SELECT id FROM colonies WHERE slug = ? AND id != ?", [$slug, $id]);
        if ($existing) $slug .= '-' . $id;

        $latitude = $_POST['latitude'] !== '' ? (float)$_POST['latitude'] : null;
        $longitude = $_POST['longitude'] !== '' ? (float)$_POST['longitude'] : null;
        $mapLink = trim($_POST['map_link'] ?? '');
        if (empty($mapLink) && $latitude !== null && $longitude !== null) {
            $mapLink = "https://maps.google.com/?q={$latitude},{$longitude}";
        }

        [$tenantSql, $tenantParams] = $this->tenantWhere();
        $this->db->query("UPDATE colonies SET district_id=?, name=?, slug=?, description=?, amenities=?, key_highlights=?, nearby_places=?, map_link=?, total_plots=?, available_plots=?, starting_price=?, image_path=?, banner_image=?, brochure_path=?, gallery_images=?, youtube_video_url=?, layout_image=?, virtual_tour_url=?, latitude=?, longitude=?, contact_phone=?, contact_email=?, meta_title=?, meta_description=?, show_plots_publicly=?, is_featured=?, is_active=? WHERE id=? $tenantSql", array_merge([
            (int)($_POST['district_id'] ?? 0), $_POST['name'] ?? '', $slug,
            $_POST['description'] ?? '', $_POST['amenities'] ?? '', $_POST['key_highlights'] ?? '', $_POST['nearby_places'] ?? '',
            $mapLink, (int)($_POST['total_plots'] ?? 0), (int)($_POST['available_plots'] ?? 0),
            (float)($_POST['starting_price'] ?? 0), $_POST['image_path'] ?? '', $_POST['banner_image'] ?? '',
            $_POST['brochure_path'] ?? '', $_POST['gallery_images'] ?? '', $_POST['youtube_video_url'] ?? '',
            $_POST['layout_image'] ?? '', $_POST['virtual_tour_url'] ?? '',
            $latitude, $longitude,
            $_POST['contact_phone'] ?? '', $_POST['contact_email'] ?? '',
            $_POST['meta_title'] ?? '', $_POST['meta_description'] ?? '',
            isset($_POST['show_plots_publicly']) ? 1 : 0,
            isset($_POST['is_featured']) ? 1 : 0,
            isset($_POST['is_active']) ? 1 : 0,
            $id,
        ], $tenantParams));
        $this->setFlash('success', 'Colony updated successfully');
        $this->redirect('/admin/colonies');
    }

    public function destroy($id)
    {
        $this->requireAdmin();
        [$tenantSql, $tenantParams] = $this->tenantWhere();
        $this->db->query("DELETE FROM colonies WHERE id = ? $tenantSql", array_merge([$id], $tenantParams));
        $this->setFlash('success', 'Colony deleted');
        $this->redirect('/admin/colonies');
    }

    public function plots($id)
    {
        $this->requireAdmin();
        $colony = $this->db->fetch("SELECT * FROM colonies WHERE id = ?", [$id]);
        $plots = $this->db->fetchAll("SELECT * FROM plots WHERE colony_id = ? ORDER BY plot_number", [$id]);
        $this->render('admin/colonies/plots', [
            'page_title' => 'Plots - ' . ($colony['name'] ?? '') . ' - Admin',
            'colony' => $colony, 'plots' => $plots,
        ]);
    }

    public function financials($id)
    {
        $this->requireAdmin();
        $colony = $this->db->fetch("SELECT * FROM colonies WHERE id = ?", [$id]);
        $totalBookings = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(b.total_amount), 0) FROM bookings b JOIN plots p ON b.property_id = p.id WHERE p.colony_id = ?", [$id]);
        $totalPlotsValue = (float)$this->db->fetchColumn("SELECT COALESCE(SUM(total_price), 0) FROM plots WHERE colony_id = ?", [$id]);
        $this->render('admin/colonies/financials', [
            'page_title' => 'Financials - ' . ($colony['name'] ?? '') . ' - Admin',
            'colony' => $colony, 'total_bookings' => $totalBookings, 'total_plots_value' => $totalPlotsValue,
        ]);
    }
}
