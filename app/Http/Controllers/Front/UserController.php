<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\BaseController;
use App\Services\Communication\NotificationService;

class UserController extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    private function requireCustomerLogin()
    {
        @session_start();

        // Check if user is logged in (user_id exists and user_type is customer or empty)
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }

        // Accept customer type OR default to customer if not specified
        $userType = $_SESSION['role'] ?? '';
        if ($userType !== '' && $userType !== 'customer') {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/login');
            exit;
        }
    }

    private function getUser()
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$user) {
            header('Location: ' . (defined('BASE_URL') ? BASE_URL : '') . '/user/logout');
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

        // ── Hot-path cache: saved-searches count for this user (30 sec TTL) ──
        $savedSearchesCount = \App\Services\Cache\HotPathCacheService::getUserSavedSearchesCount(
            (int) $user['id'],
            function () use ($user) {
                try {
                    $stmt = $this->db->prepare("SELECT COUNT(*) AS cnt FROM saved_searches WHERE user_id = ?");
                    $stmt->execute([(int) $user['id']]);
                    $row = $stmt->fetch(\PDO::FETCH_ASSOC);
                    return (int) ($row['cnt'] ?? 0);
                } catch (\Throwable $e) {
                    error_log("UserController::dashboard - savedSearchesCount: " . $e->getMessage());
                    return 0;
                }
            }
        );

        // Fetch bookings (purchased plots/properties)
        $bookings = $this->db->fetchAll("
            SELECT b.*, 
                   COALESCE(p.plot_number, p2.plot_number) as plot_number,
                   COALESCE(p.block, p2.block) as block,
                   COALESCE(p.area_sqft, p2.area_sqft) as area_sqft,
                   COALESCE(p.total_price, p2.total_price) as plot_price,
                   COALESCE(p.status, p2.status) as plot_status,
                   COALESCE(c.name, c2.name) as colony_name
            FROM bookings b
            LEFT JOIN plots p ON b.property_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN plots p2 ON b.plot_id = p2.id
            LEFT JOIN colonies c2 ON p2.colony_id = c2.id
            WHERE b.user_id = ? OR b.customer_id = ?
            ORDER BY b.created_at DESC
        ", [$user['id'], $user['id']]);

        // Recent payments
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT * FROM payment_transactions WHERE user_id = ? AND user_type = 'customer' ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$_SESSION['user_id'] ?? 0]);
            $recentPayments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $recentPayments = [];
        }

        // User documents (KYC)
        try {
            $stmt = $this->db->prepare("SELECT * FROM documents WHERE entity_type = 'user' AND entity_id = ? ORDER BY uploaded_on DESC");
            $stmt->execute([$user['id']]);
            $userDocuments = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            $userDocuments = [];
        }

        // Support tickets count
        try {
            $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM support_tickets WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $ticketStats = $stmt->fetch(\PDO::FETCH_ASSOC);
            $totalTickets = (int)($ticketStats['cnt'] ?? 0);

            $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM support_tickets WHERE user_id = ? AND status NOT IN ('resolved','closed')");
            $stmt->execute([$user['id']]);
            $openTicketsRow = $stmt->fetch(\PDO::FETCH_ASSOC);
            $openTickets = (int)($openTicketsRow['cnt'] ?? 0);
        } catch (\Exception $e) {
            $totalTickets = 0;
            $openTickets = 0;
        }

        $notifService = new NotificationService();
        $unreadNotifCount = $notifService->getUnreadCount($user['id']);

        $referralCode = $user['referral_code'] ?? '';
        $referralCount = 0;
        $referralEarnings = 0;
        $referralLink = $referralCode ? (defined('BASE_URL') ? BASE_URL : '') . '/register?ref=' . $referralCode : '';
        $twoFactorEnabled = false;
        $savedCount = 0;

        try {
            $stmt = $this->db->prepare("SELECT direct_referrals FROM mlm_profiles WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $profile = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($profile) {
                $referralCount = (int)($profile['direct_referrals'] ?? 0);
            }
            $stmt = $this->db->prepare("SELECT referral_earnings FROM wallet_points WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $wallet = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($wallet) {
                $referralEarnings = (float)($wallet['referral_earnings'] ?? 0);
            }
            // 2FA status
            $stmt = $this->db->prepare("SELECT two_factor_enabled FROM users WHERE id = ?");
            $stmt->execute([$user['id']]);
            $tf = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($tf) {
                $twoFactorEnabled = !empty($tf['two_factor_enabled']);
            }
            // Saved searches count
            $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM saved_searches WHERE user_id = ?");
            $stmt->execute([$user['id']]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $savedCount = (int)($row['cnt'] ?? 0);
        } catch (\Exception $e) {
            error_log("UserController.php: " . $e->getMessage());
        }

        $data = [
            'page_title' => 'My Dashboard - APS Dream Home',
            'page_description' => 'Manage your properties and inquiries',
            'user' => $user,
            'properties' => $properties,
            'inquiries' => $inquiries,
            'bookings' => $bookings,
            'userDocuments' => $userDocuments,
            'stats' => [
                'total_properties' => count($properties),
                'active_inquiries' => count(array_filter($inquiries, fn($i) => ($i['status'] ?? '') !== 'closed')),
                'total_bookings' => count($bookings),
                'total_inquiries' => count($inquiries),
                'total_tickets' => $totalTickets,
                'open_tickets' => $openTickets,
            ],
            'recentPayments' => $recentPayments,
            'registered' => isset($_GET['registered']),
            'loginSuccess' => isset($_GET['login']),
            'referral_code' => $referralCode,
            'referral_link' => $referralLink,
            'referral_count' => $referralCount,
            'referral_earnings' => $referralEarnings,
            'unread_notifications' => $unreadNotifCount,
            'investor_stats' => $this->safeInvestorStats((int)$user['id']),
            'twoFactorEnabled' => $twoFactorEnabled,
            'savedCount' => $savedCount,
        ];

        $this->layout = 'layouts/customer';
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

        $this->layout = 'layouts/customer';
        $this->render('pages/user_properties', $data);
    }

    public function userBookings()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $bookings = $this->db->fetchAll("
            SELECT b.*, 
                   COALESCE(p.plot_number, p2.plot_number) as plot_number,
                   COALESCE(p.block, p2.block) as block,
                   COALESCE(p.area_sqft, p2.area_sqft) as area_sqft,
                   COALESCE(p.total_price, p2.total_price) as plot_price,
                   COALESCE(c.name, c2.name) as colony_name
            FROM bookings b
            LEFT JOIN plots p ON b.property_id = p.id
            LEFT JOIN colonies c ON p.colony_id = c.id
            LEFT JOIN plots p2 ON b.plot_id = p2.id
            LEFT JOIN colonies c2 ON p2.colony_id = c2.id
            WHERE b.customer_id = ?
            ORDER BY b.created_at DESC
        ", [$user['id']]);

        $this->layout = 'layouts/customer';
        $this->render('pages/user_bookings', [
            'page_title' => 'My Bookings - APS Dream Home',
            'user' => $user,
            'bookings' => $bookings,
        ]);
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

        $this->layout = 'layouts/customer';
        $this->render('pages/user_inquiries', $data);
    }

    public function myTickets()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        try {
            $stmt = $this->db->prepare("
                SELECT t.*, 
                       (SELECT message FROM support_ticket_replies WHERE ticket_id = t.id ORDER BY created_at DESC LIMIT 1) as last_reply,
                       (SELECT created_at FROM support_ticket_replies WHERE ticket_id = t.id ORDER BY created_at DESC LIMIT 1) as last_reply_at
                FROM support_tickets t 
                WHERE t.user_id = ? 
                ORDER BY t.created_at DESC
            ");
            $stmt->execute([$user['id']]);
            $tickets = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Fetch replies for each ticket
            foreach ($tickets as &$ticket) {
                $stmt = $this->db->prepare("SELECT r.*, u.name as user_name FROM support_ticket_replies r LEFT JOIN users u ON r.user_id = u.id WHERE r.ticket_id = ? ORDER BY r.created_at ASC");
                $stmt->execute([$ticket['id']]);
                $ticket['replies'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            unset($ticket);

            // Fetch bookings for dropdown
            $bookings = $this->db->fetchAll("
                SELECT b.id, b.booking_number, COALESCE(p.plot_number, p2.plot_number) as plot_number,
                       COALESCE(c.name, c2.name) as colony_name, b.status
                FROM bookings b
                LEFT JOIN plots p ON b.property_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN plots p2 ON b.plot_id = p2.id
                LEFT JOIN colonies c2 ON p2.colony_id = c2.id
                WHERE (b.user_id = ? OR b.customer_id = ?) AND b.status NOT IN ('cancelled')
                ORDER BY b.created_at DESC
            ", [$user['id'], $user['id']]);
        } catch (\Exception $e) {
            $tickets = [];
            $bookings = [];
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user_tickets', [
            'page_title' => 'My Support Tickets - APS Dream Home',
            'user' => $user,
            'tickets' => $tickets,
            'bookings' => $bookings,
        ]);
    }

    public function createTicket()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $subject = trim($_POST['subject'] ?? '');
        $priority = trim($_POST['priority'] ?? 'medium');
        $message = trim($_POST['message'] ?? '');
        $bookingId = !empty($_POST['booking_id']) ? (int)$_POST['booking_id'] : null;

        if (empty($subject) || empty($message)) {
            $_SESSION['flash_error'] = 'Subject and message are required.';
            header('Location: ' . BASE_URL . '/user/tickets');
            exit;
        }

        if (!in_array($priority, ['low', 'medium', 'high'])) {
            $priority = 'medium';
        }

        try {
            $stmt = $this->db->prepare("INSERT INTO support_tickets (user_id, subject, message, priority, booking_id, status, category, created_at) VALUES (?, ?, ?, ?, ?, 'open', 'general', NOW())");
            $stmt->execute([$user['id'], $subject, $message, $priority, $bookingId]);
            $ticketId = $this->db->lastInsertId();

            // Add initial message as reply
            $stmt = $this->db->prepare("INSERT INTO support_ticket_replies (ticket_id, user_id, message, is_admin) VALUES (?, ?, ?, 0)");
            $stmt->execute([$ticketId, $user['id'], $message]);

            $_SESSION['flash_success'] = 'Support ticket created successfully!';
        } catch (\Exception $e) {
            $_SESSION['flash_error'] = 'Failed to create ticket. Please try again.';
        }

        header('Location: ' . BASE_URL . '/user/tickets');
        exit;
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

        // Define BASE_PATH for shared view
        if (!defined('BASE_PATH')) {
            define('BASE_PATH', dirname(__DIR__, 3));
        }

        // Set variables for shared view
        $userRole = $user['role'] ?? 'customer';
        $profileUrl = BASE_URL . '/user/profile';
        $securityUrl = null; // Front users don't have security page yet
        $canEdit = true;

        // Use customer profile view with customer layout
        $this->layout = 'layouts/customer';
        
        $this->render('pages/user_profile', [
            'user' => $user,
            'error' => $error,
            'success' => $success,
            'current_page' => 'profile'
        ]);
    }

    /**
     * Bank Details Page
     */
    public function bankDetails()
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Please login first';
            header('Location: ' . BASE_URL . '/login?redirect=/user/bank-details');
            exit;
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user_bank_details', []);
    }

    /**
     * My Address Page
     */
    public function address()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Please login first';
            header('Location: ' . BASE_URL . '/login?redirect=/user/address');
            exit;
        }
        $this->layout = 'layouts/customer';
        $this->render('pages/user/address', ['page_title' => 'My Address - APS Dream Home', 'current_page' => 'address']);
    }

    /**
     * Insurance Page
     */
    public function insurance()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Please login first';
            header('Location: ' . BASE_URL . '/login?redirect=/user/insurance');
            exit;
        }
        $this->layout = 'layouts/customer';
        $this->render('pages/user/insurance', ['page_title' => 'Insurance - APS Dream Home', 'current_page' => 'insurance']);
    }

    /**
     * Investment Plans Page
     */
    public function investmentPlans()
    {
        @session_start();
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['flash_error'] = 'Please login first';
            header('Location: ' . BASE_URL . '/login?redirect=/user/investment-plans');
            exit;
        }
        $this->layout = 'layouts/customer';
        $this->render('pages/user/investment_plans', ['page_title' => 'Investment Plans - APS Dream Home', 'current_page' => 'investment']);
    }

    /**
     * Save Bank Details
     */
    public function saveBankDetails()
    {
        @session_start();

        if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $accountHolder = trim($_POST['account_holder'] ?? '');
        $accountNumber = trim($_POST['account_number'] ?? '');
        $ifscCode = trim(strtoupper($_POST['ifsc_code'] ?? ''));
        $bankName = trim($_POST['bank_name'] ?? '');
        $branchName = trim($_POST['branch_name'] ?? '');
        $accountType = $_POST['account_type'] ?? 'savings';
        $upiId = trim($_POST['upi_id'] ?? '');

        // Validation
        if (empty($accountHolder) || empty($accountNumber) || empty($ifscCode)) {
            $_SESSION['flash_error'] = 'Please fill all required fields';
            header('Location: ' . BASE_URL . '/user/bank-details');
            exit;
        }

        // Check if account already exists
        $existing = $this->db->fetch(
            "SELECT id FROM user_bank_accounts WHERE user_id = ?",
            [$userId]
        );

        if ($existing) {
            // Update existing
            $stmt = $this->db->prepare("
                UPDATE user_bank_accounts 
                SET account_holder = ?, account_number = ?, ifsc_code = ?, 
                    bank_name = ?, branch_name = ?, account_type = ?, upi_id = ?
                WHERE user_id = ? AND is_primary = 1
            ");
            $stmt->execute([$accountHolder, $accountNumber, $ifscCode, $bankName, $branchName, $accountType, $upiId, $userId]);
        } else {
            // Insert new
            $stmt = $this->db->prepare("
                INSERT INTO user_bank_accounts 
                (user_id, account_holder, account_number, ifsc_code, bank_name, branch_name, account_type, upi_id, is_primary)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1)
            ");
            $stmt->execute([$userId, $accountHolder, $accountNumber, $ifscCode, $bankName, $branchName, $accountType, $upiId]);
        }

        $_SESSION['flash_success'] = 'Bank details saved successfully!';
        header('Location: ' . BASE_URL . '/user/bank-details');
        exit;
    }

    public function bookSiteVisit()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        try {
            $db = \App\Core\Database\Database::getInstance()->getConnection();
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $db->prepare("INSERT INTO site_visit_requests (user_id, user_name, user_email, user_phone, property_id, preferred_date, preferred_time, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
                $stmt->execute([
                    $_SESSION['user_id'],
                    $_SESSION['user_name'] ?? '',
                    $_SESSION['user_email'] ?? '',
                    $_POST['phone'] ?? $_SESSION['user_phone'] ?? '',
                    (int)($_POST['property_id'] ?? 0),
                    $_POST['visit_date'] ?? '',
                    $_POST['visit_time'] ?? '',
                    $_POST['notes'] ?? '',
                ]);
                $message = 'Your site visit request has been submitted. Our team will contact you within 24 hours.';
                $message_type = 'success';
            }
            
            // Get user's properties for dropdown
            $props = $db->prepare("SELECT id, CONCAT(property_type, ' - ', address) as name FROM user_properties WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
            $props->execute([$_SESSION['user_id']]);
            $properties = $props->fetchAll(\PDO::FETCH_ASSOC);
            
        } catch (\Exception $e) {
            $message = $message ?? 'An error occurred. Please try again.';
            $message_type = $message_type ?? 'danger';
            $properties = $properties ?? [];
        }
        
        $this->render('pages/user_book_site_visit', [
            'page_title' => 'Book Site Visit',
            'message' => $message ?? '',
            'message_type' => $message_type ?? '',
            'properties' => $properties ?? [],
        ]);
    }

    public function notificationSettings()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        try {
            $stmt = $this->db->prepare("SELECT * FROM user_notification_preferences WHERE user_id = ?");
        } catch (\Throwable $e) {
            // Gracefully handle dropped table ref
        }
        $stmt->execute([$user['id']]);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $prefs = [];
        foreach ($rows as $row) {
            $type = $row['notification_type'];
            $prefs[$type] = [
                'email' => (bool)($row['email_enabled'] ?? true),
                'sms' => (bool)($row['sms_enabled'] ?? false),
                'whatsapp' => (bool)($row['whatsapp_enabled'] ?? false),
                'push' => (bool)($row['push_enabled'] ?? true),
                'in_app' => true,
                'frequency' => $row['frequency'] ?? 'immediate',
            ];
        }

        $flash_error = $_SESSION['flash_error'] ?? '';
        $flash_success = $_SESSION['flash_success'] ?? '';
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $this->layout = 'layouts/customer';
        $this->render('pages/user_notification_settings', [
            'page_title' => 'Notification Settings - APS Dream Home',
            'user' => $user,
            'prefs' => $prefs,
            'flash_error' => $flash_error,
            'flash_success' => $flash_success,
        ]);
    }

    public function updateNotificationSettings()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $channels = $_POST['channels'] ?? [];
        $types = ['booking', 'payment', 'agreement', 'registry', 'possession', 'marketing'];

        foreach ($types as $type) {
            $typeChannels = $channels[$type] ?? [];
            $email = in_array('email', $typeChannels) ? 1 : 0;
            $sms = in_array('sms', $typeChannels) ? 1 : 0;
            $whatsapp = in_array('whatsapp', $typeChannels) ? 1 : 0;
            $push = in_array('push', $typeChannels) ? 1 : 0;

            try {
                $stmt = $this->db->prepare("
                    INSERT INTO user_notification_preferences 
                    (user_id, user_type, notification_type, email_enabled, sms_enabled, whatsapp_enabled, push_enabled, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                    ON DUPLICATE KEY UPDATE
                    email_enabled = VALUES(email_enabled),
                    sms_enabled = VALUES(sms_enabled),
                    whatsapp_enabled = VALUES(whatsapp_enabled),
                    push_enabled = VALUES(push_enabled),
                    updated_at = NOW()
                ");
            } catch (\Throwable $e) {
                // Gracefully handle dropped table ref
            }
            $stmt->execute([
                $user['id'],
                $user['role'] ?? 'customer',
                $type,
                $email,
                $sms,
                $whatsapp,
                $push,
            ]);
        }

        $_SESSION['flash_success'] = 'Notification preferences updated successfully!';
        header('Location: ' . BASE_URL . '/user/notification-settings');
        exit;
    }

    public function updateProfile()
    {
        $this->profile();
    }

    /**
     * User network page
     */
    public function network()
    {
        $this->requireLogin();
        $this->render('pages/user_network', ['page_title' => 'My Network']);
    }

    public function notifications()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $notifService = new NotificationService();
        $notifications = $notifService->getCustomerNotifications($user['id']);
        $unreadCount = $notifService->getUnreadCount($user['id']);

        $this->layout = 'layouts/customer';
        $this->render('pages/user_notifications', [
            'page_title' => 'Notifications - APS Dream Home',
            'user' => $user,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
        ]);
    }

    public function markNotificationRead($notificationId)
    {
        $this->requireCustomerLogin();

        $notifService = new NotificationService();
        $notifService->markAsRead($notificationId);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function markAllNotificationsRead()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $notifService = new NotificationService();
        $notifService->markAllAsRead($user['id']);

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    public function apiUnreadCount()
    {
        $count = 0;
        if (!empty($_SESSION['user_id'])) {
            try {
                $notifService = new NotificationService();
                $count = $notifService->getUnreadCount($_SESSION['user_id']);
            } catch (\Exception $e) {
                error_log("UserController::apiUnreadCount: " . $e->getMessage());
            }
        }
        header('Content-Type: application/json');
        echo json_encode(['count' => $count]);
        exit;
    }

    public function favorites()
    {
        $this->requireCustomerLogin();
        $userId = $_SESSION['user_id'];
        try {
            $favorites = $this->db->fetchAll(
                "SELECT p.*, f.created_at as favorited_at
                 FROM favorites f
                 JOIN user_properties p ON f.property_id = p.id
                 WHERE f.user_id = ?
                 ORDER BY f.created_at DESC",
                [$userId]
            );
        } catch (\Exception $e) {
            $favorites = [];
        }
        $this->render('pages/user_favorites', [
            'page_title' => 'My Favorites',
            'favorites' => $favorites
        ]);
    }

    public function savedSearches()
    {
        // Redirect to the new SavedSearchController which has a richer UI
        $controller = new \App\Http\Controllers\Front\SavedSearchController();
        $controller->index();
    }

    public function saveSearch()
    {
        // Redirect legacy calls to the new controller
        $controller = new \App\Http\Controllers\Front\SavedSearchController();
        $controller->store();
    }

    public function deleteSavedSearch($id)
    {
        // Redirect legacy calls to the new controller
        $controller = new \App\Http\Controllers\Front\SavedSearchController();
        $controller->destroy($id);
    }

    private function safeInvestorStats(int $userId): array
    {
        try {
            return \App\Services\CacheService::getGamification(
                'customer_investor',
                $userId,
                0,
                function () use ($userId) {
                    $svc = new \App\Services\InvestmentService();
                    return $svc->getStats($userId);
                }
            );
        } catch (\Throwable $e) {
            error_log('Investor stats error: ' . $e->getMessage());
            return ['level' => 'Bronze', 'next_level' => 'Silver', 'progress_pct' => 0, 'next_threshold' => 50000, 'total_invested' => 0, 'total' => 0, 'active' => 0, 'principal' => 0, 'current_value' => 0, 'returns' => 0];
        }
    }
}
