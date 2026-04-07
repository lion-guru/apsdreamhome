<?php
/**
 * User Controller - Handles user dashboard, profile, my properties, my inquiries
 * Uses existing users table from CustomerAuthController
 */

namespace App\Http\Controllers\Front;

class UserController
{
    private $db;

    public function __construct()
    {
        $this->db = new \PDO(
            "mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome",
            "root",
            "",
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
    }

    public function dashboard()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'customer') {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $registered = isset($_GET['registered']);
        $loginSuccess = isset($_GET['login']);

        // Get user details
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            header('Location: /user/logout');
            exit;
        }

        // Get user properties
        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get user inquiries
        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$user['email']]);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../Views/pages/user_dashboard.php';
    }

    public function myProperties()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'customer') {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Get user properties
        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../Views/pages/user_properties.php';
    }

    public function myInquiries()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'customer') {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Get user inquiries
        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../Views/pages/user_inquiries.php';
    }

    public function profile()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'customer') {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $error = '';
        $success = false;

        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($name) || empty($phone)) {
                $error = 'Please fill in required fields.';
            } elseif (!empty($newPassword)) {
                if (strlen($newPassword) < 6) {
                    $error = 'Password must be at least 6 characters.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'Passwords do not match.';
                } else {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $this->db->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
                    $stmt->execute([$name, $phone, $hashedPassword, $userId]);
                    
                    $_SESSION['user_name'] = $name;
                    $success = true;
                    $user['name'] = $name;
                    $user['phone'] = $phone;
                }
            } else {
                $stmt = $this->db->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $userId]);
                
                $_SESSION['user_name'] = $name;
                $success = true;
                $user['name'] = $name;
                $user['phone'] = $phone;
            }
        }

        include __DIR__ . '/../../../Views/pages/user_profile.php';
    }
}
?>
