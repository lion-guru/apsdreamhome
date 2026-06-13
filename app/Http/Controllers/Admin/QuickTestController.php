<?php
namespace App\Http\Controllers\Admin;

class QuickTestController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        $colonies = $this->db->fetchAll("SELECT * FROM colonies ORDER BY name ASC");
        $this->render('admin/quick_test/index', [
            'page_title' => 'Quick Test - Admin',
            'colonies' => $colonies,
        ]);
    }
}
