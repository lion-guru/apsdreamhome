<?php
namespace App\Http\Controllers\Admin;

class ExpensesController extends AdminController
{
    public function index()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $expenses = $db->query("SELECT e.*, a.name as associate_name FROM expenses e LEFT JOIN associates a ON e.associate_id=a.id ORDER BY e.expense_date DESC, e.id DESC LIMIT 100")->fetchAll(\PDO::FETCH_ASSOC) ?: [];
            $stats = [
                'total' => (int)$db->query("SELECT COUNT(*) FROM expenses")->fetchColumn(),
                'total_amount' => (float)$db->query("SELECT COALESCE(SUM(amount),0) FROM expenses")->fetchColumn(),
                'pending' => (int)$db->query("SELECT COUNT(*) FROM expenses WHERE status='pending'")->fetchColumn(),
                'approved' => (int)$db->query("SELECT COUNT(*) FROM expenses WHERE status='approved'")->fetchColumn(),
            ];
        } catch (\Exception $e) {
            $expenses = [];
            $stats = ['total' => 0, 'total_amount' => 0, 'pending' => 0, 'approved' => 0];
        }
        return $this->render('admin/expenses/index', ['expenses' => $expenses, 'stats' => $stats]);
    }

    public function create()
    {
        $this->requireAdmin();
        return $this->render('admin/expenses/create', []);
    }

    public function store()
    {
        $this->requireAdmin();
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("INSERT INTO expenses (category, amount, description, payment_mode, expense_date, status, created_at) VALUES (?, ?, ?, ?, ?, 'pending', NOW())");
            $stmt->execute([
                $_POST['category'] ?? '',
                $_POST['amount'] ?? 0,
                $_POST['description'] ?? '',
                $_POST['payment_mode'] ?? 'cash',
                $_POST['expense_date'] ?? date('Y-m-d')
            ]);
            $this->setFlashMessage('success', 'Expense recorded successfully');
        } catch (\Exception $e) {
            $this->setFlashMessage('error', 'Failed to record expense: ' . $e->getMessage());
        }
        return $this->redirect('/admin/expenses');
    }
}
