<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;
use App\Traits\TenantAwareTrait;
use App\Core\Middleware\TenantContext;
use Exception;

/**
 * AssociateColonyController
 * Handles colony map and details
 */
class ColonyController extends BaseController
{
    use TenantAwareTrait;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Require associate authentication
     */
    private function requireAuth()
    {
        @session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'associate') {
            $_SESSION['error'] = 'Please login as an associate to access this page';
            $this->redirect('/associate/login');
        }
    }

    /**
     * Colony map
     */
    public function colonyMap($id)
    {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $tid = TenantContext::getId();

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tidSql = TenantContext::getId() > 1 ? " AND tenant_id = ?" : "";
            $params = [$id];
            if (TenantContext::getId() > 1) $params[] = TenantContext::getId();

            $colony = $db->fetchOne("SELECT * FROM colonies WHERE id = ?{$tidSql} LIMIT 1", $params);

            if (!$colony) {
                $_SESSION['error'] = 'Colony not found';
                $this->redirect('/associate/browse');
                return;
            }

            // Get plots with status
            $plots = $db->fetchAll("
                SELECT p.*, up.title as listed_title, up.price as listed_price
                FROM plots p
                LEFT JOIN user_properties up ON up.colony_id = c.id AND up.user_id = ?
                LEFT JOIN colonies c ON c.id = p.colony_id
                WHERE p.colony_id = ?{$tidSql}
                ORDER BY p.plot_number
            ", array_merge([$userId, $id], TenantContext::getId() > 1 ? [TenantContext::getId()] : [])) ?: [];

            // Plot stats
            $total = count($plots);
            $available = count(array_filter($plots, fn($p) => ($p['status'] ?? '') === 'available'));
            $sold = count(array_filter($plots, fn($p) => ($p['status'] ?? '') === 'sold'));
            $held = count(array_filter($plots, fn($p) => ($p['status'] ?? '') === 'hold'));

            // Associate's properties in this colony
            $myProps = array_filter($plots, fn($p) => !empty($p['listed_title']));

            $this->render('associate/colony_map', [
                'page_title' => 'Colony Map - ' . ($colony['name'] ?? 'Colony') . ' - Associate Portal',
                'page_description' => 'View colony plot map',
                'colony' => $colony,
                'plots' => $plots,
                'total' => $total,
                'available' => $available,
                'sold' => $sold,
                'held' => $held,
                'my_props' => $myProps,
            ], 'layouts/associate');
        } catch (\Throwable $e) {
            error_log('AssociateColonyController error: ' . $e->getMessage());
        }
    }
}

