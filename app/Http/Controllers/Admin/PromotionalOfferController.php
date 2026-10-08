<?php

namespace App\Http\Controllers\Admin;

/**
 * Admin CRUD for customer-facing promotional offers
 * (property discounts consumed by newsletter automation).
 * Table pre-existed with rows but had zero writers — this is the writer.
 */
class PromotionalOfferController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $offers = $this->db->fetchAll("SELECT * FROM promotional_offers ORDER BY valid_until DESC, id DESC LIMIT 200") ?? [];
        } catch (\Exception $e) {
            $offers = [];
        }
        return $this->render('admin/promotional_offers/index', [
            'page_title' => 'Promotional Offers',
            'offers' => $offers,
        ]);
    }

    public function store()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/promotional-offers'); }
        $title = trim($_POST['title'] ?? '');
        if ($title === '') { $this->setFlash('error', 'Title required'); $this->redirect('/admin/promotional-offers'); }
        try {
            $this->db->query(
                "INSERT INTO promotional_offers (tenant_id, title, description, discount_percentage, valid_until, status) VALUES (1, ?, ?, ?, ?, 'active')",
                [$title, trim($_POST['description'] ?? ''), (float)($_POST['discount_percentage'] ?? 0), $_POST['valid_until'] ?? date('Y-m-d')]
            );
            $this->setFlash('success', 'Promotional offer created');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/promotional-offers');
    }

    public function setStatus()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/promotional-offers'); }
        $status = in_array($_POST['status'] ?? '', ['active', 'inactive', 'expired'], true) ? $_POST['status'] : 'inactive';
        try {
            $this->db->query("UPDATE promotional_offers SET status=? WHERE id=?", [$status, (int)($_POST['id'] ?? 0)]);
            $this->setFlash('success', 'Status updated');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/promotional-offers');
    }

    public function delete()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->redirect('/admin/promotional-offers'); }
        try {
            $this->db->query("DELETE FROM promotional_offers WHERE id=?", [(int)($_POST['id'] ?? 0)]);
            $this->setFlash('success', 'Offer deleted');
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed: ' . $e->getMessage());
        }
        $this->redirect('/admin/promotional-offers');
    }
}
