<?php

namespace App\Http\Controllers\Admin;

use App\Services\AdManagerService;

class AdManagerController extends AdminController
{
    private $adService;

    public function __construct()
    {
        parent::__construct();
        $this->adService = new AdManagerService();
    }

    public function index()
    {
        $this->requireAdmin();
        $slots = $this->adService->getAllSlots();

        $summary = ['total' => count($slots), 'active' => 0, 'total_views' => 0, 'total_clicks' => 0];
        foreach ($slots as $s) {
            if ($s['status'] === 'active') $summary['active']++;
            $summary['total_views'] += (int)($s['views'] ?? 0);
            $summary['total_clicks'] += (int)($s['clicks'] ?? 0);
        }

        $this->render('admin/ads/index', [
            'page_title' => 'Ad Manager',
            'slots' => $slots,
            'summary' => $summary,
        ]);
    }

    public function create()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->adService->upsertSlot($_POST);
            $this->flashMessage('Ad slot saved', 'success');
            header('Location: ' . BASE_URL . '/admin/ads');
            exit;
        }

        $this->render('admin/ads/form', [
            'page_title' => 'New Ad Slot',
            'ad' => null,
        ]);
    }

    public function edit(int $id)
    {
        $this->requireAdmin();
        $slots = $this->adService->getAllSlots();
        $ad = null;
        foreach ($slots as $s) {
            if ((int)$s['id'] === $id) { $ad = $s; break; }
        }

        if (!$ad) {
            $this->flashMessage('Ad not found', 'error');
            header('Location: ' . BASE_URL . '/admin/ads');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $_POST['id'] = $id;
            $this->adService->upsertSlot($_POST);
            $this->flashMessage('Ad slot updated', 'success');
            header('Location: ' . BASE_URL . '/admin/ads');
            exit;
        }

        $this->render('admin/ads/form', [
            'page_title' => 'Edit Ad: ' . $ad['title'],
            'ad' => $ad,
        ]);
    }

    public function delete(int $id)
    {
        $this->requireAdmin();
        $this->adService->deleteSlot($id);
        $this->flashMessage('Ad slot deleted', 'success');
        header('Location: ' . BASE_URL . '/admin/ads');
        exit;
    }
}
