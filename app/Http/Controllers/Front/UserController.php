<?php

namespace App\Http\Controllers\Front;

use PDO;

class UserController 
{
    private $db;

    public function __construct()
    {
        $this->db = new PDO(
            "mysql:host=127.0.0.1;port=3307;dbname=apsdreamhome",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
    }

    public function register()
    {
        $error = '';
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($name) || empty($email) || empty($phone) || empty($password)) {
                $error = 'Please fill in all required fields.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Please enter a valid email address.';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                try {
                    // Check if email already exists
                    $stmt = $this->db->prepare("SELECT id FROM customers WHERE email = ?");
                    $stmt->execute([$email]);
                    if ($stmt->fetch()) {
                        $error = 'Email already registered. Please login instead.';
                    } else {
                        // Create user
                        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $this->db->prepare("
                            INSERT INTO customers (name, email, phone, password, status, created_at) 
                            VALUES (?, ?, ?, ?, 'active', NOW())
                        ");
                        $stmt->execute([$name, $email, $phone, $hashedPassword]);
                        $userId = $this->db->lastInsertId();

                        // Auto login
                        $_SESSION['user_id'] = $userId;
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_phone'] = $phone;

                        header('Location: /user/dashboard?registered=1');
                        exit;
                    }
                } catch (\Exception $e) {
                    $error = 'Registration failed. Please try again.';
                    error_log("Registration error: " . $e->getMessage());
                }
            }
        }

        include __DIR__ . '/../../../Views/pages/user_register.php';
    }

    public function login()
    {
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Please fill in all fields.';
            } else {
                try {
                    $stmt = $this->db->prepare("SELECT * FROM customers WHERE email = ? AND status = 'active' LIMIT 1");
                    $stmt->execute([$email]);
                    $user = $stmt->fetch(\PDO::FETCH_ASSOC);

                    if ($user && password_verify($password, $user['password'])) {
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['user_name'] = $user['name'];
                        $_SESSION['user_email'] = $user['email'];
                        $_SESSION['user_phone'] = $user['phone'];

                        header('Location: /user/dashboard?login=1');
                        exit;
                    } else {
                        $error = 'Invalid email or password.';
                    }
                } catch (\Exception $e) {
                    $error = 'Login failed. Please try again.';
                    error_log("Login error: " . $e->getMessage());
                }
            }
        }

        include __DIR__ . '/../../../Views/pages/user_login.php';
    }

    public function logout()
    {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_phone']);
        session_destroy();
        header('Location: /');
        exit;
    }

    public function dashboard()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $registered = isset($_GET['registered']);
        $loginSuccess = isset($_GET['login']);

        // Get user details
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Get user properties
        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE phone = ? ORDER BY created_at DESC");
        $stmt->execute([$user['phone']]);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        // Get user inquiries
        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([$user['email']]);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../Views/pages/user_dashboard.php';
    }

    public function myProperties()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->getUser($userId);

        // Get user properties
        $stmt = $this->db->prepare("SELECT * FROM user_properties WHERE phone = ? ORDER BY created_at DESC");
        $stmt->execute([$user['phone']]);
        $properties = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../Views/pages/user_properties.php';
    }

    public function myInquiries()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->getUser($userId);

        // Get user inquiries
        $stmt = $this->db->prepare("SELECT * FROM inquiries WHERE email = ? ORDER BY created_at DESC");
        $stmt->execute([$user['email']]);
        $inquiries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        include __DIR__ . '/../../../Views/pages/user_inquiries.php';
    }

    public function profile()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $user = $this->getUser($userId);
        $error = '';
        $success = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $phone = trim($_POST['phone'] ?? '');
            $newPassword = $_POST['new_password'] ?? '';

            if (empty($name) || empty($phone)) {
                $error = 'Please fill in required fields.';
            } else {
                try {
                    if (!empty($newPassword) && strlen($newPassword) >= 6) {
                        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                        $stmt = $this->db->prepare("UPDATE customers SET name = ?, phone = ?, password = ? WHERE id = ?");
                        $stmt->execute([$name, $phone, $hashedPassword, $userId]);
                    } else {
                        $stmt = $this->db->prepare("UPDATE customers SET name = ?, phone = ? WHERE id = ?");
                        $stmt->execute([$name, $phone, $userId]);
                    }

                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_phone'] = $phone;
                    $success = true;
                    $user = $this->getUser($userId);
                } catch (\Exception $e) {
                    $error = 'Update failed. Please try again.';
                }
            }
        }

        include __DIR__ . '/../../../Views/pages/user_profile.php';
    }

    private function getUser($userId)
    {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }
}
?>
