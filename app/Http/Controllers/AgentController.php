<?php

namespace App\Http\Controllers;

use App\Core\App;
use App\Core\Auth;
use App\Http\BaseController;

class AgentController extends BaseController
{
    public function dashboard()
    {
        // Check if agent is logged in
        if (!isset($_SESSION['user_id']) || (isset($_SESSION['user_role']) && $_SESSION['user_role'] !== 'agent')) {
            header("Location: /agent/login");
            exit();
        }

        $agent_id = $_SESSION['user_id'];
        $agent_name = $_SESSION['user_name'] ?? 'Agent';
        $agent_level = $_SESSION['level'] ?? 'Agent'; // Should be fetched from DB

        $db = App::database();
        $agent_data = [];

        // Get comprehensive agent data from mlm_profiles + users
        try {
            // Join users and mlm_profiles
            $query = "
                SELECT u.name, u.email, u.phone, mp.* 
                FROM users u 
                JOIN mlm_profiles mp ON u.id = mp.user_id 
                WHERE u.id = :uid AND mp.user_type = 'agent'
            ";
            // Use fetchOne or similar if available, or just query
            // Assuming $db is App\Core\Database instance which likely has query() and fetch() methods
            // If fetch() is not available, use query()->fetch()
            $stmt = $db->query($query, ['uid' => $agent_id]);
            $agent_data = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$agent_data) {
                // Handle case where user exists but no MLM profile or not an agent
                $agent_data = [
                    'referral_code' => 'N/A',
                    'current_level' => 'Agent',
                    'lifetime_sales' => 0,
                    'total_commission' => 0,
                    'total_team_size' => 0,
                    'direct_referrals' => 0,
                    'profile_image' => null
                ];
            } else {
                // Update session data with latest from DB
                $agent_level = $agent_data['current_level'];
                $_SESSION['level'] = $agent_level;
            }
        } catch (\Exception $e) {
            logger()->error("Error fetching agent data: " . $e->getMessage());
            $agent_data = [];
        }

        // Get dashboard statistics
        $stats = [
            'total_sales' => $agent_data['lifetime_sales'] ?? 0,
            'commission_earned' => $agent_data['total_commission'] ?? 0,
            'total_customers' => $agent_data['direct_referrals'] ?? 0,
            'pending_leads' => 0 // Placeholder
        ];

        $data = [
            'page_title' => 'Agent Dashboard | APS Dream Homes',
            'layout' => 'layouts/base', // Use base layout
            'agent_name' => $agent_name,
            'agent_level' => $agent_level,
            'agent_data' => $agent_data,
            'stats' => $stats
        ];

        $this->view('agent/dashboard', $data, 'layouts/base');
    }

    public function login()
    {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            if ($_SESSION['user_role'] === 'agent') {
                header("Location: /agent/dashboard");
                exit();
            }
        }
        $this->view('auth/login', ['role' => 'agent']);
    }

    public function logout()
    {
        session_destroy();
        header("Location: /login");
        exit();
    }
}
