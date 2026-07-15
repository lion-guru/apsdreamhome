<?php
namespace App\Http\Controllers;

class CustomerController extends BaseController
{
    public function index() 
    {
        // Customer Dashboard
        include __DIR__ . "/../../views/customer/dashboard.php";
    }
    
    public function profile() 
    {
        // Customer Profile Management (GET = view, POST = update)
        $userId = $_SESSION['user_id'] ?? null;

        // Handle POST — profile update
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$userId) {
                $_SESSION['error'] = 'Session expired';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }

            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $address = trim($_POST['address'] ?? '');

            if (empty($name)) {
                $_SESSION['error'] = 'Name is required';
                header('Location: ' . BASE_URL . '/customer/profile');
                exit;
            }

            try {
                $svc = new \App\Services\UserRegistrationService();
                $result = $svc->updateProfile($userId, [
                    'name' => $name,
                    'phone' => $phone,
                    'address' => $address,
                ]);

                if ($result['success']) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['success'] = $result['message'];
                } else {
                    $_SESSION['error'] = $result['message'];
                }
            } catch (\Exception $e) {
                error_log("Customer profile update error: " . $e->getMessage());
                $_SESSION['error'] = 'Failed to update profile';
            }

            header('Location: ' . BASE_URL . '/customer/profile');
            exit;
        }

        // GET — show profile
        $user = [];

        if ($userId) {
            try {
                $user = $this->db->fetch(
                    "SELECT * FROM users WHERE id = ? AND status = 'active'",
                    [$userId]
                );
            } catch (\Exception $e) {
                error_log("Error getting customer: " . $e->getMessage());
    }
}

        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 3));
        }

        $userRole = 'customer';
        $profileUrl = BASE_URL . '/customer/profile';
        $securityUrl = null;
        $canEdit = true;

        $profileView = __DIR__ . '/../../views/shared/profile.php';
        if (file_exists($profileView)) {
            include $profileView;
        } else {
            echo '<div class="alert alert-warning">Profile page under construction</div>';
        }
    }
    
    public function wishlist() 
    {
        // Customer Wishlist
        include __DIR__ . "/../../views/customer/wishlist.php";
    }
    
    public function inquiries() 
    {
        // Customer Inquiries
        include __DIR__ . "/../../views/customer/inquiries.php";
    }
    
    public function documents() 
    {
        // Customer Documents
        include __DIR__ . "/../../views/customer/documents.php";
    }
    
    public function settings() 
    {
        // Customer Settings
        include __DIR__ . "/../../views/customer/settings.php";
    }
    
    public function propertyHistory() 
    {
        // Property History
        include __DIR__ . "/../../views/customer/property_history.php";
    }
    
    public function payments() 
    {
        // Payment History
        include __DIR__ . "/../../views/customer/payments.php";
    }
    
    public function notifications() 
    {
        // Notifications
        include __DIR__ . "/../../views/customer/notifications.php";
    }
}
?>