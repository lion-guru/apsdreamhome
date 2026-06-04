<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Services\BulkOperationsService;

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
            $content = file_get_contents($_FILES['csv']['tmp_name']);
            $result = $svc->importCSV($table, $content, (int)($_SESSION['user_id'] ?? 0));
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
}
