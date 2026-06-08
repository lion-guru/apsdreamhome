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
        $kycStatus = 'not_started';

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
            // KYC status
            try {
                $stmt = $this->db->prepare("SELECT status FROM kyc_requests WHERE user_id = ? ORDER BY id DESC LIMIT 1");
                $stmt->execute([$user['id']]);
                $kycRow = $stmt->fetch(\PDO::FETCH_ASSOC);
                $kycStatus = $kycRow['status'] ?? 'not_started';
            } catch (\Exception $e) {
                $kycStatus = 'not_started';
            }
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
            'kycStatus' => $kycStatus ?? 'not_started',
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
        $this->myBookings();
    }

    public function myBookings()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];

        $bookings = [];
        try {
            $bookings = $this->db->fetchAll("
                SELECT pb.*,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       c.name as colony_name,
                       (SELECT COALESCE(SUM(bps.paid_amount), 0) FROM booking_payment_schedules bps WHERE bps.booking_id = pb.id) as total_paid,
                       (SELECT COUNT(*) FROM booking_payment_schedules bps WHERE bps.booking_id = pb.id AND bps.status = 'overdue') as overdue_count,
                       (SELECT COUNT(*) FROM booking_payment_schedules bps WHERE bps.booking_id = pb.id AND bps.status = 'pending') as pending_count,
                       (SELECT COUNT(*) FROM booking_payment_schedules bps WHERE bps.booking_id = pb.id) as total_installments
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                WHERE pb.customer_id = ?
                ORDER BY pb.created_at DESC
            ", [$userId]);
        } catch (\Throwable $e) {
            error_log("UserController::myBookings - " . $e->getMessage());
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/bookings', [
            'page_title' => 'My Bookings - APS Dream Home',
            'user' => $user,
            'bookings' => $bookings,
        ]);
    }

    public function bookingDetail($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $bookingId = (int)$id;

        if ($bookingId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $booking = null;
        try {
            $booking = $this->db->fetch("
                SELECT pb.*,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       p.corner_plot, p.road_width_ft,
                       c.name as colony_name, c.contact_phone as colony_phone,
                       c.contact_email as colony_email,
                       d.name as district_name, s.name as state_name,
                       u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                LEFT JOIN states s ON d.state_id = s.id
                LEFT JOIN users u ON pb.customer_id = u.id
                WHERE pb.id = ? AND pb.customer_id = ?
            ", [$bookingId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingDetail - " . $e->getMessage());
        }

        if (!$booking) {
            $this->layout = 'layouts/customer';
            $this->render('pages/user/booking_detail', [
                'page_title' => 'Booking Not Found - APS Dream Home',
                'user' => $user,
                'booking' => null,
                'payments' => [],
                'receipts' => [],
                'documents' => [],
                'history' => [],
            ]);
            return;
        }

        $payments = [];
        try {
            $payments = $this->db->fetchAll("
                SELECT * FROM booking_payment_schedules
                WHERE booking_id = ?
                ORDER BY installment_no ASC
            ", [$bookingId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingDetail payments - " . $e->getMessage());
        }

        $receipts = [];
        try {
            $receipts = $this->db->fetchAll("
                SELECT * FROM booking_payment_receipts
                WHERE booking_id = ?
                ORDER BY receipt_date DESC
            ", [$bookingId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingDetail receipts - " . $e->getMessage());
        }

        $documents = [];
        try {
            $documents = $this->db->fetchAll("
                SELECT * FROM booking_documents
                WHERE booking_id = ?
                ORDER BY created_at DESC
            ", [$bookingId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingDetail documents - " . $e->getMessage());
        }

        $history = [];
        try {
            $history = $this->db->fetchAll("
                SELECT bsh.*, u.name as changed_by_name
                FROM booking_status_history bsh
                LEFT JOIN users u ON bsh.changed_by = u.id
                WHERE bsh.booking_id = ?
                ORDER BY bsh.created_at ASC
            ", [$bookingId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingDetail history - " . $e->getMessage());
        }

        $totalPaid = 0;
        foreach ($payments as $p) {
            $totalPaid += (float)($p['paid_amount'] ?? 0);
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/booking_detail', [
            'page_title' => 'Booking ' . htmlspecialchars($booking['booking_number'] ?? '') . ' - APS Dream Home',
            'user' => $user,
            'booking' => $booking,
            'payments' => $payments,
            'receipts' => $receipts,
            'documents' => $documents,
            'history' => $history,
            'total_paid' => $totalPaid,
        ]);
    }

    public function demandLetter($installmentId = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $instId = (int)$installmentId;

        if ($instId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $installment = null;
        $booking = null;
        $colony = null;
        $plot = null;

        try {
            $installment = $this->db->fetch("
                SELECT bps.*, pb.customer_id, pb.booking_number, pb.total_plot_value
                FROM booking_payment_schedules bps
                JOIN plot_bookings pb ON bps.booking_id = pb.id
                WHERE bps.id = ? AND pb.customer_id = ?
            ", [$instId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::demandLetter installment - " . $e->getMessage());
        }

        if (!$installment) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        try {
            $booking = $this->db->fetch("
                SELECT pb.*, u.name as customer_name, u.email as customer_email, u.phone as customer_phone
                FROM plot_bookings pb
                LEFT JOIN users u ON pb.customer_id = u.id
                WHERE pb.id = ?
            ", [$installment['booking_id']]);
        } catch (\Throwable $e) {
            error_log("UserController::demandLetter booking - " . $e->getMessage());
        }

        try {
            $plot = $this->db->fetch("SELECT * FROM plots WHERE id = ?", [$booking['plot_id'] ?? 0]);
        } catch (\Throwable $e) {
            error_log("UserController::demandLetter plot - " . $e->getMessage());
        }

        try {
            $colony = $this->db->fetch("SELECT c.*, d.name as district_name FROM colonies c LEFT JOIN districts d ON c.district_id = d.id WHERE c.id = ?", [$plot['colony_id'] ?? 0]);
        } catch (\Throwable $e) {
            error_log("UserController::demandLetter colony - " . $e->getMessage());
        }

        if (!$booking || !$colony || !$plot) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $html = \App\Services\PDFService::generateDemandLetter($booking, $installment, $colony, $plot);

        header('Content-Type: text/html; charset=utf-8');
        echo $html;
        exit;
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

    public function newBooking()
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();

        $colonies = [];
        try {
            $colonies = $this->db->fetchAll("
                SELECT c.id, c.name, c.slug, c.description, c.image_path,
                       c.total_plots, c.available_plots, c.starting_price, c.is_active,
                       d.name as district_name
                FROM colonies c
                LEFT JOIN districts d ON c.district_id = d.id
                WHERE c.is_active = 1 AND c.available_plots > 0
                ORDER BY c.name
            ");
        } catch (\Throwable $e) {
            error_log("UserController::newBooking colonies - " . $e->getMessage());
        }

        $selectedColony = (int)($_GET['colony_id'] ?? 0);
        $plots = [];
        if ($selectedColony > 0) {
            try {
                $plots = $this->db->fetchAll("
                    SELECT p.id, p.plot_number, p.block, p.area_sqft, p.total_price,
                           p.width_ft, p.length_ft, p.dimension_label, p.facing,
                           p.corner_plot, p.road_width_ft, c.name as colony_name
                    FROM plots p
                    JOIN colonies c ON p.colony_id = c.id
                    WHERE p.colony_id = ? AND p.status = 'available'
                    ORDER BY p.block, p.plot_number
                ", [$selectedColony]);
            } catch (\Throwable $e) {
                error_log("UserController::newBooking plots - " . $e->getMessage());
            }
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/new_booking', [
            'page_title' => 'Book a Plot - APS Dream Home',
            'user' => $user,
            'colonies' => $colonies,
            'plots' => $plots,
            'selected_colony' => $selectedColony,
        ]);
    }

    public function createBooking()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'error' => 'Please login to continue'], 401);
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token'], 403);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $plotId = (int)($_POST['plot_id'] ?? 0);

        if ($plotId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid plot selection'], 400);
            return;
        }

        try {
            $plot = $this->db->fetch(
                "SELECT p.*, c.name as colony_name FROM plots p JOIN colonies c ON p.colony_id = c.id WHERE p.id = ? AND p.status = 'available'",
                [$plotId]
            );
        } catch (\Throwable $e) {
            error_log("UserController::createBooking plot fetch - " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Unable to verify plot availability'], 500);
            return;
        }

        if (!$plot) {
            $this->json(['success' => false, 'error' => 'This plot is no longer available. Please select another.'], 400);
            return;
        }

        try {
            $existing = $this->db->fetch(
                "SELECT id FROM plot_bookings WHERE plot_id = ? AND status NOT IN ('cancelled')",
                [$plotId]
            );
        } catch (\Throwable $e) {
            error_log("UserController::createBooking existing check - " . $e->getMessage());
            $existing = null;
        }

        if ($existing) {
            $this->json(['success' => false, 'error' => 'This plot already has an active booking.'], 400);
            return;
        }

        $bookingNumber = 'APS-BK-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $totalAmount = (float)$plot['total_price'];
        $tokenAmount = 25000.00;

        try {
            $this->db->beginTransaction();

            $this->db->prepare("
                INSERT INTO plot_bookings
                    (plot_id, customer_id, booking_number, booking_date, total_plot_value,
                     booking_amount, agreement_value, status, channel, notes, created_at, updated_at)
                VALUES (?, ?, ?, CURDATE(), ?, ?, ?, 'token_paid', 'direct', ?, NOW(), NOW())
            ")->execute([
                $plotId,
                $userId,
                $bookingNumber,
                $totalAmount,
                $tokenAmount,
                $totalAmount,
                trim($_POST['notes'] ?? ''),
            ]);

            $bookingId = (int)$this->db->lastInsertId();

            $this->db->prepare("UPDATE plots SET status = 'booked', customer_id = ?, booking_date = CURDATE(), updated_at = NOW() WHERE id = ?")
                ->execute([$userId, $plotId]);

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                try { $this->db->rollBack(); } catch (\Throwable $e2) {}
            }
            error_log("UserController::createBooking insert - " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Booking failed. Please try again.'], 500);
            return;
        }

        // Send booking confirmation notifications (best-effort)
        try {
            $user = $this->getUser();
            $notifier = new \App\Services\BookingNotificationService();
            $colony = $this->db->fetch("SELECT * FROM colonies WHERE id = ?", [$plot['colony_id'] ?? 0]);
            $bookingData = [
                'booking_number' => $bookingNumber,
                'total_plot_value' => $totalAmount,
                'booking_amount' => $tokenAmount,
                'plot_number' => $plot['plot_number'],
                'colony_name' => $plot['colony_name'],
            ];
            $notifier->sendBookingConfirmation($bookingData, $user, $plot, $colony ?: ['name' => $plot['colony_name']]);
        } catch (\Throwable $e) {
            error_log("[UserController] Booking notification failed: " . $e->getMessage());
        }

        $this->json([
            'success' => true,
            'booking_id' => $bookingId,
            'booking_number' => $bookingNumber,
            'plot' => $plot['plot_number'],
            'colony' => $plot['colony_name'],
            'total_amount' => $totalAmount,
            'token_amount' => $tokenAmount,
        ]);
    }

    public function bookingConfirmation($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $bookingId = (int)$id;

        if ($bookingId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $booking = null;
        try {
            $booking = $this->db->fetch("
                SELECT pb.*,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       p.corner_plot, p.road_width_ft,
                       c.name as colony_name, d.name as district_name
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                WHERE pb.id = ? AND pb.customer_id = ?
            ", [$bookingId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::bookingConfirmation fetch - " . $e->getMessage());
        }

        if (!$booking) {
            $this->layout = 'layouts/customer';
            $this->render('pages/user/booking_confirmation', [
                'page_title' => 'Booking Not Found - APS Dream Home',
                'user' => $user,
                'booking' => null,
            ]);
            return;
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/booking_confirmation', [
            'page_title' => 'Booking Confirmed - APS Dream Home',
            'user' => $user,
            'booking' => $booking,
        ]);
    }

    /* ------------------------------------------------------------------ *
     *  TOKEN PAYMENT FLOW (Razorpay)
     * ------------------------------------------------------------------ */

    public function payToken($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $bookingId = (int)$id;

        if ($bookingId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $booking = $this->fetchBookingWithPlot($bookingId, $userId);
        if (!$booking) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        // Already paid token — redirect to detail
        if (($booking['status'] ?? '') !== 'token_paid') {
            header('Location: ' . BASE_URL . '/user/bookings/' . $bookingId);
            exit;
        }

        // Create Razorpay order for the token amount
        $tokenAmount = (float)($booking['booking_amount'] ?? 25000);
        $razorpaySvc = new \App\Services\Gateway\RazorpayService();
        $orderResp = $razorpaySvc->createOrder(
            $tokenAmount,
            'INR',
            'TOKEN_' . $booking['booking_number'],
            [
                'booking_id'    => $bookingId,
                'user_id'       => $userId,
                'customer_name' => $user['name'] ?? '',
                'customer_email'=> $user['email'] ?? '',
                'customer_phone'=> $user['phone'] ?? '',
                'description'   => 'Token payment for Plot ' . ($booking['plot_number'] ?? '') . ' — ' . ($booking['colony_name'] ?? ''),
            ]
        );

        $orderId = null;
        if ($orderResp['success'] && isset($orderResp['data']['id'])) {
            $orderId = $orderResp['data']['id'];
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/pay_token', [
            'page_title'   => 'Pay Token Amount — APS Dream Home',
            'user'         => $user,
            'booking'      => $booking,
            'token_amount' => $tokenAmount,
            'order_id'     => $orderId,
            'razorpay'     => [
                'key_id'  => $razorpaySvc->getKeyId(),
                'test'    => $razorpaySvc->isTestMode() || !$razorpaySvc->isConfigured(),
            ],
        ]);
    }

    public function processTokenPayment($id = null)
    {
        @session_start();

        if (!isset($_SESSION['user_id'])) {
            $this->json(['success' => false, 'error' => 'Please log in.'], 401);
            return;
        }

        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!$this->validateCsrfToken($token)) {
            $this->json(['success' => false, 'error' => 'Invalid CSRF token.'], 403);
            return;
        }

        $userId = (int)$_SESSION['user_id'];
        $bookingId = (int)$id;

        if ($bookingId <= 0) {
            $this->json(['success' => false, 'error' => 'Invalid booking.'], 400);
            return;
        }

        $razorpay_order_id   = $_POST['razorpay_order_id']   ?? '';
        $razorpay_payment_id  = $_POST['razorpay_payment_id']  ?? '';
        $razorpay_signature   = $_POST['razorpay_signature']   ?? '';

        if (!$razorpay_order_id || !$razorpay_payment_id || !$razorpay_signature) {
            $this->json(['success' => false, 'error' => 'Missing payment response data.'], 400);
            return;
        }

        $booking = $this->fetchBookingWithPlot($bookingId, $userId);
        if (!$booking) {
            $this->json(['success' => false, 'error' => 'Booking not found.'], 404);
            return;
        }

        if (($booking['status'] ?? '') !== 'token_paid') {
            $this->json(['success' => false, 'error' => 'Token already paid or booking in unexpected state.'], 400);
            return;
        }

        // Verify HMAC-SHA256 signature
        $razorpaySvc = new \App\Services\Gateway\RazorpayService();
        if (!$razorpaySvc->verifyPaymentSignature($razorpay_order_id, $razorpay_payment_id, $razorpay_signature)) {
            error_log("UserController::processTokenPayment signature mismatch booking #{$bookingId}");
            $this->json(['success' => false, 'error' => 'Payment signature verification failed.'], 400);
            return;
        }

        $tokenAmount = (float)($booking['booking_amount'] ?? 25000);

        try {
            $this->db->beginTransaction();

            // Advance booking status
            $this->db->prepare("
                UPDATE plot_bookings
                SET status = 'agreement_signed', updated_at = NOW()
                WHERE id = ? AND customer_id = ? AND status = 'token_paid'
            ")->execute([$bookingId, $userId]);

            // Log payment transaction
            $this->db->prepare("
                INSERT INTO payment_transactions
                    (transaction_id, user_id, booking_id, amount, currency, payment_method,
                     payment_status, gateway_transaction_id, gateway_response, created_at, updated_at)
                VALUES (?, ?, ?, ?, 'INR', 'razorpay', 'completed', ?, ?, NOW(), NOW())
            ")->execute([
                'APS-RCP-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6),
                $userId,
                $bookingId,
                $tokenAmount,
                $razorpay_payment_id,
                json_encode([
                    'order_id'   => $razorpay_order_id,
                    'payment_id' => $razorpay_payment_id,
                    'signature'  => $razorpay_signature,
                ]),
            ]);

            // Update payment_orders record (set paid status)
            try {
                $this->db->prepare("
                    UPDATE payment_orders
                    SET status = 'paid', payment_id = ?, signature = ?, paid_at = NOW()
                    WHERE order_id = ?
                ")->execute([$razorpay_payment_id, $razorpay_signature, $razorpay_order_id]);
            } catch (\Throwable $e) {
                error_log("UserController::processTokenPayment order update: " . $e->getMessage());
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            if ($this->db->inTransaction()) {
                try { $this->db->rollBack(); } catch (\Throwable $e2) {}
            }
            error_log("UserController::processTokenPayment db error booking #{$bookingId}: " . $e->getMessage());
            $this->json(['success' => false, 'error' => 'Payment processing failed. Please contact support.'], 500);
            return;
        }

        // Send payment receipt notification (best-effort)
        try {
            $user = $this->getUser();
            $notifier = new \App\Services\BookingNotificationService();
            $notifier->sendPaymentReceipt($booking, $user, $tokenAmount, $razorpay_payment_id);
        } catch (\Throwable $e) {
            error_log("[UserController] Payment notification failed: " . $e->getMessage());
        }

        $this->json([
            'success'  => true,
            'redirect' => BASE_URL . '/user/bookings/' . $bookingId . '/payment-success',
        ]);
    }

    public function paymentSuccess($id = null)
    {
        $this->requireCustomerLogin();
        $user = $this->getUser();
        $userId = (int)$user['id'];
        $bookingId = (int)$id;

        if ($bookingId <= 0) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        $booking = $this->fetchBookingWithPlot($bookingId, $userId);
        if (!$booking) {
            header('Location: ' . BASE_URL . '/user/bookings');
            exit;
        }

        // Fetch the latest payment transaction for this booking
        $payment = null;
        try {
            $payment = $this->db->fetch("
                SELECT * FROM payment_transactions
                WHERE booking_id = ? AND user_id = ? AND payment_status = 'completed'
                ORDER BY created_at DESC LIMIT 1
            ", [$bookingId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::paymentSuccess fetch payment: " . $e->getMessage());
        }

        $this->layout = 'layouts/customer';
        $this->render('pages/user/payment_success', [
            'page_title' => 'Payment Successful — APS Dream Home',
            'user'       => $user,
            'booking'    => $booking,
            'payment'    => $payment,
            'token_amount' => (float)($booking['booking_amount'] ?? 25000),
        ]);
    }

    /**
     * Shared helper: fetch booking + plot + colony with ownership check.
     */
    private function fetchBookingWithPlot(int $bookingId, int $userId): ?array
    {
        try {
            return $this->db->fetch("
                SELECT pb.*,
                       p.plot_number, p.block, p.area_sqft, p.total_price as plot_price,
                       p.width_ft, p.length_ft, p.dimension_label, p.facing,
                       p.corner_plot, p.road_width_ft,
                       c.name as colony_name, d.name as district_name
                FROM plot_bookings pb
                LEFT JOIN plots p ON pb.plot_id = p.id
                LEFT JOIN colonies c ON p.colony_id = c.id
                LEFT JOIN districts d ON c.district_id = d.id
                WHERE pb.id = ? AND pb.customer_id = ?
            ", [$bookingId, $userId]);
        } catch (\Throwable $e) {
            error_log("UserController::fetchBookingWithPlot - " . $e->getMessage());
            return null;
        }
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
