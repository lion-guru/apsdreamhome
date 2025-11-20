<?php
/**
 * Network Controller
 * Handles network visualization and MLM dashboard
 */

class NetworkController {
    private $referralService;
    
    public function __construct() {
        $this->referralService = new ReferralService();
    }
    
    /**
     * Show network dashboard
     */
    public function dashboard() {
        if (!isset($_SESSION['user_logged_in'])) {
            header('Location: ' . BASE_URL . 'login');
            exit();
        }
        
        $user_id = $_SESSION['user_id'];
        $dashboard = $this->referralService->getReferralDashboard($user_id);
        
        require_once __DIR__ . '/../views/user/network_dashboard.php';
    }
    
    /**
     * Get network tree data (AJAX)
     */
    public function getNetworkTree() {
        if (!isset($_SESSION['user_logged_in'])) {
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $max_depth = $_GET['depth'] ?? 5;
        $query = $_GET['query'] ?? null;
        $rank = $_GET['rank'] ?? null;

        $options = [];
        if ($query) {
            $options['query'] = $query;
        }
        if ($rank) {
            $options['rank'] = $rank;
        }
        
        $tree = $this->referralService->getNetworkTree($user_id, $max_depth, $options);
        
        echo json_encode([
            'success' => true,
            'data' => $tree
        ]);
    }
    
    /**
     * Get referral analytics (AJAX)
     */
    public function getAnalytics() {
        if (!isset($_SESSION['user_logged_in'])) {
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $days = $_GET['days'] ?? 30;
        
        $analytics = $this->referralService->getReferralAnalytics($user_id, $days);
        
        echo json_encode([
            'success' => true,
            'data' => $analytics
        ]);
    }
    
    /**
     * Get referral link
     */
    public function getReferralLink() {
        if (!isset($_SESSION['user_logged_in'])) {
            echo json_encode(['error' => 'Not authenticated']);
            return;
        }
        
        $user_id = $_SESSION['user_id'];
        $role = $_GET['role'] ?? null;
        
        $link = $this->referralService->getReferralLink($user_id, $role);
        
        echo json_encode([
            'success' => true,
            'link' => $link
        ]);
    }
    
    /**
     * Validate referral code
     */
    public function validateCode() {
        $code = $_GET['code'] ?? '';
        
        if (empty($code)) {
            echo json_encode(['valid' => false, 'error' => 'Code required']);
            return;
        }
        
        $referrer = $this->referralService->validateReferralCode($code);
        
        echo json_encode([
            'valid' => !!$referrer,
            'referrer' => $referrer
        ]);
    }
}
?>