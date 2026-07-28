<?php

// TODO: Add proper error handling with try-catch blocks


namespace App\Http\Controllers\SaaS;

use App\Http\Controllers\BaseController;
use Exception;

class ProfessionalToolsController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    protected function getCurrentUser()
    {
        $userData = [];
        if (isset($_SESSION['user_id'])) {
            $userData['id'] = $_SESSION['user_id'];
            $userData['uid'] = $_SESSION['user_id'];
            $userData['name'] = $_SESSION['user_name'] ?? '';
            $userData['email'] = $_SESSION['user_email'] ?? '';
            $userData['role'] = $_SESSION['role'] ?? '';
        } elseif (isset($_SESSION['admin_id'])) {
            $userData['id'] = $_SESSION['admin_id'];
            $userData['uid'] = $_SESSION['admin_id'];
            $userData['name'] = $_SESSION['admin_name'] ?? '';
            $userData['role'] = 'admin';
        }
        return $userData;
    }

    /**
     * Inventory Management for Builders
     */
    public function inventory()
    {
        $user = $this->getCurrentUser();
        $projects = [];
        $plots = [];
        try {
            if (class_exists('App\Models\Project')) {
                $projects = $this->model('Project')->all(['where' => ['builder_id' => $user['uid'] ?? 0]]);
            }
            if (class_exists('App\Models\Plot')) {
                $plots = $this->model('Plot')->all(['limit' => 20]);
            }
        } catch (\Throwable $e) {
            error_log("ProfessionalToolsController::inventory - " . $e->getMessage());
        }
        $data = [
            'page_title' => 'Inventory Management',
            'user' => $user,
            'projects' => $projects,
            'plots' => $plots
        ];
        return $this->render('saas/tools/inventory', $data);
    }

    /**
     * Construction Workflow for Builders/Contractors
     */
    public function workflow()
    {
        $user = $this->getCurrentUser();
        $data = [
            'page_title' => 'Construction Workflow',
            'user' => $user,
            'workflows' => [
                ['id' => 1, 'name' => 'Foundation', 'status' => 'completed', 'progress' => 100],
                ['id' => 2, 'name' => 'Framing', 'status' => 'in_progress', 'progress' => 45],
                ['id' => 3, 'name' => 'Roofing', 'status' => 'pending', 'progress' => 0]
            ]
        ];
        return $this->render('saas/tools/workflow', $data);
    }

    /**
     * Expense Tracker (Lekha-Jhokha)
     */
    public function expenses()
    {
        $user = $this->getCurrentUser();
        $db = \App\Core\Database\Database::getInstance()->getConnection();

        $totalExpenses = 0;
        $upcomingPayments = 0;
        $recentExpenses = [];

        try {
            $row = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE MONTH(expense_date) = MONTH(CURDATE()) AND YEAR(expense_date) = YEAR(CURDATE()) AND status != 'rejected'")->fetch(\PDO::FETCH_ASSOC);
            $totalExpenses = (float)($row['total'] ?? 0);
        } catch (\Throwable $e) {}

        try {
            $row = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM expenses WHERE status = 'pending'")->fetch(\PDO::FETCH_ASSOC);
            $upcomingPayments = (float)($row['total'] ?? 0);
        } catch (\Throwable $e) {}

        try {
            $recentExpenses = $db->query("SELECT * FROM expenses ORDER BY expense_date DESC LIMIT 20")->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Throwable $e) {}

        $data = [
            'page_title' => 'Expense Tracker',
            'user' => $user,
            'total_expenses' => $totalExpenses,
            'upcoming_payments' => $upcomingPayments,
            'recent_expenses' => $recentExpenses,
        ];
        return $this->render('saas/tools/expenses', $data);
    }

    /**
     * Labor Management
     */
    public function labor()
    {
        $user = $this->getCurrentUser();
        $data = [
            'page_title' => 'Labor Management',
            'user' => $user,
            'labor_records' => []
        ];
        return $this->render('saas/tools/labor', $data);
    }

    /**
     * WhatsApp Marketing Tool
     */
    public function whatsapp()
    {
        $user = $this->getCurrentUser();
        $data = [
            'page_title' => 'WhatsApp Marketing',
            'user' => $user,
            'templates' => [
                ['name' => 'New Property Launch', 'content' => 'Hi, we just launched a new project...'],
                ['name' => 'Payment Reminder', 'content' => 'Dear Customer, this is a reminder...']
            ]
        ];
        return $this->render('saas/tools/whatsapp', $data);
    }

    /**
     * Referral Program
     */
    public function referrals()
    {
        $user = $this->getCurrentUser();
        $referrals = [];
        try {
            if (class_exists('App\Models\Referral')) {
                $referrals = $this->model('Referral')->all(['where' => ['referred_by' => $user['uid'] ?? 0]]);
            }
        } catch (\Throwable $e) {
            error_log("ProfessionalToolsController::referrals - " . $e->getMessage());
        }
        $data = [
            'page_title' => 'Referral Program',
            'user' => $user,
            'referrals' => $referrals
        ];
        return $this->render('saas/tools/referrals', $data);
    }

    /**
     * Document Vault
     */
    public function documents()
    {
        $user = $this->getCurrentUser();
        $data = [
            'page_title' => 'Document Vault',
            'user' => $user,
            'documents' => []
        ];
        return $this->render('saas/tools/documents', $data);
    }
}
