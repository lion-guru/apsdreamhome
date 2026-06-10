<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\BulkOperationsService;
use UploadValidator;

class BulkOperationsController extends AdminController
{
    public function __construct() { parent::__construct(); }

    public function index()
    {
        $this->requireAdmin();
        $svc = new BulkOperationsService($this->db);
        $tables = $svc->getAllowedTables();
        $rowCounts = [];
        foreach ($tables as $t) $rowCounts[$t] = $svc->getRowCount($t);
        $this->data = array_merge($this->data, [
            'page_title' => 'Bulk Import/Export',
            'tables' => $tables,
            'row_counts' => $rowCounts,
        ]);
        return $this->render('admin/features/bulk_operations', $this->data);
    }

    public function template($table)
    {
        $this->requireAdmin();
        $svc = new BulkOperationsService($this->db);
        $template = $svc->getTemplate($table);
        if (!$template) {
            http_response_code(400);
            echo "Invalid table";
            exit;
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $table . '_template.csv"');
        echo $template;
        exit;
    }

    public function import()
    {
        $this->requireAdmin();
        $svc = new BulkOperationsService($this->db);
        $table = $_POST['table'] ?? '';
        $result = null;
        if (!empty($_FILES['csv']['tmp_name'])) {
            $v = UploadValidator::validate($_FILES['csv'], 'csv');
            if ($v !== true) {
                $result = ['ok' => false, 'error' => $v];
            } else {
                $content = file_get_contents($_FILES['csv']['tmp_name']);
                $result = $svc->importCSV($table, $content, (int)($_SESSION['user_id'] ?? 0));
            }
        } else {
            $result = ['ok' => false, 'error' => 'No file uploaded'];
        }
        $_SESSION['bulk_result'] = $result;
        header('Location: ' . BASE_URL . '/admin/bulk-operations');
        exit;
    }

    public function export($table)
    {
        $this->requireAdmin();
        $svc = new BulkOperationsService($this->db);
        $filters = $_GET;
        unset($filters['limit']);
        $csv = $svc->exportCSV($table, $filters, (int)($_GET['limit'] ?? 1000));
        if (!$csv) {
            http_response_code(400);
            echo "Invalid table or no data";
            exit;
        }
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $table . '_export_' . date('Ymd_His') . '.csv"');
        echo $csv;
        exit;
    }

    // ============================================================
    // PROPERTY-SPECIFIC BULK IMPORT
    // ============================================================
    public function propertyImport()
    {
        $this->requireAdmin();
        $this->data = array_merge($this->data, [
            'page_title' => 'Bulk Property Import',
            'preview' => $_SESSION['bulk_property_preview'] ?? null,
            'result' => $_SESSION['bulk_property_result'] ?? null,
            'csrf' => $this->getCsrfToken(),
            'flash_success' => $_SESSION['flash_success'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['bulk_property_preview'], $_SESSION['bulk_property_result'], $_SESSION['flash_success'], $_SESSION['flash_error']);
        return $this->render('admin/bulk/property_import', $this->data);
    }

    public function propertyImportUpload()
    {
        $this->requireAdmin();
        if (empty($_FILES['csv']['tmp_name'])) {
            $_SESSION['flash_error'] = 'No file uploaded';
            header('Location: ' . BASE_URL . '/admin/bulk/property-import');
            exit;
        }
        $v = UploadValidator::validate($_FILES['csv'], 'csv');
        if ($v !== true) {
            $_SESSION['flash_error'] = $v;
            header('Location: ' . BASE_URL . '/admin/bulk/property-import');
            exit;
        }
        $content = file_get_contents($_FILES['csv']['tmp_name']);
        require_once __DIR__ . '/../../../Services/Bulk/PropertyImportService.php';
        $svc = new \App\Services\Bulk\PropertyImportService($this->db);
        $preview = $svc->previewImport($content);
        $_SESSION['bulk_property_preview'] = $preview;
        $_SESSION['bulk_property_csv'] = $content;
        header('Location: ' . BASE_URL . '/admin/bulk/property-import');
        exit;
    }

    public function propertyImportExecute()
    {
        $this->requireAdmin();
        $content = $_SESSION['bulk_property_csv'] ?? '';
        if ($content === '') {
            $_SESSION['flash_error'] = 'No CSV content in session. Please re-upload.';
            header('Location: ' . BASE_URL . '/admin/bulk/property-import');
            exit;
        }
        $options = ['skip_duplicates' => !empty($_POST['skip_duplicates'])];
        require_once __DIR__ . '/../../../Services/Bulk/PropertyImportService.php';
        $svc = new \App\Services\Bulk\PropertyImportService($this->db);
        $result = $svc->importCsv($content, $options);
        unset($_SESSION['bulk_property_csv'], $_SESSION['bulk_property_preview']);
        $_SESSION['bulk_property_result'] = $result;
        if (!empty($result['ok'])) {
            $_SESSION['flash_success'] = "Imported {$result['imported']} properties, skipped {$result['skipped']}";
        } else {
            $_SESSION['flash_error'] = $result['error'] ?? 'Import failed';
        }
        header('Location: ' . BASE_URL . '/admin/bulk/property-import');
        exit;
    }

    public function propertyImportTemplate()
    {
        $this->requireAdmin();
        require_once __DIR__ . '/../../../Services/Bulk/PropertyImportService.php';
        $svc = new \App\Services\Bulk\PropertyImportService($this->db);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="property_import_template.csv"');
        echo $svc->getTemplate();
        exit;
    }

    public function propertyImportSample()
    {
        $this->requireAdmin();
        require_once __DIR__ . '/../../../Services/Bulk/PropertyImportService.php';
        $svc = new \App\Services\Bulk\PropertyImportService($this->db);
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="property_import_sample.csv"');
        echo $svc->getSampleCsv();
        exit;
    }
}
