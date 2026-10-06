<?php

namespace App\Http\Controllers\Admin;

use App\Services\PlotImportService;

class PlotImportController extends AdminController
{
    use \App\Traits\TenantAwareTrait;

    private $plotImportService;

    public function __construct()
    {
        parent::__construct();
        $this->plotImportService = new PlotImportService();
    }

    public function importForm()
    {
        $this->requireAdmin();

        try {
            $colonies = $this->db->fetchAll("SELECT id, name FROM colonies ORDER BY name");
        } catch (\Exception $e) {
            $colonies = [];
        }

        return $this->render('admin/plot-import/import', [
            'page_title' => 'Bulk Import/Export Plots',
            'colonies' => $colonies,
        ]);
    }

    public function import()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->redirect('/admin/plots/import');
        }

        $this->validateCsrfOrFail();

        if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Please select a valid CSV file');
            return $this->redirect('/admin/plots/import');
        }

        $file = $_FILES['import_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            $this->setFlash('error', 'Only CSV files are allowed');
            return $this->redirect('/admin/plots/import');
        }

        $colonyId = !empty($_POST['colony_id']) ? (int)$_POST['colony_id'] : null;

        try {
            $stats = $this->plotImportService->importCsv($file['tmp_name'], $colonyId);

            $this->setFlash('success', "Import complete: {$stats['success']} imported, {$stats['errors']} errors out of {$stats['total']} rows");

            if (!empty($stats['error_details'])) {
                $_SESSION['import_errors'] = array_slice($stats['error_details'], 0, 50);
            }

            $this->logImportActivity($stats, $colonyId);
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Import failed: ' . $e->getMessage());
        }

        return $this->redirect('/admin/plots/import');
    }

    public function export()
    {
        $this->requireAdmin();

        $colonyId = !empty($_GET['colony_id']) ? (int)$_GET['colony_id'] : null;

        try {
            $result = $this->plotImportService->exportCsv($colonyId);

            if (!$result['success'] || !file_exists($result['filepath'])) {
                $this->setFlash('error', 'Export failed');
                return $this->redirect('/admin/plots/import');
            }

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            header('Content-Length: ' . filesize($result['filepath']));
            readfile($result['filepath']);
            exit;
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Export failed: ' . $e->getMessage());
            return $this->redirect('/admin/plots/import');
        }
    }

    public function template()
    {
        $this->requireAdmin();

        try {
            $result = $this->plotImportService->downloadTemplate();

            if (!$result['success'] || !file_exists($result['filepath'])) {
                $this->setFlash('error', 'Template generation failed');
                return $this->redirect('/admin/plots/import');
            }

            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="' . $result['filename'] . '"');
            header('Content-Length: ' . filesize($result['filepath']));
            readfile($result['filepath']);
            exit;
        } catch (\Throwable $e) {
            $this->setFlash('error', 'Template download failed: ' . $e->getMessage());
            return $this->redirect('/admin/plots/import');
        }
    }

    public function importHistory()
    {
        $this->requireAdmin();

        try {
            $history = $this->db->fetchAll(
                "SELECT * FROM user_activity_logs_unified WHERE action = 'plots_import' ORDER BY created_at DESC LIMIT 50"
            );
        } catch (\Exception $e) {
            $history = [];
        }

        return $this->render('admin/plot-import/history', [
            'page_title' => 'Import History',
            'history' => $history,
        ]);
    }

    private function logImportActivity($stats, $colonyId)
    {
        try {
            $this->db->insert('user_activity_logs_unified', [
                'user_id' => $_SESSION['admin_id'] ?? $_SESSION['user_id'] ?? 0,
                'action' => 'plots_import',
                'context' => json_encode([
                    'imported' => $stats['success'],
                    'errors' => $stats['errors'],
                    'total' => $stats['total'],
                    'colony_id' => $colonyId,
                ]),
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            error_log('Failed to log import activity: ' . $e->getMessage());
        }
    }
}
