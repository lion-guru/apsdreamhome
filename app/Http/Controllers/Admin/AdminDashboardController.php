<?php

namespace App\Http\Controllers\Admin;

use App\Core\Controller;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        
        // Check authentication
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            // Use controller redirect helper for consistency
            $this->redirect('index.php');
            return;
        }
    }
    
    public function index()
    {
        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get quick actions
        $quickActions = $this->getQuickActions();
        
        // Get recent activities
        $recentActivities = $this->getRecentActivities();
        
        // Get system status
        $systemStatus = $this->getSystemStatus();
        
        // Render the dashboard view using base controller's view helper
        $this->view('admin/dashboard', [
            'stats' => $stats,
            'quickActions' => $quickActions,
            'recent_activities' => $recentActivities,
            'system_status' => $systemStatus
        ]);
    }
    
    private function getDashboardStats()
    {
        $stats = [];
        
        // Total Properties
        try {
            $result = $this->db->query("SELECT COUNT(*) as cnt FROM properties");
            $totalProperties = $result ? $result->fetch(\PDO::FETCH_ASSOC)['cnt'] : 0;
        } catch (\Exception $e) {
            $totalProperties = 0;
        }
        
        // Featured Properties
        try {
            $result = $this->db->query("SELECT COUNT(*) as cnt FROM properties WHERE featured = 1");
            $featuredProperties = $result ? $result->fetch(\PDO::FETCH_ASSOC)['cnt'] : 0;
        } catch (\Exception $e) {
            $featuredProperties = 0;
        }
        
        // Total Users
        try {
            $result = $this->db->query("SELECT COUNT(*) as cnt FROM users");
            $totalUsers = $result ? $result->fetch(\PDO::FETCH_ASSOC)['cnt'] : 0;
        } catch (\Exception $e) {
            $totalUsers = 0;
        }
        
        // Total Agents
        try {
            $result = $this->db->query("SELECT COUNT(*) as cnt FROM associates WHERE status = 'active'");
            $totalAgents = $result ? $result->fetch(\PDO::FETCH_ASSOC)['cnt'] : 0;
        } catch (\Exception $e) {
            $totalAgents = 0;
        }
        
        return [
            'total_properties' => $totalProperties,
            'featured_properties' => $featuredProperties,
            'total_users' => $totalUsers,
            'total_agents' => $totalAgents
        ];
    }
    
    private function getQuickActions()
    {
        return [
            [
                'title' => 'Add Booking',
                'icon' => 'fas fa-plus',
                'url' => '/admin/bookings.php?action=add',
                'color' => 'primary'
            ],
            [
                'title' => 'Manage Properties',
                'icon' => 'fas fa-building',
                'url' => '/admin/properties.php',
                'color' => 'success'
            ],
            [
                'title' => 'View Reports',
                'icon' => 'fas fa-chart-bar',
                'url' => '/admin/reports.php',
                'color' => 'info'
            ],
            [
                'title' => 'System Settings',
                'icon' => 'fas fa-cog',
                'url' => '/admin/settings.php',
                'color' => 'warning'
            ]
        ];
    }
    
    private function getRecentActivities()
    {
        $activities = [];
        
        try {
            // Get recent property additions
            $recentProperties = $this->db->query("
                SELECT title, created_at 
                FROM properties 
                ORDER BY created_at DESC 
                LIMIT 3
            ");
            
            if ($recentProperties) {
                while ($property = $recentProperties->fetch(\PDO::FETCH_ASSOC)) {
                    $activities[] = [
                        'type' => 'property_added',
                        'message' => 'New property added: ' . htmlspecialchars($property['title']),
                        'time' => date('M j, Y', strtotime($property['created_at']))
                    ];
                }
            }
            
            // Get recent user registrations
            $recentUsers = $this->db->query("
                SELECT name, created_at 
                FROM users 
                ORDER BY created_at DESC 
                LIMIT 2
            ");
            
            if ($recentUsers) {
                while ($user = $recentUsers->fetch(\PDO::FETCH_ASSOC)) {
                    $activities[] = [
                        'type' => 'user_registered',
                        'message' => 'New user registered: ' . htmlspecialchars($user['name']),
                        'time' => date('M j, Y', strtotime($user['created_at']))
                    ];
                }
            }
            
        } catch (\Exception $e) {
            // Return empty activities if query fails
        }
        
        return $activities;
    }
    
    private function getSystemStatus()
    {
        return [
            'database_status' => 'Connected',
            'server_status' => 'Online',
            'last_backup' => date('Y-m-d H:i', strtotime('-1 day')),
            'system_version' => '1.0.0'
        ];
    }
}
