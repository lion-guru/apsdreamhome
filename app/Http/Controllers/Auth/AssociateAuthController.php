<?php
/**
 * Associate Authentication Controller
 */

namespace App\Http\Controllers\Auth;

require_once __DIR__ . '/../BaseController.php';

use App\Http\Controllers\BaseController;
use App\Core\Database\Database;

class AssociateAuthController extends BaseController
{
    public function associateRegister()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $csrf_token = $this->getCsrfToken();
        $errors = $_SESSION['errors'] ?? [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['errors'], $_SESSION['old_input']);
        $base = BASE_URL;
        extract(compact('csrf_token', 'errors', 'old'));
        include __DIR__ . '/../../../views/auth/associate_register.php';
    }

    public function handleAssociateRegister()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $referral = trim($_POST['referral_code'] ?? '');

        $errors = [];
        if (empty($name)) $errors[] = "Name is required";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = "Valid 10-digit phone required";
        if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
        if ($password !== $confirm) $errors[] = "Passwords do not match";

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . BASE_URL . '/associate/register');
            exit;
        }

        try {
            $db = Database::getInstance();
            $exists = $db->fetchOne("SELECT id FROM users WHERE email = ? LIMIT 1", [$email]);
            if ($exists) {
                $_SESSION['errors'] = ["Email already registered"];
                header('Location: ' . BASE_URL . '/associate/register');
                exit;
            }

            $referrer_id = null;
            if (!empty($referral)) {
                $ref = $db->fetchOne("SELECT id FROM users WHERE referral_code = ? LIMIT 1", [$referral]);
                if ($ref) $referrer_id = $ref['id'];
            }

            $associate_id = 'ASC' . date('Y') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $referral_code = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $db->insert('users', [
                'customer_id' => $associate_id, 'name' => $name, 'email' => $email,
                'phone' => $phone, 'password' => $hashed, 'referral_code' => $referral_code,
                'referred_by' => $referrer_id, 'user_type' => 'associate', 'role' => 'associate',
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'), 'updated_at' => date('Y-m-d H:i:s')
            ]);

            $_SESSION['success'] = "Associate registration successful! ID: $associate_id. Please login.";
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        } catch (\Exception $e) {
            error_log("Associate registration error: " . $e->getMessage());
            $_SESSION['errors'] = ["Registration failed: " . $e->getMessage()];
            header('Location: ' . BASE_URL . '/associate/register');
            exit;
        }
    }

    public function associateLogin()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (isset($_SESSION['user_id']) && ($_SESSION['user_type'] ?? '') === 'associate') {
            header('Location: ' . BASE_URL . '/associate/dashboard');
            exit;
        }
        $csrf_token = $this->getCsrfToken();
        $error = $_SESSION['errors'][0] ?? $_SESSION['error'] ?? null;
        $success = $_SESSION['success'] ?? null;
        unset($_SESSION['errors'], $_SESSION['error'], $_SESSION['success']);
        $base = BASE_URL;
        extract(compact('csrf_token', 'error', 'success'));
        include __DIR__ . '/../../../views/auth/associate_login.php';
    }

    public function authenticateAssociate()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $_SESSION['errors'] = ["Email and password are required"];
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        }

        try {
            $db = Database::getInstance();
            $user = $db->fetchOne("SELECT * FROM users WHERE (email = ? OR phone = ?) AND user_type = 'associate' LIMIT 1", [$email, $email]);
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['associate_id'] = $user['customer_id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_type'] = 'associate';
                $_SESSION['logged_in'] = true;
                header('Location: ' . BASE_URL . '/associate/dashboard');
                exit;
            }
            $_SESSION['errors'] = ["Invalid email or password"];
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        } catch (\Exception $e) {
            $_SESSION['errors'] = ["Login failed"];
            header('Location: ' . BASE_URL . '/associate/login');
            exit;
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_destroy();
        header('Location: ' . BASE_URL . '/associate/login');
        exit;
    }
}
