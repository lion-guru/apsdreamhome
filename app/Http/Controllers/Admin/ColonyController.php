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

        $this->db->query("INSERT INTO colonies (district_id, name, slug, description, amenities, key_highlights, nearby_places, map_link, total_plots, available_plots, starting_price, image_path, banner_image, brochure_path, gallery_images, youtube_video_url, contact_phone, contact_email, meta_title, meta_description, show_plots_publicly, is_featured, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
            $districtId, $name, $slug,
            $_POST['description'] ?? '', $_POST['amenities'] ?? '', $_POST['key_highlights'] ?? '', $_POST['nearby_places'] ?? '',
            $_POST['map_link'] ?? '', (int)($_POST['total_plots'] ?? 0), (int)($_POST['available_plots'] ?? 0),
            (float)($_POST['starting_price'] ?? 0), $_POST['image_path'] ?? '', $_POST['banner_image'] ?? '',
            $_POST['brochure_path'] ?? '', $_POST['gallery_images'] ?? '', $_POST['youtube_video_url'] ?? '',
            $_POST['contact_phone'] ?? '', $_POST['contact_email'] ?? '',
            $_POST['meta_title'] ?? '', $_POST['meta_description'] ?? '',
            isset($_POST['show_plots_publicly']) ? 1 : 0,
            isset($_POST['is_featured']) ? 1 : 0,
            isset($_POST['is_active']) ? 1 : 0,
        ]);
        $this->setFlash('success', 'Colony created successfully');
        $this->redirect('/admin/colonies');
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
        $plots = $this->db->fetchAll("SELECT * FROM plots WHERE colony_id = ? ORDER BY plot_number", [$id]);
        $this->render('admin/colonies/show', [
            'page_title' => $colony['name'] . ' - Admin',
            'colony' => $colony, 'plots' => $plots,
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

        $this->db->query("UPDATE colonies SET district_id=?, name=?, slug=?, description=?, amenities=?, key_highlights=?, nearby_places=?, map_link=?, total_plots=?, available_plots=?, starting_price=?, image_path=?, banner_image=?, brochure_path=?, gallery_images=?, youtube_video_url=?, contact_phone=?, contact_email=?, meta_title=?, meta_description=?, show_plots_publicly=?, is_featured=?, is_active=? WHERE id=?", [
            (int)($_POST['district_id'] ?? 0), $_POST['name'] ?? '', $slug,
            $_POST['description'] ?? '', $_POST['amenities'] ?? '', $_POST['key_highlights'] ?? '', $_POST['nearby_places'] ?? '',
            $_POST['map_link'] ?? '', (int)($_POST['total_plots'] ?? 0), (int)($_POST['available_plots'] ?? 0),
            (float)($_POST['starting_price'] ?? 0), $_POST['image_path'] ?? '', $_POST['banner_image'] ?? '',
            $_POST['brochure_path'] ?? '', $_POST['gallery_images'] ?? '', $_POST['youtube_video_url'] ?? '',
            $_POST['contact_phone'] ?? '', $_POST['contact_email'] ?? '',
            $_POST['meta_title'] ?? '', $_POST['meta_description'] ?? '',
            isset($_POST['show_plots_publicly']) ? 1 : 0,
            isset($_POST['is_featured']) ? 1 : 0,
            isset($_POST['is_active']) ? 1 : 0,
            $id,
        ]);
        $this->setFlash('success', 'Colony updated successfully');
        $this->redirect('/admin/colonies');
    }

    public function destroy($id)
    {
        $this->requireAdmin();
        $this->db->query("DELETE FROM colonies WHERE id = ?", [$id]);
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
