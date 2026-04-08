<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    private function requireCustomerLogin()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'customer') {
            header('Location: /login');
            exit;
        }
    }

    private function getUser()
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            header('Location: /user/logout');
            exit;
        }

        return $user;
    }

    public function dashboard()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$user['email']]);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'page_title' => 'My Dashboard - APS Dream Home',
            'page_description' => 'Manage your properties and inquiries',
            'user' => $user,
            'properties' => $properties,
            'inquiries' => $inquiries,
            'registered' => isset($_GET['registered']),
            'loginSuccess' => isset($_GET['login']),
        ];

        $this->render('pages/user_dashboard', $data);
    }

    public function myProperties()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'page_title' => 'My Properties - APS Dream Home',
            'page_description' => 'View and manage your listed properties',
            'user' => $user,
            'properties' => $properties,
        ];

        $this->render('pages/user_properties', $data);
    }

    public function myInquiries()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $data = [
            'page_title' => 'My Inquiries - APS Dream Home',
            'page_description' => 'Track your property inquiries',
            'user' => $user,
            'inquiries' => $inquiries,
        ];

        $this->render('pages/user_inquiries', $data);
    }

    public function profile()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $error = '';
        $success = false;

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
                    $stmt->execute([$name, $phone, $hashedPassword, $_SESSION['user_id']]);
                    
                    $_SESSION['user_name'] = $name;
                    $success = true;
                    $user['name'] = $name;
                    $user['phone'] = $phone;
                }
            } else {
                $stmt = $this->db->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
                $stmt->execute([$name, $phone, $_SESSION['user_id']]);
                
                $_SESSION['user_name'] = $name;
                $success = true;
                $user['name'] = $name;
                $user['phone'] = $phone;
            }
        }

        $data = [
            'page_title' => 'My Profile - APS Dream Home',
            'page_description' => 'Manage your account settings',
            'user' => $user,
            'error' => $error,
            'success' => $success,
        ];

        $this->render('pages/user_profile', $data);
    }
}
