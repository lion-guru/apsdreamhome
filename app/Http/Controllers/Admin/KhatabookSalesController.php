<?php

namespace App\Http\Controllers\Admin;

use App\Core\Database\Database;

class KhatabookSalesController extends AdminController
{
    use \App\Traits\TenantAwareTrait;
    public function index()
    {
        $this->requireAdmin();

        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 50;
        $offset = ($page - 1) * $perPage;

        $where = [];
        $params = [];

        if (!empty($_GET['search'])) {
            $where[] = "(customer_name LIKE ? OR customer_phone LIKE ? OR reference_no LIKE ?)";
            $s = '%' . $_GET['search'] . '%';
            $params[] = $s; $params[] = $s; $params[] = $s;
        }
        if (!empty($_GET['date_from'])) {
            $where[] = "transaction_date >= ?";
            $params[] = $_GET['date_from'];
        }
        if (!empty($_GET['date_to'])) {
            $where[] = "transaction_date <= ?";
            $params[] = $_GET['date_to'];
        }
        if (!empty($_GET['batch'])) {
            $where[] = "import_batch = ?";
            $params[] = $_GET['batch'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        try {
            $db = Database::getInstance()->getConnection();

            $countStmt = $db->prepare("SELECT COUNT(*) as total FROM khatabook_sales {$whereClause}");
            $countStmt->execute($params);
            $total = (int)($countStmt->fetch(\PDO::FETCH_ASSOC)['total'] ?? 0);

            $stmt = $db->prepare("SELECT * FROM khatabook_sales {$whereClause} ORDER BY transaction_date DESC, id DESC LIMIT ? OFFSET ?");
            $stmt->execute(array_merge($params, [$perPage, $offset]));
            $sales = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $batchStmt = $db->query("SELECT DISTINCT import_batch FROM khatabook_sales WHERE import_batch IS NOT NULL ORDER BY import_batch DESC LIMIT 20");
            $batches = $batchStmt->fetchAll(\PDO::FETCH_ASSOC);

            $sumStmt = $db->query("SELECT COUNT(*) as total_records, SUM(amount) as total_amount FROM khatabook_sales");
            $summary = $sumStmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $sales = [];
            $batches = [];
            $summary = ['total_records' => 0, 'total_amount' => 0];
            $total = 0;
        }

        $totalPages = ceil($total / $perPage);

        $this->render('admin/khatabook-sales/index', [
            'page_title' => 'Khatabook Sales Records',
            'sales' => $sales,
            'batches' => $batches,
            'summary' => $summary,
            'total' => $total,
            'page' => $page,
            'totalPages' => $totalPages,
            'perPage' => $perPage,
        ]);
    }

    public function show(int $id)
    {
        $this->requireAdmin();

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM khatabook_sales WHERE id = ?");
            $stmt->execute([$id]);
            $sale = $stmt->fetch(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $sale = null;
        }

        if (!$sale) {
            $this->flashMessage('Sale record not found', 'error');
            header('Location: ' . BASE_URL . '/admin/khatabook-sales');
            exit;
        }

        $this->render('admin/khatabook-sales/view', [
            'page_title' => 'Sale #' . $id,
            'sale' => $sale,
        ]);
    }

    public function export()
    {
        $this->requireAdmin();

        try {
            $db = Database::getInstance()->getConnection();

            $where = [];
            $params = [];
            if (!empty($_GET['date_from'])) {
                $where[] = "transaction_date >= ?";
                $params[] = $_GET['date_from'];
            }
            if (!empty($_GET['date_to'])) {
                $where[] = "transaction_date <= ?";
                $params[] = $_GET['date_to'];
            }
            $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

            $stmt = $db->prepare("SELECT * FROM khatabook_sales {$whereClause} ORDER BY transaction_date DESC");
            $stmt->execute($params);
            $sales = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="khatabook_sales_export_' . date('Y-m-d') . '.csv"');

            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            if (!empty($sales)) {
                fputcsv($output, array_keys($sales[0]));
                foreach ($sales as $row) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
        } catch (\Exception $e) {
            $this->flashMessage('Export failed: ' . $e->getMessage(), 'error');
            header('Location: ' . BASE_URL . '/admin/khatabook-sales');
        }
        exit;
    }

    public function delete(int $id)
    {
        $this->requireAdmin();

        try {
            $db = Database::getInstance()->getConnection();
            [$tenantSql, $tenantParams] = $this->tenantWhere();
            $stmt = $db->prepare("DELETE FROM khatabook_sales WHERE id = ? {$tenantSql}");
            $stmt->execute(array_merge([$id], $tenantParams));
            $this->flashMessage('Sale record deleted', 'success');
        } catch (\Exception $e) {
            $this->flashMessage('Delete failed: ' . $e->getMessage(), 'error');
        }

        header('Location: ' . BASE_URL . '/admin/khatabook-sales');
        exit;
    }
}
