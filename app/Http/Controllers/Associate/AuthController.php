<?php

namespace App\Http\Controllers\Associate;

use App\Http\Controllers\BaseController;

/**
 * AssociateAuthController
 * Handles associate registration
 */
class AuthController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Associate registration page
     */
    public function register()
    {
        if ($this->isLoggedIn()) {
            $this->redirect('/associate/dashboard');
            return;
        }

        @session_start();
        $csrf_token = $_SESSION['csrf_token'] ?? '';
        $errors = [];
        $old = $_SESSION['old_input'] ?? [];
        unset($_SESSION['old_input']);

        $flashError = $_SESSION['error'] ?? null;
        unset($_SESSION['error']);

        if ($flashError) {
            $errors[] = $flashError;
        }

        $this->render('auth/associate_register', [
            'page_title' => 'Associate Registration - APS Dream Home',
            'page_description' => 'Register as a Property Associate',
            'csrf_token' => $csrf_token,
            'errors' => $errors,
            'old' => $old,
        ], 'layouts/base');
    }

    /**
     * Store associate registration
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/associate/register');
            return;
        }

        $name = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $sponsorCode = trim($_POST['sponsor_code'] ?? '');
        $errors = [];

        // Validation
        if (empty($name)) $errors[] = 'Full name is required';
        if (empty($email)) $errors[] = 'Email is required';
        if (empty($phone) || !preg_match('/^[0-9]{10}$/', $phone)) $errors[] = 'Valid 10-digit phone number is required';
        if (empty($password)) $errors[] = 'Password is required';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match';

        if (!empty($errors)) {
            $_SESSION['error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            $this->redirect('/associate/register');
            return;
        }

        if (!$this->tenantEnforce('add_user')) {
            $_SESSION['error'] = $_SESSION['error'] ?? 'User limit reached for your plan';
            $this->redirect('/associate/register');
            return;
        }

        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $tid = TenantContext::getId();

            // Check if email already exists
            $tidSql = $tid > 1 ? " AND tenant_id = ?" : "";
            $params = [$email];
            if ($tid > 1) $params[] = $tid;
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?{$tidSql} LIMIT 1");
            $stmt->execute($params);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Email already registered';
                $this->redirect('/associate/register');
                return;
            }

            // Check if phone already exists
            $params = [$phone];
            if ($tid > 1) $params[] = $tid;
            $stmt = $db->prepare("SELECT id FROM users WHERE phone = ?{$tidSql} LIMIT 1");
            $stmt->execute($params);
            if ($stmt->fetch()) {
                $_SESSION['error'] = 'Phone number already registered';
                $this->redirect('/associate/register');
                return;
            }

            // Check sponsor
            $sponsorId = null;
            if (!empty($sponsorCode)) {
                $params = [$sponsorCode];
                if ($tid > 1) $params[] = $tid;
                $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ?{$tidSql} LIMIT 1");
                $stmt->execute($params);
                $sponsor = $stmt->fetch();
                if ($sponsor) $sponsorId = $sponsor['id'];
            }

            // Create user
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $referralCode = strtoupper(substr($name, 0, 3)) . date('ymd') . rand(100, 999);
            $cols = "name, email, phone, password, role, status, referral_code, referred_by, created_at, updated_at";
            $vals = "?, ?, ?, ?, 'associate', 'active', ?, ?, NOW(), NOW()";
            $uParams = [$name, $email, $phone, $passwordHash, $referralCode, $sponsorId];
            $insertExtra = $this->tenantInsertData();
            if (!empty($insertExtra)) { $cols .= ", tenant_id"; $vals .= ", ?"; $uParams[] = $insertExtra['tenant_id']; }
            $stmt = $db->prepare("INSERT INTO users ($cols) VALUES ($vals)");
            $stmt->execute($uParams);
            $userId = $db->lastInsertId();

            // Create associate record
            $assocCols = "user_id, sponsor_id, referral_code, status, joined_at, created_at, updated_at";
            $assocVals = "?, ?, ?, 'active', NOW(), NOW(), NOW()";
            $assocParams = [$userId, $sponsorId, $referralCode];
            if (!empty($insertExtra)) { $assocCols .= ", tenant_id"; $assocVals .= ", ?"; $assocParams[] = $insertExtra['tenant_id']; }
            $stmt = $db->prepare("INSERT INTO associates ($assocCols) VALUES ($assocVals)");
            $stmt->execute($assocParams);

            // Create MLM network entry
            $mlmParentId = $sponsorId ?? 1;
            $mlmCols = "associate_id, sponsor_id, parent_id, level, created_at";
            $mlmVals = "?, ?, ?, 1, NOW()";
            $mlmParams = [$userId, $sponsorId, $mlmParentId];
            if (!empty($insertExtra)) { $mlmCols .= ", tenant_id"; $mlmVals .= ", ?"; $mlmParams[] = $insertExtra['tenant_id']; }
            $stmt = $db->prepare("INSERT INTO mlm_network_tree ($mlmCols) VALUES ($mlmVals)");
            $stmt->execute($mlmParams);

            // Create wallet
            $walletCols = "user_id, balance, created_at, updated_at";
            $walletVals = "?, 0, NOW(), NOW()";
            $walletParams = [$userId];
            if (!empty($insertExtra)) { $walletCols .= ", tenant_id"; $walletVals .= ", ?"; $walletParams[] = $insertExtra['tenant_id']; }
            $stmt = $db->prepare("INSERT INTO wallet_points ($walletCols) VALUES ($walletVals)");
            $stmt->execute($walletParams);

            // Auto-login
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$userId;
            $_SESSION['role'] = 'associate';
            $_SESSION['tenant_id'] = $tid;
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $_SESSION['associate_id'] = $userId;

            $this->redirect('/associate/dashboard');
        } catch (\Throwable $e) {
            error_log('AssociateAuthController::store error: ' . $e->getMessage());
            $_SESSION['error'] = 'Registration failed: ' . $e->getMessage();
            $this->redirect('/associate/register');
        }
    }
}

