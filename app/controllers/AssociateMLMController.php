<?php
/**
 * Associate MLM Controller
 * Handles MLM operations for associates
 */

namespace App\Controllers;

class AssociateMLMController extends BaseController {

    private $associateMLM;

    public function __construct() {
        $this->associateMLM = new \App\Models\AssociateMLM();
    }

    /**
     * Display MLM dashboard for associate
     */
    public function dashboard() {
        // Check if user is logged in
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        // Check if user is an associate
        $associate_mlm = $this->associateMLM->getAssociateMLM($user_id);
        if (!$associate_mlm) {
            $this->setFlashMessage('error', 'You are not registered as an associate');
            $this->redirect(BASE_URL . 'dashboard');
            return;
        }

        // Get dashboard data
        $dashboard_data = $this->associateMLM->getDashboardData($user_id);

        // Set page data
        $this->data['page_title'] = 'MLM Dashboard - ' . APP_NAME;
        $this->data['dashboard_data'] = $dashboard_data;
        $this->data['mlm_levels'] = $this->associateMLM->getLevelConfig();

        $this->render('associate/mlm_dashboard');
    }

    /**
     * Display genealogy tree
     */
    public function genealogy() {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $user_id = $_SESSION['user_id'];
        $associate_mlm = $this->associateMLM->getAssociateMLM($user_id);

        if (!$associate_mlm) {
            $this->setFlashMessage('error', 'You are not registered as an associate');
            $this->redirect(BASE_URL . 'dashboard');
            return;
        }

        $genealogy = $this->associateMLM->getGenealogy($user_id);

        $this->data['page_title'] = 'MLM Genealogy - ' . APP_NAME;
        $this->data['genealogy'] = $genealogy;
        $this->data['associate_info'] = $associate_mlm;

        $this->render('associate/genealogy');
    }

    /**
     * Display downline management
     */
    public function downline() {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $user_id = $_SESSION['user_id'];
        $associate_mlm = $this->associateMLM->getAssociateMLM($user_id);

        if (!$associate_mlm) {
            $this->setFlashMessage('error', 'You are not registered as an associate');
            $this->redirect(BASE_URL . 'dashboard');
            return;
        }

        $levels = $_GET['levels'] ?? 3;
        $downline = $this->associateMLM->getDownline($user_id, $levels);
        $stats = $this->associateMLM->getMLMStats($user_id);

        $this->data['page_title'] = 'Downline Management - ' . APP_NAME;
        $this->data['downline'] = $downline;
        $this->data['stats'] = $stats;
        $this->data['levels'] = $levels;

        $this->render('associate/downline');
    }

    /**
     * Display commission history
     */
    public function commissions() {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $user_id = $_SESSION['user_id'];

        // Get commission data (placeholder - would need commission_history table)
        $commissions = $this->getCommissionHistory($user_id);

        $this->data['page_title'] = 'Commission History - ' . APP_NAME;
        $this->data['commissions'] = $commissions;

        $this->render('associate/commissions');
    }

    /**
     * Display rank and achievements
     */
    public function rank() {
        if (!$this->isLoggedIn()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $user_id = $_SESSION['user_id'];
        $associate_mlm = $this->associateMLM->getAssociateMLM($user_id);

        if (!$associate_mlm) {
            $this->setFlashMessage('error', 'You are not registered as an associate');
            $this->redirect(BASE_URL . 'dashboard');
            return;
        }

        $rank_info = $this->associateMLM->getAssociateRank($user_id);

        $this->data['page_title'] = 'Rank & Achievements - ' . APP_NAME;
        $this->data['rank_info'] = $rank_info;
        $this->data['mlm_levels'] = $this->associateMLM->getLevelConfig();

        $this->render('associate/rank');
    }

    /**
     * Admin - MLM Management Dashboard
     */
    public function adminDashboard() {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $filters = [
            'level' => $_GET['level'] ?? '',
            'status' => $_GET['status'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];

        $report_data = $this->associateMLM->getMLMReport($filters);
        $top_performers = $this->associateMLM->getTopPerformers(10);

        $this->data['page_title'] = 'MLM Management - ' . APP_NAME;
        $this->data['report_data'] = $report_data;
        $this->data['top_performers'] = $top_performers;
        $this->data['filters'] = $filters;
        $this->data['mlm_levels'] = $this->associateMLM->getLevelConfig();

        $this->render('admin/mlm_dashboard');
    }

    /**
     * Admin - View associate details
     */
    public function adminAssociateDetails($associate_id) {
        if (!$this->isAdmin()) {
            $this->redirect(BASE_URL . 'login');
            return;
        }

        $associate_mlm = $this->associateMLM->getAssociateMLM($associate_id);
        if (!$associate_mlm) {
            $this->setFlashMessage('error', 'Associate not found');
            $this->redirect(BASE_URL . 'admin/mlm');
            return;
        }

        $dashboard_data = $this->associateMLM->getDashboardData($associate_id);
        $genealogy = $this->associateMLM->getGenealogy($associate_id, 5);

        $this->data['page_title'] = 'Associate Details - ' . APP_NAME;
        $this->data['associate'] = $associate_mlm;
        $this->data['dashboard_data'] = $dashboard_data;
        $this->data['genealogy'] = $genealogy;

        $this->render('admin/associate_details');
    }

    /**
     * Get commission history (placeholder)
     */
    private function getCommissionHistory($user_id) {
        // This would query a commission_history table
        // For now, return sample data
        return [
            [
                'id' => 1,
                'sale_id' => 'SALE001',
                'commission_amount' => 1500.00,
                'commission_date' => '2024-01-15',
                'level' => 1,
                'description' => 'Commission from property sale'
            ],
            [
                'id' => 2,
                'sale_id' => 'SALE002',
                'commission_amount' => 2250.00,
                'commission_date' => '2024-01-10',
                'level' => 2,
                'description' => 'Commission from downline sale'
            ]
        ];
    }

    /**
     * API - Get associate MLM data
     */
    public function apiGetMLMData() {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
        }

        $user_id = $_SESSION['user_id'];
        $associate_mlm = $this->associateMLM->getAssociateMLM($user_id);

        if (!$associate_mlm) {
            sendJsonResponse(['success' => false, 'error' => 'Not registered as associate'], 404);
        }

        $dashboard_data = $this->associateMLM->getDashboardData($user_id);

        sendJsonResponse([
            'success' => true,
            'data' => $dashboard_data
        ]);
    }

    /**
     * API - Get genealogy data
     */
    public function apiGetGenealogy() {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
        }

        $user_id = $_SESSION['user_id'];
        $levels = $_GET['levels'] ?? 3;

        $genealogy = $this->associateMLM->getGenealogy($user_id, $levels);

        sendJsonResponse([
            'success' => true,
            'data' => $genealogy
        ]);
    }

    /**
     * API - Get downline data
     */
    public function apiGetDownline() {
        header('Content-Type: application/json');

        if (!$this->isLoggedIn()) {
            sendJsonResponse(['success' => false, 'error' => 'Authentication required'], 401);
        }

        $user_id = $_SESSION['user_id'];
        $levels = $_GET['levels'] ?? 3;

        $downline = $this->associateMLM->getDownline($user_id, $levels);

        sendJsonResponse([
            'success' => true,
            'data' => $downline
        ]);
    }
}
