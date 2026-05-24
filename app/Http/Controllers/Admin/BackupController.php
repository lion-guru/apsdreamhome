<?php

namespace App\Http\Controllers\Admin;

class BackupController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $this->data['page_title'] = 'System Backup - APS Dream Home';
            $this->render('admin/backup/index', $this->data);
        } catch (\Exception $e) {
            $this->setFlash('error', 'Failed to load backup page');
            $this->redirect('/admin/dashboard');
        }
    }
}
